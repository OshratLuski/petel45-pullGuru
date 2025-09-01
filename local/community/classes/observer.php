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
 * @package local_community
 * @copyright   2019 Devlion <info@devlion.co>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_community;

/**
 * Local community event handler.
 */
class observer {

    /**
     * Triggered via any defined delete event.
     * - Dispatches metadata type specific event, if it exists.
     * - Currently only monitors "[context]_deleted" events.
     *
     * @param \core\event\* $event
     * @return bool true on success
     */
    public static function all_events($event) {
        $localobserver = substr(strrchr($event->eventname, '\\'), 1);
        if (method_exists('local_metadata\observer', $localobserver)) {
            return self::$localobserver($event);
        } else {
            return true;
        }
    }

    /**
     * Triggered via course_deleted event.
     * - Removes course metadata
     *
     * @param \core\event\course_deleted $event
     * @return bool true on success
     */
    public static function course_deleted(\core\event\course_deleted $event) {
        global $DB;

        if (!empty($event->objectid)) {
            $DB->delete_records('community_sharewith_task', array('sourcecourseid' => $event->objectid));
            $DB->delete_records('community_sharewith_task', array('courseid' => $event->objectid));

            $DB->delete_records('community_sharewith_shared', array('courseid' => $event->objectid));

            $DB->delete_records('community_social_shrd_crss', array('courseid' => $event->objectid));
        }

        return true;
    }

    /**
     * Triggered via user_deleted event.
     * - Removes user metadata
     *
     * @param \core\event\user_deleted $event
     * @return bool true on success
     */
    public static function user_deleted(\core\event\user_deleted $event) {
        global $DB;

        if (!empty($event->objectid)) {
            $DB->delete_records('community_sharewith_task', array('sourceuserid' => $event->objectid));
            $DB->delete_records('community_sharewith_task', array('userid' => $event->objectid));

            $DB->delete_records('community_sharewith_shared', array('useridto' => $event->objectid));
            $DB->delete_records('community_sharewith_shared', array('useridfrom' => $event->objectid));

            $DB->delete_records('community_social_shrd_crss', array('userid' => $event->objectid));

            $DB->delete_records('community_social_usr_dtls', array('userid' => $event->objectid));
        }

        return true;
    }

    /**
     * Triggered via module_deleted event.
     * - Removes module metadata
     *
     * @param \core\event\course_module_deleted $event
     * @return bool true on success
     */
    public static function course_module_deleted(\core\event\course_module_deleted $event) {
        global $DB;

        if (!empty($event->objectid)) {
            $DB->delete_records('community_sharewith_task', array('sourceactivityid' => $event->objectid));

            $DB->delete_records('community_sharewith_shared', array('activityid' => $event->objectid));

        }

        return true;
    }
}
