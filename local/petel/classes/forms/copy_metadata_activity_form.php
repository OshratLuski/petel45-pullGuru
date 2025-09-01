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
 * This file contains the profile field category form.
 *
 * @package local_petel
 * @copyright  2007 onwards Shane Elliot {@link http://pukunui.com}
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_petel\forms;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/lib/formslib.php');

/**
 * Class copy_metadata_activity_form
 *
 * @copyright  2007 onwards Shane Elliot {@link http://pukunui.com}
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class copy_metadata_activity_form extends \moodleform {

    /**
     * Define the form.
     */
    public function definition() {
        $mform = $this->_form;

        $mform->addElement('hidden', 'mdfieldserror');
        $mform->setType('mdfieldserror', PARAM_RAW);

        $mdfields = $mform->optional_param('mdfields', [], PARAM_RAW);
        if (!empty($_POST) && !empty($mdfields)) {
            $mform->addElement('html', '
                <div class="alert alert-success">
                ' . get_string('cmasuccess', 'local_petel') . '
                </div>
            ');
        }

        $mform->addElement('text', 'source_cmid', get_string('cmasourcecmid', 'local_petel'),
                array('class' => '', 'maxlength' => 255));
        $mform->setType('source_cmid', PARAM_RAW);

        $mform->addElement('textarea', 'target_cmids', get_string('cmatargetcmids', 'local_petel'),
                ['rows' => 5, 'cols' => 60]);

        $mform->addElement('static', '', get_string('cmaheadermdfields', 'local_petel'),
                \html_writer::tag('div', '', array('class' => '')));

        $mdfields = $mform->optional_param('mdfields', [], PARAM_RAW);
        if (!empty($_POST) && empty($mdfields)) {
            $mform->addElement('html', '
                <div class="alert alert-danger">
                ' . get_string('cmaerrormdfields', 'local_petel') . '
                </div>
            ');
        }

        // Checkboxes.
        $fieldsdefault = [];
        foreach (\local_metadata\mcontext::module()->getFields() as $field) {

            $default = in_array($field->shortname, $fieldsdefault) ? 1 : 0;
            $mform->addElement('checkbox', 'mdfields[' . $field->shortname . ']', $field->name);
            $mform->setDefault('mdfields[' . $field->shortname . ']', $default);
        }

        $this->add_action_buttons(true, get_string('cmasubmitlabel', 'local_petel'));
    }

    /**
     * Perform some moodle validation.
     *
     * @param array $data
     * @param array $files
     * @return array
     */
    public function validation($data, $files) {
        global $DB;
        $errors = parent::validation($data, $files);

        $data = (object) $data;

        if (!isset($data->source_cmid) || empty(trim($data->source_cmid)) || !is_numeric(trim($data->source_cmid))) {
            $errors['source_cmid'] = get_string('cmaerrorsourcecmid', 'local_petel');
        }

        if (!isset($data->target_cmids) || empty($data->target_cmids)) {
            $errors['target_cmids'] = get_string('cmaerrortargetcmids', 'local_petel');
        }

        if (isset($data->target_cmids) && !empty($data->target_cmids)) {
            foreach (explode(',', $data->target_cmids) as $cmid) {
                if (!is_numeric(trim($cmid))) {
                    $errors['target_cmids'] = get_string('cmaerrortargetcmids', 'local_petel');
                }
            }
        }

        if (!isset($data->mdfields) || empty($data->mdfields)) {
            $errors['mdfieldserror'] = get_string('cmaerrormdfields', 'local_petel');
        }

        return $errors;
    }
}


