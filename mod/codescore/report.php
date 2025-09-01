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

global $USER;

if ($data = data_submitted()) {
     require_once($CFG->libdir.'/gradelib.php');
     $grade = optional_param('grade', '', PARAM_RAW);
     $id = optional_param('id', '', PARAM_RAW);
     $cmid = optional_param('cmid', '', PARAM_RAW);
     $userid = optional_param('userid', '', PARAM_RAW);
     $data->grade = $grade;
     $data->id = $id;
     // Save attempt.
     $modulecontext = context_module::instance($cmid);
     $isteacher = has_capability('mod/codescore:addinstance', $modulecontext);
    if (!$isteacher) {
          return;
    }
     $attemptid = codescore_set_grade_attempt($data);

     $modulecontext = context_module::instance($id);

     $cm = get_coursemodule_from_id('codescore', $cmid, 0, false, MUST_EXIST);
     $course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
     $moduleinstance = $DB->get_record('codescore', array('id' => $cm->instance), '*', MUST_EXIST);

     $redirecturl = new moodle_url('/mod/codescore/view.php', array('id' => $cmid));
     $params = [
          'relateduserid' => $USER->id,
          'objectid' => context_module::instance($cmid)->id,
          'context' => context_module::instance($cmid),
          'other' => [
               'grade' => $grade,
          ],
     ];
     $event = \mod_codescore\event\attempt_grade::create($params);
     $event->trigger();

     $moduleinstance->grade = $data->grade;
     $moduleinstance->userid = $userid;
     $attempts = codescore_get_user_attempts($cm->id, $userid);
     $maxgrade = 0;
     if (count($attempts) === 1 || count($attempts) === 0) {
          codescore_grade_item_update($moduleinstance);
     } else {
         foreach ($attempts as &$value) {
             if ((int)$value->grade >= $maxgrade) {
                  $maxgrade = (int)$value->grade;
             }
         }
     }
     if ($maxgrade <= (int)$data->grade) {
          codescore_grade_item_update($moduleinstance);
     }

     redirect($redirecturl);
}

$id = required_param('cmid', PARAM_INT); // Course module id
$userid = required_param('userid', PARAM_INT); // Course module id
$attemptindex = required_param('attempt', PARAM_INT);
$index = required_param('index', PARAM_INT);

$PAGE->requires->css('/mod/codescore/codemirror/codemirrormerge.css');
$PAGE->requires->css('/mod/codescore/codemirror/codemirror.css');

if ($id) {
     $cm = get_coursemodule_from_id('codescore', $id, 0, false, MUST_EXIST);
     $course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
     $moduleinstance = $DB->get_record('codescore', array('id' => $cm->instance), '*', MUST_EXIST);
} else {
     $moduleinstance = $DB->get_record('codescore', array('id' => $c), '*', MUST_EXIST);
     $course = $DB->get_record('course', array('id' => $moduleinstance->course), '*', MUST_EXIST);
     $cm = get_coursemodule_from_instance('codescore', $moduleinstance->id, $course->id, false, MUST_EXIST);
}
$modulecontext = context_module::instance($id);
require_login($course, true, $cm);
require_capability("mod/codescore:view", $modulecontext);

$PAGE->set_context($modulecontext);
$PAGE->set_url('/mod/codescore/report.php');
$PAGE->set_title(format_string($moduleinstance->name));
$PAGE->set_heading(format_string($course->fullname));

echo $OUTPUT->header();

$attempt = $DB->get_records(
     'codescore_attempts',
     ['userid' => $userid, 'codescore' => $id, 'attempt' => $attemptindex]
);

$user = $DB->get_record('user', array('id' => $userid));
$name = $user->firstname . " " . $user->lastname;
$isteacher = has_capability('mod/quiz:addinstance', $modulecontext);

$langobject = [
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

];

$attempt[$index]->cmid = $id;
$attempt[$index]->grade = intval($attempt[$index]->grade);
$attempt[$index]->aigrade = intval($attempt[$index]->aigrade);
$attempt[$index]->backUrl = new moodle_url('/mod/codescore/report.php', array('cmid' => $id));
$attempt[$index]->isteacher = $isteacher;
$attempt[$index]->lang = $langobject[$moduleinstance->programminglang];
$attempt[$index]->task = $moduleinstance->task;
$attempt[$index]->name = $name;
$attempt[$index]->rubrics = json_decode($attempt[$index]->rubricsobject, true);

echo $OUTPUT->render_from_template('mod_codescore/report', $attempt[$index]);

$params = [
     'objectid' => $attemptindex,
     'relateduserid' => $USER->id,
     'context' => $modulecontext,
];
$event = \mod_codescore\event\attempt_viewed::create($params);
$event->add_record_snapshot('codescore_attempts', $attempt[$index]);
$event->trigger();
$attempts = codescore_get_user_attempts($id, $userid);

echo $OUTPUT->footer();
