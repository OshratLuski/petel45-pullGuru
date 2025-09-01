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
 * Menu profile field definition.
 *
 * @package    profilefield_treeselect
 * @copyright  2007 onwards Shane Elliot {@link http://pukunui.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace metadatafieldtype_treeselect;

defined('MOODLE_INTERNAL') || die;

require_once($CFG->dirroot .'/local/metadata/fieldtype/treeselect/locallib.php');

/**
 * Class local_metadata_define_menu
 *
 * @copyright  2007 onwards Shane Elliot {@link http://pukunui.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class define extends \local_metadata\fieldtype\define_base {

    /**
     * Adds elements to the form for creating/editing this type of profile field.
     * @param moodleform $form
     */
    public function define_form_specific($form) {
        global $PAGE;

        // Param 1 for menu type contains the options.
        $form->addElement('textarea', 'param1', get_string('profilemenuoptions', 'admin'), ['rows' => 6, 'cols' => 60, 'class' => 'form-textarea']);
        $form->setType('param1', PARAM_TEXT);
        $form->setDefault('param1', get_string('describe', 'metadatafieldtype_treeselect'));

        $PAGE->requires->js_call_amd('metadatafieldtype_treeselect/menufield', 'init');

        // Default data.
        $form->addElement('text', 'defaultdata', get_string('profiledefaultdata', 'admin'), 'size="50"');
        $form->setType('defaultdata', PARAM_TEXT);
        $form->addHelpButton('defaultdata', 'describedefault', 'metadatafieldtype_treeselect');
    }

    /**
     * Validates data for the profile field.
     *
     * @param array $data
     * @param array $files
     * @return array
     */
    public function define_validate_specific($data, $files) {
        $err = [];

        json_decode($data->param1);

        if(json_last_error() !== JSON_ERROR_NONE){
            $err['param1'] = get_string('profilemenunooptions', 'admin');
        }

        list($options, $ids) = \class_treeselect::get_options($data->param1);

        if(!empty($data->defaultdata)) {
            foreach (explode(',', $data->defaultdata) as $id) {
                if (!in_array(trim($id), $ids)) {
                    $err['defaultdata'] = get_string('profilemenudefaultnotinoptions', 'admin');
                }
            }
        }

        return $err;
    }

    /**
     * Processes data before it is saved.
     * @param array|stdClass $data
     * @return array|stdClass
     */
    public function define_save_preprocess($data) {

        //$data->param1 = trim(preg_replace('/\s\s+/', ' ', $data->param1));

        return $data;
    }

}


