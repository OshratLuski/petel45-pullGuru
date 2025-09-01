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
 * Code to be executed after the plugin's database scheme has been installed is defined here.
 *
 * @package     local
 * @subpackage  exportgrade
 * @category    upgrade
 * @copyright   2017 nadavkav@gmail.com
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Custom code to be run on installing the plugin.
 */
function xmldb_local_exportgrade_install()
{

    global $DB;
    $time = time();

    $category = new stdClass();
    $category->name = get_string('customfieldcategory', 'local_exportgrade');
    $category->timecreated = $time;
    $category->timemodified = $time;
    $category->component = 'core_course';
    $category->area = 'course';
    $category = $DB->insert_record('customfield_category', $category);

    $customfields = ['year', 'school_symbol', 'learninggroup_id'];
    foreach ($customfields as $fieldname) {
        $field = new stdClass();
        $field->shortname = $fieldname;
        $field->name = get_string('customfield' . $fieldname, 'local_exportgrade');
        $field->type = 'text';
        $field->sortorder = 0;
        $field->categoryid = $category;
        $field->configdata = '{"required":"0","uniquevalues":"0","defaultvalue":"","displaysize":50,"maxlength":50,"ispassword":"0","link":"", "locked":"0","visibility":"2"}';
        $field->description = get_string('customfield' . $fieldname . "_desc", 'local_exportgrade');
        $field->timecreated = $time;
        $field->timemodified = $time;
        if (!$DB->get_record('customfield_field', ['shortname' => $fieldname])) {
            $fieldid = $DB->insert_record('customfield_field', $field);
        }
    }
    return true;
}
