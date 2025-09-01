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
 * Defines the editing form for the diagnosticadvai question type.
 *
 * @package    qtype_diagnosticadvai
 * @copyright  2007 Jamie Pratt
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * diagnosticadvai editing form definition.
 *
 * @copyright  2007 Jamie Pratt
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qtype_diagnosticadvai_edit_form extends question_edit_form {
    /**
     * Add question-type specific form fields.
     *
     * @param MoodleQuickForm $mform the form being built.
     */
    protected function definition_inner($mform) {
        global $PAGE, $DB, $COURSE;
        // We don't need this default element.
        $mform->removeElement('defaultmark');
        $mform->addElement('hidden', 'defaultmark', 0);
        $mform->setType('defaultmark', PARAM_RAW);

        $mform->addElement('text', 'temperature',
                get_string('temperature', 'qtype_diagnosticadvai'));
        $mform->setType('temperature', PARAM_FLOAT);
        $mform->setDefault('temperature', get_config('qtype_diagnosticadvai', 'temperature'));

        $sql = "SELECT  slot.id, q.id as qid, CONCAT(quiz.name, ' - ', q.name, ' (CMID: ', cm.id, ')') AS displayname, quiz.id AS quizid
                FROM {question} q
                JOIN {question_versions} qv ON q.id = qv.questionid
                JOIN {question_bank_entries} qbe ON qbe.id = qv.questionbankentryid
                JOIN {question_categories} qc ON qc.id = qbe.questioncategoryid
                JOIN {context} c ON qc.contextid = c.id
                JOIN {question_references} qr ON qr.questionbankentryid = qbe.id
                    AND qr.component = 'mod_quiz' AND qr.questionarea = 'slot'
                JOIN {quiz_slots} slot ON slot.id = qr.itemid
                JOIN {quiz} quiz ON quiz.id = slot.quizid
                JOIN {course_modules} cm ON cm.instance = quiz.id AND cm.module = (SELECT id FROM {modules} WHERE name = 'quiz')
                WHERE q.qtype = 'diagnosticadv'
                  AND cm.course = ?
                ORDER BY quiz.name, q.name";

        $questions = $DB->get_records_sql($sql, [$COURSE->id]);

        $options = ['' => get_string('choose', 'moodle')];

        if ($questions) {
            foreach ($questions as $question) {
                $options[$question->qid . '_' . $question->quizid] = $question->displayname;
            }
        } else {
            $options[''] = get_string('noquestions', 'qtype_diagnosticadvdesc');
        }

        $mform->addElement('select', 'related_question',
                get_string('selectdiagnosticadv', 'qtype_diagnosticadvai'),
                $options
        );

        $record = $DB->get_record('qtype_diagadvai_options', ['questionid' => $this->question->id ?? null]);
        if ($record && $record->relatedqid && $record->quizid) {
            $mform->setDefault('related_question', $record->relatedqid . '_' . $record->quizid);
        }

        $mform->addElement('textarea', 'teacherprompt',
                get_string('teacherprompt', 'qtype_diagnosticadvai'), array('rows' => 10));
        $mform->setType('teacherprompt', PARAM_TEXT);
        $mform->setDefault('teacherprompt', get_config('qtype_diagnosticadvai', 'prompttemaplate'));
    }

    /**
     * Returns the question type name.
     *
     * @return string The question type name
     */
    public function qtype() {
        return 'diagnosticadvai';
    }
}
