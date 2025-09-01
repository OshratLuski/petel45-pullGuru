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
 * Self enrolment plugin - support for user self enrolment.
 *
 * @package    enrol_self
 * @copyright  2018 Nadav Kavalerchik {@mailto nadav.kavalerchik@weizmann.ac.il}
 * @copyright  2010 Petr Skoda  {@link http://skodak.org}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('../../config.php');
require_once($CFG->dirroot.'/group/lib.php');

$enrolkey = required_param('enrolkey', PARAM_TEXT);
$confirm = optional_param('confirm', 0, PARAM_BOOL);

$enrolkey = rtrim(ltrim($enrolkey)); // Remove redundant spaces (left and right)
$instance = $DB->get_record('enrol', array('password' => $enrolkey, 'enrol' => 'self'));
$groups = $DB->get_records('groups', array( 'enrolmentkey' => $enrolkey));

if ((null === $instance && null === $groups) || '' === $enrolkey) {
    $PAGE->set_url('/enrol/self/enrolwithkey.php', array('enrolkey' => $enrolkey));
    $PAGE->set_title(get_string('error'));
    echo $OUTPUT->header();
    redirect(new moodle_url('/my'), get_string('enrolkey_error', 'theme_petel'), 5);
    echo $OUTPUT->footer();
}

$group = false;
$courseid = 0;
if (!$instance) {
    if ($group = array_shift($groups)) {
        $courseid = $group->courseid;
    }
} else {
    $courseid = $instance->courseid;
}

if (!$courseid) {
    redirect(new moodle_url('/my'), get_string('enrolkey_error', 'theme_petel'), 5);
}

$course = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);
$context = context_course::instance($course->id, MUST_EXIST);
$PAGE->set_context($context);

require_login();

if (!$enrolself = enrol_get_plugin('self')) {
    throw new coding_exception('Can not instantiate enrol_self');
}

if ($confirm && confirm_sesskey()) {
    // Enrol user as "student" into course.
    //$studentrole = $DB->get_record('role', array('shortname' => 'student'), '*', MUST_EXIST);

    if (!$instance && $courseid) {
        // Probably group key enrolment
        $instance = $DB->get_record('enrol', array('courseid' => $courseid, 'enrol' => 'self'));
    }
    // Enrol user with role from course enrolment instance (should be set to student, as default)
    // but, also allow other types of roles.
    $enrolself->enrol_user($instance, $USER->id, $instance->roleid);

    // Also, add users to group(s), if a proper key was given.
    if ($group) {
        // It must be a group enrolment, let's assign group too.
        $groups = $DB->get_records('groups', array('courseid'=>$instance->courseid), 'id', 'id, enrolmentkey');
        foreach ($groups as $group) {
            if (empty($group->enrolmentkey)) {
                continue;
            }
            if ($group->enrolmentkey === $enrolkey) {

                // Add user to group.
                groups_add_member($group->id, $USER->id);
                break;
            }
        }
    }

    // Send welcome message.
    //if ($instance->customint4 != ENROL_DO_NOT_SEND_EMAIL) {
        //$this->email_welcome_message($instance, $USER);
    //}

    // Take user to course.
    redirect(new moodle_url('/course/view.php', array('id'=>$course->id)));
}

$PAGE->set_url('/enrol/self/enrolwithkey.php', array('enrolkey' => $enrolkey));
$PAGE->set_title($enrolself->get_instance_name($instance));

echo $OUTPUT->header();
$yesurl = new moodle_url($PAGE->url, array('confirm' => 1, 'sesskey' => sesskey(), 'enrolkey' => $enrolkey));
$nourl = new moodle_url('/my');
$message = get_string('enrolselfconfirm', 'theme_petel', format_string($course->fullname));
echo $OUTPUT->confirm($message, $yesurl, $nourl);
echo $OUTPUT->footer();
