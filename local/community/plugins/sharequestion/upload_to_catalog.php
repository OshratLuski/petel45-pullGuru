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
 * A form for the creation and editing of groups.
 *
 * @package     community_sharequestion
 * @copyright   2019 Devlion <info@devlion.co>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

require_once($CFG->dirroot . '/lib/formslib.php');
require_once($CFG->dirroot . '/local/community/plugins/sharequestion/classes/sharequestion.php');

/**
 * Group form class
 *
 * @copyright 2006 The Open University, N.D.Freear AT open.ac.uk, J.White AT open.ac.uk
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @package   community_sharequestion
 */
class upload_to_catalog extends moodleform {

    /**
     * Definition of the form
     */
    public function definition() {

        $mform =& $this->_form;

        //$this->_customdata['selected_questions'];

        $sharequestion = new sharequestion();
        $sharequestion->prepare_active_fields();

        foreach ($sharequestion->get_required_fields() as $item) {
            $this->build_element($item);
        }

        $shownonrequieredfields = get_config('community_sharequestion', 'shownonrequieredfields');
        $notrequiredfields = $sharequestion->get_not_required_fields();
        if (!empty($notrequiredfields)) {
            if ($shownonrequieredfields == 1) {
                $html = '<button class="btn btn-primary mb-2" type="button" data-toggle="collapse" data-target="#collapseNotRequired"
                    aria-expanded="false" aria-controls="collapseNotRequired">
                    ' . get_string('advanced_catalog_options', 'community_sharequestion') . '                
                </button>
                <div class="form-group mb-4">
                    <div class="collapse" id="collapseNotRequired">
                        <div class="">';
                $mform->addElement('html', $html);

                foreach ($notrequiredfields as $item) {
                    $this->build_element($item);
                }

                $html = '</div>
                    </div>
                </div>';
                $mform->addElement('html', $html);
            } else {
                foreach ($notrequiredfields as $item) {
                    $this->build_element($item);
                }
            }
        }
    }

    public function build_element($item) {
        global $OUTPUT, $CFG;

        $mform = $this->_form;

        switch ($item->datatype) {

            // Not standart types.
            case 'selectsections':
                $html = $OUTPUT->render_from_template('community_sharequestion/elements/section-competency-block-wrapper', []);
                $mform->addElement('html', $html);
                break;

            case 'durationactivity':
                $html = $OUTPUT->render_from_template('community_sharequestion/elements/radio-duration', $item);
                $mform->addElement('html', $html);
                break;

            case 'levelactivity':
                $html = $OUTPUT->render_from_template('community_sharequestion/elements/radio-buttons-row-difficalty', $item);
                $mform->addElement('html', $html);
                break;

            case 'originality':
                $html = $OUTPUT->render_from_template('community_sharequestion/elements/radio-buttons-column', $item);
                $mform->addElement('html', $html);
                break;

            case 'tags':
                $html = $OUTPUT->render_from_template('community_sharequestion/elements/tags', $item);
                $mform->addElement('html', $html);
                break;

            // Standart types.
            case 'fileupload':

                $html = $OUTPUT->render_from_template('community_sharequestion/elements/form-item', $item);
                $arr = explode('html-delimiter', $html);

                $mform->addElement('html', $arr[0]);

                $filemanageroptions = array(
                        'accepted_types' => array('.jpg', '.png', '.svg'),
                        'maxbytes' => 0,
                        'maxfiles' => 1,
                        'subdirs' => 0,
                        'areamaxbytes' => 10485760,
                        'return_types' => FILE_INTERNAL | FILE_EXTERNAL
                );

                $mform->addElement('filemanager', $item->shortname, null, null, $filemanageroptions);

                $mform->addElement('html', $arr[1]);
                break;

            case 'textarea':
                $textfieldoptions = array(
                        'trusttext' => true,
                        'subdirs' => true,
                        'maxfiles' => 1,
                        'maxbytes' => $CFG->maxbytes,
                        'clean' => true,
                        'context' => \context_system::instance()
                );

                $default = array('text' => $item->defaultdata, 'format' => FORMAT_HTML);

                $elname = $item->shortname . '_' . time();

                $html = $OUTPUT->render_from_template('community_sharequestion/elements/form-item', $item);
                $arr = explode('html-delimiter', $html);

                $mform->addElement('html', $arr[0]);

                $mform->addElement('editor', $elname, '', null, $textfieldoptions);
                $mform->setType($elname, PARAM_RAW);
                $mform->setDefault($elname, $default);

                $mform->addElement('html', $arr[1]);
                break;

            case 'text':
                $html = $OUTPUT->render_from_template('community_sharequestion/elements/input', $item);
                $mform->addElement('html', $html);
                break;

            case 'multimenu':
                $html = $OUTPUT->render_from_template('community_sharequestion/elements/checkbox-buttons', $item);
                $mform->addElement('html', $html);
                break;

            case 'menu':
                if (!isset($item->format_checkbox)) {
                    $item->format_checkbox = false;
                    $item->format_radio = true;
                }
                $html = $OUTPUT->render_from_template('community_sharequestion/elements/radio-buttons-row', $item);
                $mform->addElement('html', $html);
                break;

            case 'multiselect':
                if ($item->multiselecttype == 'single') {
                    if (!isset($item->format_checkbox)) {
                        $item->format_checkbox = false;
                        $item->format_radio = true;
                    }
                    $html = $OUTPUT->render_from_template('community_sharequestion/elements/radio-buttons-row', $item);
                    $mform->addElement('html', $html);
                }

                if ($item->multiselecttype == 'multi') {
                    $html = $OUTPUT->render_from_template('community_sharequestion/elements/checkbox-buttons', $item);
                    $mform->addElement('html', $html);
                }
                break;

            case 'checkbox':
                $html = $OUTPUT->render_from_template('community_sharequestion/elements/checkbox', $item);
                $mform->addElement('html', $html);
                break;
        }
    }

    /**
     * Extend the form definition after the data has been parsed.
     */
    public function definition_after_data() {

        $mform = $this->_form;
    }

    /**
     * Form validation
     *
     * @param array $data
     * @param array $files
     * @return array $errors An array of errors
     */
    public function validation($data, $files) {

        $errors = parent::validation($data, $files);

        return $errors;
    }

    /**
     * Get editor options for this form
     *
     * @return array An array of options
     */
    public function get_editor_options() {
        return $this->_customdata['editoroptions'];
    }
}
