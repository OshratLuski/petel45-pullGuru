<?php
// This file is part of Moodle - https://moodle.org/
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
 * Question type class for the savpl question type.
 * @package    qtype_savpl
 * @copyright  Astor Bizard, 2019
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once(__DIR__.'/locallib.php');

use qtype_savpl\editor\vpl_editor_util;
/**
 * The savpl type class.
 * @copyright  Astor Bizard, 2019
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qtype_savpl extends question_type {

    /**
     * {@inheritDoc}
     * @see question_type::extra_question_fields()
     */
    public function extra_question_fields() {
        global $PAGE;

        $return = array("question_savpl",
            "templatelang",
            "templatecontext",
            "answertemplate",
            "teachercorrection",
            "validateonsave",
            "templatefilename",
            "execfiles",
            "precheckpreference",
            "precheckexecfiles",
            "gradingmethod",
            "disablerun",
            "disableevaluate",
            "trywithoutgrade",
            "aisupport",
            "aiteacherprompt",
            "ainumrequests"
        );

        if ($PAGE->has_set_url() && $PAGE->url->compare(new moodle_url('/mod/quiz/report.php'), URL_MATCH_BASE)) {

            $return = array("question_savpl",
                "templatelang",
                "templatecontext",
                "answertemplate",
                "teachercorrection",
                "validateonsave",
                "templatefilename",
                "precheckpreference",
                "gradingmethod",
                "disablerun",
                "aisupport",
                "aiteacherprompt",
                "ainumrequests",
                "disableevaluate",
                "trywithoutgrade"
            );
        }

        return $return;
    }

    /**
     * Saves question-type specific options
     *
     * This is called by {@link save_question()} to save the question-type specific data from a
     * submitted form. This method takes the form data and formats into the correct format for
     * writing to the database. It then calls the parent method to actually write the data.
     *
     * @param object $question  This holds the information from the editing form,
     *                          it is not a standard question object.
     * @return object $result->error or $result->noticeyesno or $result->notice
     */
    public function save_question_options($question) {

        if (!empty($question->oldparent)) {
            //since we have no ability to get execfiles from formdata - we just get it from old question
            $oldfiles = \qtype_savpl\editor\vpl_editor_util::get_files($question->oldparent, 'execfiles');
            \qtype_savpl\editor\vpl_editor_util::save_files($question->id, $oldfiles, 'execfiles');
        }

        $syscontext = \context_system::instance();

        if (!empty($question->importexecfiles)) {
            file_save_draft_area_files($question->importexecfiles, $syscontext->id,
                'qtype_savpl', 'execfiles', $question->id,
                array('subdirs' => 0, 'maxbytes' => 0));
        }

        if (!empty($question->importprecheckexecfiles)) {
            file_save_draft_area_files($question->importprecheckexecfiles, $syscontext->id,
                'qtype_savpl', 'precheckexecfiles', $question->id,
                array('subdirs' => 0, 'maxbytes' => 0));
        }

        parent::save_question_options($question);
    }

    public function export_to_xml($question, qformat_xml $format, $extra = null) {
        $fs = get_file_storage();
        $syscontext = \context_system::instance();
        $templatecontext = base64_encode($question->options->templatecontext);
        $teachercorrection = base64_encode($question->options->teachercorrection);
        $answertemplate = base64_encode($question->options->answertemplate);
        $output = "    <templatelang>{$question->options->templatelang}</templatelang>\n";
        $output .= "    <templatefilename>{$question->options->templatefilename}</templatefilename>\n";
        $output .= "    <templatecontext>{$templatecontext}</templatecontext>\n";
        $output .= "    <teachercorrection>{$teachercorrection}</teachercorrection>\n";
        $output .= "    <validateonsave>{$question->options->validateonsave}</validateonsave>\n";
        $output .= "    <answertemplate>{$answertemplate}</answertemplate>\n";
        $output .= "    <precheckpreference>{$question->options->precheckpreference}</precheckpreference>\n";
        $output .= "    <gradingmethod>{$question->options->gradingmethod}</gradingmethod>\n";
        $output .= "    <disablerun>{$question->options->disablerun}</disablerun>\n";
        $output .= "    <aisupport>{$question->options->aisupport}</aisupport>\n";
        $output .= "    <aiteacherprompt>{$question->options->aiteacherprompt}</aiteacherprompt>\n";
        $output .= "    <ainumrequests>{$question->options->ainumrequests}</ainumrequests>\n";
        $output .= "    <disableevaluate>{$question->options->disableevaluate}</disableevaluate>\n";
        $output .= "    <trywithoutgrade>{$question->options->trywithoutgrade}</trywithoutgrade>\n";


        $output .= '<execfiles>';

        $execfiles = vpl_editor_util::get_files($question->id, 'execfiles');
        $output .=  static::write_files($execfiles)."\n";
        $output .= '</execfiles><precheckexecfiles>';
        $precheckexecfiles = vpl_editor_util::get_files($question->id, 'precheckexecfiles');
        $output .= static::write_files($precheckexecfiles)."\n";
        $output .= '</precheckexecfiles>';

        return $output;
    }

    protected static function write_files($files) {
        if (empty($files)) {
            return '';
        }
        $string = '';
        foreach ($files as $filename => $filecontent) {
            $string .= '<file name="' . $filename . '" path="/" encoding="base64">';
            $string .= base64_encode($filecontent);
            $string .= "</file>\n";
        }
        return $string;
    }

    public function import_from_xml($data, $question, qformat_xml $format, $extra=null) {
        if (!isset($data['@']['type']) || $data['@']['type'] != 'savpl') {
            return false;
        }

        $question = $format->import_headers($data);
        $question->qtype = 'savpl';
        $question->templatelang = $format->getpath($data, array('#', 'templatelang', '0', '#'), '');
        $question->templatefilename = $format->getpath($data, array('#', 'templatefilename', '0', '#'), '');
        $question->templatecontext = base64_decode($format->getpath($data, array('#', 'templatecontext', '0', '#'), ''));
        $question->teachercorrection = base64_decode($format->getpath($data, array('#', 'teachercorrection', '0', '#'), ''));
        $question->validateonsave = $format->getpath($data, array('#', 'validateonsave', '0', '#'), 0);
        $question->answertemplate = base64_decode($format->getpath($data, array('#', 'answertemplate', '0', '#'), ''));
        $question->precheckpreference = $format->getpath($data, array('#', 'precheckpreference', '0', '#'), '');
        $question->gradingmethod = $format->getpath($data, array('#', 'gradingmethod', '0', '#'), 0);
        $question->disablerun = $format->getpath($data, array('#', 'disablerun', '0', '#'), 0);
        $question->aisupport = $format->getpath($data, array('#', 'aisupport', '0', '#'), 0);
        $question->ainumrequests = $format->getpath($data, array('#', 'ainumrequests', '0', '#'), 0);
        $question->aiteacherprompt = $format->getpath($data, array('#', 'aiteacherprompt', '0', '#'), 0);
        $question->disableevaluate = $format->getpath($data, array('#', 'disableevaluate', '0', '#'), 0);
        $question->trywithoutgrade = $format->getpath($data, array('#', 'trywithoutgrade', '0', '#'), 0);

        $execfilesxml = $format->getpath($data, array('#', 'execfiles', '0', '#', 'file'), array());
        $question->importexecfiles = $format->import_files_as_draft($execfilesxml);
        $precheckexecfilesxml = $format->getpath($data, array('#', 'precheckexecfiles', '0', '#', 'file'), array());
        $question->importprecheckexecfiles = $format->import_files_as_draft($precheckexecfilesxml);

        return $question;
    }
}