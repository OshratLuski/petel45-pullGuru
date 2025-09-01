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
 * Question type class for the diagnosticadvdesc 'question' type.
 *
 * @package    qtype_diagnosticadvdesc
 * @subpackage diagnosticadvdesc
 * @copyright  1999 onwards Martin Dougiamas  {@link http://moodle.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/questionlib.php');

/**
 * The diagnosticadvdesc 'question' type.
 *
 * @copyright  1999 onwards Martin Dougiamas  {@link http://moodle.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qtype_diagnosticadvdesc extends question_type {
    /**
     * Determines if this is a real question type.
     *
     * @return bool False as this is not a real question type
     */
        public function is_real_question_type() {
            return true;
        }

    /**
     * Checks if the question type can be used by random question selection.
     *
     * @return bool False if not usable by random selection
     */
    public function is_usable_by_random() {
        return false;
    }

    /**
     * Checks if responses can be analyzed for this question type.
     *
     * @return bool False if responses cannot be analyzed
     */
    public function can_analyse_responses() {
        return false;
    }

    /**
     * Saves the question with specific settings.
     *
     * @param object $question The question object
     * @param object $form The form data
     * @return object The saved question
     * @throws coding_exception
     * @throws moodle_exception
     */
    public function save_question($question, $form) {
        $form->defaultmark = 0;
        return parent::save_question($question, $form);
    }

    /**
     * Returns the number of actual questions.
     *
     * @param object $question The question object
     * @return int Number of questions (0 for this type)
     */
        public function actual_number_of_questions($question) {
            return 1;
        }

    /**
     * Saves the question options specific to this type.
     *
     * @param object $question The question object with options
     * @return bool True on success
     */
    public function save_question_options($question) {
        global $DB, $COURSE;
        $data = new stdClass();

        list($data->relatedqid, $data->quizid) = explode('_', $question->related_question);

        $data->courseid = $COURSE->id;
        $data->questionid = $question->id;

        $record = $DB->get_record('qtype_diagnosticadvdesc', ['questionid' => $data->questionid]);

        if (!$record) {
            $data->timecreated = time();
            $data->timemodified = time();
            $DB->insert_record('qtype_diagnosticadvdesc', $data);
        } else {
            $record->relatedqid = $data->relatedqid;
            $record->quizid = $data->quizid;

            $record->timemodified = time();
            $DB->update_record('qtype_diagnosticadvdesc', $record);
        }
        return true;
    }

    /**
     * Gets the random guess score for the question.
     *
     * @param object $questiondata The question data
     * @return null No random guess score
     */
        public function get_random_guess_score($questiondata) {
            return null;
        }

        public function export_to_xml($question, qformat_xml $format, $extra = null) {
            global $DB;

            $expout = '';
            $options = $DB->get_record('qtype_diagnosticadvdesc', ['questionid' => $question->id]);

            if ($options) {
                $expout .= "    <relatedqid>{$format->xml_escape($options->relatedqid)}</relatedqid>\n";
                $expout .= "    <quizid>{$format->xml_escape($options->quizid)}</quizid>\n";
            }
            
            return $expout;
        }

        public function import_from_xml($data, $question, qformat_xml $format, $extra = null) {
            if (!isset($data['@']['type']) || $data['@']['type'] != 'diagnosticadvdesc') {
                return false;
            }

            $qo = $format->import_headers($data);
            $qo->qtype = 'diagnosticadvdesc';

            $relatedqid = $format->getpath($data, ['#', 'relatedqid', 0, '#'], 0);
            $qo->related_question = $relatedqid;

            $quizid = $format->getpath($data, ['#', 'quizid', 0, '#'], 0);
            $qo->quizid = $quizid;

            $qo->related_question = $relatedqid . '_' . $quizid;

            return $qo;
        }

    }
