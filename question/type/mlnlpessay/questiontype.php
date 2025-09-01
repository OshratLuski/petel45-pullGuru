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
 * Question type class for the essay question type.
 *
 * @package    qtype
 * @subpackage essay
 * @copyright  2005 Mark Nielsen
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once $CFG->dirroot . '/question/type/mlnlpessay/locallib.php';
require_once $CFG->libdir . '/questionlib.php';

/**
 * The essay question type.
 *
 * @copyright  2005 Mark Nielsen
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qtype_mlnlpessay extends question_type {
    public function is_manual_graded() {
        return true;
    }

    public function response_file_areas() {
        return array('attachments', 'answer');
    }

    public function get_question_options($question) {
        global $DB;
        $question->options = $DB->get_record('qtype_mlnlpessay_options',
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

    public function save_question_options($question) {
        global $DB, $USER, $COURSE;

        $context = $question->context;
        $usercontext = \context_user::instance($USER->id);

        $options = $DB->get_record('qtype_mlnlpessay_options', array('questionid' => $question->id));
        if (!$options) {
            $options = new stdClass();
            $options->questionid = $question->id;
            $options->categoriesweightteacher = isset($question->categoriesweightteacher)?$question->categoriesweightteacher:null;
            $options->categoriesweight = isset($question->categoriesweight)?$question->categoriesweight:'';
            $options->timecreated = time();
            $options->timemodified = time();
            $options->id = $DB->insert_record('qtype_mlnlpessay_options', $options);
        }

        $options->responseformat = $question->responseformat;
        $options->responserequired = $question->responserequired;
        $options->responsefieldlines = $question->responsefieldlines;
        $options->minwordlimit = isset($question->minwordenabled) ? $question->minwordlimit : null;
        $options->maxwordlimit = isset($question->maxwordenabled) ? $question->maxwordlimit : null;
        $options->attachments = $question->attachments;
        if ((int)$question->attachments === 0 && $question->attachmentsrequired > 0) {
            // Adjust the value for the field 'attachmentsrequired' when the field 'attachments' is set to 'No'.
            $options->attachmentsrequired = 0;
        } else {
            $options->attachmentsrequired = $question->attachmentsrequired;
        }
        if (!isset($question->filetypeslist)) {
            $options->filetypeslist = null;
        } else {
            $options->filetypeslist = $question->filetypeslist;
        }
        $options->maxbytes = $question->maxbytes ?? 0;
        $options->graderinfo = $this->import_or_save_files($question->graderinfo,
                $context, 'qtype_mlnlpessay', 'graderinfo', $question->id);
        $options->graderinfoformat = $question->graderinfo['format'];
        $options->responsetemplate = $question->responsetemplate['text'];
        $options->responsetemplateformat = $question->responsetemplate['format'];

        $activecategories = get_config('qtype_mlnlpessay', 'numberofcategories');
        $choosen = [];

        $hascapedit = hascapedit($COURSE->id, $USER->id);

        if ($hascapedit) {
            foreach (json_decode($question->rubiccategoryfulltable) as $key => $value) {
                $choosen[$value->id] = [
                        'sortorder' => $key,
                        'id' => $value->id,
                        'name' => $value->name,
                        'weight' => isset($value->weight) ? $value->weight : null,
                        'type' => isset($value->type) ? $value->type : null,
                        'iscategoryselected' => isset($value->iscategoryselected) && $value->iscategoryselected ? 1 : 0,
                ];
            }
            if ($choosen) {
                $options->categoriesweight = json_encode($choosen, JSON_FORCE_OBJECT);
            } else if (!empty($options->categoriesweight)) {
                $options->categoriesweightteacher = $question->categoriesweightteacher;
                $options->categoriesweight = $question->categoriesweight;
            }
        }

        $options->timemodified = time();
        $DB->update_record('qtype_mlnlpessay_options', $options);
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

        $DB->delete_records('qtype_mlnlpessay_options', array('questionid' => $questionid));
        parent::delete_question($questionid, $contextid);
    }

    /**
     * @return array the different response formats that the question type supports.
     * internal name => human-readable name.
     */
    public function response_formats() {
        return array(
                'editor' => get_string('formateditor', 'qtype_mlnlpessay'),
                'editorfilepicker' => get_string('formateditorfilepicker', 'qtype_mlnlpessay'),
                'plain' => get_string('formatplain', 'qtype_mlnlpessay'),
                'monospaced' => get_string('formatmonospaced', 'qtype_mlnlpessay'),
                'noinline' => get_string('formatnoinline', 'qtype_mlnlpessay'),
        );
    }

    /**
     * @return array the choices that should be offerd when asking if a response is required
     */
    public function response_required_options() {
        return array(
                1 => get_string('responseisrequired', 'qtype_mlnlpessay'),
                0 => get_string('responsenotrequired', 'qtype_mlnlpessay'),
        );
    }

    /**
     * @return array the choices that should be offered for the input box size.
     */
    public function response_sizes() {
        $choices = [
            2 => get_string('nlines', 'qtype_essay', 2),
            3 => get_string('nlines', 'qtype_essay', 3),
        ];
        for ($lines = 5; $lines <= 40; $lines += 5) {
            $choices[$lines] = get_string('nlines', 'qtype_mlnlpessay', $lines);
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
                0 => get_string('attachmentsoptional', 'qtype_mlnlpessay'),
                1 => '1',
                2 => '2',
                3 => '3',
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
                $newcontextid, 'qtype_mlnlpessay', 'graderinfo', $questionid);
    }

    protected function delete_files($questionid, $contextid) {
        parent::delete_files($questionid, $contextid);
        $fs = get_file_storage();
        $fs->delete_area_files($contextid, 'qtype_mlnlpessay', 'graderinfo', $questionid);
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
        $expout .= "    <minwordlimit>" . $question->options->minwordlimit .
            "</minwordlimit>\n";
        $expout .= "    <maxwordlimit>" . $question->options->minwordlimit .
            "</maxwordlimit>\n";
        $expout .= "    <attachments>" . $question->options->attachments .
                "</attachments>\n";
        $expout .= "    <attachmentsrequired>" . $question->options->attachmentsrequired .
                "</attachmentsrequired>\n";
        $expout .= "    <filetypeslist>" . $question->options->filetypeslist .
                "</filetypeslist>\n";
        $expout .= "    <maxbytes>" . $question->options->maxbytes .
            "</maxbytes>\n";
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
        $qo->qtype = 'mlnlpessay';

        $qo->responseformat = $format->getpath($data,
                array('#', 'responseformat', 0, '#'), 'editor');
        $qo->responsefieldlines = $format->getpath($data,
                array('#', 'responsefieldlines', 0, '#'), 15);
        $qo->responsefieldlines = $format->getpath($data,
            array('#', 'responsefieldlines', 0, '#'), 15);
        $qo->minwordlimit = $format->getpath($data,
            array('#', 'minwordlimit', 0, '#'), 0);
        $qo->maxwordlimit = $format->getpath($data,
            array('#', 'maxwordlimit', 0, '#'), 0);
        $qo->maxbytes = $format->getpath($data,
            array('#', 'maxbytes', 0, '#'), 0);
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
