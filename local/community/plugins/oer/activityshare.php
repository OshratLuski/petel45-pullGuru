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
require(__DIR__ . '/../../../../config.php');

require_login();

// We get MID of a module that is in the OER catalog, and find its CMID.
$mid = required_param('id', PARAM_INT);

$comments = optional_param('comments', null, PARAM_INT);
$source = optional_param('source', '', PARAM_TEXT);
$openpopup = optional_param('popup', 0, PARAM_INT);

// Set cookie.
if ($openpopup) {
    setcookie("openpopupreview", 1, time() + 86400, '/');
}

$sql = "
        SELECT cm.id 'cmid', lm.data 'mid', m.name 'name'
        FROM {course_modules} cm
         JOIN {modules} m ON m.id = cm.module
         JOIN {course} c ON c.id = cm.course
         JOIN {course_categories} oercat ON oercat.id = c.category
         JOIN {local_metadata} lm ON lm.instanceid = cm.id
         JOIN {local_metadata_field} ldf ON ldf.id = lm.fieldid
            AND ldf.contextlevel = 70
            AND ldf.shortname = 'ID'
        WHERE FIND_IN_SET(c.category,
                          (SELECT GROUP_CONCAT(ccc.id) 'OER catalog 2nd level categories'
                           FROM {course_categories} cc
                            JOIN {course_categories} ccc ON ccc.parent = cc.id
                           WHERE cc.id = (SELECT config.value
                                          FROM {config_plugins} config
                                          WHERE config.plugin = 'local_community'
                                            AND config.name = 'catalogcategoryid')))
            AND lm.data = ?
        ";

$module = $DB->get_record_sql($sql, array($mid));

if (!empty($module)) {
    switch ($module->name) {
        case "quiz":
            $urlactivity = $CFG->wwwroot . '/mod/' . $module->name . '/startattempt.php?cmid=' . $module->cmid . '&sesskey=' .
                    $USER->sesskey;
            break;
        case "questionnaire":
            $urlactivity = $CFG->wwwroot . '/mod/' . $module->name . '/preview.php?id=' . $module->cmid . '&cmid=' . $module->cmid;
            break;
        default:
            $urlactivity = $CFG->wwwroot . '/mod/' . $module->name . '/view.php?id=' . $module->cmid . '&cmid=' . $module->cmid;
    }
} else {
    throw new moodle_exception('incorrectmid', 'error');
}

// Update counter community_oer_wht_new.
\community_oer\activity_oer::funcs()::whats_new_update_counter($module->cmid);

// Event.
$eventdata = array(
        'userid' => $USER->id,
        'activityid' => $module->cmid,
        'other' => ['source' => $source]
);
\community_oer\event\oer_activity_share_link::create_event($module->cmid, $eventdata)->trigger();

if ($comments) {
    $urlactivity .= '#comments';
}

redirect($urlactivity);
