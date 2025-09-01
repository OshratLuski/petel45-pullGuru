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
 *
 * @package     mod_codescore
 * @copyright   2024 Devlion <info@devlion.co>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');
require_once($CFG->dirroot . '/mod/codescore/locallib.php');
require_once(__DIR__ . '/lib.php');

global $USER, $PAGE;

$id = required_param('cmid', PARAM_INT); // Course module id

if ($id) {
    $cm = get_coursemodule_from_id('codescore', $id, 0, false, MUST_EXIST);
    $course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
    $moduleinstance = $DB->get_record('codescore', array('id' => $cm->instance), '*', MUST_EXIST);
} else {
    $moduleinstance = $DB->get_record('codescore', array('id' => $c), '*', MUST_EXIST);
    $course = $DB->get_record('course', array('id' => $moduleinstance->course), '*', MUST_EXIST);
    $cm = get_coursemodule_from_instance('codescore', $moduleinstance->id, $course->id, false, MUST_EXIST);
}

$modulecontext = context_module::instance($cm->id);
require_login($course, true, $cm);
require_capability("mod/codescore:view", $modulecontext);

if ($modulecontext->contextlevel == CONTEXT_MODULE) {
    // Calling $PAGE->set_context should be enough, but it seems that it is not.
    // Therefore, we get the right $cm and $course, and set things up ourselves.
    $cm = get_coursemodule_from_id(false, $modulecontext->instanceid, 0, false, MUST_EXIST);
    $PAGE->set_cm($cm, $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST));
}

$PAGE->set_url('/mod/codescore/reports.php', array('id' => $cm->id));
$PAGE->set_title(format_string($moduleinstance->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($modulecontext);

echo $OUTPUT->header();

$attempts = codescore_get_attempts($id);
$data = new stdClass;
$data->attempts = array_values($attempts);

foreach ($data->attempts as &$value) {
    $userid = $value->userid;
    $user = $DB->get_record('user', array('id' => $userid));
    $name = fullname($user);
    $value->username = $name;
}
$data->title = get_string('studentsreports', 'mod_codescore');
$data->candelete = has_capability('mod/codescore:addinstance', context_system::instance());
$data->hasAttempts = $attempts ? true : false;
echo $OUTPUT->render_from_template('mod_codescore/reports', $data);
$PAGE->requires->js_call_amd('mod_codescore/reports', 'init', [$data]);

echo $OUTPUT->footer();
