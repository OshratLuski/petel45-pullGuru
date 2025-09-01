<?php
// This file is part of Moodle - https://moodle.org/
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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Prints an instance of mod_codescore.
 *
 * @package     mod_codescore
 * @copyright   2024 Devlion <info@devlion.co>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__ . '/../../config.php');
require_once(__DIR__ . '/lib.php');
require_once($CFG->dirroot . '/mod/codescore/locallib.php');

global $COURSE, $USER;

// Course module id.
$id = optional_param('id', 0, PARAM_INT);

if ($id) {
    $cm = get_coursemodule_from_id('codescore', $id, 0, false, MUST_EXIST);
    $course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
    $moduleinstance = $DB->get_record('codescore', array('id' => $cm->instance), '*', MUST_EXIST);
} else {
    $moduleinstance = $DB->get_record('codescore', array('id' => $c), '*', MUST_EXIST);
    $course = $DB->get_record('course', array('id' => $moduleinstance->course), '*', MUST_EXIST);
    $cm = get_coursemodule_from_instance('codescore', $moduleinstance->id, $course->id, false, MUST_EXIST);
}

$context = context_module::instance($cm->id);
require_login($course, true, $cm);
require_capability("mod/codescore:view", $context);

$modulecontext = context_module::instance($cm->id);

$PAGE->requires->css('/mod/codescore/codemirror/codemirror.css');

$event = \mod_codescore\event\course_module_viewed::create(array(
    'objectid' => $moduleinstance->id,
    'context' => $modulecontext,
));
$event->add_record_snapshot('course', $course);
$event->add_record_snapshot('codescore', $moduleinstance);
$event->trigger();

$PAGE->set_url('/mod/codescore/view.php', array('id' => $cm->id));
$PAGE->set_title(format_string($moduleinstance->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($modulecontext);

echo $OUTPUT->header();

$issubmitted = codescore_get_user_attempts($id, $USER->id);

$canattempt = has_capability('mod/quiz:attempt', $context);
$isteacher = has_capability('mod/quiz:addinstance', $context);

$currenttime = time();
$timeopened = $moduleinstance->timeopened;
$timeclosed = (int)$moduleinstance->timeclosed;
$isavaible = true;
if ($timeclosed === 0) {
    $timeclosed = INF;
}

if ((int)$timeopened < $currenttime && $timeclosed > $currenttime) {
    $isavaible = false;
}

if ($isteacher) {
    $url = new moodle_url('/mod/codescore/reports.php', array('cmid' => $id));
    echo $OUTPUT->render_from_template('mod_codescore/reportsbtn', ['url' => $url]);
}

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
    'buttonClass' => '',
    'textareaClass' => '',
    'value' => '',
    'submission' => get_string('notsubmitted', 'codescore'),
    'issubmitted' => false,
    'cmid' => $cm->id,
    'task' => $moduleinstance->task,
    'isavaible' => $isavaible,
    'lang' => $langs[$moduleinstance->programminglang],
];
if ($issubmitted) {
    $renderdata = (object) [
        'buttonClass' => 'd-none',
        'textareaClass' => 'disabled',
        'value' => 'function() { }',
        'submission' => get_string('submitted', 'codescore'),
        'issubmitted' => $moduleinstance->multiattempts === '1' ? false : true,
        'cmid' => $cm->id,
        'task' => $moduleinstance->task,
        'isavaible' => $isavaible,
        'lang' => $langs[$moduleinstance->programminglang],
    ];
}

echo $OUTPUT->render_from_template('mod_codescore/view', $renderdata);


$attempts = codescore_get_user_attempts($id, $USER->id);
$data = new stdClass;
$data->attempts = array_values($attempts);
foreach ($data->attempts as &$value) {
    $userid = $value->userid;
    $user = $DB->get_record('user', array('id' => $userid));
    $name = $user->firstname . " " . $user->lastname;
    $value->username = $name;
}
$data->candelete = has_capability('mod/codescore:addinstance', context_system::instance());

$gradinginfo = grade_get_grades($COURSE->id, 'mod', 'codescore', $moduleinstance->id, array($USER->id));
$lastrenderdata = end($data->attempts);
$rubricscount = NULL;
if ($lastrenderdata) {
    $lastrenderdata->grade = $lastrenderdata->timegraded === "0" ? "" : (int)end($gradinginfo->items[0]->grades)->grade;
    $lastrenderdata->aigrade = $lastrenderdata->aigrade ? (int)$lastrenderdata->aigrade : '';
    $lastrenderdata->hasAttempts = $attempts ? true : false;
    $lastrenderdata->title = get_string('yoursubmissions', 'codescore');

    $codescore = $DB->get_record('codescore', ['id' => $cm->instance]);

    if ($lastrenderdata->timegraded !== "0" && $codescore->showfeedback === '1') {
        $lastrenderdata->formatfeedback = get_string('aifeedback', 'codescore') . ' ' .$lastrenderdata->feedback;
    }
    if ($lastrenderdata->timegraded === "0") {
        $lastrenderdata->status = get_string('waitingforgrade', 'codescore');
    } else {
        $lastrenderdata->status = get_string('graded', 'codescore');
    }
    $lastrenderdata->timestart = $lastrenderdata->timestart !== '0' ? date('Y-m-d h:i:s', $lastrenderdata->timestart) : '';
    $lastrenderdata->timefinish = $lastrenderdata->timefinish !== '0' ? date('Y-m-d h:i:s', $lastrenderdata->timefinish) : '';
    $lastrenderdata->convertedTime = $lastrenderdata->timegraded !== '0' ? date('Y-m-d h:i:s', $lastrenderdata->timegraded) : get_string('notgraded', 'codescore');
    $lastrenderdata->isteacher = $isteacher;
    $lastrenderdata->rubrics = json_decode($lastrenderdata->rubricsobject, true);
}

if ($attempts) {
    echo $OUTPUT->render_from_template('mod_codescore/lastattempt', $lastrenderdata);
    $PAGE->requires->js_call_amd('mod_codescore/lastattempt', 'init', []);
}


echo $OUTPUT->footer();
