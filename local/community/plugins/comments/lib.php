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
 * Local plugin "OER catalog" - Library
 *
 * @package    community_comments
 * @copyright   2019 Devlion <info@devlion.co>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Allow plugins to provide some content to be rendered in the navbar.
 * The plugin must define a PLUGIN_render_navbar_output function that returns
 * the HTML they wish to add to the navbar.
 *
 * @return string HTML for the navbar
 */
function community_comments_render_navbar_output() {

    global $DB, $PAGE;

    if ($PAGE->cm === null) {
        return false;
    }

    // Check permission.
    if (!\community_oer\main_oer::check_if_user_admin_or_teacher()) {
        return false;
    }

    $pagedata = $PAGE->cm->get_course_module_record();
    $module = $DB->get_record('modules', array('id' => $pagedata->module));

    // Check mods.
    $disabledmods = array();
    if (!isset($module->name) || in_array($module->name, $disabledmods)) {
        return false;
    }

    if (!empty($pagedata->id) && !empty($pagedata->course)) {
        $activity = $DB->get_record('course_modules',
                array('instance' => $pagedata->instance, 'course' => $pagedata->course, 'module' => $pagedata->module));

        if (!empty($activity->id)) {
            list($oercategories, $oercourses, $oeractivities) = \community_oer\main_oer::get_main_structure_elements();

            if (in_array($activity->id, $oeractivities)) {
                $data = array(
                        $activity->id,
                );
            }
        }
    }

    return true;
}
