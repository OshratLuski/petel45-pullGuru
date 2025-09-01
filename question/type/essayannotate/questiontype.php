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
 * Question type class for the essayannotate question type.
 *
 * @package    qtype_essayannotate
 * @copyright  2024 IIT Palakkad
 * @copyright  based on work done by 2005 Mark Nielsen
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * updatedby   Nideesh N, VM Sreeram
 * Added functions for importing and exporting in XML format.
 */


defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/questionlib.php');


/**
 * The essayannotate question type.
 *
 * @copyright  2005 Mark Nielsen
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qtype_essayannotate extends question_type {
    public function is_manual_graded() {
        return true;
    }

    /**
     * Extra fields to add to the question table.
     * This function overrides the function with the same name in question/type/questiontypebase.php.
     */
    public function extra_question_fields() {
        return array($this->plugin_name().'_options', // DB table name
                     'responseformat', 'responserequired', 'responsefieldlines',
                     'attachments', 'attachmentsrequired',
                     'graderinfo', 'graderinfoformat',
                     'responsetemplate', 'responsetemplateformat',
                     'minwordlimit', 'maxwordlimit',
                     'maxbytes', 'filetypeslist');
    }

    public function response_file_areas() {
        return array('attachments', 'answer');
    }

    public function get_question_options($question) {
        global $DB;
        $question->options = $DB->get_record('qtype_essayannotate_options',
                array('questionid' => $question->id), '*', MUST_EXIST);
        parent::get_question_options($question);
    }

    public function save_defaults_for_new_questions(stdClass $fromform): void {
        parent::save_defaults_for_new_questions($fromform);
        $this->set_default_value('responseformat', $fromform->responseformat);
        $this->set_default_value('responserequired', $fromform->responserequired);
        $this->set_default_value('responsefieldlines', $fromform->responsefieldlines);
        $this->set_default_value('attachments', $fromform->attachments);
        $this->set_default_value('attachmentsrequired', $fromform->attachmentsrequired);
        $this->set_default_value('maxbytes', $fromform->maxbytes);
    }

    public function save_question_options($formdata) {
        global $DB;
        $context = $formdata->context;

        $options = $DB->get_record('qtype_essayannotate_options', array('questionid' => $formdata->id));
        if (!$options) {
            $options = new stdClass();
            $options->questionid = $formdata->id;
            $options->id = $DB->insert_record('qtype_essayannotate_options', $options);
        }

        $options->responseformat = $formdata->responseformat;
        $options->responserequired = $formdata->responserequired;
        $options->responsefieldlines = $formdata->responsefieldlines;
        $options->minwordlimit = isset($formdata->minwordenabled) ? $formdata->minwordlimit : null;
        $options->maxwordlimit = isset($formdata->maxwordenabled) ? $formdata->maxwordlimit : null;
        $options->attachments = $formdata->attachments;
        if ((int)$formdata->attachments === 0 && $formdata->attachmentsrequired > 0) {
            // Adjust the value for the field 'attachmentsrequired' when the field 'attachments' is set to 'No'.
            $options->attachmentsrequired = 0;
        } else {
            $options->attachmentsrequired = $formdata->attachmentsrequired;
        }
        if (!isset($formdata->filetypeslist)) {
            $options->filetypeslist = null;
        } else {
            $options->filetypeslist = $formdata->filetypeslist;
        }
        $options->maxbytes = $formdata->maxbytes ?? 0;
        $options->graderinfo = $this->import_or_save_files($formdata->graderinfo,
                $context, 'qtype_essayannotate', 'graderinfo', $formdata->id);
        $options->graderinfoformat = $formdata->graderinfo['format'];
        $options->responsetemplate = $formdata->responsetemplate['text'];
        $options->responsetemplateformat = $formdata->responsetemplate['format'];
        $DB->update_record('qtype_essayannotate_options', $options);
    }

    protected function initialise_question_instance(question_definition $question, $questiondata) {
        parent::initialise_question_instance($question, $questiondata);
        $question->responseformat = $questiondata->options->responseformat;
        $question->responserequired = $questiondata->options->responserequired;
        $question->responsefieldlines = $questiondata->options->responsefieldlines;
        $question->minwordlimit = $questiondata->options->minwordlimit;
        $question->maxwordlimit = $questiondata->options->maxwordlimit;
        $question->attachments = $questiondata->options->attachments;
        $question->attachmentsrequired = $questiondata->options->attachmentsrequired;
        $question->graderinfo = $questiondata->options->graderinfo;
        $question->graderinfoformat = $questiondata->options->graderinfoformat;
        $question->responsetemplate = $questiondata->options->responsetemplate;
        $question->responsetemplateformat = $questiondata->options->responsetemplateformat;
        $filetypesutil = new \core_form\filetypes_util();
        $question->filetypeslist = $filetypesutil->normalize_file_types($questiondata->options->filetypeslist);
        $question->maxbytes = $questiondata->options->maxbytes;
    }

    public function delete_question($questionid, $contextid) {
        global $DB;

        $DB->delete_records('qtype_essayannotate_options', array('questionid' => $questionid));
        parent::delete_question($questionid, $contextid);
    }

    /**
     * @return array the different response formats that the question type supports.
     * internal name => human-readable name.
     */
    public function response_formats() {
        return array(
            'editor' => get_string('formateditor', 'qtype_essayannotate'),
            'editorfilepicker' => get_string('formateditorfilepicker', 'qtype_essayannotate'),
            'plain' => get_string('formatplain', 'qtype_essayannotate'),
            'monospaced' => get_string('formatmonospaced', 'qtype_essayannotate'),
            'noinline' => get_string('formatnoinline', 'qtype_essayannotate'),
        );
    }

    /**
     * @return array the choices that should be offerd when asking if a response is required
     */
    public function response_required_options() {
        return array(
            1 => get_string('responseisrequired', 'qtype_essayannotate'),
            0 => get_string('responsenotrequired', 'qtype_essayannotate'),
        );
    }

    /**
     * @return array the choices that should be offered for the input box size.
     */
    public function response_sizes() {
        $choices = [
            2 => get_string('nlines', 'qtype_essayannotate', 2),
            3 => get_string('nlines', 'qtype_essayannotate', 3),
        ];
        for ($lines = 5; $lines <= 40; $lines += 5) {
            $choices[$lines] = get_string('nlines', 'qtype_essayannotate', $lines);
        }
        return $choices;
    }

    /**
     * @return array the choices that should be offered for the number of attachments.
     */
    public function attachment_options() {
        return array(
            0 => get_string('no'),
            1 => '1',
            2 => '2',
            3 => '3',
            -1 => get_string('unlimited'),
        );
    }

    /**
     * @return array the choices that should be offered for the number of required attachments.
     */
    public function attachments_required_options() {
        return array(
            0 => get_string('attachmentsoptional', 'qtype_essayannotate'),
            1 => '1',
            2 => '2',
            3 => '3'
        );
    }

    /**
     * Return array of the choices that should be offered for the maximum file sizes.
     * @return array|lang_string[]|string[]
     */
    public function max_file_size_options() {
        global $CFG, $COURSE;
        return get_max_upload_sizes($CFG->maxbytes, $COURSE->maxbytes);
    }

    public function move_files($questionid, $oldcontextid, $newcontextid) {
        parent::move_files($questionid, $oldcontextid, $newcontextid);
        $fs = get_file_storage();
        $fs->move_area_files_to_new_context($oldcontextid,
                $newcontextid, 'qtype_essayannotate', 'graderinfo', $questionid);
    }

    protected function delete_files($questionid, $contextid) {
        parent::delete_files($questionid, $contextid);
        $fs = get_file_storage();
        $fs->delete_area_files($contextid, 'qtype_essayannotate', 'graderinfo', $questionid);
    }

    /**
     * Imports question from the Moodle XML format
     *
     * @param array $data
     * @param qtype_essayannotate $question (or null)
     * @param qformat_xml $format
     * @param string $extra (optional, default=null)
     * @return object New question object
     *
     * Modified from import_essay function of question/format/xml/format.php
     */
    public function import_from_xml($data, $question, qformat_xml $format, $extra=null) {
        $questiontype = $format->getpath($data, ['@', 'type'], '');
        if ($questiontype != 'essayannotate') {
            return false;
        }

        $qo = $format->import_headers($data);
        $qo->qtype = $questiontype;

        $qo->responseformat = $format->getpath($data,
                ['#', 'responseformat', 0, '#'], 'editor');
        $qo->responsefieldlines = $format->getpath($data,
                ['#', 'responsefieldlines', 0, '#'], 15);
        $qo->responserequired = $format->getpath($data,
                ['#', 'responserequired', 0, '#'], 1);
        $qo->minwordlimit = $format->getpath($data,
                ['#', 'minwordlimit', 0, '#'], null);
        $qo->minwordenabled = !empty($qo->minwordlimit);
        $qo->maxwordlimit = $format->getpath($data,
                ['#', 'maxwordlimit', 0, '#'], null);
        $qo->maxwordenabled = !empty($qo->maxwordlimit);
        $qo->attachments = $format->getpath($data,
                ['#', 'attachments', 0, '#'], 0);
        $qo->attachmentsrequired = $format->getpath($data,
                ['#', 'attachmentsrequired', 0, '#'], 0);
        $qo->filetypeslist = $format->getpath($data,
                ['#', 'filetypeslist', 0, '#'], null);
        $qo->maxbytes = $format->getpath($data,
                ['#', 'maxbytes', 0, '#'], null);
        $qo->graderinfo = $format->import_text_with_files($data,
                ['#', 'graderinfo', 0], '', $format->get_format($qo->questiontextformat));
        $qo->responsetemplate['text'] = $format->getpath($data,
                ['#', 'responsetemplate', 0, '#', 'text', 0, '#'], '', true);
        $qo->responsetemplate['format'] = $format->trans_format($format->getpath($data,
                ['#', 'responsetemplate', 0, '@', 'format'], $format->get_format($qo->questiontextformat)));
        return $qo;
    }

    /**
     * Exports question to XML format
     *
     * @param object $question
     * @param qformat_xml $format
     * @param string $extra (optional, default=null)
     * @return string XML representation of question
     *
     * Modified from export_to_xml function of question/type/questiontypebase.php.
     */
    public function export_to_xml($question, qformat_xml $format, $extra=null) {
        $extraquestionfields = $this->extra_question_fields();
        if (!is_array($extraquestionfields)) {
            return false;
        }

        // Omit table name.
        array_shift($extraquestionfields);
        $expout = '';
        foreach ($extraquestionfields as $field) {
            $exportedvalue = $format->xml_escape($question->options->$field);
            $expout .= "    <{$field}>{$exportedvalue}</{$field}>\n";
        }

        $extraanswersfields = $this->extra_answer_fields();
        if (is_array($extraanswersfields)) {
            array_shift($extraanswersfields);
        }
        foreach ($question->options->answers as $answer) {
            $extra = '';
            if (is_array($extraanswersfields)) {
                foreach ($extraanswersfields as $field) {
                    $exportedvalue = $format->xml_escape($answer->$field);
                    $extra .= "      <{$field}>{$exportedvalue}</{$field}>\n";
                }
            }

            $expout .= $format->write_answer($answer, $extra);
        }
        return $expout;
    }
}
