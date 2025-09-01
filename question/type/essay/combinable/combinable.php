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
 * @package    qtype_essay
 * @copyright  2019 Jean-Michel Vedrine
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

class qtype_combined_combinable_type_essay extends qtype_combined_combinable_type_base {

    protected $identifier = 'essay';

    protected function extra_question_properties() {
        return array('answerprefix' => '', 'allowedfuncs' => array('all' => 1), 'answer' => [],
        );
    }

    protected function extra_answer_properties() {
        return array();

    }

    public function subq_form_fragment_question_option_fields() {
        return array(
            'responseformat'      => null,
            'responserequired'    => null,
            'responsefieldlines'  => null,
            'attachments'         => null,
            'attachmentsrequired' => null,
            'filetypeslist'       => null,
            'responsetemplate'    => null,
            'graderinfo'          => null,
        );
    }
}

class qtype_combined_combinable_essay extends qtype_combined_combinable_text_entry {

    /**
     * @param moodleform      $combinedform
     * @param MoodleQuickForm $mform
     * @param                 $repeatenabled
     * @return mixed
     */
    public function add_form_fragment(moodleform $combinedform, MoodleQuickForm $mform, $repeatenabled) {
        global $CFG;

        $qtype = question_bank::get_qtype('essay');

        $formats = $qtype->response_formats();
        unset($formats['noinline']);

        $mform->addElement('select', $this->form_field_name('responseformat'),
            get_string('responseformat', 'qtype_essay'), $formats);

        $responseformat = (!empty($CFG->qtype_essay_responseformat)) ? $CFG->qtype_essay_responseformat : 'editor';
        $mform->setDefault($this->form_field_name('responseformat'), $responseformat);

        $mform->addElement('select', $this->form_field_name('responserequired'),
            get_string('responserequired', 'qtype_essay'), $qtype->response_required_options());
        $mform->setDefault($this->form_field_name('responserequired'), 1);
        $mform->disabledIf($this->form_field_name('responserequired'), 'responseformat', 'eq', 'noinline');

        $mform->addElement('select', $this->form_field_name('responsefieldlines'),
            get_string('responsefieldlines', 'qtype_essay'), $qtype->response_sizes());
        $mform->setDefault($this->form_field_name('responsefieldlines'), 15);
        $mform->disabledIf($this->form_field_name('responsefieldlines'), 'responseformat', 'eq', 'noinline');

        // $mform->addElement('select', $this->form_field_name('attachments'),
        //     get_string('allowattachments', 'qtype_essay'), $qtype->attachment_options());

        $mform->addElement('hidden', $this->form_field_name('attachments'),
            get_string('allowattachments', 'qtype_essay'));
        $mform->setType($this->form_field_name('attachments'), PARAM_INT);
        $mform->setDefault($this->form_field_name('attachments'), 0);

        // $attachments = (!empty($CFG->qtype_essay_attachments)) ? $CFG->qtype_essay_attachments : 0;
        // $mform->setDefault($this->form_field_name('attachments'), $attachments);

        $mform->addElement('hidden', $this->form_field_name('attachmentsrequired'),
            get_string('allowattachments', 'qtype_essay'));
        $mform->setType($this->form_field_name('attachmentsrequired'), PARAM_INT);
        $mform->setDefault($this->form_field_name('attachmentsrequired'), 0);

        // $mform->addElement('select', $this->form_field_name('attachmentsrequired'),
        //     get_string('attachmentsrequired', 'qtype_essay'), $qtype->attachments_required_options());
        // $mform->setDefault($this->form_field_name('attachmentsrequired'), 0);
        // $mform->addHelpButton($this->form_field_name('attachmentsrequired'), $this->form_field_name('attachmentsrequired'), 'qtype_essay');
        // $mform->disabledIf($this->form_field_name('attachmentsrequired'), $this->form_field_name('attachments'), 'eq', 0);

        // $mform->addElement('filetypes', $this->form_field_name('filetypeslist'), get_string('acceptedfiletypes', 'qtype_essay'));
        // $mform->addHelpButton($this->form_field_name('filetypeslist'), $this->form_field_name('acceptedfiletypes'), 'qtype_essay');
        // $mform->disabledIf($this->form_field_name('filetypeslist'), $this->form_field_name('attachments'), 'eq', 0);

        $mform->addElement('editor', $this->form_field_name('responsetemplate'), get_string('responsetemplate', 'qtype_essay'),
            array('rows' => 10), $combinedform->editoroptions);
        $mform->addHelpButton($this->form_field_name('responsetemplate'), 'responsetemplate', 'qtype_essay');

        $mform->addElement('editor', $this->form_field_name('graderinfo'), get_string('graderinfo', 'qtype_essay'),
            array('rows' => 10), $combinedform->editoroptions);
    }

    public function data_to_form($context, $fileoptions) {
        $data = parent::data_to_form($context, $fileoptions);

        $options = [];

        $options['responseformat']      = $this->questionrec->options->responseformat;
        $options['responserequired']    = $this->questionrec->options->responserequired;
        $options['responsefieldlines']  = $this->questionrec->options->responsefieldlines;
        // $options['attachments']         = $this->questionrec->options->attachments;
        // $options['attachmentsrequired'] = $this->questionrec->options->attachmentsrequired;
        $options['filetypeslist']       = $this->questionrec->options->filetypeslist;

        $draftid                       = file_get_submitted_draft_itemid('graderinfo');
        $options['graderinfo']         = array();
        $options['graderinfo']['text'] = file_prepare_draft_area(
            $draftid, // Draftid
            $context->id, // context
            'qtype_essay', // component
            'graderinfo', // filarea
            !empty($this->questionrec->id) ? (int) $this->questionrec->id : null, // itemid
            $this->fileoptions ?? null, // options
            $this->questionrec->options->graderinfo// text.
        );
        $options['graderinfo']['format'] = $this->questionrec->options->graderinfoformat;
        $options['graderinfo']['itemid'] = $draftid;

        $options['responsetemplate']['text']   = $this->questionrec->options->responsetemplate;
        $options['responsetemplate']['format'] = $this->questionrec->options->responsetemplateformat;

        return $options + $data;
    }

    public function validate() {
        $errors = array();
        $data   = (array) $this->formdata;

        // if ($data['responseformat'] == 'noinline' && !$data['attachments']) {
        //     $errors['attachments'] = get_string('mustattach', 'qtype_essay');
        // }

        // If 'no inline response' is set, force the teacher to require attachments;
        // otherwise there will be nothing to grade.
        // if ($data['responseformat'] == 'noinline' && !$data['attachmentsrequired']) {
        //     $errors['attachmentsrequired'] = get_string('mustrequire', 'qtype_essay');
        // }

        // Don't allow the teacher to require more attachments than they allow; as this would
        // create a condition that it's impossible for the student to meet.
        // if ($data['attachments'] != -1 && $data['attachments'] < $data['attachmentsrequired']) {
        //     $errors['attachmentsrequired'] = get_string('mustrequirefewer', 'qtype_essay');
        // }

        return $errors;
    }

    public function get_sup_sub_editor_option() {
        return null;
    }

    public function has_submitted_data() {

        return true;

        //return $this->submitted_data_array_not_empty('answer') || parent::has_submitted_data();
    }
}
