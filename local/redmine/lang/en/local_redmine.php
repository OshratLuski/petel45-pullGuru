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
 * Plugin strings are defined here.
 *
 * @package     local_redmine
 * @category    support
 * @copyright   2021 <nadav.kavalerchik@weizmann.ac.il>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['pluginname'] = 'Redmine';
$string['myissues'] = 'Issues I reported';
$string['plugintitle'] = 'My issues';

// Settings.
$string['redmineurl'] = 'Redmine url';
$string['redmineurldesc'] = 'Redmine server url';
$string['redminestatus'] = 'Redmine status';
$string['redminestatusdesc'] = 'Turn on/off redmine support';
$string['redmineusername'] = 'Redmine user name';
$string['redmineusernamedesc'] = '';
$string['redminepassword'] = 'Redmine password';
$string['redminepassworddesc'] = '';
$string['redmineadminusername'] = 'Redmine admin user name';
$string['redmineadminusernamedesc'] = '';
$string['redmineadminpassword'] = 'Redmine admin password';
$string['redmineadminpassworddesc'] = '';
$string['redminereporterid'] = 'Redmine Helpdesk user ID';
$string['redminereporteriddesc'] = '';
$string['redminewatcherbugid'] = 'Redmine watcher bug user ID';
$string['redminewatcherbugiddesc'] = 'Comma separated user IDs for issue watchers';
$string['redmine_leadoercatalog'] = 'Redmine lead OER catalog user ID';
$string['redmine_leadoercatalogdesc'] = '';
$string['redmine_technopedagogical'] = 'Redmine techno-pedagogical user ID';
$string['redmine_technopedagogicaldesc'] = '';
$string['redminesearchwords'] = 'Number of words';
$string['redminesearchwordsdesc'] = 'Number of words in search (0 not working)';
$string['redmineshowresults'] = 'Number of results';
$string['redmineshowresultsdesc'] = 'Number of results shown (0 not working)';
$string['allmightymentor'] = 'Lead mentor';
$string['allmightymentordesc'] = 'Full text details of lead mentor, that is added to each report';
$string['settingssupportcourse'] = 'Support course';
$string['settingssupportcoursedesc'] = 'Please select support course';
$string['enabled'] = 'Enabled';
$string['disabled'] = 'Disabled';

// Events.
$string['eventsupportrequestaudit'] = 'Support request audit';

// Popups.
$string['asq_questions_and_report_everything'] =
        'Here you can ask us questions and report everything, and we will make sure that PeTel\'s support center responds to you as soon as possible with an answer or a solution to the problem.';
$string['how_can_we_help_you'] = 'How can we help you?';
$string['i_want_to_ask_a_question'] = 'Technical support';
$string['i_want_to_get_pedagogical_help'] = 'Pedagogical guidance';
$string['i_want_to_suggest_improvement'] = 'Suggest improvement';
$string['i_want_to_report_a_contentbug'] = 'Report a bug in the content';
$string['i_want_to_report_a_bug'] = 'Report a bug';
$string['question_type_error'] = 'Please select';
$string['can_you_tell_more'] = 'Can you tell us more?';
$string['can_you_tell_more_desc'] = 'You can start typing the question / request. In the list of topics displayed, you can select the existing tutorial. It will open in a new tab.
If no suitable topic is found, we will be happy to receive a detailed explanation in order to optimize our help.';
$string['we_have_found_something'] = 'We have found something that maybe can help you:';
$string['more_info_error'] = 'Please fill';
$string['were_almost_done'] = "We're almost done!";
$string['do_you_have_question_to_teacher'] = 'Do you have a question for your teacher?';
$string['here_you_can_ask_your_teacher'] = 'Here you can ask your teacher a question about one of the topics listed in PeTeL. The question will reach the teacher plus a link to a page from the message has been sent.';
$string['my_question'] = 'My question';
$string['screenshot_question'] = 'A screenshot that will clarify to the teacher the question';
$string['have_you_a_question'] = 'Do you have a question / technical problem?';
$string['send'] = 'Send';
$string['question_to_teacher'] = 'Question to teacher';
$string['redmine_description'] = '*דיווח חדש*
*שם*: {$a->name}
*דואל*: {$a->email}
*סוג*: {$a->question}
*תוכלו לספר לנו עוד?*
{$a->moreinfo}


IP: {$a->userip}
Browser: {$a->userbrowser}
Resolution: {$a->resolution}
PageUrl: {$a->pageurl}
';
$string['redmine_subject'] = '{$a->digest} - Support request ';
$string['supportconfirmsmall'] = 'Your support request was successfully received.';
$string['supportconfirmbody'] =
        'Support team representative will contact you shortly for more information. Thank you! PeTeL team. request id: {$a}';
$string['supportmoreinfo'] = 'More information about your support request is available from the main toolbar > user menu > my issues.';
$string['supportconfirmsubject'] = 'Support request confirmation: {$a}';
$string['supportstudent_description'] = 'Hello {$a->teacher_name},<br>
the student {$a->name} is interested in support.<br>
Below is his reference<br><br>
<hr>
{$a->moreinfo}<br>
<hr>
Email: {$a->email}<br>
IP: {$a->userip}<br>
Browser: {$a->userbrowser}<br>
Resolution: {$a->resolution}<br>
עמוד: {$a->pageurl}<br>
';
$string['supportstudent_description_notification'] = 'Hello {$a->teacher_name},<br>
the student <a href="{$a->userprofileurl}">{$a->name}</a> is interested in support.<br>
Below is his reference<br><br>
<hr>
{$a->moreinfo}<br>
<hr>
Email: <a href="mailto:{$a->email}">{$a->email}</a><br>
PageUrl: <a href="{$a->pageurl}">{$a->pageurl}</a><br>
';
$string['support_subject']='Request form PeTeL: {$a->digest}';
$string['supporturl'] = 'Request page URL';
$string['supportsuccesssendtitle'] = 'Message';
$string['supportsuccesssendcontent'] = 'Your referral has been successfully received';
$string['supportstudentsuccesssendtitle'] = 'Message';
$string['supportstudentsuccesssendcontent'] = 'Your message was sent to admin';

// Issues main.
$string['periodhalfyear'] = 'Last half year';
$string['periodmonth'] = 'Current month';
$string['periodlastyear'] = 'Last year';
$string['activemyissues'] = 'My issues';
$string['searchplaceholder'] = 'Type, content or referral number';
$string['titlehistory'] = 'History';
$string['issues'] = 'issues';
$string['of'] = 'of';
$string['showing'] = 'Showing';
$string['typeissue'] = 'Issue';
$string['contentissue'] = 'Content issue';
$string['dateissue'] = 'Date issue';
$string['closingdate'] = 'Closing date';
$string['numberissue'] = 'Number issue';
$string['statusissue'] = 'Status';
$string['authornameissue'] = 'Creator of issue';
$string['noissueshistory'] = ', you have no history at this time';
$string['noissuesactive'] = 'Dear teacher, you currently have no active inquiries';
$string['activeissues'] = 'Active issues';
$string['attentioninfotext'] = 'There are {$a} days left for an answer, if no answer is received from you, the application will be closed';
$string['statusnew'] = 'New';
$string['statusyouranswer'] = 'Awaiting your reply';
$string['statustreatment'] = 'In treatment';
$string['statusclosed'] = 'Closed';

// Page single issue.
$string['back'] = 'Back to "My References" page';
$string['issuenumber'] = 'Issue number';
$string['issuedetails'] = 'Issue details';
$string['issuelastchanges'] = 'Issue last changes';
$string['issuepage'] = 'Issue page';
$string['chatmessages'] = 'Content issue';
$string['chatplaceholder'] = 'Write your answer…';
$string['chatnow'] = 'Now';
$string['alertnote'] = 'נותרו {$a->days} ימים למענה, במידה ולא יתקבל מענה הפנייה תיסגר';

// Chat page.
$string['deletefile'] = 'Delete file';
$string['addfile'] = 'Add file';
$string['filealreadyadded'] = 'File already added';
$string['wrongfileformat'] = "Wrong file format. Please add '.jpg', '.jpeg' or '.png' format file.";
$string['textneeded'] = "Please note, only one image can be attached per comment. Please be sure to attach an explanation to the image.";
$string['teacherresponse'] = "Teacher response";
$string['responsefrom'] = "Answer by";

// Support button and menu.
$string['support'] = 'Support';
$string['support_menu_newappeal'] = 'Open new issue';
$string['support_menu_myappeals'] = 'My issues';
$string['support_menu_petelguides'] = 'Manuals - how to work with PeTeL';
$string['support_menu_activeissues'] = 'Inquiries are awaiting your consideration';

// Email.
$string['mailsubject'] = 'Message from Redmine';
