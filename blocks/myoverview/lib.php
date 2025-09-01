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
 * Library functions for overview.
 *
 * @package   block_myoverview
 * @copyright 2018 Peter Dias
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Constants for the user preferences grouping options
 */
define('BLOCK_MYOVERVIEW_GROUPING_ALLINCLUDINGHIDDEN', 'allincludinghidden');
define('BLOCK_MYOVERVIEW_GROUPING_ALL', 'all');
define('BLOCK_MYOVERVIEW_GROUPING_INPROGRESS', 'inprogress');
define('BLOCK_MYOVERVIEW_GROUPING_FUTURE', 'future');
define('BLOCK_MYOVERVIEW_GROUPING_PAST', 'past');
define('BLOCK_MYOVERVIEW_GROUPING_FAVOURITES', 'favourites');
define('BLOCK_MYOVERVIEW_GROUPING_HIDDEN', 'hidden');
define('BLOCK_MYOVERVIEW_GROUPING_CUSTOMFIELD', 'customfield');

/**
 * Allows selection of all courses without a value for the custom field.
 */
define('BLOCK_MYOVERVIEW_CUSTOMFIELD_EMPTY', -1);

/**
 * Constants for the user preferences sorting options
 * timeline
 */
define('BLOCK_MYOVERVIEW_SORTING_TITLE', 'title');
define('BLOCK_MYOVERVIEW_SORTING_LASTACCESSED', 'lastaccessed');
define('BLOCK_MYOVERVIEW_SORTING_SHORTNAME', 'shortname');

/**
 * Constants for the user preferences view options
 */
define('BLOCK_MYOVERVIEW_VIEW_CARD', 'card');
define('BLOCK_MYOVERVIEW_VIEW_LIST', 'list');
define('BLOCK_MYOVERVIEW_VIEW_SUMMARY', 'summary');
define('BLOCK_MYOVERVIEW_VIEW_PETEL', 'petel');

/**
 * Constants for the user paging preferences
 */
define('BLOCK_MYOVERVIEW_PAGING_12', 12);
define('BLOCK_MYOVERVIEW_PAGING_24', 24);
define('BLOCK_MYOVERVIEW_PAGING_48', 48);
define('BLOCK_MYOVERVIEW_PAGING_96', 96);
define('BLOCK_MYOVERVIEW_PAGING_ALL', 0);

/**
 * Constants for the admin category display setting
 */
define('BLOCK_MYOVERVIEW_DISPLAY_CATEGORIES_ON', 'on');
define('BLOCK_MYOVERVIEW_DISPLAY_CATEGORIES_OFF', 'off');

/**
 * Get the current user preferences that are available
 *
 * @uses core_user::is_current_user
 *
 * @return array[] Array representing current options along with defaults
 */
function block_myoverview_user_preferences(): array {
    $preferences['block_myoverview_user_grouping_preference'] = array(
        'null' => NULL_NOT_ALLOWED,
        'default' => BLOCK_MYOVERVIEW_GROUPING_ALL,
        'type' => PARAM_ALPHA,
        'choices' => array(
            BLOCK_MYOVERVIEW_GROUPING_ALLINCLUDINGHIDDEN,
            BLOCK_MYOVERVIEW_GROUPING_ALL,
            BLOCK_MYOVERVIEW_GROUPING_INPROGRESS,
            BLOCK_MYOVERVIEW_GROUPING_FUTURE,
            BLOCK_MYOVERVIEW_GROUPING_PAST,
            BLOCK_MYOVERVIEW_GROUPING_FAVOURITES,
            BLOCK_MYOVERVIEW_GROUPING_HIDDEN,
            BLOCK_MYOVERVIEW_GROUPING_CUSTOMFIELD,
        ),
        'permissioncallback' => [core_user::class, 'is_current_user'],
    );

    $preferences['block_myoverview_user_grouping_customfieldvalue_preference'] = [
        'null' => NULL_ALLOWED,
        'default' => null,
        'type' => PARAM_RAW,
        'permissioncallback' => [core_user::class, 'is_current_user'],
    ];

    $preferences['block_myoverview_user_sort_preference'] = array(
        'null' => NULL_NOT_ALLOWED,
        'default' => BLOCK_MYOVERVIEW_SORTING_LASTACCESSED,
        'type' => PARAM_ALPHA,
        'choices' => array(
            BLOCK_MYOVERVIEW_SORTING_TITLE,
            BLOCK_MYOVERVIEW_SORTING_LASTACCESSED,
            BLOCK_MYOVERVIEW_SORTING_SHORTNAME
        ),
        'permissioncallback' => [core_user::class, 'is_current_user'],
    );

    $preferences['block_myoverview_user_view_preference'] = array(
        'null' => NULL_NOT_ALLOWED,
        'default' => BLOCK_MYOVERVIEW_VIEW_CARD,
        'type' => PARAM_ALPHA,
        'choices' => array(
            BLOCK_MYOVERVIEW_VIEW_CARD,
            BLOCK_MYOVERVIEW_VIEW_LIST,
            BLOCK_MYOVERVIEW_VIEW_SUMMARY,
            BLOCK_MYOVERVIEW_VIEW_PETEL
        ),
        'permissioncallback' => [core_user::class, 'is_current_user'],
    );

    $preferences['/^block_myoverview_hidden_course_(\d)+$/'] = array(
        'isregex' => true,
        'choices' => array(0, 1),
        'type' => PARAM_INT,
        'null' => NULL_NOT_ALLOWED,
        'default' => 0,
        'permissioncallback' => [core_user::class, 'is_current_user'],
    );

    $preferences['block_myoverview_user_paging_preference'] = array(
        'null' => NULL_NOT_ALLOWED,
        'default' => BLOCK_MYOVERVIEW_PAGING_12,
        'type' => PARAM_INT,
        'choices' => array(
            BLOCK_MYOVERVIEW_PAGING_12,
            BLOCK_MYOVERVIEW_PAGING_24,
            BLOCK_MYOVERVIEW_PAGING_48,
            BLOCK_MYOVERVIEW_PAGING_96,
            BLOCK_MYOVERVIEW_PAGING_ALL
        ),
        'permissioncallback' => [core_user::class, 'is_current_user'],
    );

    return $preferences;
}

/**
 * Pre-delete course hook to cleanup any records with references to the deleted course.
 *
 * @param stdClass $course The deleted course
 */
function block_myoverview_pre_course_delete(\stdClass $course) {
    // Removing any favourited courses which have been created for users, for this course.
    $service = \core_favourites\service_factory::get_service_for_component('core_course');
    $service->delete_favourites_by_type_and_item('courses', $course->id);
}

function block_myoverview_assignment_human_dates($event) {
    global $DB;

    $output = false;
    $currenttime = time();

    if ($event->modulename == 'questionnaire') {
        if ($cm = $DB->get_record('course_modules', ['id' => $event->instance])) {
            $questionnaire = $DB->get_record('questionnaire', ['id' => $cm->instance]);
    
            if ($questionnaire) {
                $opendate = $questionnaire->opendate;
                $closedate = $questionnaire->closedate;
    
                if ($closedate > $currenttime) {
                    // Still open – show how long until deadline.
                    $delta = $closedate - $currenttime;
    
                    if ($delta <= 86400) {
                        $a = ceil($delta / 3600) . ' ' . get_string('hours');
                        $str = get_string('cut_of_date_less_days_label', 'block_myoverview', $a);
                    } else if ($delta <= 3 * 86400) {
                        $days = floor($delta / 86400);
                        $hours = floor(($delta % 86400) / 3600);
                        $a = $days . ' ' . get_string('days') . ' ' . get_string('and', 'block_myoverview') . ' ' . $hours . ' ' . get_string('hours');
                        $str = get_string('cut_of_date_less_days_label', 'block_myoverview', $a);
                    } else {
                        $str = get_string('cut_of_date', 'block_myoverview');
                    }
    
                    $url = new moodle_url('/calendar/view.php', ['view' => 'day', 'time' => $closedate]);
                    $output = html_writer::link($url, $str);
    
                } else if ($closedate > 0 && ($currenttime - $closedate) <= (7 * 86400)) {
                    // Closed within last 7 days – still relevant.
                    $str = get_string('cut_of_date', 'block_myoverview');
                    $url = new moodle_url('/calendar/view.php', ['view' => 'day', 'time' => $closedate]);
                    $output = html_writer::link($url, $str);
                }
            }

        }
    }

    if ($event->modulename == 'assign') {
        $unixtime = $event->timestart;
        $defaulttime = $event->formattedtime;
        $seconds_since_due = $currenttime - $unixtime;
    
        if ($unixtime > $currenttime) {
            // Assignment is in the future.
    
            if (($unixtime - $currenttime) <= 86400) {
                // One day before.
                $str = get_string('one_day_before_assignment', 'block_myoverview');
            } else if (($unixtime - $currenttime) <= 2 * 86400) {
                // Two days before.
                $str = get_string('two_days_before_assignment', 'block_myoverview');
            } else {
                // More than two days – show default formatted time.
                $output = $defaulttime;
                return $output;
            }
            $url = new moodle_url('/calendar/view.php', ['view' => 'day', 'time' => $unixtime]);
            $output = html_writer::link($url, $str);
        } else if ($seconds_since_due <= 7 * 86400) {
            // Assignment is overdue, but within the last 7 days.
            $str = get_string('cut_of_date', 'block_myoverview');
            $url = new moodle_url('/calendar/view.php', ['view' => 'day', 'time' => $unixtime]);
            $output = html_writer::link($url, $str);
        } else {
            // Assignment is overdue and too old – show nothing.
            $output = '';
        }
    }
    return $output;
}