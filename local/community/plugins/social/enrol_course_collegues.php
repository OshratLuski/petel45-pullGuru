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
 * This is a one-line short description of the file
 *
 * You can have a rather longer description of the file as well,
 * if you like, and it can span multiple lines.
 *
 * @package    community_social
 * @copyright  2019 Devlion <info@devlion.co>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../../../config.php');
require_once(__DIR__ . '/lib.php');

require_login();
$PAGE->set_context(context_system::instance());

$courseid = optional_param('id', 0, PARAM_INT);
$userid = optional_param('userid', 0, PARAM_INT);

if (!$row = $DB->get_record('course', ['id' => $courseid]) || !$userid) {
    throw new coding_exception('Wrong course');
}

if ($obj = $DB->get_record('community_social_shrd_crss', array('courseid' => $courseid, 'userid' => $userid))) {
    if ($obj->userid != $USER->id) {

        $context = \context_course::instance($courseid);
        foreach (get_user_roles($context, $USER->id, false) as $role) {
            if ($role->shortname == 'teachercolleague') {
                redirect(new moodle_url('/course/view.php', ['id' => $courseid]));
                break;
            }
        }

        // Save to community_social_collegues.
        $row = $DB->get_record('community_social_collegues', ['userid' => $USER->id, 'social_shared_courses_id' => $obj->id]);
        if (empty($row)) {
            $dataobject = new stdClass();
            $dataobject->userid = $USER->id;
            $dataobject->social_shared_courses_id = $obj->id;
            $dataobject->approved = 1;
            $dataobject->timecreated = time();
            $dataobject->timemodified = time();
            $DB->insert_record('community_social_collegues', $dataobject);
        } else {
            $row->approved = 1;
            $DB->update_record('community_social_collegues', $row, $bulk = false);
        }

        \community_social\funcs::open_permission_course($USER->id, $obj->courseid);

        // Recache user.
        $social = new \community_social\social();
        $social->refreshUser($USER->id);
        $social->refreshUser($obj->userid);
    }
}

redirect(new moodle_url('/course/view.php', ['id' => $courseid]));
