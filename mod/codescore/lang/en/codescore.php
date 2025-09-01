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
 * Plugin strings are defined here.
 *
 * @package     mod_codescore
 * @category    string
 * @copyright   2024 Devlion <info@devlion.co>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['missingidandcmid'] = 'Missing id and cmid';
$string['modulename'] = 'Codescore';
$string['modulename_help'] = 'Codescore helps teacher grade the student\'s code using AI';
$string['timing'] = 'Timing';
$string['codescoreopenclose'] = 'Open and close dates';
$string['codescoreopenclose_help'] = 'Students can only start their attempt(s) after the open time and they must complete their attempts before the close time.';
$string['codescoreopen'] = 'Open the activity';
$string['codescoreclose'] = 'Close the activity';
$string['codescoreclose_help'] = 'Students can only start their attempt(s) after the open time and they must complete their attempts before the close time.';
$string['feedbacklang'] = 'Language of feedback';

$string['modulenameplural'] = 'Codescore\'s';
$string['newmodulesettings'] = 'Settings';
$string['nonewmodules'] = 'No new modules';
$string['pluginadministration'] = 'Codescore plugin administration';
$string['pluginname'] = 'Code Score';
$string['task'] = 'Write task';
$string['codescorefieldset'] = 'Fieldset';
$string['codescoresettings'] = 'Settings';
$string['codescorename'] = 'Title';
$string['codescorename_help'] = 'Help';
$string['privacy:metadata'] = 'Code Score does not store any personal data';
$string['view'] = 'View';
$string['grading'] = 'Grading options';
$string['syntaxgrade'] = 'Grade for syntax';
$string['syntaxgrade_help'] = "Maximum grade for student's code syntax correctness";
$string['correctoutput'] = 'Grade for correct output';
$string['correctoutput_help'] = "Maximum grade for student's code output/function output";
$string['correctsolution'] = 'Grade for correct solution';
$string['correctsolution_help'] = "Maximum grade for student's code optimization/patterns";
$string['allcases'] = 'Grade for covering all cases';
$string['allcases_help'] = "Maximum grade for student's code covering all cases (e.g function works properly with any set of parameters)";
$string['programminglang'] = 'Select programming language';
$string['programminglangview'] = 'Programming language:';
$string['autograde'] = 'Set automatic grade by AI';
$string['alowedlanguages'] = 'Select languages that will be used';
$string['multiattempts'] = 'Multiple attempts';
$string['python'] = 'Python';
$string['filltask'] = 'Please provide task description';
$string['selectlang'] = 'Please select language';
$string['yes'] = 'Yes';
$string['no'] = 'No';
$string['showfeedback'] = 'Show AI feedback to students';
$string['aifeedback'] = 'AI feedback:';
$string['noinstances'] = 'There are no CodeScore instances';
$string['modgradeswarn'] = 'Total sum of active grades should be equal to 100';

// Admin settings
$string['apikey'] = 'API Key';
$string['apikeyexplain'] = 'The key to access AI API. The free version allows you 10 prompt executions per week. To get the Pro version, contact us at <a href="mailto:info@devlion.co">info@devlion.co</a>';
$string['apiurl'] = 'API URL';
$string['apiurlexplain'] = 'The url to access AI API';
$string['adminheading'] = 'Rubrics';
$string['adminheadingexplain'] = 'Change grading rubrics';
$string['rubricscount'] = 'Number of rubrics';
$string['rubricblock'] = 'Rubric';
$string['rubricname'] = 'Rubric name';
$string['rubricprompt'] = 'Rubric prompt';
$string['rubric1prompt'] = 'Grade for student\'s code syntax';
$string['rubric2prompt'] = 'Grade for the output of the code';
$string['rubric3prompt'] = 'Grade for correct code solution';
$string['rubric4prompt'] = 'Grade for code covering all cases';
$string['gradewarn'] = 'Total sum of grades should be equal to 100';

// View
$string['viewreportsbtn'] = 'View reports';
$string['taskview'] = 'The task is:';
$string['submissionstatus'] = 'Submission status: ';
$string['submitted'] = 'submitted';
$string['notsubmitted'] = 'not submitted';
$string['yoursubmissions'] = 'Your attempt: ';
$string['waitingforgrade'] = 'Waiting for grade';
$string['graded'] = 'Graded';
$string['notgraded'] = 'Not graded yet';
$string['viewdategraded'] = 'Date graded:';
$string['gradestatus'] = 'Status:';
$string['unavaibleactivity'] = "This activity isn't available for now";
$string['startattempt'] = 'Start attempt';

// Reports
$string['studentsreports'] = "Student's reports:";
$string['noreports'] = "No reports yet";
$string['deleteBtnText'] = 'Delete attempts';
$string['areyousure'] = 'Are you sure you want to delete attempts?';
$string['irreversible'] = 'This action will be irreversible';
$string['regrade'] = 'Regrade attempts';

// Attempt
$string['taskheader'] = 'The task is:';
$string['saveandexit'] = "Save and exit";
$string['notesplaceholder'] = 'You can write notes for teachers here';

// Attempts table
$string['tablename'] = "Name";
$string['startedat'] = "Started at";
$string['timefinish'] = "Submitted at";
$string['timegraded'] = "Graded at";
$string['syntaxgradetable'] = "Syntax grade";
$string['outputgradetable'] = "Output grade";
$string['problemgradetable'] = "Problem solution grade";
$string['casesgradetable'] = "All cases coverage grade";
$string['tableaigrade'] = "AI grade";
$string['tableteachergrade'] = "Final grade";
$string['statusattempt'] = "Status";
$string['finishedstatus'] = "Finished";
$string['pendingstatus'] = "Pending";
$string['submittedstatus'] = "Submitted";

// Report
$string['taskoverview'] = 'Task overview';
$string['reportlang'] = 'Programming language: ';
$string['taskreport'] = 'Task: ';
$string['maxgrade'] = 'Maximum for';
$string['reportname'] = 'report';
$string['reportgrade'] = 'Grade: ';
$string['aigradereport'] = 'AI grade: ';
$string['syntaxreport'] = 'Syntax grade: ';
$string['outputreport'] = 'Output grade: ';
$string['problemreport'] = 'Problem solving grade: ';
$string['casesreport'] = 'All cases coverage grade: ';
$string['notesreport'] = 'Notes: ';
$string['codediffreport'] = 'Code diff: ';
$string['studentcodereport'] = "Student's code";
$string['correctcodereport'] = 'Corrected code';
$string['reportgradebtn'] = 'Grade';

// Privacy
$string['privacy:metadata:codescore_attempts'] = 'This table contains info about user\'s attempts';
$string['privacy:metadata:codescore_attempts:userid'] = 'The ID of the user that saved this report.';
$string['privacy:metadata:codescore_attempts:attempt'] = 'Sequentially numbers of this student\'s attempts.';
$string['privacy:metadata:codescore_attempts:timestart'] = 'Time at which user started attempt.';
$string['privacy:metadata:codescore_attempts:timefinish'] = 'Time at which user finished attempt.';
$string['privacy:metadata:codescore_attempts:timemodified'] = 'Time at which user modified attempt.';
$string['privacy:metadata:codescore_attempts:timemodifiedoffline'] = 'Time at which user modified attempt being offline.';
$string['privacy:metadata:codescore_attempts:timegraded'] = 'Time at which user graded attempt.';
$string['privacy:metadata:codescore_attempts:code'] = 'Code that was submitted in this attempt.';
$string['privacy:metadata:codescore_attempts:studentnotes'] = 'Notes for this attempt.';
$string['privacy:metadata:codescore_attempts:grade'] = 'Grade for this attempt.';

$string['privacy:metadata:codescore'] = 'This table contains info that is sent to API so it can give feedback about student\'s code';
$string['privacy:metadata:codescore:task'] = 'Coding task of the instance';
$string['privacy:metadata:codescore:programminglang'] = 'Programming language that needs to be used in the instance';
$string['privacy:metadata:codescore:syntaxgrading'] = "Maximum grade for student's code syntax correctness";
$string['privacy:metadata:codescore:outputgrading'] = "Maximum grade for student's code output/function output";
$string['privacy:metadata:codescore:problemsolutiongrading'] = "Maximum grade for student's code optimization/patterns";
$string['privacy:metadata:codescore:allcasesgrading'] = "Maximum grade for student's code covering all cases (e.g function works properly with any set of parameters)";
