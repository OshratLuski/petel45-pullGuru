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
 * Library functions for the Date Extension report.
 *
 * This file contains utility functions for extending Moodle's navigation,
 * and managing settings for quizzes in the Date Extension report plugin.
 *
 * @package   report_dateextend
 * @copyright 2024 Oshrat Luski <oshrat.luski@weizmann.ac.il>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

/**
 * Extends the navigation menu with a link to the Date Extension report.
 *
 * Adds a link to the report under the course's navigation node if the user has the appropriate capability.
 *
 * @param navigation_node $navigation The navigation node to extend.
 * @param stdClass $course The course object where the navigation is extended.
 * @param stdClass $context The context of the course to check capabilities.
 * @return void
 */
function report_dateextend_extend_navigation_course($navigation, $course, $context) {
    global $CFG, $OUTPUT;
    if (has_capability('report/dateextend:view', $context)) {
        $url = new moodle_url('/report/dateextend/index.php', array('id' => $course->id));
        if ($activitytype = optional_param('activitytype', '', PARAM_PLUGIN)) {
            $url->param('activitytype', $activitytype);
        }
        $navigation->add(get_string( 'dateextend', 'report_dateextend' ),
                $url, navigation_node::TYPE_SETTING, null, 'dateextend', new pix_icon('i/report', ''));
    }
}


/**
 * Saves the settings for the specified activities by updating or inserting records in the database.
 *
 * Processes a list of course module IDs, identifies associated quizzes,
 * and updates their settings in the 'quizaccess_changebehaviour' table.
 * If a course module ID does not correspond to a quiz, the function skips it.
 *
 * @param array $activities An array of course module IDs representing the activities to process.
 * @param array $settings An associative array of settings to save, including:
 *                        - 'behaviourtime' (int): The time when the behaviour starts.
 *                        - 'behaviourduration' (int): The duration of the behaviour.
 *                        - 'newbehaviour' (string): The behaviour type to apply.
 *                        - 'penalty' (int): The penalty for incorrect answers.
 * @return void
 */
function report_dateextend_save_settings($activities, $settings, $course) {
    global $DB;

    foreach ($activities as $cmid) {
        $quizid = $DB->get_field_sql("
            SELECT cm.instance
            FROM {course_modules} cm
            JOIN {modules} m ON m.id = cm.module
            WHERE m.name = 'quiz' AND cm.id = :cmid
        ", ['cmid' => $cmid]);

        if (!$quizid) {
            debugging("No quizid found for cmid: $cmid");
            continue;
        }
        $DB->delete_records('quizaccess_changebehaviour', ['quizid' => $quizid]);

        if (!empty($settings['behaviourtime']) || !empty($settings['behaviourduration'])) {
            $record = new stdClass();
            $record->quizid = $quizid;
            $record->behaviourtime = $settings['behaviourtime'];
            $record->behaviourduration = $settings['behaviourduration'];
            $record->newbehaviour = $settings['newbehaviour'];
            $record->penalty = $settings['penalty'];
            $DB->insert_record('quizaccess_changebehaviour', $record);
        }
        // Trigger the course module updated event.
        $cm = get_coursemodule_from_instance('quiz', $quizid, $course->id);
        
        // Convert the course module to cm_info to ensure compatibility.
        $cmcontext = context_module::instance($cm->id);
        $event = \core\event\course_module_updated::create([
            'context' => $cmcontext,
            'objectid' => $cm->id,
            'other' => [
                'modulename' => $cm->modname,
                'instanceid' => $cm->instance,
                'name' => $cm->name,
            ],
        ]);
        $event->trigger();
    }
}