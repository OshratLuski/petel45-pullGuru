<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * This file defines the quiz diagnostic stats report class.
 *
 * @package   quiz_diagnosticstats
 * @copyright 2024 Oshrat Luski <oshrat.luski@weizmann.ac.il>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();
define('QUESTION_PREVIEW_MAX_VARIANTS', 100);

use core\output\html_writer;
use mod_quiz\quiz_settings;

require_once($CFG->dirroot . '/question/engine/bank.php');
require_once($CFG->libdir . '/questionlib.php');

class quiz_diagnosticstats_report extends mod_quiz\local\reports\report_base{

    /** @var stdClass the course settings. */
    protected $course;

    /** @var stdClass the course_module settings. */
    protected $cm;

    /** @var stdClass the quiz settings. */
    protected $quiz;

    /** @var context the quiz context. */
    protected $context;

    /**
     * Displays the diagnostic stats report for the quiz.
     *
     * @param stdClass $quiz The quiz object.
     * @param stdClass $cm The course module object.
     * @param stdClass $course The course object.
     * @return void
     */
    public function display($quiz, $cm, $course) {
        global $OUTPUT, $PAGE;

        $PAGE->add_body_class('diagnosticstats-page');
        $PAGE->requires->js_call_amd('quiz_diagnosticstats/correctanswer', 'init');

        $this->quiz = $quiz;
        $this->cm = $cm;
        $this->course = $course;
        $this->context = context_module::instance($this->cm->id);

        $questionid = optional_param('questionid', null, PARAM_INT);
        $quizobj = new quiz_settings($quiz, $cm, $course);
        $structure = $quizobj->get_structure();

        $this->print_header_and_tabs($cm, $course, $quiz, 'diagnosticstats');
        echo $OUTPUT->heading(get_string('diagnosticstats', 'quiz_diagnosticstats'));

        $PAGE->requires->strings_for_js(['studentlabel'], 'quiz_diagnosticstats');

        echo $OUTPUT->render_from_template('quiz_diagnosticstats/anonymousmode', []);

        $anonymous_state = $this->get_anon_state_for_user() ? true : false;

        $data = [
            'anonymousState' => $anonymous_state,
            'cmid' => $cm->id
        ];

        $PAGE->requires->js_call_amd(
            'quiz_diagnosticstats/anonymousmode',
            'init',
            [json_encode($data)]
        );

        // Display report: either for a single question (if questionid is provided) or for all diagnostic questions.
        if ($questionid) {
            $combined_summary_data = $this->generate_question_summary_data($questionid, $structure);
            echo !empty($combined_summary_data) ? $combined_summary_data :
                html_writer::div(get_string('nostatsforquestion', 'quiz_diagnosticstats'), 'alert alert-warning');
        } else {
            $all_questions_data = $this->generate_all_diagnostic_questions_summary($structure);
            echo !empty($all_questions_data) ? $all_questions_data :
                html_writer::div(get_string('nostatsforquestions', 'quiz_diagnosticstats'), 'alert alert-warning');
        }
    }

    /**
     * Displays a single question with the correct answers.
     *
     * @param int $questionid The question ID.
     * @param int $slot The slot number within the QUBA.
     * @param question_usage_by_activity $quba The QUBA object.
     * @param int $displayednumber The displayed number of the question.
     * @return string Rendered question HTML.
     */
    private function display_question($questionid, $slot, $quba, $displayednumber) {
        global $DB;
        $question = question_bank::load_question($questionid);
        $qa = $quba->get_question_attempt($slot);

        $options = new question_display_options();
        $options->readonly = true;
        $options->correctness = question_display_options::VISIBLE;
        $options->rightanswer = question_display_options::VISIBLE;

        $content = $quba->render_question($slot, $options, $displayednumber);

        $questionusageid = $DB->get_field('question_usages', 'id', ['contextid' =>  $this->context->id]);
        $content = $this->update_image_src_with_usageid($content, $questionusageid);

        return $content;
    }

    /**
     * Generates summary data for a single question.
     *
     * @param int $questionid The question ID.
     * @param object $structure The quiz structure object.
     * @return string Rendered question summary data.
     * @throws moodle_exception If summary data cannot be generated.
     */
    private function generate_question_summary_data ($questionid, $structure) {
        try {
            list($quba, $slots) = $this->build_quba_and_slots($structure);

            return $this->display_question($questionid, $slots[$questionid], $quba, $this->get_displayed_number($questionid, $structure));

        } catch (Exception $e) {
            $a = new \stdClass();
            $a->questionid = $questionid;
            $a->errormessage = $e->getMessage();
            throw new \moodle_exception(get_string('failedsummarygeneration', 'quiz_diagnosticstats', $a));
        }
    }

    /**
     * Generates summary data for all diagnostic questions in the quiz.
     *
     * @param object $structure The quiz structure object.
     * @return string Rendered summary data for all diagnostic questions.
     * @throws moodle_exception If summary data cannot be generated.
     */
    private function generate_all_diagnostic_questions_summary($structure) {
        $combinedsummarydata = '';

        try {
            list($quba, $slots) = $this->build_quba_and_slots($structure);

            foreach ($structure->get_slots() as $slot) {
                $question = $this->get_question_by_id($slot->questionid);
                if ($question && $question->qtype === 'diagnosticadv') {
                    $combinedsummarydata .= $this->display_question($slot->questionid, $slots[$slot->questionid], $quba, $slot->displayednumber);
                }
            }

        } catch (Exception $e) {
            $a = new \stdClass();
            $a->errormessage = $e->getMessage();

            throw new \moodle_exception(get_string('failedloadsummary', 'quiz_diagnosticstats', $a));
        }

        return $combinedsummarydata;
    }

    /**
     * Gets the displayed number of the question.
     *
     * @param int $questionid The question ID.
     * @param object $structure The quiz structure object.
     * @return int|string The displayed number or an empty string if not found.
     */
    private function get_displayed_number($questionid, $structure) {
        foreach ($structure->get_slots() as $item) {
            if ($item->questionid == $questionid) {
                return $item->displayednumber;
            }
        }

        debugging('Displayed number not found for question ID: ' . $questionid, DEBUG_DEVELOPER);
        return '';
    }

    /**
     * Retrieves a question record by its ID from the database.
     *
     * @param int $questionid The question ID.
     * @return object The question record.
     * @throws dml_exception If the record does not exist.
     */
    private function get_question_by_id($questionid) {
        global $DB;
        return $DB->get_record('question', array('id' => $questionid), '*', MUST_EXIST);
    }

    /**
     * Builds the QUBA and returns it with a mapping of question IDs to slot numbers.
     *
     * @param object $structure The quiz structure object.
     * @return array Contains the QUBA object and the slots array.
     */
    private function build_quba_and_slots($structure) {
        global $USER;
        $quba = question_engine::make_questions_usage_by_activity('core_question_preview', context_user::instance($USER->id));
        $quba->set_preferred_behaviour('manualgraded');
        $slots = [];
        // Loop through all slots to add questions to the QUBA and store their slot numbers.
        foreach ($structure->get_slots() as $slot) {
            $question = question_bank::load_question($slot->questionid);
            if ($question) {
                $slot_in_quba = $quba->add_question($question);
                $quba->start_question($slot_in_quba);
                $slots[$slot->questionid] = $slot_in_quba;
            }
        }

        return [$quba, $slots];
    }

    /**
     * Updates the image src in the HTML content by replacing the incorrect usage ID
     * after 'questiontext' with the correct $questionusageid.
     *
     * Scans the HTML for <img> tags in <div class="qtext"> and updates the src attribute.
     *
     * @param string $html_content The original HTML content that contains the question text and images.
     * @param string $questionusageid The correct question usage ID that will replace the incorrect one in the image src.
     * @return string The updated HTML content with the correct image URLs.
     */
    private function update_image_src_with_usageid($html_content, $questionusageid) {
        $pattern = '/(\/questiontext\/)([^\/]+)(\/)/';

        $replacement = '${1}' . $questionusageid . '${3}';

        return preg_replace($pattern, $replacement, $html_content);
    }

    /**
     * Sets the anonymous state preference for the current user in the context of the current course module.
     *
     * @param int $state The anonymous state (1 for enabled, 0 for disabled).
     * @return bool True if the preference was successfully set, false otherwise.
     */
    private function set_anon_state_for_user($state) {
        global $USER;

        $name = 'quiz_advancedoverview_anon_' . $this->cm->id;
        return set_user_preference($name, (int) $state, $USER->id);
    }

    /**
     * Retrieves the anonymous state preference for the current user in the context of the current course module.
     *
     * @return int The anonymous state (1 for enabled, 0 for disabled). Defaults to 0 if not set.
     */
    public function get_anon_state_for_user() {
        global $USER;

        $name = 'quiz_advancedoverview_anon_' . $this->cm->id;
        return get_user_preferences($name, 0, $USER->id);
    }

}
