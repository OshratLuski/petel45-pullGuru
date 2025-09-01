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
 * Defines the editing form for the shortanswer question type.
 *
 * @package    qtype
 * @subpackage shortanswer
 * @copyright  2007 Jamie Pratt
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();


/**
 * Short answer question editing form definition.
 *
 * @copyright  2007 Jamie Pratt
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qtype_shortanswer_edit_form extends question_edit_form {

    protected function definition_inner($mform) {
        global $PAGE;

        $menu = [
            get_string('caseno', 'qtype_shortanswer'),
            get_string('caseyes', 'qtype_shortanswer')
        ];
        $mform->addElement('select', 'usecase',
                get_string('casesensitive', 'qtype_shortanswer'), $menu);
        $mform->setDefault('usecase', $this->get_default_value('usecase', $menu[0]));

        $mform->addElement('static', 'answersinstruct',
                get_string('correctanswers', 'qtype_shortanswer'),
                get_string('filloutoneanswer', 'qtype_shortanswer'));
        $mform->closeHeaderBefore('answersinstruct');

        // Mathlive default.
        $PAGE->requires->js_amd_inline('
            require(["jquery"], function($) {
                $("input[name='."'mathliveenable'".']").change(function() {            
                    $("form").find("#id_updatebutton").click();     
                });                
            });
        ');

        // Mathlive enable.
        $mform->addElement('checkbox', 'mathliveenable', get_string('mathliveenable', 'qtype_shortanswer'), ' ');
        $mform->setType('mathliveenable', PARAM_INT);
        $mform->setDefault('mathliveenable', 0);

        $this->add_per_answer_fields($mform, get_string('answerno', 'qtype_shortanswer', '{no}'),
                question_bank::fraction_options());

        $this->add_interactive_settings();
    }

    protected function get_per_answer_fields($mform, $label, $gradeoptions,
                                             &$repeatedoptions, &$answersoption) {
        $repeated = array();
        $answeroptions = array();

        // Mathlive default.
        $default = optional_param('mathliveenable', 'undefined', PARAM_BOOL);

        $flag = false;
        if($default === 'undefined' && isset($this->question->options->mathliveenable) && $this->question->options->mathliveenable == 1){
            $flag = true;
        }else{
            if($default == 1){
                $flag = true;
            }
        }

        // Input mathlive or normal.
        if($flag) {
            $answeroptions[] = $mform->createElement('mathlive', 'answer', $label, []);
        }else{
            $answeroptions[] = $mform->createElement('text', 'answer', $label, array('size' => 40));
        }

        $answeroptions[] = $mform->createElement('select', 'fraction',
            get_string('grade', 'grades'), $gradeoptions);
        $repeated[] = $mform->createElement('group', 'answeroptions',
            $label, $answeroptions, null, false);
        $repeated[] = $mform->createElement('editor', 'feedback',
            get_string('feedback', 'question'), array('rows' => 5), $this->editoroptions);
        $repeatedoptions['answer']['type'] = PARAM_RAW;
        $repeatedoptions['fraction']['default'] = 0;
        $answersoption = 'answers';
        return $repeated;
    }

    protected function get_more_choices_string() {
        return get_string('addmoreanswerblanks', 'qtype_shortanswer');
    }

    protected function data_preprocessing($question) {
        $question = parent::data_preprocessing($question);
        $question = $this->data_preprocessing_answers($question);
        $question = $this->data_preprocessing_hints($question);

        return $question;
    }

    public function validation($data, $files) {
        $errors = parent::validation($data, $files);
        $answers = $data['answer'];
        $answercount = 0;
        $maxgrade = false;
        foreach ($answers as $key => $answer) {
            $trimmedanswer = trim($answer);
            if ($trimmedanswer !== '') {
                $answercount++;
                if ($data['fraction'][$key] == 1) {
                    $maxgrade = true;
                }
            } else if ($data['fraction'][$key] != 0 ||
                    !html_is_blank($data['feedback'][$key]['text'])) {
                $errors["answeroptions[{$key}]"] = get_string('answermustbegiven', 'qtype_shortanswer');
                $answercount++;
            }
        }
        if ($answercount==0) {
            $errors['answeroptions[0]'] = get_string('notenoughanswers', 'qtype_shortanswer', 1);
        }
        if ($maxgrade == false) {
            $errors['answeroptions[0]'] = get_string('fractionsnomax', 'question');
        }
        return $errors;
    }

    public function qtype() {
        return 'shortanswer';
    }
}
