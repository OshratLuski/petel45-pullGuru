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
require_once($CFG->dirroot . '/mod/codescore/classes/task/check_attempt.php');
require_once(__DIR__ . '/lib.php');

global $USER;

$id = required_param('cmid', PARAM_INT); // Course module id

if ($data = data_submitted()) {
    $code = optional_param('code', '', PARAM_RAW);
    $notes = optional_param('notes', '', PARAM_RAW);
    $sesskey = optional_param('sesskey', '', PARAM_RAW);
    $timestart = optional_param('timestart', '', PARAM_RAW);
    $data->timestart = $timestart;
    $data->rubriccount = get_config('mod_codescore', 'rubricscount');

    $cm = get_coursemodule_from_id('codescore', $id, 0, false, MUST_EXIST);
    $moduleinstance = $DB->get_record('codescore', array('id' => $cm->instance), '*', MUST_EXIST);

    $moduledata = json_decode($moduleinstance->rubricsobject, true);
    $config = get_config('mod_codescore');
    $newrubrics = [];

    foreach ($moduledata as $key => $value) {
        array_push($newrubrics, array(
            'name' => $value['name'],
            'enabled' => $value['enabled'],
            'maxgrade' => $value['maxgrade'],
            'grade' => '',
            'prompt' => $config->{'rubric' . strval(intval($key) + 1) . 'prompt'}
        ));
    }
    $data->rubricsobject = json_encode($newrubrics);
    confirm_sesskey($sesskey);

    // Save attempt.
    $attemptid = codescore_save_attempt($data);

    // TODO: Exec ADHOC
    codescore_exec_adhoc($attemptid);

    $redirecturl = new moodle_url('/mod/codescore/view.php', array('id' => $id));
    redirect($redirecturl);
}
$timestart = time();
if ($id) {
    $cm = get_coursemodule_from_id('codescore', $id, 0, false, MUST_EXIST);
    $course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
    $moduleinstance = $DB->get_record('codescore', array('id' => $cm->instance), '*', MUST_EXIST);
} else {
    $moduleinstance = $DB->get_record('codescore', array('id' => $c), '*', MUST_EXIST);
    $course = $DB->get_record('course', array('id' => $moduleinstance->course), '*', MUST_EXIST);
    $cm = get_coursemodule_from_instance('codescore', $moduleinstance->id, $course->id, false, MUST_EXIST);
}
$PAGE->requires->css('/mod/codescore/codemirror/codemirror.css');

$modulecontext = context_module::instance($id);
require_login($course, true, $cm);
require_capability("mod/codescore:view", $modulecontext);

$url = new moodle_url('/mod/codescore/attempt.php', array('cmid' => $id, 'userid' => $USER->id));

$PAGE->set_context($modulecontext);
$PAGE->set_url('/mod/codescore/attempt.php', array('cmid' => $id, 'userid' => $USER->id));
$PAGE->set_title(format_string($moduleinstance->name));
$PAGE->set_heading(format_string($course->fullname));

if ($modulecontext->contextlevel == CONTEXT_MODULE) {
    // Calling $PAGE->set_context should be enough, but it seems that it is not.
    // Therefore, we get the right $cm and $course, and set things up ourselves.
    $cm = get_coursemodule_from_id(false, $modulecontext->instanceid, 0, false, MUST_EXIST);
    $PAGE->set_cm($cm, $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST));
}

echo $OUTPUT->header();

$cmdb = $DB->get_record('codescore', array('id' => $moduleinstance->id));

$langs = array(
    '0' => 'Javascript',
    '1' => 'C++',
    '2' => 'Python',
    '3' => 'Java',
    '4' => 'GO',
    '5' => 'C#',
    '6' => 'PHP',
    '7' => 'HTML',
    '8' => 'Typescript',
    '9' => 'Visual Basic',
    '10' => 'Ruby',
    '11' => 'SQL',
    '12' => 'Assembly',
);

$renderdata = (object) [
    'backUrl' => new moodle_url('/mod/codescore/attempt.php', array('cmid' => $id)),
    'cmid' => $id,
    'task' => $cmdb->task,
    'timestart' => $timestart,
    'lang' => $langs[$moduleinstance->programminglang],
];

echo $OUTPUT->render_from_template('mod_codescore/attempt', $renderdata);

$params = [
    'relateduserid' => $USER->id,
    'context' => $modulecontext,
    'objectid' => $moduleinstance->id,
];

$event = \mod_codescore\event\attempt_start::create($params);
$event->trigger();

echo $OUTPUT->footer();
