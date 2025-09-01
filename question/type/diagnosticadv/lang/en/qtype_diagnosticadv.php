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
 * Strings for component 'qtype_diagnosticadv', language 'en', branch 'MOODLE_41_STABLE'
 *
 * @package    qtype
 * @subpackage diagnosticadv
 * @copyright  Devlion.ltd <info@devlion.co>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['addmoreanswerblanks'] = 'Blanks for {no} More Answers';
$string['answer'] = 'Answer: {$a}';
$string['answermustbegiven'] = 'You must enter an answer if there is a grade or feedback.';
$string['answerno'] = 'Answer {$a}';
$string['caseno'] = 'No, case is unimportant';
$string['casesensitive'] = 'Case sensitivity';
$string['caseyes'] = 'Yes, case must match';
$string['correctansweris'] = 'The correct answer is: <span dir="auto">{$a}</span>';
$string['customanswers'] = 'Own answer (leave blank to disable)';
$string['security'] = 'Enable security question';
$string['hidemark'] = 'Force hidden mark from student';
$string['showhide'] = 'Answer statistics';
$string['nodata'] = 'No report data available';
$string['tablehdranswer'] = 'Answer';
$string['tablehdranswernum'] = 'Num answers';
$string['tablehdranswersured'] = 'Sured answers';
$string['answertotal'] = 'Total';
$string['custom'] = 'Custom answer';
$string['usecase'] = 'Case sensitivity for custom answers';
$string['anonymous'] = 'Anonymous answers';
$string['required'] = 'Comment and security option is required';
$string['customlabel'] = 'Own answer';
$string['commenthdr'] = 'Please explain your answer';
$string['securityhdr'] = 'How sure are you in your answer?';
$string['securitysureyes'] = 'I am sure';
$string['securitysureno'] = 'Not so sure';
$string['securitynohdr'] = 'Please explain why';
$string['correctanswers'] = 'Correct answers';
$string['filloutoneanswer'] = 'You must provide at least one possible answer. Answers left blank will not be used. \'*\' can be used as a wildcard to match any characters. The first matching answer will be used to determine the score and feedback.';
$string['notenoughanswers'] = 'This type of question requires at least {$a} answers';
$string['customanswererror'] = 'There are some "other" answers.';
$string['pleaseenterananswer'] = 'Please enter an answer.';
$string['pleaseentercomment'] = 'Please enter the comment, it\'s required.';
$string['pleaseentersecuritysure'] = 'Please choose if you are sure in your answer (if not, please also fill in the reason why).';
$string['pleaseentersecurity'] = 'Please enter the reason why are you not sure, it\'s required.';
$string['pluginname'] = 'Diagnostic ADV';
$string['pluginname_help'] = 'Diagnostic questionnaire-like question with CBM elements.';
$string['pluginname_link'] = 'question/type/diagnosticadv';
$string['pluginnameadding'] = 'Adding a Diagnostic ADV question';
$string['pluginnameediting'] = 'Editing a Diagnostic ADV question';
$string['pluginnamesummary'] = 'Multichoice answer with a diagnosticadv option.';
$string['privacy:metadata'] = 'Diagnostic ADV question type plugin allows question authors to set default options as user preferences.';
$string['privacy:preference:defaultmark'] = 'The default mark set for a given question.';
$string['privacy:preference:penalty'] = 'The penalty for each incorrect try when questions are run using the \'Interactive with multiple tries\' or \'Adaptive mode\' behaviour.';
$string['privacy:preference:usecase'] = 'Whether the answers should be case sensitive.';

$string['analiticsalert'] = 'Please note! The anonymity settings in AI analysis are separate from the display settings for this page and are controlled by the PROMPT settings in the question settings.';
$string['tablehdruser'] = 'User';
$string['reasonforanswer'] = 'Reason for the answer';
$string['tablehdrsecurity'] = 'Security';
$string['commentsandsecurityhdr'] = 'Comments and Security';
$string['commentshdr'] = 'Comments';
$string['securityhdr'] = 'Security';
$string['totalnotanswered'] = 'Submitted but did not answer this question';
$string['anonymoususer'] = 'Anonymous user {$a}';
$string['customlabelform'] = 'Answer {no}';
$string['noanswerselected'] = 'This answer was not selected';
$string['explanationuncertainty'] = 'Explanation for uncertainty';
$string['showcorrectanswer'] = 'Show Correct Answer';


$string['settingstitle'] = 'Define Systems Promt for the question DIAGNOSTICS ADV';
$string['systempromt'] = 'Define your system prompt here.';
$string['systempromt_help'] = 'Define your system prompt.';
$string['systempromt_default'] = '';
$string['promttemaplate'] = 'Define Template for the teacher promt';
$string['promttemaplate_help'] = 'Define your template prompt for the teacher {{LOG}} would be replaced with  ';
$string['promttemaplate_dafault'] = '';

$string['teacherpromt'] = 'Define your teacher promt here';
$string['aianalytics'] = 'Display the option to analyze student answers in the report';
$string['temperature'] = 'Define Temperature for AI Model';

$string['showanalitcistitle'] = 'Show Analytics for Student Answers';
$string['analyse'] = 'Analyse Student Answers';
$string['analyseother'] = 'Analyse Student Answers again';
$string['exportpdf'] = 'Export to PDF';
$string['eventaianalyticscreated'] = 'Event AI Analytics created';
$string['eventaianalyticsexportpdf'] = 'Event Export PDF';
$string['temperature'] = 'Define Temerature';
$string['temperature_help'] = 'Define Temerature';
$string['disclaimer'] = 'Declaimer';
$string['disclaimer_help'] = 'Define text for disclaimer';
$string['disclaimer_default'] = 'The analysis was performed by artificial intelligence; the output should be regarded as a recommendation only.';
$string['exportpdftitle'] = 'Result:';
$string['exportpdfpromt'] = 'Promt:';
$string['availabletocohort'] = 'Cohort to enabled AI Proccess';
$string['availabletocohort_help'] = 'Cohort to enabled AI Proccess';

$string['teacherdesc'] = 'Description of the question for the teacher';
$string['logcolumns'] = 'Columns of th LOG for AI process';
$string['logcolumns_help'] = 'Columns must have explanations for each line, each line is column and description:<br>  
student:Number of student<br> 
answer:Answer of Student<br> 
comment:Comment of student<br> 
issecured:If Choose Student is Secured<br> 
securedcomment:Comment if studnet is sure<br> 
';
$string['logcolumns_default'] = "
studentname:Number of student\n
answer:Answer of student\n
comment:Comment of student\n
ifsecured:If Choose student is Secured\n
securedanswer:Comment if student is sure in his answer
";


