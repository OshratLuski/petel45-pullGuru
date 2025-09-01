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
 * Defines the editing form for the multiple choice question type.
 *
 * @package    qtype
 * @subpackage diagnostic
 * @copyright  2007 Jamie Pratt
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();


/**
 * Multiple choice editing form definition.
 *
 * @copyright  2007 Jamie Pratt
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qtype_diagnostic_edit_form extends question_edit_form {
    /**
     * Add question-type specific form fields.
     *
     * @param object $mform the form being built.
     */
    protected function definition_inner($mform) {
        $menu = array(
            get_string('answersingleno', 'qtype_diagnostic'),
            get_string('answersingleyes', 'qtype_diagnostic'),
        );
        $mform->addElement('select', 'single',
                get_string('answerhowmany', 'qtype_diagnostic'), $menu);
        $mform->setDefault('single', 1);

        $mform->addElement('advcheckbox', 'shuffleanswers',
                get_string('shuffleanswers', 'qtype_diagnostic'), null, null, array(0, 1));
        $mform->addHelpButton('shuffleanswers', 'shuffleanswers', 'qtype_diagnostic');
        $mform->setDefault('shuffleanswers', 1);

        $mform->addElement('select', 'answernumbering',
                get_string('answernumbering', 'qtype_diagnostic'),
                qtype_diagnostic::get_numbering_styles());
        $mform->setDefault('answernumbering', 'abc');
        $mform->addElement('advcheckbox', 'otheranswer',
        get_string('otheranswer', 'qtype_diagnostic'), null, null, array(0, 1));
        $mform->addHelpButton('otheranswer', 'otheranswer', 'qtype_diagnostic');
        $mform->setDefault('otheranswer', 1);
		$mform->addElement('text', 'otheranswertext', get_string('otheranswertext', 'qtype_diagnostic'), 'maxlength="100" size="25"');
		$mform->setType('otheranswertext', PARAM_RAW);
		$mform->addHelpButton('otheranswertext', 'otheranswertext', 'qtype_diagnostic');
		$mform->disabledIf('otheranswertext', 'otheranswer');
        $mform->addElement('advcheckbox', 'answerreason',
        get_string('answerreason', 'qtype_diagnostic'), null, null, array(0, 1));
        $mform->addHelpButton('answerreason', 'answerreason', 'qtype_diagnostic');
        $mform->setDefault('answerreason', 1);
        $mform->addElement('advcheckbox', 'addcbm',
        get_string('addcbm', 'qtype_diagnostic'), null, null, array(0, 1));
        $mform->addHelpButton('addcbm', 'addcbm', 'qtype_diagnostic');
        $mform->setDefault('addcbm', 1);
        $this->add_per_answer_fields($mform, get_string('choiceno', 'qtype_diagnostic', '{no}'),
                question_bank::fraction_options_full(), max(5, QUESTION_NUMANS_START));
        $this->add_combined_feedback_fields(true);
        $mform->disabledIf('shownumcorrect', 'single', 'eq', 1);
        $this->add_interactive_settings(true, true);
    }

    protected function get_per_answer_fields($mform, $label, $gradeoptions,
            &$repeatedoptions, &$answersoption) {
        $repeated = array();
        $repeated[] = $mform->createElement('editor', 'answer',
                $label, array('rows' => 1), $this->editoroptions);
        $repeated[] = $mform->createElement('editor', 'feedback',
                get_string('feedback', 'question'), array('rows' => 1), $this->editoroptions);
        $repeatedoptions['answer']['type'] = PARAM_RAW;
        $repeatedoptions['fraction']['default'] = 0;
        $answersoption = 'answers';
        return $repeated;
    }

    protected function get_hint_fields($withclearwrong = false, $withshownumpartscorrect = false) {
        list($repeated, $repeatedoptions) = parent::get_hint_fields($withclearwrong, $withshownumpartscorrect);
        $repeatedoptions['hintclearwrong']['disabledif'] = array('single', 'eq', 1);
        $repeatedoptions['hintshownumcorrect']['disabledif'] = array('single', 'eq', 1);
        return array($repeated, $repeatedoptions);
    }

    protected function data_preprocessing($question) {

        $question = parent::data_preprocessing($question);
        $question = $this->data_preprocessing_answers($question, true);
        $question = $this->data_preprocessing_combined_feedback($question, true);
        $question = $this->data_preprocessing_hints($question, true, true);

        if (!empty($question->options)) {
            $question->single = $question->options->single;
            $question->shuffleanswers = $question->options->shuffleanswers;
            $question->answernumbering = $question->options->answernumbering;
            $question->otheranswer = $question->options->otheranswer;
            $question->otheranswertext = $question->options->otheranswertext;
            $question->answerreason = $question->options->answerreason;
            $question->addcbm = $question->options->addcbm;
        }

        return $question;
    }

    public function validation($data, $files) {
        $errors = parent::validation($data, $files);
        $answers = $data['answer'];
        $answercount = 0;

        $totalfraction = 0;
        $maxfraction = -1;

        foreach ($answers as $key => $answer) {
            // Check no of choices.
            $trimmedanswer = trim($answer['text']);

            if ($trimmedanswer === '' && empty($fraction)) {
                continue;
            }

            $answercount++;
        }

        if ($answercount == 0) {
            $errors['answer[0]'] = get_string('notenoughanswers', 'qtype_diagnostic', 2);
            $errors['answer[1]'] = get_string('notenoughanswers', 'qtype_diagnostic', 2);
        } else if ($answercount == 1) {
            $errors['answer[1]'] = get_string('notenoughanswers', 'qtype_diagnostic', 2);

        }

        return $errors;
    }

    public function qtype() {
        return 'diagnostic';
    }
}
