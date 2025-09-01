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
 * Question type class for the opensheet question type.
 *
 * @package    qtype
 * @subpackage opensheet
 * @copyright  2005 Mark Nielsen
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/questionlib.php');


/**
 * The opensheet question type.
 *
 * @copyright  2005 Mark Nielsen
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qtype_opensheet extends question_type {
    public function is_manual_graded() {
        return true;
    }

    public function response_file_areas() {
        return array('attachments', 'answer');
    }

    public function get_question_options($question) {
        global $DB;
        $question->options = $DB->get_record('qtype_opensheet_options',
                array('questionid' => $question->id), '*', MUST_EXIST);
        parent::get_question_options($question);
    }

    public function save_question_options($formdata) {
        global $DB;
        $context = $formdata->context;

        $options = $DB->get_record('qtype_opensheet_options', array('questionid' => $formdata->id));
        if (!$options) {
            $options = new stdClass();
            $options->questionid = $formdata->id;
            $options->id = $DB->insert_record('qtype_opensheet_options', $options);
        }

        $options->responseformat = $formdata->responseformat;
        $options->responserequired = $formdata->responserequired;
        $options->responsefieldlines = $formdata->responsefieldlines;
        $options->attachments = $formdata->attachments;
        $options->attachmentsrequired = $formdata->attachmentsrequired;
        if (!isset($formdata->filetypeslist)) {
            $options->filetypeslist = null;
        } else {
            $options->filetypeslist = $formdata->filetypeslist;
        }
        $options->graderinfo = $this->import_or_save_files($formdata->graderinfo,
                $context, 'qtype_opensheet', 'graderinfo', $formdata->id);
        $options->graderinfoformat = $formdata->graderinfo['format'];
        $options->responsetemplate = $formdata->responsetemplate['text'];
        $options->responsetemplateformat = $formdata->responsetemplate['format'];
        $DB->update_record('qtype_opensheet_options', $options);
    }

    protected function initialise_question_instance(question_definition $question, $questiondata) {
        parent::initialise_question_instance($question, $questiondata);
        $question->responseformat = $questiondata->options->responseformat;
        $question->responserequired = $questiondata->options->responserequired;
        $question->responsefieldlines = $questiondata->options->responsefieldlines;
        $question->attachments = $questiondata->options->attachments;
        $question->attachmentsrequired = $questiondata->options->attachmentsrequired;
        $question->graderinfo = $questiondata->options->graderinfo;
        $question->graderinfoformat = $questiondata->options->graderinfoformat;
        $question->responsetemplate = $questiondata->options->responsetemplate;
        $question->responsetemplateformat = $questiondata->options->responsetemplateformat;
        $filetypesutil = new \core_form\filetypes_util();
        $question->filetypeslist = $filetypesutil->normalize_file_types($questiondata->options->filetypeslist);
    }

    public function delete_question($questionid, $contextid) {
        global $DB;

        $DB->delete_records('qtype_opensheet_options', array('questionid' => $questionid));
        parent::delete_question($questionid, $contextid);
    }

    /**
     * @return array the different response formats that the question type supports.
     * internal name => human-readable name.
     */
    public function response_formats() {
        return array(
            'editor' => get_string('formateditor', 'qtype_opensheet'),
            'editorfilepicker' => get_string('formateditorfilepicker', 'qtype_opensheet'),
            'plain' => get_string('formatplain', 'qtype_opensheet'),
            'monospaced' => get_string('formatmonospaced', 'qtype_opensheet'),
            'noinline' => get_string('formatnoinline', 'qtype_opensheet'),
        );
    }

    /**
     * @return array the choices that should be offerd when asking if a response is required
     */
    public function response_required_options() {
        return array(
            1 => get_string('responseisrequired', 'qtype_opensheet'),
            0 => get_string('responsenotrequired', 'qtype_opensheet'),
        );
    }

    /**
     * @return array the choices that should be offered for the input box size.
     */
    public function response_sizes() {
        $choices = array();
        for ($lines = 5; $lines <= 40; $lines += 5) {
            $choices[$lines] = get_string('nlines', 'qtype_opensheet', $lines);
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
            0 => get_string('attachmentsoptional', 'qtype_opensheet'),
            1 => '1',
            2 => '2',
            3 => '3'
        );
    }

    public function move_files($questionid, $oldcontextid, $newcontextid) {
        parent::move_files($questionid, $oldcontextid, $newcontextid);
        $fs = get_file_storage();
        $fs->move_area_files_to_new_context($oldcontextid,
                $newcontextid, 'qtype_opensheet', 'graderinfo', $questionid);
    }

    protected function delete_files($questionid, $contextid) {
        parent::delete_files($questionid, $contextid);
        $fs = get_file_storage();
        $fs->delete_area_files($contextid, 'qtype_opensheet', 'graderinfo', $questionid);
    }

    public function export_to_xml($question, qformat_xml $format, $extra = null) {

        $contextid = $question->contextid;
        $fs = get_file_storage();
        $expout='';
        $expout .= "    <responseformat>" . $question->options->responseformat .
            "</responseformat>\n";
        $expout .= "    <responserequired>" . $question->options->responserequired .
            "</responserequired>\n";
        $expout .= "    <responsefieldlines>" . $question->options->responsefieldlines .
            "</responsefieldlines>\n";
        $expout .= "    <attachments>" . $question->options->attachments .
            "</attachments>\n";
        $expout .= "    <attachmentsrequired>" . $question->options->attachmentsrequired .
            "</attachmentsrequired>\n";
        $expout .= "    <filetypeslist>" . $question->options->filetypeslist .
            "</filetypeslist>\n";
        $expout .= "    <graderinfo " .
            $format->format($question->options->graderinfoformat) . ">\n";
        $expout .= $format->writetext($question->options->graderinfo, 3);
        $expout .= $format->write_files($fs->get_area_files($contextid, 'qtype_mlnlpessay',
            'graderinfo', $question->id));
        $expout .= "    </graderinfo>\n";
        $expout .= "    <responsetemplate " .
            $format->format($question->options->responsetemplateformat) . ">\n";
        $expout .= $format->writetext($question->options->responsetemplate, 3);
        $expout .= "    </responsetemplate>\n";
        $expout .= "    <categoriesweightteacher>" . $question->options->categoriesweightteacher;
        $expout .= "    </categoriesweightteacher>\n";
        $expout .= "    <categoriesweight>" . $question->options->categoriesweight;
        $expout .= "    </categoriesweight>\n";

        return $expout;
    }

    public function import_from_xml($data, $question, qformat_xml $format, $extra = null) {

        // Get common parts.
        $qo = $format->import_headers($data);

        // Header parts particular to essay.
        $qo->qtype = 'opensheet';

        $qo->responseformat = $format->getpath($data,
            array('#', 'responseformat', 0, '#'), 'editor');
        $qo->responsefieldlines = $format->getpath($data,
            array('#', 'responsefieldlines', 0, '#'), 15);
        $qo->responserequired = $format->getpath($data,
            array('#', 'responserequired', 0, '#'), 1);
        $qo->attachments = $format->getpath($data,
            array('#', 'attachments', 0, '#'), 0);
        $qo->attachmentsrequired = $format->getpath($data,
            array('#', 'attachmentsrequired', 0, '#'), 0);
        $qo->filetypeslist = $format->getpath($data,
            array('#', 'filetypeslist', 0, '#'), null);
        $qo->graderinfo = $format->import_text_with_files($data,
            array('#', 'graderinfo', 0), '', $format->get_format($qo->questiontextformat));
        $qo->responsetemplate['text'] = $format->getpath($data,
            array('#', 'responsetemplate', 0, '#', 'text', 0, '#'), '', true);
        $qo->responsetemplate['format'] = $format->trans_format($format->getpath($data,
            array('#', 'responsetemplate', 0, '@', 'format'), $format->get_format($qo->questiontextformat)));
        $qo->categoriesweightteacher = $format->getpath($data,
            array('#', 'categoriesweightteacher', 0, '#'), '');
        $qo->categoriesweight = $format->getpath($data,
            array('#', 'categoriesweight', 0, '#'), '');
        return $qo;
    }

}
