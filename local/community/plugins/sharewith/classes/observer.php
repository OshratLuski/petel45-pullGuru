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
 * Event observer for meta enrolment plugin.
 *
 * @package    community_sharewith
 * @copyright  2018 Devlion <info@devlion.co>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Event observer for community_sharewith.
 *
 * @package    community_sharewith
 * @copyright  2013 Rajesh Taneja <rajesh@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class community_sharewith_observer {

    /**
     * Triggered via course_deleted event.
     *
     * @param \core\event\course_deleted $event
     * @return bool true on success
     */
    public static function course_deleted(\core\event\course_deleted $event) {
        global $DB;

        if ($event->courseid) {
            $DB->delete_records('community_sharewith_task', array('status' => 0, 'sourcecourseid' => $event->courseid));
            $DB->delete_records('community_sharewith_task', array('status' => 0, 'courseid' => $event->courseid));
        }

        return true;
    }

}
