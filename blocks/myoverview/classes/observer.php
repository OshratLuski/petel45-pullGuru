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
 * @package    block_myoverview
 * @copyright  2013 Rajesh Taneja <rajesh@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/enrol/meta/locallib.php');

/**
 * Event observer for enrol_meta.
 *
 * @package    block_myoverview
 * @copyright  2013 Rajesh Taneja <rajesh@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class block_myoverview_observer extends enrol_meta_handler {

    /**
     * Triggered via user_enrolment_created event.
     *
     * @param \core\event\user_enrolment_created $event
     * @return bool true on success.
     */
    public static function user_enrolment_created(\core\event\user_enrolment_created $event) {

        //$event->courseid, $event->relateduserid

        // Initial cache.
        $cache = cache::make('block_myoverview', 'enrolled_courses');

        if($cache->get('user_'.$event->relateduserid) == false) return false;

        foreach($cache->get('user_'.$event->relateduserid) as $key){
            $cache->delete($key);
        }

        $cache->delete('user_'.$event->relateduserid);
        $cache->delete('user_' . $event->relateduserid.'_last_access');

        return true;
    }

    /**
     * Triggered via user_enrolment_deleted event.
     *
     * @param \core\event\user_enrolment_deleted $event
     * @return bool true on success.
     */
    public static function user_enrolment_deleted(\core\event\user_enrolment_deleted $event) {

        // Initial cache.
        $cache = cache::make('block_myoverview', 'enrolled_courses');

        if($cache->get('user_'.$event->relateduserid) == false) return false;

        foreach($cache->get('user_'.$event->relateduserid) as $key){
            $cache->delete($key);
        }

        $cache->delete('user_'.$event->relateduserid);
        $cache->delete('user_' . $event->relateduserid.'_last_access');

        return true;
    }

    /**
     * Triggered via user_enrolment_updated event.
     *
     * @param \core\event\user_enrolment_updated $event
     * @return bool true on success
     */
    public static function user_enrolment_updated(\core\event\user_enrolment_updated $event) {

        // Initial cache.
        $cache = cache::make('block_myoverview', 'enrolled_courses');

        if($cache->get('user_'.$event->relateduserid) == false) return false;

        foreach($cache->get('user_'.$event->relateduserid) as $key){
            $cache->delete($key);
        }

        $cache->delete('user_'.$event->relateduserid);
        $cache->delete('user_' . $event->relateduserid.'_last_access');

        return true;
    }

    /**
     * Observer for \core\event\course_created event.
     *
     * @param \core\event\course_created $event
     * @return void
     */
    public static function course_created(\core\event\course_created $event) {
        global $DB;

        $course = $DB->get_field('course', 'id', array('id' => $event->courseid));
        $coursecontext = context_course::instance($course, IGNORE_MISSING);

        if ($users = get_enrolled_users($coursecontext, '', 0, 'u.id')) {
            // Initial cache.
            $cache = cache::make('block_myoverview', 'enrolled_courses');
            foreach ($users as $user) {
                if ($cache->get('user_' . $user->id)) {
                    foreach ($cache->get('user_' . $user->id) as $key) {
                        $cache->delete($key);
                    }
                }

                $cache->delete('user_' . $user->id);
                $cache->delete('user_' . $user->id . '_last_access');

            }

        }
    }

    /**
     * Observer for \core\event\course_updated event.
     *
     * @param \core\event\course_updated $event
     * @return void
     */
    public static function course_updated(\core\event\course_updated $event) {
        global $DB;

        $course = $DB->get_field('course', 'id', array('id' => $event->courseid));
        $coursecontext = context_course::instance($course, IGNORE_MISSING);

        if ($users = get_enrolled_users($coursecontext, '', 0, 'u.id')) {
            // Initial cache.
            $cache = cache::make('block_myoverview', 'enrolled_courses');
            foreach ($users as $user) {
                if ($cache->get('user_' . $user->id)) {
                    foreach ($cache->get('user_' . $user->id) as $key) {
                        $cache->delete($key);
                    }
                }

                $cache->delete('user_' . $user->id);
                $cache->delete('user_' . $user->id . '_last_access');

            }

        }

    }
}
