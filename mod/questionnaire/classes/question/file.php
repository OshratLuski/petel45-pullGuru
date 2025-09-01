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
 * This file contains the parent class for file question types.
 *
 * @author Mike Churchward
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package questiontypes
 */

namespace mod_questionnaire\question;

require_once($CFG->dirroot . '/repository/lib.php');
require_once($CFG->dirroot . '/lib/form/filemanager.php');

defined('MOODLE_INTERNAL') || die();
use \html_writer;

class file extends question {

    protected function responseclass() {
        return '\\mod_questionnaire\\responsetype\\file';
    }


    /**
     * Override and return a form template if provided. Output of question_survey_display is iterpreted based on this.
     * @return boolean | string
     */
    public function question_template() {
        return 'mod_questionnaire/question_file';
    }

    public function helpname() {
        return 'file';
    }

    /**
     * @param \mod_questionnaire\responsetype\response\response $response
     * @param $descendantsdata
     * @param bool $blankquestionnaire
     * @return object|string
     */
    protected function question_survey_display($response, $descendantsdata, $blankquestionnaire=false) {
        global $PAGE;

        $questiontags = new \stdClass();
        $questiontags->qelements = new \stdClass();
        $choice = new \stdClass();
        $choice->onkeypress = 'return event.keyCode != 13;';
        $choice->name = 'q'.$this->id;
        $choice->id = self::qtypename($this->type_id) . $this->id;
        $choice->rid = $this->id;
        $options = new \stdClass();
//        $options->maxfiles = $this->length;
        $options->maxfiles = 1;
        $options->maxbytes = 20971520;
        $options->itemid = $this->id.time();
        $options->accepted_types = $this->get_format($this->precise);
        $options->return_types = FILE_INTERNAL | FILE_CONTROLLED_LINK;
        $fm = new \form_filemanager($options);
        $output = $PAGE->get_renderer('core', 'files');
        $choice->file_manager = $output->render($fm);
        $choice->file_itemid = $options->itemid;
        $questiontags->qelements->choice = $choice;
        return $questiontags;
    }

    /**
     * @param \mod_questionnaire\responsetype\response\response $response
     * @return object|string
     */
    protected function response_survey_display($response) {
        //$output = '';
        //$output .= '<div class="response file">';
        //$output .= !empty($data->{'q'.$this->id}) ? format_text($response->{'q'.$this->id}, FORMAT_HTML) : '&nbsp;';
        //$output .= '</div>';
        //return $output;

        $output = '';
        if ($rows = $this->get_results($response->id, false)) {
            // Count identical answers (numeric questions only).
            foreach ($rows as $row) {
                if (!empty($row->response) || $row->response === "0") {
                    $url = \moodle_url::make_pluginfile_url($row->contextid, $row->component, $row->filearea, $row->itemid, '/', $row->filename, false);
                    if (strpos('image/png, image/jpg, image/gif', $row->mimetype) !== false) {
                        $output .= '<div class="qtypefile"><img class="img-responsive" src="'.$url->out().'" alt="'.$row->filename.'"></div>';
                    } else {
                        $output .= '<div class="qtypefile"><a target="_blank" href="'.$url->out().'">'.$row->filename.'</a></div>';
                    }
                }
            }
        } else {
            $output .= '<p class="generaltable">&nbsp;'.get_string('noresponsedata', 'questionnaire').'</p>';
        }
        return $output;
    }



    // Note - intentianally returning 'precise' for length and 'length' for precise.
    /**
     * @param \MoodleQuickForm $mform
     * @param string $helptext
     * @return \MoodleQuickForm|void
     * @throws \coding_exception
     */
    protected function form_length(\MoodleQuickForm $mform, $helptext = '') {
        $responseformats = array(
            "0" => get_string('text_format','questionnaire'),
            "1" => get_string('pic_format','questionnaire'),
            "2" => get_string('pdf_format','questionnaire'));
        $mform->addElement('select', 'precise', get_string('format','questionnaire'), $responseformats);
        $mform->setType('precise', PARAM_INT);

        return $mform;

    }

    /**
     * True if question provides mobile support.
     *
     * @return bool
     */
    public function supports_mobile() {
        return false;
    }

    /**
     * @param \MoodleQuickForm $mform
     * @param string $helptext
     * @return \MoodleQuickForm|void
     * @throws \coding_exception
     */
    protected function form_precise(\MoodleQuickForm $mform, $helptext = '') {
//        $responseformats = array(
//            "1" => "1",
//            "2" => "2",
//            "3" => "3");
//        $mform->addElement('select', 'length', get_string('max_file','questionnaire'), $responseformats);
//        $mform->setType('length', PARAM_INT);


        return $mform;
    }

    private function get_format($format) {
        switch ($format){
            case 0:
                return array("application/vnd.openxmlformats-officedocument.wordprocessingml.document");
            case 1:
                return array("image/png","image/jpeg", "image/gif");
            case 2:
                return  array("application/pdf");
            default:
                throw new \Moodle_exception('invalidformatpara','error', '', '',
                            'Unexpected mod/questionnaire file qtype format value');
        }
    }
}
