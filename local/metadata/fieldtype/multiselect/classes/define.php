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
 * Multiselect field definition.
 *
 * @package    metadatafieldtype_multiselect
 * @copyright  2022 Tamir Hajaj {@link https://sysbind.co.il}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace metadatafieldtype_multiselect;

defined('MOODLE_INTERNAL') || die;

/**
 * Class define
 *
 * @copyright  2022 Avi Levy {@link https://sysbind.co.il}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class define extends \local_metadata\fieldtype\define_base {

    /**
     * Adds elements to the form for creating/editing this type of profile field.
     *
     * @param moodleform $form
     * @throws coding_exception
     */
    public function define_form_specific($form) {

        // Select of type.
        $types = [
            '0' => get_string('singlechoice', 'metadatafieldtype_multiselect'),
            '1' => get_string('multichoice', 'metadatafieldtype_multiselect'),
        ];

        $form->addElement('select', 'param2', get_string('type', 'metadatafieldtype_multiselect'), $types, []);
        $form->setType('param2', PARAM_INT);

        // Sending the first param of multiselect.
        $form->addElement('textarea', 'param1', get_string('profilemenuoptions', 'admin'), array('rows' => 6, 'cols' => 40));
        $form->setType('param1', PARAM_TEXT);
        $form->addHelpButton('param1', 'rightformat', 'metadatafieldtype_multiselect');

        $content = \html_writer::tag('p', get_string('example', 'metadatafieldtype_multiselect') );
        $content .= \html_writer::tag('p', '1:he=בננה|en=banana' );
        $content .= \html_writer::tag('p', '2:he=פרח|en=flower' );
        $content .= \html_writer::tag('p', '3:he=עגבנייה|en=tomato' );

        $div = \html_writer::div($content, 'col-md-9 align-items-start felement ml-auto');
        $form->addElement('html', $div);

        // Default data.
        $form->addElement('text', 'defaultdata', get_string('profiledefaultdata', 'admin'), 'size="50"');
        $form->setType('defaultdata', PARAM_TEXT);
    }

    /**
     * Validates data for the profile field.
     *
     * @param array $data
     * @return array
     */
    public function define_validate_specific($data, $files) {
        $err = [];
        $valuesarray = [];
        $currentclangs = array_keys(get_string_manager()->get_list_of_languages());
        $data->param1 = str_replace("\r", '', $data->param1);

        // Check if data as at list two values like  <value>:<lang>=<string>|<lang2>=<string2>.
        $dataform = array_filter(explode("\n", $data->param1));

        if (empty($dataform)) {
            $err['param1'] = get_string('profilemenunooptions', 'admin');
        }

        foreach ($dataform as $stringdata) {
            $valueseparator = explode(":", $stringdata);
            $valuesarray[] = $valueseparator[0];
            $pipeseparator = explode("|", end($valueseparator));
            if (strlen(reset($valueseparator)) == 0) {
                $err['param1'] = get_string('notinrightformat', 'metadatafieldtype_multiselect');
            } else {

                $flagenpresent = false;
                foreach ($pipeseparator as $lang) {
                    $langvalue = explode("=", $lang);

                    // Find default lang 'en'.
                    if ($langvalue[0] == 'en') {
                        $flagenpresent = true;
                    }

                    if (!in_array($langvalue[0], $currentclangs) || $langvalue[1] == '') {
                        $err['param1'] = get_string('notinrightformat', 'metadatafieldtype_multiselect');
                    }
                }

                if ($flagenpresent === false) {
                    $err['param1'] = get_string('notinrightformat', 'metadatafieldtype_multiselect');
                }
            }
        }

        // Check for duplicates values.
        if (array_unique($valuesarray) !== $valuesarray) {
            $err['param1'] = get_string('duplicatevalues', 'metadatafieldtype_multiselect');
        }

        // Check default.
        if (!empty($data->defaultdata) && !in_array($data->defaultdata, $valuesarray)) {
            // Check the default data exists in the options.
            $err['defaultdata'] = get_string('profilemenudefaultnotinoptions', 'admin');
        }

        return $err;
    }
    /**
     * Processes data before it is saved.
     * @param array|stdClass $data
     * @return array|stdClass
     */
    public function define_save_preprocess($data) {

        $arr = explode("\n", $data->param1);
        foreach($arr as $key => $str){
            $str = trim($str);
            if(empty($str)){
                unset($arr[$key]);
            }
        }

        $data->param1 = implode("\n", $arr);
        $data->param1 = str_replace("\r", '', $data->param1);

        return $data;
    }
}
