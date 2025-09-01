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
 * Event observers supported by this module
 *
 * @package    community_sharecourse
 * @copyright  2018 Devlion <info@devlion.co>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Event observers supported by this module
 *
 * @package    community_sharecourse
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class community_sharecourse_observer {

    /**
     * Triggered via course_deleted event.
     *
     * @param \core\event\course_deleted $event
     * @return bool true on success
     */
    public static function course_deleted(\core\event\course_deleted $event) {
        global $DB;

        if ($event->courseid) {
            $DB->delete_records('community_sharecourse_task', array('status' => 0, 'courseid' => $event->courseid));
        }

        return true;
    }

    /**
     * Triggered via config_log_created event.
     *
     * @param \core\event\config_log_created $event
     * @return bool true on success
     */
    public static function config_log_created(\core\event\config_log_created $event) {
        global $DB;

        if ($event->other['plugin'] == 'community_sharecourse') {

            if (in_array($event->other['name'], ['oercoursecohort', 'oercoursecohortrole'])) {

                // Get courses.
                $courses = [];
                foreach ($DB->get_records('community_oer_course') as $item) {
                    $courses[$item->cid] = $item->cid;
                }

                if ($event->other['name'] == 'oercoursecohortrole') {
                    $roleid = $event->other['oldvalue'];
                } else {
                    $roleid = get_config('community_sharecourse', 'oercoursecohortrole');
                }

                if ($event->other['name'] == 'oercoursecohort') {
                    $cohortid = $event->other['oldvalue'];
                } else {
                    $cohortid = get_config('community_sharecourse', 'oercoursecohort');
                }

                $sharecourse = new \community_sharecourse\sharecourse();
                $plugin = enrol_get_plugin('cohort');

                foreach ($courses as $cid) {

                    // Unenrol course.
                    $instance = $DB->get_record('enrol', [
                            'enrol' => 'cohort',
                            'courseid' => $cid,
                            'roleid' => $roleid,
                            'customint1' => $cohortid,
                    ]);

                    if ($instance) {
                        $plugin->delete_instance($instance);
                    }

                    // Enrol course.
                    $sharecourse->enrol_course($cid);
                }
            }
        }

        return true;
    }
}
