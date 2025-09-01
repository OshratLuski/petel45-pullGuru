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
 * diagnosticadvdesc 'question' renderer class.
 *
 * @package    qtype_diagnosticadvdesc
 * @subpackage diagnosticadvdesc
 * @copyright  2009 The Open University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();
require_once($CFG->dirroot . '/question/type/diagnosticadv/lib.php');

/**
 * Generates the output for diagnosticadvdesc 'question's.
 *
 * @copyright  2024 Devlion
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qtype_diagnosticadvdesc_renderer extends qtype_renderer {
    /**
     * Generates the formulation and controls for the question.
     *
     * @param question_attempt $qa The question attempt object
     * @param question_display_options $options Display options for the question
     * @return string HTML output
     * @throws dml_exception
     * @throws moodle_exception
     */
    public function formulation_and_controls(question_attempt $qa, question_display_options $options) {
        global $DB, $CFG, $COURSE, $USER;
        require_once($CFG->dirroot . '/mod/quiz/locallib.php');
        require_once($CFG->dirroot . '/question/lib.php');

        $extendetoption = $DB->get_record('qtype_diagnosticadvdesc', ['questionid' => $qa->get_question()->id]);
        $data = get_diagnosticadv_attempts_data($COURSE->id, $extendetoption->relatedqid, $extendetoption->quizid);

        $memberinteam = get_user_in_teamwork($qa->get_last_step()->get_user_id(), $options->context->instanceid);
        $result = [];
        foreach ($data as $key => $row) {
            if (in_array($row['userid'], $memberinteam)) {
                $row['fullname'] = fullname($DB->get_record('user', ['id' => $row['userid']]));
                $result[] = $row;
            }
        }

        $data['rows'] = $result;
        $questionrelated = \question_bank::load_question_data($extendetoption->relatedqid);
        if ($questionrelated) {

            $question = $qa->get_question();
            $data['questiontext'] = $question->format_questiontext($qa);

            $sql = "SELECT qa.*
                    FROM {quiz_slots} quizslots
                    JOIN {question_references} qr ON qr.itemid = quizslots.id
                    JOIN {question_bank_entries} qbe ON qbe.id = qr.questionbankentryid
                    JOIN {question_versions} qv ON qv.questionbankentryid = qbe.id
                    JOIN {question} q ON q.id = qv.questionid
                    JOIN {question_categories} qc ON qc.id = qbe.questioncategoryid
                    JOIN {course_modules} cm ON (cm.instance = quizslots.quizid)
                    JOIN {modules}  m ON m.id = cm.module
                    JOIN {quiz_attempts} qa ON cm.instance = qa.quiz
                    WHERE cm.course = ? AND q.id = ? AND qa.userid = ?   AND m.name = 'quiz' order by qa.id DESC limit 1 ";
            $qarelated = $DB->get_record_sql($sql, [$COURSE->id, $extendetoption->relatedqid, $qa->get_last_step()->get_user_id()]);

            if (!empty($qarelated->id)) {
                $quizattempt = \mod_quiz\quiz_attempt::create($qarelated->id);
                $slots = $quizattempt->get_slots();
                foreach ($slots as $slot) {
                    $questionattempt = $quizattempt->get_question_attempt($slot);
                    $questionrelated = $questionattempt->get_question();
                    if ($questionrelated->id == $extendetoption->relatedqid) {
                        break;
                    }
                }
            }
            if (!empty($questionattempt)) {
                $data['questiondetails'] = $questionrelated->format_questiontext($questionattempt);
            } else {
                $options = new \stdClass;
                $options->noclean = true;
                $options->para = false;

                $questiontext = question_rewrite_question_preview_urls($questionrelated->questiontext, $questionrelated->id,
                        $questionrelated->contextid, 'question', 'questiontext', $questionrelated->id,
                        $questionrelated->contextid, 'core_question');

                $questiontext = format_text($questiontext, $questionrelated->questiontextformat, $options);
                $data['questiondetails'] = $questiontext;
            }

        }
        return $this->output->render_from_template('qtype_diagnosticadvdesc/result', $data);
    }
}
