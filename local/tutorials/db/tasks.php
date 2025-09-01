<?php

/**
 * Local plugin "Tutorials"
 * Send please complete your tutorial (SCORM activity) reminder, by intervals.
 *
 * @package    local_tutorials
 * @copyright  2022 Nadav Kavalerchik <nadav.kavalerchik@weizmann.ac.il>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$tasks = array(
        array(
            'classname' => 'local_tutorials\task\send_uncompleted_reminders',
            'blocking' => 0,
            'minute' => '22',
            'hour' => '2',
            'day' => '*',
            'dayofweek' => '*',
            'month' => '*',
        ),
        array(
            'classname' => 'local_tutorials\task\safety_course_completion_reminders',
            'blocking' => 0,
            'minute' => '13',
            'hour' => '13',
            'day' => '*',
            'dayofweek' => '0',
            'month' => '*',
            'disabled' => true,
            
        ),
        array(
            'classname' => 'local_tutorials\task\send_fire_prevention_reminders',
            'blocking' => 0,
            'minute' => '13',
            'hour' => '*', //'hour' => '13',
            'day' => '*',
            'dayofweek' => '*', //'dayofweek' => '0',
            'month' => '*',
            'disabled' => true,
        )
);
