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
 * Defines the hooks necessary to make the algebra question type combinable
 *
 * @package    qtype_shortanswer
 * @copyright  2019 Jean-Michel Vedrine
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

class qtype_combined_combinable_type_shortanswer extends qtype_combined_combinable_type_base {

    protected $identifier = 'shortanswer';

    protected function extra_question_properties() {
        return array('answerprefix' => '', 'allowedfuncs' => array('all' => 1));
    }

    protected function extra_answer_properties() {
        return array();

    }

    public function subq_form_fragment_question_option_fields() {
        return array(
            'usecase'         => null,
            'answersinstruct' => null, //TODO:
            'answerno'        => null,
            'allowedfuncs'    => null,
        );
    }
}

class qtype_combined_combinable_shortanswer extends qtype_combined_combinable_text_entry {

    const DEFAULT_NUM_HINTS = 2;

    /**
     * @param moodleform      $combinedform
     * @param MoodleQuickForm $mform
     * @param                 $repeatenabled
     * @return mixed
     */
    public function add_form_fragment(moodleform $combinedform, MoodleQuickForm $mform, $repeatenabled) {
        global $CFG;

        $menu = array(
            get_string('caseno', 'qtype_shortanswer'),
            get_string('caseyes', 'qtype_shortanswer'),
        );
        $mform->addElement('select', $this->form_field_name('usecase'),
            get_string('casesensitive', 'qtype_shortanswer'), $menu);

        $mform->addElement('static', $this->form_field_name('answersinstruct'),
            get_string('correctanswers', 'qtype_shortanswer'),
            get_string('filloutoneanswer', 'qtype_shortanswer'));
        $mform->closeHeaderBefore('answersinstruct');

        $this->add_per_answer_fields($combinedform, $mform, get_string('answerno', 'qtype_shortanswer', '{no}'),
            question_bank::fraction_options());

        // TODO: remove?
        // $this->add_interactive_settings($combinedform, $mform);
    }

    protected function add_per_answer_fields($combinedform, &$mform, $label, $gradeoptions,
        $minoptions = QUESTION_NUMANS_START, $addoptions = QUESTION_NUMANS_ADD) {

        $answersoption   = '';
        $repeatedoptions = array();
        $repeated        = $this->get_per_answer_fields($combinedform, $mform, $label, $gradeoptions,
            $repeatedoptions, $answersoption);

        if (isset($this->questionrec->options)) {
            $repeatsatstart = count($this->questionrec->options->$answersoption);
        } else {
            $repeatsatstart = $minoptions;
        }

        $combinedform->repeat_elements($repeated, $repeatsatstart, $repeatedoptions,
            'noanswers', 'addanswers', $addoptions,
            $this->get_more_choices_string(), true);
    }

    protected function get_per_answer_fields($combinedform, $mform, $label, $gradeoptions,
        &$repeatedoptions, &$answersoption) {
        $repeated      = array();
        $answeroptions = array();
        // TODO: test mathlive
        $answeroptions[] = $mform->createElement('text', $this->form_field_name('answer'),
            $label, array('size' => 40));
        $mform->setType($this->form_field_name('answer'), PARAM_TEXT);
        $answeroptions[] = $mform->createElement('select', $this->form_field_name('fraction'),
            get_string('grade', 'grades'), $gradeoptions);
        $repeated[] = $mform->createElement('group', $this->form_field_name('answeroptions'),
            $label, $answeroptions, null, false);
        $repeated[] = $mform->createElement('editor', $this->form_field_name('feedback'),
            get_string('feedback', 'question'), array('rows' => 5), $combinedform->editoroptions);
        $repeatedoptions['answer']['type']      = PARAM_RAW;
        $repeatedoptions['fraction']['default'] = 0;
        $answersoption                          = 'answers';
        return $repeated;
    }

    protected function get_more_choices_string() {
        return get_string('addmorechoiceblanks', 'question');
    }

    // TODO: ??? or remove
    protected function add_interactive_settings($combinedform, &$mform, $withclearwrong = false,
        $withshownumpartscorrect = false) {
        global $CFG;

        $mform->addElement('header', 'multitriesheader',
            get_string('settingsformultipletries', 'question'));

        $penalties = array(
            1.0000000,
            0.5000000,
            0.3333333,
            0.2500000,
            0.2000000,
            0.1000000,
            0.0000000,
        );
        if (!empty($combinedform->question->penalty) && !in_array($this->question->penalty, $penalties)) {
            $penalties[] = $this->question->penalty;
            sort($penalties);
        }
        $penaltyoptions = array();
        foreach ($penalties as $penalty) {
            $penaltyoptions["{$penalty}"] = (100 * $penalty) . '%';
        }
        $mform->addElement('select', 'penalty',
            get_string('penaltyforeachincorrecttry', 'question'), $penaltyoptions);
        $mform->addHelpButton('penalty', 'penaltyforeachincorrecttry', 'question');
        $mform->setDefault('penalty', 0.3333333);

        if (isset($this->question->hints)) {
            $counthints = count($this->question->hints);
        } else {
            $counthints = 0;
        }

        // FIXME: Access private property. Remove at all?
        if ($combinedform->question->formoptions->repeatelements) {
            $repeatsatstart = max(self::DEFAULT_NUM_HINTS, $counthints);
        } else {
            $repeatsatstart = $counthints;
        }

        list($repeated, $repeatedoptions) = $this->get_hint_fields($combinedform, $mform,
            $withclearwrong, $withshownumpartscorrect);
        $combinedform->repeat_elements($repeated, $repeatsatstart, $repeatedoptions,
            'numhints', 'addhint', 1, get_string('addanotherhint', 'question'), true);

        if ($CFG->instancename === 'physics') {
            $mform->getElement('hint[0]')->setValue(array('text' => get_string('questionhintdefault1', 'local_petel')));
            $mform->getElement('hint[1]')->setValue(array('text' => get_string('questionhintdefault2', 'local_petel')));
        }
    }

    protected function get_hint_fields($combinedform, $mform, $withclearwrong = false, $withshownumpartscorrect = false) {

        $repeatedoptions = array();
        $repeated        = array();
        $repeated[]      = $mform->createElement('editor', 'hint', get_string('hintn', 'question'),
            array('rows' => 5), $combinedform->editoroptions);
        $repeatedoptions['hint']['type'] = PARAM_RAW;

        $optionelements = array();
        if ($withclearwrong) {
            $optionelements[] = $mform->createElement('advcheckbox', 'hintclearwrong',
                get_string('options', 'question'), get_string('clearwrongparts', 'question'));
        }
        if ($withshownumpartscorrect) {
            $optionelements[] = $mform->createElement('advcheckbox', 'hintshownumcorrect', '',
                get_string('shownumpartscorrect', 'question'));
        }

        if (count($optionelements)) {
            $repeated[] = $mform->createElement('group', 'hintoptions',
                get_string('hintnoptions', 'question'), $optionelements, null, false);
        }

        return array($repeated, $repeatedoptions);
    }

    public function data_to_form($context, $fileoptions) {
        $answers = array(
            'answer'   => array(),
            'fraction' => array(),
            'feedback' => array(),
        );
        if ($this->questionrec !== null) {
            foreach ($this->questionrec->options->answers as $answer) {
                $answers['answer'][]           = $answer->answer;
                $answers['fraction'][]         = $answer->fraction;
                $answers['feedback'][]['text'] = $answer->feedback;
            }
        }
        $data = parent::data_to_form($context, $fileoptions) + $answers;
        return $data;
    }

    public function validate() {
        $errors      = array();
        $data        = (array) $this->formdata;
        $answers     = $data['answer'];
        $answercount = 0;
        $maxgrade    = false;
        foreach ($answers as $key => $answer) {
            $trimmedanswer = trim($answer);
            if ($trimmedanswer !== '') {
                $answercount++;
                if ($data['fraction'][$key] == 1) {
                    $maxgrade = true;
                }
            } else if ($data['fraction'][$key] != 0 ||
                !html_is_blank($data['feedback'][$key]['text'])) {
                $errors[$this->form_field_name('answeroptions') . "[{$key}]"] = get_string('answermustbegiven', 'qtype_shortanswer');
                $answercount++;
            }
        }
        if ($answercount == 0) {
            $errors[$this->form_field_name('answeroptions') . '[0]'] = get_string('notenoughanswers', 'qtype_shortanswer', 1);
        }
        if ($maxgrade == false) {
            $errors[$this->form_field_name('answeroptions') . '[0]'] = get_string('fractionsnomax', 'question');
        }
        return $errors;
    }

    public function get_sup_sub_editor_option() {
        return null;
    }

    public function has_submitted_data() {
        return $this->submitted_data_array_not_empty('answer') || parent::has_submitted_data();
    }
}
