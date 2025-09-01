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
 * Defines the editing form for the diagnosticadv question type.
 *
 * @package    qtype
 * @subpackage diagnosticadv
 * @copyright  Devlion.ltd <info@devlion.co>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();


/**
 * Diagnostic ADV question editing form definition.
 *
 * @copyright  Devlion.ltd <info@devlion.co>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qtype_diagnosticadv_edit_form extends question_edit_form {

    protected function definition_inner($mform) {
        global $PAGE;

        $mform->addElement('editor', 'teacherdesc', get_string('teacherdesc', 'qtype_diagnosticadv'));
        $mform->setType('teacherdesc', PARAM_RAW);

        $mform->addElement('advcheckbox', 'hidemark',
                get_string('hidemark', 'qtype_diagnosticadv'));

        $mform->addElement('advcheckbox', 'security',
            get_string('security', 'qtype_diagnosticadv'));

        $mform->addElement('advcheckbox', 'required',
            get_string('required', 'qtype_diagnosticadv'));

        $mform->disabledIf('required', 'security', 'eq', 0);

        $mform->addElement('advcheckbox', 'usecase',
            get_string('usecase', 'qtype_diagnosticadv'));

        $mform->addElement('advcheckbox', 'aianalytics',
                get_string('aianalytics', 'qtype_diagnosticadv'));

        $mform->addElement('textarea', 'promt',
                get_string('teacherpromt', 'qtype_diagnosticadv'), array('rows' => 10));
        $mform->setDefault('promt', get_config('qtype_diagnosticadv', 'promttemaplate'));

        $mform->addElement('text', 'temperature',
                get_string('temperature', 'qtype_diagnosticadv'));
        $mform->setType('temperature', PARAM_TEXT);
        $mform->setDefault('temperature', get_config('qtype_diagnosticadv', 'temperature'));

        $this->add_per_answer_fields($mform, get_string('answerno', 'qtype_diagnosticadv', '{no}'),
                question_bank::fraction_options());
        $mform->addElement('html', '<script>
    document.addEventListener("DOMContentLoaded", function() {
      
      document.querySelectorAll("input[id=id_security]")[0].addEventListener("click", function() {
          const requiredCheckbox = document.querySelector("input[id=id_required]");
          if (!this.checked){
              document.querySelectorAll("input[name=required]").forEach(function(input) {
                            input.value = 0;
              });
               requiredCheckbox.checked = false;
               requiredCheckbox.disabled = true;
          } else {
               requiredCheckbox.disabled = false;
          }
      });   
      document.querySelectorAll("input[id=id_required]")[0].addEventListener("click", function() {
          if (!this.checked){
              console.log(this.checked);
                 document.querySelectorAll("input[name=required]").forEach(function(input) {
                            input.value = 0;
              });
          } else {
               document.querySelectorAll("input[name=required]").forEach(function(input) {
                            input.value = 1;
              });
          }
      });
       if (!document.querySelector("input[id=id_security]").checked){
           document.querySelector("input[id=id_required]").disabled = true;
       }
  });
</script>');

        $this->add_interactive_settings();
    }

    protected function get_more_choices_string() {
        return get_string('addmoreanswerblanks', 'qtype_diagnosticadv');
    }


    protected function data_preprocessing($question) {
        global $DB;

        $question = parent::data_preprocessing($question);
        $question = $this->data_preprocessing_answers($question);
        $question = $this->data_preprocessing_hints($question);

        if (isset($question->teacherdesc)) {
            $question->teacherdesc = ['text' => $question->teacherdesc, 'format' => 1];
        }

        return $question;
    }

    protected function data_preprocessing_answers($question, $withanswerfiles = false) {
        $question = parent::data_preprocessing_answers($question, $withanswerfiles);

        unset($question->answer);

        if (!isset($question->options->answers)) {
            return $question;
        }
    
        $context = \context::instance_by_id($question->contextid); // חובה!
    
        $key = 0;
        foreach ($question->options->answers as $answer) {
            $draftid = file_get_submitted_draft_itemid("answer[{$key}]");
            $question->answer[$key] = [
                'text' => file_prepare_draft_area(
                    $draftid,
                    $context->id,
                    'question',
                    'answer',
                    $answer->id,
                    null,
                    $answer->answer
                ),
                'format' => $answer->answerformat,
                'itemid' => $draftid,
            ];
            $question->custom[$key] = $answer->custom;
            $question->fraction[$key] = $answer->fraction;

            $key++;
        }

        return $question;
    }


    public function data_postprocessing($data) {
        if ($data['qtype'] == 'diagnosticadv') {
            $data['qtype'] = 'diagnosticadv';
        }

        return $data;
    }

    public function validation($data, $files) {
        $errors = parent::validation($data, $files);
        $answers = $data['answer'];
        $customs = $data['custom'];
        $fractions = $data['fraction'];
        $maxgrade = false;

        $countcustoms = 0;
        foreach ($answers as $key => $answer) {

            $trimmedanswer = trim($answer['text']);

            if ($customs[$key] == 0 && empty($trimmedanswer) && $fractions[$key] == 0) {
                continue;
            }

            if ($customs[$key] == 1) {
                $countcustoms++;
            }

            if ($fractions[$key] == 1) {
                $maxgrade = true;
            }

            if ($customs[$key] != 1 && !mb_strlen($trimmedanswer)) {
                $errors["answeroptions[{$key}]"] = get_string('answermustbegiven', 'qtype_diagnosticadv');
            }
        }

        if ($countcustoms > 1) {
            $errors['answeroptions[0]'] = get_string('customanswererror', 'qtype_diagnosticadv');
        }

        if ($maxgrade == false) {
            $errors['answeroptions[0]'] = get_string('fractionsnomax', 'question');
        }

        return $errors;
    }

    public function qtype() {
        return 'diagnosticadv';
    }

    /**
     * Get the list of form elements to repeat, one for each answer.
     * @param object $mform the form being built.
     * @param string $label the label to use for each option.
     * @param array $gradeoptions the possible grades for each answer.
     * @param array $repeatedoptions reference to array of repeated options to fill
     * @param string $answersoption reference to return the name of $question->options field holding an array of answers
     * @return array of form fields.
     */
    protected function get_per_answer_fields($mform, $label, $gradeoptions,
                                             &$repeatedoptions, &$answersoption) {

        // Example: "Answer {no}" – Moodle replaces {no} with the number.
        $label = get_string('answer', 'qtype_diagnosticadv', '{no}');

        $repeated = array();

        // 1. Group for checkbox and grade – this gets the numbering label.
        $answergroup = array();
        $answergroup[] = $mform->createElement('advcheckbox', 'custom', get_string('custom', 'qtype_diagnosticadv'));
        $answergroup[] = $mform->createElement('select', 'fraction',
            get_string('gradenoun'), $gradeoptions);

        $repeated[] = $mform->createElement('group', 'answeroptions', $label, $answergroup, null, false);

        // 2. Answer editor (Atto) on its own row – not part of group, but still repeated.
        $repeated[] = $mform->createElement('editor', 'answer',
            get_string('answer', 'question'), array('rows' => 5), $this->editoroptions);

        // 3. Feedback editor (optional, also on its own row).
        $repeated[] = $mform->createElement('editor', 'feedback',
            get_string('feedback', 'question'), array('rows' => 5), $this->editoroptions);

        // Required by Moodle to process the repeated fields properly.
        $repeatedoptions['answer']['type'] = PARAM_RAW;
        $repeatedoptions['fraction']['default'] = 0;
        $answersoption = 'answers';

        return $repeated;
    }

}
