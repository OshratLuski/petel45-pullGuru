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

require_once("../../config.php");
require_once($CFG->dirroot.'/mod/questionnaire/questionnaire.class.php');
require_once($CFG->dirroot.'/lib/excellib.class.php');

$instance = optional_param('instance', false, PARAM_INT);   // Questionnaire ID.


$sid = optional_param('sid', null, PARAM_INT);              // Survey id.
$rid = optional_param('rid', false, PARAM_INT);
$type = optional_param('type', '', PARAM_ALPHA);
$action = optional_param('action', '', PARAM_ALPHA);
$byresponse = optional_param('byresponse', false, PARAM_INT);
$individualresponse = optional_param('individualresponse', false, PARAM_INT);
$currentgroupid = optional_param('group', 0, PARAM_INT); // Groupid.
$user = optional_param('user', '', PARAM_INT);
$userid = $USER->id;

if ($instance === false) {
    if (!empty($SESSION->instance)) {
        $instance = $SESSION->instance;
    } else {
        throw new \moodle_exception('requiredparameter', 'questionnaire');
    }
}
$SESSION->instance = $instance;
$usergraph = get_config('questionnaire', 'usergraph');

if (! $questionnaire = $DB->get_record("questionnaire", array("id" => $instance))) {
    throw new \moodle_exception('incorrectquestionnaire', 'questionnaire');
}
if (! $course = $DB->get_record("course", array("id" => $questionnaire->course))) {
    throw new \moodle_exception('coursemisconf');
}
if (! $cm = get_coursemodule_from_instance("questionnaire", $questionnaire->id, $course->id)) {
    throw new \moodle_exception('invalidcoursemodule');
}

require_course_login($course, true, $cm);

$questionnaire = new questionnaire($course, $cm, $questionnaire->id, $questionnaire);

// Add renderer and page objects to the questionnaire object for display use.
$questionnaire->add_renderer($PAGE->get_renderer('mod_questionnaire'));
$questionnaire->add_page(new \mod_questionnaire\output\reportpage());

// If you can't view the questionnaire, or can't view a specified response, error out.
$context = context_module::instance($cm->id);
if (!has_capability('mod/questionnaire:readallresponseanytime', $context) &&
  !($questionnaire->capabilities->view && $questionnaire->can_view_response($rid))) {
    // Should never happen, unless called directly by a snoop...
    throw new \moodle_exception('nopermissions', 'moodle', $CFG->wwwroot.'/mod/questionnaire/view.php?id='.$cm->id);
}

$questionnaire->canviewallgroups = has_capability('moodle/site:accessallgroups', $context);
$sid = $questionnaire->survey->id;

$url = new moodle_url($CFG->wwwroot.'/mod/questionnaire/report.php');
if ($instance) {
    $url->param('instance', $instance);
}

$url->param('action', $action);

if ($type) {
    $url->param('type', $type);
}
if ($byresponse || $individualresponse) {
    $url->param('byresponse', 1);
}
if ($user) {
    $url->param('user', $user);
}
if ($action == 'dresp') {
    $url->param('action', 'dresp');
    $url->param('byresponse', 1);
    $url->param('rid', $rid);
    $url->param('individualresponse', 1);
}
if ($currentgroupid !== null) {
    $url->param('group', $currentgroupid);
}

$PAGE->set_url($url);
$PAGE->set_context($context);

// Tab setup.
if (!isset($SESSION->questionnaire)) {
    $SESSION->questionnaire = new stdClass();
}
$SESSION->questionnaire->current_tab = 'allreport';

// Get all responses for further use in viewbyresp and deleteall etc.
// All participants.
$params = array('questionnaireid' => $instance, 'complete' => 'y');
$respsallparticipants = $DB->get_records('questionnaire_response', $params, 'id', 'id,questionnaireid,submitted,userid');
$SESSION->questionnaire->numrespsallparticipants = count ($respsallparticipants);
$SESSION->questionnaire->numselectedresps = $SESSION->questionnaire->numrespsallparticipants;

// Available group modes (0 = no groups; 1 = separate groups; 2 = visible groups).
$groupmode = groups_get_activity_groupmode($cm, $course);
$questionnairegroups = '';
$groupscount = 0;
$SESSION->questionnaire->respscount = 0;
$SESSION->questionnaire_survey_id = $sid;


require_capability('mod/questionnaire:downloadresponses', $context);

// Use the questionnaire name as the file name. Clean it and change any non-filename characters to '_'.
$name = clean_param($questionnaire->name, PARAM_FILE);

//$name = preg_replace("/[^A-Z0-9]+/i", "_", trim($name));
$name = trim($name);

$choicecodes = optional_param('choicecodes', '0', PARAM_INT);
$choicetext  = optional_param('choicetext', '0', PARAM_INT);
$output = $questionnaire->generate_csv('', $user, $choicecodes, $choicetext, $currentgroupid);
        
$downloadfilename = clean_filename("$name.xls");
$strgrades = get_string('grades', 'questionnaire');

// Creating a workbook
$workbook = new MoodleExcelWorkbook("-");
// Sending HTTP headers
$workbook->send($downloadfilename);
// Adding the worksheet
$myxls = $workbook->add_worksheet($strgrades);  

foreach ($output as $key => $item) {
    foreach ($item as $id => $field) {
        $myxls->write_string($key, $id, $field);
    }        
}        
      
$workbook->close();
exit();
