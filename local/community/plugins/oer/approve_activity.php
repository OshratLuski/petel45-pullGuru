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
 * View the poster instance
 *
 * @package    community_oer
 * @copyright  2018 Devlion <info@devlion.co>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require("../../../../config.php");
require_once("../../../../course/lib.php");

global $DB, $PAGE;

$show = optional_param('show', 0, PARAM_INT);
$sesskey = optional_param('sesskey', 0, PARAM_INT);

$PAGE->set_url(new moodle_url('/local/community/plugins/oer/approve_activity.php'));
$PAGE->set_context(context_system::instance());

if (!empty($show) && confirm_sesskey()) {
    list($course, $cm) = get_course_and_cm_from_cmid($show);
    require_login($course, false, $cm);
    require_capability('moodle/course:activityvisibility', $cm->context);
    $section = $cm->get_section_info();

    if (set_coursemodule_visible($cm->id, 1)) {
        \core\event\course_module_updated::create_from_cm($cm)->trigger();

        if (get_config('local_community', 'mailnewoeractivity')) {
            $oercategory = get_config('local_community', 'catalogcategoryid');
            $inoer = $DB->get_record('course_categories', array('id' => $course->category, 'parent' => $oercategory));
            if ($inoer) {
                $task = new \community_oer\task\adhoc_email_toteachers_new_oer_activity();
                $task->set_custom_data(
                        array("cmid" => $cm->id, "name" => $cm->name, "course" => $course->fullname)
                );
                \core\task\manager::queue_adhoc_task($task);
            }
        }
        redirect(course_get_url($course, $section->section),
                get_string('oeractivityapprovesuccess', 'community_oer',
                        array('cmname' => $cm->name, 'coursename' => $course->fullname)), 30);
    }
}
echo get_string('oeractivityapprovefaild', 'community_oer');

