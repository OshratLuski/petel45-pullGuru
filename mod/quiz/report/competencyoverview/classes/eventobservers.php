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
 * Observer class containing methods monitoring various events.
 *
 * @package    quiz_competencyoverview
 * @copyright  2014 onwards Ankit Agarwal <ankit.agrr@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace quiz_competencyoverview;

defined('MOODLE_INTERNAL') || die();

/**
 * Observer class containing methods monitoring various events.
 *
 * @since      Moodle 2.8
 * @package    quiz_competencyoverview
 * @copyright  2014 onwards Ankit Agarwal <ankit.agrr@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class eventobservers {

    /** @var array $buffer buffer of events. */
    protected $buffer = array();

    /** @var int Number of entries in the buffer. */
    protected $count = 0;

    /** @var  eventobservers a reference to a self instance. */
    protected static $instance;

    /**
     * Observer that monitors course module deleted event and delete user subscriptions.
     *
     * @param \core\event\course_module_deleted $event the event object.
     */
    public static function course_module_deleted(\core\event\course_module_deleted $event) {
        global $DB;

        $cmid = $event->contextinstanceid;

        $DB->delete_records('quiz_competencyoverview_aa', ['activityid' => $cmid]);

    }
}
