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
 * Capability definitions for the sharewith module.
 *
 * @package community_sharewith
 * @category event
 * @copyright 2019 Devlion <info@devlion.co>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

require_once($CFG->dirroot . '/lib/formslib.php');
require_once($CFG->dirroot . '/local/community/plugins/sharewith/classes/sharewith.php');

/**
 * Group form class
 *
 * @copyright 2006 The Open University, N.D.Freear AT open.ac.uk, J.White AT open.ac.uk
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @package   community_sharewith
 */
class sharewith_form extends moodleform {

    /**
     * Definition of the form
     */
    public function definition() {
        $default = $this->_ajaxformdata;
        $mform =& $this->_form;

        $sharewith = new sharewith();
        $sharewith->setactivityid($this->_customdata['cmid'], $this->_customdata['courseid']);
        $sharewith->prepare_active_fields();

        foreach ($sharewith->get_required_fields() as $item) {
            $this->build_element($item, $default);
        }

        $shownonrequieredfields = get_config('community_sharewith', 'shownonrequieredfields');
        $notrequiredfields = $sharewith->get_not_required_fields();
        if (!empty($notrequiredfields)) {
            if ($shownonrequieredfields == 1) {
                $html = '<button class="btn btn-primary mb-2" type="button" data-toggle="collapse" 
                        data-target="#collapseNotRequired" aria-expanded="false" aria-controls="collapseNotRequired">
                    ' . get_string('advanced_catalog_options', 'community_sharewith') . '                
                </button>
                <div class="form-group mb-4">
                    <div class="collapse" id="collapseNotRequired">
                        <div class="">';
                $mform->addElement('html', $html);

                foreach ($notrequiredfields as $item) {
                    $this->build_element($item, $default);
                }

                $html = '</div>
                    </div>
                </div>';
                $mform->addElement('html', $html);
            } else {
                foreach ($notrequiredfields as $item) {
                    $this->build_element($item, $default);
                }
            }
        }
    }

    public function build_element($item, $default) {
        global $OUTPUT, $CFG;

        $mform = $this->_form;

        $sharewith = new sharewith();
        $sharewith->setactivityid($this->_customdata['cmid'], $this->_customdata['courseid']);

        switch ($item->datatype) {

            // Not standart types.
            case 'selectsections':
                $html = $OUTPUT->render_from_template('community_sharewith/elements/section-competency-block-wrapper',
                        $sharewith->add_specific_data());
                $mform->addElement('html', $html);
                break;

            case 'durationactivity':
                $item->defaultdata = 0;
                foreach ($item->data_formated as $key => $t) {
                    if ($t['metadata_value'] == $default[$item->shortname]) {
                        $item->defaultdata = $key;
                    }
                }

                $html = $OUTPUT->render_from_template('community_sharewith/elements/radio-duration', $item);
                $mform->addElement('html', $html);
                break;

            case 'levelactivity':
                if (isset($default[$item->shortname])) {
                    $item->element_0['metadata_checked'] =
                    $item->element_1['metadata_checked'] = $item->element_2['metadata_checked'] = false;
                    foreach ($item->data_formated as $key => $t) {
                        if ($t['metadata_value'] == $default[$item->shortname]) {
                            $element = 'element_' . $key;
                            $item->$element['metadata_checked'] = true;
                        }
                    }
                }
                $html = $OUTPUT->render_from_template('community_sharewith/elements/radio-buttons-row-difficalty', $item);
                $mform->addElement('html', $html);
                break;

            case 'originality':
                if (isset($default[$item->shortname]) &&
                        !empty(trim($default[$item->shortname]) && $default[$item->shortname . '_checkbox'] == 'true')) {
                    $item->checkbox_no = false;
                    $item->checkbox_yes = true;
                    $item->textarea_value = $default[$item->shortname];
                } else {
                    $item->checkbox_no = null;
                    $item->checkbox_yes = false;
                    $item->textarea_value = '';
                }

                // Checkbox agree to copyright.
                $agreetocopyright = get_user_preferences('activity_agree_to_copyright');
                $item->agree_to_copyright_enable = !$agreetocopyright ? true : false;

                $html = $OUTPUT->render_from_template('community_sharewith/elements/radio-buttons-column', $item);
                $mform->addElement('html', $html);
                break;

            case 'tags':
                $html = $OUTPUT->render_from_template('community_sharewith/elements/tags', $item);
                $mform->addElement('html', $html);
                break;

            // Standart types.
            case 'fileupload':

                $html = $OUTPUT->render_from_template('community_sharewith/elements/form-item', $item);
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

                if (isset($default[$item->shortname]) && !empty(trim($default[$item->shortname]))) {
                    $mform->setDefault($item->shortname, trim($default[$item->shortname]));
                }

                $mform->addElement('html', $arr[1]);
                break;

            case 'textarea':
                if (isset($default[$item->shortname]) && !empty(trim($default[$item->shortname]))) {
                    $item->defaultdata = $default[$item->shortname];
                }
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

                $html = $OUTPUT->render_from_template('community_sharewith/elements/form-item', $item);
                $arr = explode('html-delimiter', $html);

                $mform->addElement('html', $arr[0]);

                $mform->addElement('editor', $elname, '', null, $textfieldoptions);
                $mform->setType($elname, PARAM_RAW);
                $mform->setDefault($elname, $default);

                $mform->addElement('html', $arr[1]);
                break;

            case 'text':
                if (isset($default[$item->shortname]) && !empty(trim($default[$item->shortname]))) {
                    $item->defaultdata = $default[$item->shortname];
                }
                $html = $OUTPUT->render_from_template('community_sharewith/elements/input', $item);
                $mform->addElement('html', $html);
                break;

            case 'multiselect':
                if ($item->multiselecttype == 'single') {
                    if (isset($default[$item->shortname])) {
                        foreach ($item->data_formated as $key => $t) {
                            if ($t['metadata_value'] == $default[$item->shortname]) {
                                $item->data_formated[$key]['metadata_checked'] = true;
                            } else {
                                $item->data_formated[$key]['metadata_checked'] = false;
                            }
                        }
                    }
                    if (!isset($item->format_checkbox)) {
                        $item->format_checkbox = false;
                        $item->format_radio = true;
                    }
                    $html = $OUTPUT->render_from_template('community_sharewith/elements/radio-buttons-row', $item);
                    $mform->addElement('html', $html);
                }

                if ($item->multiselecttype == 'multi') {
                    if (isset($default[$item->shortname]) && is_array($default[$item->shortname])) {
                        foreach ($item->data_formated as $key => $t) {
                            if (in_array($t['metadata_value'], $default[$item->shortname])) {
                                $item->data_formated[$key]['metadata_checked'] = true;
                            } else {
                                $item->data_formated[$key]['metadata_checked'] = false;
                            }
                        }
                    }
                    $html = $OUTPUT->render_from_template('community_sharewith/elements/checkbox-buttons', $item);
                    $mform->addElement('html', $html);
                }
                break;

            case 'multimenu':
                if (isset($default[$item->shortname]) && is_array($default[$item->shortname])) {
                    foreach ($item->data_formated as $key => $t) {
                        if (in_array($t['metadata_value'], $default[$item->shortname])) {
                            $item->data_formated[$key]['metadata_checked'] = true;
                        } else {
                            $item->data_formated[$key]['metadata_checked'] = false;
                        }
                    }
                }
                $html = $OUTPUT->render_from_template('community_sharewith/elements/checkbox-buttons', $item);
                $mform->addElement('html', $html);
                break;

            case 'menu':
                if (isset($default[$item->shortname])) {
                    foreach ($item->data_formated as $key => $t) {
                        if ($t['metadata_value'] == $default[$item->shortname]) {
                            $item->data_formated[$key]['metadata_checked'] = true;
                        } else {
                            $item->data_formated[$key]['metadata_checked'] = false;
                        }
                    }
                }
                if (!isset($item->format_checkbox)) {
                    $item->format_checkbox = false;
                    $item->format_radio = true;
                }
                $html = $OUTPUT->render_from_template('community_sharewith/elements/radio-buttons-row', $item);
                $mform->addElement('html', $html);
                break;

            case 'checkbox':
                if (isset($default[$item->shortname]) && $default[$item->shortname] == 1) {
                    $item->defaultdata = true;
                } else {
                    $item->defaultdata = false;
                }
                $html = $OUTPUT->render_from_template('community_sharewith/elements/checkbox', $item);
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
