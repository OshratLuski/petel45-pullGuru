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
 * This file defines the quiz overview report class.
 *
 * @package   quiz_correctanswer
 * @copyright 2022 Nadav Kavalerchik <nadav.kavalerchik@weizmann.ac.il>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author    Devlion Moodle Development <service@devlion.co>
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/questionlib.php');

define('QUESTION_PREVIEW_MAX_VARIANTS', 100);

/**
 * Quiz report subclass for the overview (grades) report.
 *
 * @copyright 2022 Nadav Kavalerchik <nadav.kavalerchik@weizmann.ac.il>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class quiz_correctanswer_report extends mod_quiz\local\reports\attempts_report {

    public function display($quiz, $cm, $course) {
        global $OUTPUT, $PAGE, $DB;

        $showanswers = optional_param('answers', '', PARAM_ALPHA);
        $enablehints = optional_param('hint', 0, PARAM_INT);
        $PAGE->set_title(get_string('correctanswer', 'quiz_correctanswer'));

        $quizobj = new mod_quiz\quiz_settings($quiz, $cm, $course);
        $structure = $quizobj->get_structure();

        if(empty($structure->get_slots())){
            echo $OUTPUT->header();
            echo $OUTPUT->heading(get_string('correctanswer', 'quiz_correctanswer'));
            echo \html_writer::start_tag('h3');
            echo get_string('noquestions', 'quiz_correctanswer');
            echo \html_writer::end_tag('h3');
            echo $OUTPUT->footer();
            exit;
        }

        $content = '<form class="correct-answer">';

        foreach($structure->get_slots() as $item){
            list($quba, $slot, $options) = $this->get_quba($item->questionid, $cm->id, $showanswers);

            $quba->render_question_head_html($slot);

            ob_start();
            echo $quba->render_question($slot, $options, $item->displayednumber);

            $content .= ob_get_contents();
            ob_end_clean();

            $data = [];

            // Hints.
            $qhints = [];
            if($hints = $DB->get_records('question_hints', ['questionid' => $item->questionid])){
                $counter = 0;
                foreach($hints as $hint){
                    $counter++;
                    $html = format_text($hint->hint);
                    $html = preg_replace('/brokenfile.php#/', 'mod/quiz/report/correctanswer/imagefile.php', $html);
                    $qhints[] = ['hint' => $html, 'counter' => $counter];
                }
            }

            $data['enable_hints'] = $enablehints && !empty($qhints) ? true : false;
            $data['hints'] = $qhints;
            $context = context_module::instance($cm->id);

            // General feedback.
            $generalfeedback = $DB->get_field('question', 'generalfeedback', ['id' => $item->questionid]);
            $questionusageid = $DB->get_field_sql(
                "SELECT id 
                 FROM {question_usages} 
                 WHERE contextid = :contextid 
                 ORDER BY id DESC LIMIT 1",
                ['contextid' => $context->id]
            );
            if (!empty($generalfeedback) && $showanswers !== 'no') {
                $imageurl = "{$questionusageid}/{$item->slot}/{$item->questionid}";

                $generalfeedback = file_rewrite_pluginfile_urls($generalfeedback, 'pluginfile.php', $context->id, 'question', 'generalfeedback', $imageurl);

                // Format text to process equations and other formatting.
                $generalfeedback = format_text($generalfeedback, FORMAT_HTML, ['context' => $context]);
                $data['generalfeedback'] = $generalfeedback;
            } else {
                $data['generalfeedback'] = '';
            }

            // Question metadata.
            $qexpectedanswer = format_text(\local_metadata\mcontext::question()->get($item->questionid, 'qexpectedanswer'));
            $qexpectedanswer = preg_replace('/brokenfile.php#/', 'mod/quiz/report/correctanswer/imagefile.php', $qexpectedanswer);
            $data['qexpectedanswer'] = $qexpectedanswer;

            $qteachercomments = format_text(\local_metadata\mcontext::question()->get($item->questionid, 'qteachercomments'));
            $qteachercomments = preg_replace('/brokenfile.php#/', 'mod/quiz/report/correctanswer/imagefile.php', $qteachercomments);
            $data['qteachercomments'] = $qteachercomments;

            $content .= $OUTPUT->render_from_template('quiz_correctanswer/main', $data);
        }

        $content .= '</form>';

        echo $OUTPUT->header();
        if($showanswers === 'no'){
            echo $OUTPUT->heading(get_string('printquestions', 'quiz'));
        }elseif($enablehints){
            echo $OUTPUT->heading(get_string('correctanswerwithhint', 'quiz_correctanswer'));
        }
        else{
            echo $OUTPUT->heading(get_string('correctanswer', 'quiz_correctanswer'));
        }

        echo \html_writer::start_tag('div', ['class' => 'question-area']);
        echo $content;
        echo \html_writer::end_tag('div');

        // Buttons.
        echo '<div class="mdl-align centerbuttons">
            <input type="button" id="ca_print" class="btn btn-primary m-r-1" value="'.get_string('print', 'quiz_correctanswer').'">
            </div>';

        // CSS.
        if($showanswers){
            echo '
                <style>
                    #courseheaderimage {
                        display: none;
                    }
                
                    #page-footer {
                        display: none;
                    }
                </style>
            ';
        }

        question_engine::initialise_js();
        $PAGE->requires->js_module('core_question_engine');

        $PAGE->requires->js_call_amd('quiz_correctanswer/main', 'init', []);
    }

    private function get_quba($qid, $cmid, $showanswers){
        global $USER, $DB;

        $question = question_bank::load_question($qid);
        $context = context_module::instance($cmid);

        question_require_capability_on($question, 'use');

        // Get and validate display options.
        $maxvariant = min($question->get_num_variants(), QUESTION_PREVIEW_MAX_VARIANTS);
        $options = new \qbank_previewquestion\question_preview_options($question);
        $options->load_user_defaults();
        $options->set_from_request();

        // Build question.
        $quba = question_engine::make_questions_usage_by_activity(
                'core_question_preview', context_user::instance($USER->id));
        $quba->set_preferred_behaviour($options->behaviour);
        $slot = $quba->add_question($question, $options->maxmark);

        if ($options->variant) {
            $options->variant = min($maxvariant, max(1, $options->variant));
        } else {
            $options->variant = rand(1, $maxvariant);
        }

        $quba->start_question($slot, $options->variant);

        $transaction = $DB->start_delegated_transaction();
        question_engine::save_questions_usage_by_activity($quba);
        $transaction->allow_commit();

        if ($question->qtype->name() === 'diagnosticadv') {
            $correctresponse = [];
            if (isset($question->answers)) {
                foreach ($question->answers as $answerid => $answer) {
                    if ($answer->fraction > 0) {
                        $correctresponse['answer'] = $answerid;
                        break;
                    }
                }
            }
        } else {
            $correctresponse = $quba->get_correct_response($slot);
        }

        if (!is_null($correctresponse) && $showanswers !== 'no') {
            $quba->process_action($slot, $correctresponse);

            $transaction = $DB->start_delegated_transaction();
            question_engine::save_questions_usage_by_activity($quba);
            $transaction->allow_commit();
        }

        $previewid = $quba->get_id();

        try {
            $quba = question_engine::load_questions_usage_by_activity($previewid);

        } catch (Exception $e) {
            // This may not seem like the right error message to display, but
            // actually from the user point of view, it makes sense.
            throw new \moodle_exception ('submissionoutofsequencefriendlymessage', 'question',
                    \qbank_previewquestion\helper::question_preview_url($question->id, $options->behaviour,
                            $options->maxmark, $options, $options->variant, $context), null, $e);
        }

        if ($quba->get_owning_context()->instanceid != $USER->id) {
            throw new \moodle_exception ('notyourpreview', 'question');
        }

        $slot = $quba->get_first_question_number();
        $usedquestion = $quba->get_question($slot, false);
        if ($usedquestion->id != $question->id) {
            throw new \moodle_exception ('questionidmismatch', 'question');
        }
        $question = $usedquestion;
        $options->variant = $quba->get_variant($slot);
        $options->behaviour = $quba->get_preferred_behaviour();
        $options->maxmark = $quba->get_question_max_mark($slot);

        // Prepare technical info to be output.
        $qa = $quba->get_question_attempt($slot);

        return [$quba, $slot, $options];
    }
}
