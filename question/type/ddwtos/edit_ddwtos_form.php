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
 * Defines the editing form for the drag-and-drop words into sentences question type.
 *
 * @package   qtype_ddwtos
 * @copyright 2009 The Open University
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/question/type/gapselect/edit_form_base.php');


/**
 * Drag-and-drop words into sentences editing form definition.
 *
 * @copyright  2009 The Open University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qtype_ddwtos_edit_form extends qtype_gapselect_edit_form_base {
    public function qtype() {
        return 'ddwtos';
    }

    /**
     * Creates an array with elements for a choice group.
     *
     * @param object $mform The Moodle form we are working with
     * @param int $maxgroup The number of max group generate element select.
     * @return array Array for form elements
     */
    protected function choice_group_custom($mform) {
        global $DB;

        $options = array();
        for ($i = 1; $i <= $this->get_maximum_choice_group_number(); $i += 1) {
            $options[$i] = question_utils::int_to_letter($i);
        }
        $grouparray = array();

        // Mathlive enable.
        $flag = false;
        if (isset($this->question->id)) {
            $obj = $DB->get_record('question_ddwtos', ['questionid' => $this->question->id]);
            if (!empty($obj) && $obj->mathliveenable == 1) {
                $flag = true;
            }
        }

        if(!$flag) {
            $grouparray[] = $mform->createElement('text', 'answer',
                    get_string('answer', 'qtype_gapselect'), array('size' => 30, 'class' => 'tweakcss'));
        }else{
            $grouparray[] = $mform->createElement('mathlive', 'answer', get_string('answer', 'qtype_gapselect'), []);
        }

        $grouparray[] = $mform->createElement('select', 'choicegroup',
                get_string('group', 'qtype_gapselect'), $options);
        return $grouparray;
    }

    protected function data_preprocessing_choice($question, $answer, $key) {
        $question = parent::data_preprocessing_choice($question, $answer, $key);
        $options = unserialize_object($answer->feedback);
        $question->choices[$key]['choicegroup'] = $options->draggroup ?? 1;
        $question->choices[$key]['infinite'] = !empty($options->infinite);
        return $question;
    }

    protected function choice_group($mform) {
        $grouparray = $this->choice_group_custom($mform);
        $grouparray[] = $mform->createElement('checkbox', 'infinite', get_string('infinite', 'qtype_ddwtos'), '', null,
                array('size' => 1, 'class' => 'tweakcss'));
        return $grouparray;
    }

    protected function extra_slot_validation(array $slots, array $choices): ?string {
        foreach ($slots as $slot) {
            if (count(array_filter($slots, fn($value) => $value == $slot)) > 1) {
                $choice = $choices[$slot - 1];
                if (!isset($choice['infinite']) || $choice['infinite'] != 1) {
                    return get_string('errorlimitedchoice', 'qtype_ddwtos',
                        html_writer::tag('b', $slot));
                }
            }
        }
        return null;
    }

    protected function definition_inner($mform) {
        global $PAGE, $DB;

        // Mathlive default.
        $PAGE->requires->js_amd_inline('
            require(["jquery"], function($) {
                $("input[name='."'mathliveenable'".']").change(function() {            
                    $("form").find("#id_updatebutton").click();     
                });                
            });
        ');

        // Mathlive enable.
        $mform->addElement('checkbox', 'mathliveenable', get_string('mathliveenable', 'qtype_ddwtos'), ' ');
        $mform->setType('mathliveenable', PARAM_INT);

        if (isset($this->question->id)) {
            $obj = $DB->get_record('question_ddwtos', ['questionid' => $this->question->id]);

            if(!empty($obj)){
                $mform->setDefault('mathliveenable', $obj->mathliveenable);
            }else{
                $mform->setDefault('mathliveenable', 0);
            }
        } else {
            $mform->setDefault('mathliveenable', 0);
        }

        // Add the answer (choice) fields to the form.
        $this->definition_answer_choice($mform);

        $this->add_combined_feedback_fields(true);
        $this->add_interactive_settings(true, true);

        //parent::definition_inner($mform);
        //$mform->insertElementBefore($mform->createElement('static', 'previewarea', '',
        //        get_string('choicesacceptedtext', 'qtype_ddwtos')), 'shuffleanswers');
    }

}
