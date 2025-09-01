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
 * @package    qtype_shortmath
 * @copyright  2019 Jean-Michel Vedrine
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

class qtype_combined_combinable_type_shortmath extends qtype_combined_combinable_type_base {

    protected $identifier = 'shortmath';

    protected function extra_question_properties() {
        return array(
            'usecase',
            'editorconfig',
        );
    }

    protected function extra_answer_properties() {
        return array();
    }

    public function subq_form_fragment_question_option_fields() {
        return array(
            'usecase'         => null,
            'editorconfig'    => null,
            'answersinstruct' => null,
            'answerno'        => null,
            'allowedfuncs'    => null,
        );
    }
}

class qtype_combined_combinable_shortmath extends qtype_combined_combinable_text_entry {

    const DEFAULT_NUM_HINTS = 2;

    /**
     * @param moodleform      $combinedform
     * @param MoodleQuickForm $mform
     * @param                 $repeatenabled
     * @return mixed
     */
    public function add_form_fragment(moodleform $combinedform, MoodleQuickForm $mform, $repeatenabled) {
        global $CFG, $DB, $PAGE;

        $questionid = isset($this->questionrec->id) ? $this->questionrec->id : "null";

        $menu = array(
            get_string('caseno', 'qtype_shortanswer'),
            get_string('caseyes', 'qtype_shortanswer'),
        );
        $mform->addElement(
            'select',
            $this->form_field_name('usecase'),
            get_string('casesensitive', 'qtype_shortanswer'),
            $menu
        );

        $mform->addElement('hidden', $this->form_field_name('originalconfig'));
        $mform->setType($this->form_field_name('originalconfig'), PARAM_RAW);

        $templates = $DB->get_records('qtype_shortmath_templates', null, 'id');

        $options = array();
        if (!empty($this->questionrec->options)) {
            $options['-1'] = '';
        }

        foreach ($templates as $template) {
            $options[$template->id] = $template->name;
        }

        $editorconf = $this->form_field_name('editorconfig');

        // Fill originalconfig.
        if($opt = $DB->get_record('qtype_shortmath_options', ['questionid' => $questionid])){
            $mform->setDefault($this->form_field_name('originalconfig'), $opt->editorconfig);
        }else{
            $tmp = array_values($templates);
            $obj = array_shift($tmp);

            if(isset($obj->template)) {
                $mform->setDefault($this->form_field_name('originalconfig'), $obj->template);
            }
        }

        $toolbargroup = [
            $mform->createElement('html', \html_writer::start_tag('div', array('id' => 'template-container_'.$editorconf,
                    'class' => 'controlswrapper visual-math-input-wrapper'))),
            $mform->createElement('html', \html_writer::end_tag('div')),

        ];
        $mform->addElement('group', $this->form_field_name('toolbargroup'), get_string('toolbar', 'qtype_shortmath'), $toolbargroup, '', false);
        $mform->addHelpButton($this->form_field_name('toolbargroup'), 'toolbargroup', 'qtype_shortmath');

        $selecttemplate = $mform->addElement(
            'select',
            $this->form_field_name('editorconfig'),
            get_string('toolbar_template', 'qtype_shortmath'),
            $options
        );
        $mform->addHelpButton($this->form_field_name('editorconfig'), 'toolbar_template', 'qtype_shortmath');

        $defaultid = get_config('qtype_shortmath', 'defaultconfiguration');
        $default   = $DB->get_field('qtype_shortmath_templates', 'id', array('id' => $defaultid));
        $selecttemplate->setSelected($default);

        //$mform->addElement(
        //    'advcheckbox',
        //    $this->form_field_name('configchangeconfirm'),
        //    '',
        //    get_string('configchangeconfirm', 'qtype_shortmath')
        //);

        $mform->addElement(
            'static',
            $this->form_field_name('answersinstruct'),
            get_string('correctanswers', 'qtype_shortanswer'),
            get_string('filloutoneanswer', 'qtype_shortmath')
        );
        $mform->closeHeaderBefore('answersinstruct');

        $this->add_per_answer_fields(
            $combinedform,
            $mform,
            get_string('answerno', 'qtype_shortanswer', '{no}'),
            question_bank::fraction_options()
        );

        $PAGE->requires->js_call_amd('qtype_shortmath/question_form', 'initCombinable', [$questionid, $editorconf]);

        $PAGE->requires->css('/question/type/shortmath/visualmathinput/mathquill.css');
        $PAGE->requires->css('/question/type/shortmath/visualmathinput/visual-math-input.css');
        $PAGE->requires->css('/question/type/shortmath/editor/editor.css');
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
        $repeated        = array();
        $answeroptions   = array();
        $answeroptions[] = $mform->createElement('text', $this->form_field_name('answer'),
            $label, array('size' => 40));
        $answeroptions[] = $mform->createElement('select', $this->form_field_name('fraction'),
            get_string('grade', 'grades'), $gradeoptions);
        $repeated[] = $mform->createElement('group', $this->form_field_name('answeroptions'),
            $label, $answeroptions, null, false);
        $repeated[] = $mform->createElement('editor', $this->form_field_name('feedback'),
            get_string('feedback', 'question'), array('rows' => 5), $combinedform->editoroptions);
        $repeatedoptions[$this->form_field_name('answer')]['type'] = PARAM_RAW;
        $repeatedoptions['fraction']['default']                    = 0;
        $answersoption                                             = 'answers';
        return $repeated;
    }

    protected function get_more_choices_string() {
        return get_string('addmorechoiceblanks', 'question');
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
        global $PAGE;

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
        $parent = parent::data_to_form($context, $fileoptions);
        $data   = $parent + $answers;

        return $data;
    }

    public function validate() {
        $data                = (array) $this->formdata;
        $errors              = array();
        $originalconfigvalue = $data['originalconfig'];

        //if (
        //    $data['editorconfig'] !== '-1'
        //    && $originalconfigvalue !== 'none'
        //    && !$data['configchangeconfirm']
        //) {
        //    $errors['configchangeconfirm'] = get_string('youmustconfirm', 'qtype_shortmath');
        //}

        return $errors;
    }

    public function get_sup_sub_editor_option() {
        return null;
    }

    public function has_submitted_data() {
        return $this->submitted_data_array_not_empty('answer') || parent::has_submitted_data();
    }
}
