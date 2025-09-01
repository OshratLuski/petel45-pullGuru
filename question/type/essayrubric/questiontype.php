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
 * Question type class for the essayrubric question type.
 *
 * @package    qtype
 * @subpackage essayrubric
 * @copyright  2005 Mark Nielsen
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once ($CFG->libdir . '/questionlib.php');
require_once ($CFG->dirroot . '/question/type/essayrubric/locallib.php');

/**
 * The essayrubric question type.
 *
 * @copyright  2005 Mark Nielsen
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qtype_essayrubric extends question_type {
    public function is_manual_graded() {
        return true;
    }

    public function response_file_areas() {
        return array('attachments', 'answer');
    }

    public function get_question_options($question) {
        global $DB;
        $question->options = $DB->get_record('qtype_essayrubric_options',
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
        global $DB;
        $context = $question->context;

        $options = $DB->get_record('qtype_essayrubric_options', array('questionid' => $question->id));
        if (!$options) {
            $options = new stdClass();
            $options->questionid = $question->id;
            $options->id = $DB->insert_record('qtype_essayrubric_options', $options);
        }

        $options->responseformat = $question->responseformat;
        $options->responserequired = $question->responserequired;
        $options->responsefieldlines = $question->responsefieldlines;
        $options->minwordlimit = isset($question->minwordenabled) ? $question->minwordlimit : null;
        $options->maxwordlimit = isset($question->maxwordenabled) ? $question->maxwordlimit : null;
        $options->attachments = $question->attachments;
        if ((int) $question->attachments === 0 && $question->attachmentsrequired > 0) {
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
        // $options->graderinfo = $this->import_or_save_files($question->graderinfo,
        //     $context, 'qtype_essayrubric', 'graderinfo', $question->id);
        // $options->graderinfoformat = $question->graderinfo['format'];
        $options->responsetemplate = $question->responsetemplate['text'];
        $options->responsetemplateformat = $question->responsetemplate['format'] ?? FORMAT_HTML;

        if (isset($question->indicators)) {
            // $indicatorsoptions = json_decode($question->indicators);
            $indicatorsoptions = $question->indicators;
        } else {
            // Parse questionindicatorfulltable.
            $isgradestypescalar = $question->weightstyle;
            $researchquestion = $question->researchquestion;
            $indicatorsoptions = qtype_essayrubric_prepare_ind_options_json($isgradestypescalar, $question->questionindicatorfulltable, $researchquestion);
        }

        // $indicatorsoptions = new stdClass();
        // $indicatorsoptions->isgradestypescalar = $isgradestypescalar;
        // $indicatorsoptions->indicatorlist = json_decode($question->questionindicatorfulltable);
        // $options->indicators = json_encode($indicatorsoptions);

        $options->indicators = $indicatorsoptions;

        // PTL_7328 Save Allowcheck option co config, w/o addind new field to 'qtype_essayrubric_options'.
        set_config('allowcheck_' . $question->id, $question->allowcheck, 'qtype_essayrubric');

        $DB->update_record('qtype_essayrubric_options', $options);
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

        $DB->delete_records('qtype_essayrubric_options', array('questionid' => $questionid));
        parent::delete_question($questionid, $contextid);
    }

    /**
     * @return array the different response formats that the question type supports.
     * internal name => human-readable name.
     */
    public function response_formats() {
        return array(
            'editor' => get_string('formateditor', 'qtype_essayrubric'),
            'editorfilepicker' => get_string('formateditorfilepicker', 'qtype_essayrubric'),
            'plain' => get_string('formatplain', 'qtype_essayrubric'),
            'monospaced' => get_string('formatmonospaced', 'qtype_essayrubric'),
            'noinline' => get_string('formatnoinline', 'qtype_essayrubric'),
        );
    }

    /**
     * @return array the choices that should be offerd when asking if a response is required
     */
    public function response_required_options() {
        return array(
            1 => get_string('responseisrequired', 'qtype_essayrubric'),
            0 => get_string('responsenotrequired', 'qtype_essayrubric'),
        );
    }

    /**
     * @return array the choices that should be offered for the input box size.
     */
    public function response_sizes() {
        $choices = [
            2 => get_string('nlines', 'qtype_essayrubric', 2),
            3 => get_string('nlines', 'qtype_essayrubric', 3),
        ];
        for ($lines = 5; $lines <= 40; $lines += 5) {
            $choices[$lines] = get_string('nlines', 'qtype_essayrubric', $lines);
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
            0 => get_string('attachmentsoptional', 'qtype_essayrubric'),
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
            $newcontextid, 'qtype_essayrubric', 'graderinfo', $questionid);
    }

    protected function delete_files($questionid, $contextid) {
        parent::delete_files($questionid, $contextid);
        $fs = get_file_storage();
        $fs->delete_area_files($contextid, 'qtype_essayrubric', 'graderinfo', $questionid);
    }

    public function extra_question_fields() {
        return array('qtype_essayrubric_options',
            'responseformat',
            'responserequired',
            'responsefieldlines',
            'attachments',
            'attachmentsrequired',
            'indicators',
        );
    }

}
