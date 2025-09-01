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
 * @package     theme_petel
 * @category    string
 * @copyright   2023 Devlion <info@devlion.co>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

$string['pluginname'] = 'Petel';
$string['choosereadme'] = '';

$string['siteadminquicklink'] = 'Site Administration';

// Edit Button Text.
$string['editon'] = 'Turn Edit On';
$string['editoff'] = 'Turn Edit Off';

$string['back_to_course'] = 'Back to course';

// General Settings.
$string['generalsettings'] = 'General Settings';
$string['configtitle'] = 'PeTel';

// Instance Settings.
$string['instancesettings'] = 'Instance Settings';
$string['instancename'] = 'Instance name';
$string['instancenamedesc'] = 'Instance name';
$string['instancename_physics'] = 'Physics';
$string['instancename_chemistry'] = 'Chemistry';
$string['instancename_biology'] = 'Biology';
$string['instancename_math'] = 'Mathematics';
$string['instancename_sciences'] = 'Sciences';
$string['instancename_feinberg'] = 'Feinberg';
$string['instancename_computerscience'] = 'Computer Science';
$string['instancename_tutorials'] = 'Tutorials';
$string['instancename_demo'] = 'Demo';
$string['instancename_learnmed'] = 'LearnMed';
$string['blockexpanded'] = 'Remember Blocks visibility';
$string['blockexpanded_desc'] = 'Per user, store blocks sidebar open or close state.';
$string['search_label'] = 'Search: ';
$string['searchcourse'] = 'Search course';
$string['noresults'] = 'No results';
$string['foundxresults'] = ' results found, you can use the up and down keys to navigate ';

/* custom css */
$string['customcss'] = 'Custom CSS';
$string['customcssdesc'] =
        'You may customise the CSS from the text box above. The changes will be reflected on all the pages of your site.';

/* custom accessibility policy */
$string['accessibility_policy'] = 'Accessibility Policy';
$string['accessibility_policy_link_descr'] = 'Accessibility Policy link';

/* terms link */
$string['terms_of_use'] = 'Terms';
$string['privacy_policy'] = 'Privacy policy';

// Login page.
$string['forgotten'] = 'Forgot Password?';
$string['showless'] = 'Show Less';
$string['showmore'] = 'Show More';
$string['sectionactivities'] = 'Activities';

$string['checked'] = 'Checked ({$a})';
$string['cancel'] = 'Cancel';
$string['logintitle_petel'] = 'Login <br> to Petel - {$a}!';
$string['logintitle_wiz'] = 'Login to {$a}!';
$string['loginpolicy'] = 'Privacy & Policy';
$string['loginterms'] = 'Terms & Conditions';
$string['logo_department_of_science_teaching'] = 'Logo Department of Science teaching';

// Support popup.
$string['messageprovider:support_request'] = 'Support request';
$string['messageprovider:shared_notification'] = 'Activity copied notification';
$string['messageprovider:sharewith_notification'] = 'Activity shared notification';

$string['my_dashboard'] = 'My dashboard';
$string['quickaccess'] = 'Quick access';
$string['disablequickaccess'] = 'Disable quick access';

$string['topblocks'] = 'topblocks';
$string['region-topblocks'] = 'topblocks';

$string['viewallcourses'] = 'View all courses';

$string['region-side-pre'] = 'Right side';

$string['ask_details'] = 'Ask details';
$string['copy'] = "Copy";
$string['copy_environment'] = 'Copy to My Environment';
$string['copy_section'] = "Copy the section";
$string['how_to_copy_collegue'] = "Copy";

$string['backgroundimage_desc'] = 'Background for login page';
$string['backgroundimage'] = 'Background for login page';

// Privacy & terms.
$string['privacy'] = 'Privacy & Policy';
$string['privacyurl'] = 'Privacy & Policy link';
$string['privacyurldesc'] = 'Privacy & Policy link';

$string['terms'] = 'Terms & conditions';
$string['termsurl'] = 'Terms & conditions link';
$string['termsurldesc'] = 'Terms & conditions link';

// Register form
$string['successfulyregisterd'] = 'You have successfully registered to the PETEL system, and now redirected to your course page...';
$string['mustgiveemailorphone'] = 'Must give email or phone';
$string['idnumbernotvalid'] = 'Invalid ';
$string['onlyenglishletters'] = 'Only English letters';
$string['onlyhebrewletters'] = 'Only Hebrew letters and "minus" char';
$string['onlyarabicletters'] = 'Only Arabic letters';
$string['idnumber'] = 'PASSPORT NUMBER';
$string['idnumberexists'] = 'PASSPORT NUMBER exists';
$string['phone1exists'] = 'Phone number exists';
$string['missingidnumber'] = 'PASSPORT NUMBER missing';
$string['longerusername'] = 'Username must be at least 7 characters long';
$string['noidnumberinusername'] = 'Username must not be a valid Israeli IDNUMBER';
$string['usernamerestrictions'] = 'Username must be at least 7 characters long, and can include English small letters, and numbers';
$string['phonenotnumerical'] = 'Phone not numerical';

// Forgot password.
$string['searchbyphone'] = 'Search by phone';
$string['usernameoremailorphone'] = 'Enter either username or email address or phone';
$string['phonenotexists'] = 'Phone not exists';
$string['wrongphone'] = 'Wrong phone';
$string['textforsmscode'] = 'Your code validation: ';
$string['smsvalidation'] = 'SMS Validation';
$string['varificationcode'] = 'Code varification';
$string['sendcode'] = 'Send';
$string['emptycodesms'] = 'Code empty';
$string['wrongcodesms'] = 'Wrong code';
$string['passwordforgotteninstructions2']='If the phone number is in the system, we will send you an SMS code.
                                    The code should be entered here. If no code has been received in a minute, please try again.';

// Course page
$string['to_submission'] = 'to submission';
$string['cut_of_date'] = 'Not submitted';
$string['cut_of_date_label'] = 'Submit until {$a->date}';
$string['cut_of_date_less_days_label'] = 'Submit within ';
$string['and'] = ' and ';
$string['no_submission_date'] = 'No submission date';
$string['wait_for_submit'] = 'Wait for submit';
$string['complete'] = 'Complete';
$string['of'] = 'of';
$string['waitgrade'] = 'Submitted and not yet reviewed';
$string['complited'] = 'Completed';
$string['waiting_to_grade'] = 'Waiting to grade';
$string['share'] = 'Share';
$string['quizinprogress'] = 'In progress';
$string['quizwithgrades'] = 'Graded';
$string['quizsubmittedwitgrades'] = 'Submitted and graded';
$string['quizsubmitted'] = 'Submitted';
$string['quizwithoutgrades'] = 'Needs grading';
$string['quiznosubmit'] = 'Not submitted';
$string['quizwithoutstarted'] = 'Not started';
$string['assignsubmitted'] = 'Submitted';
$string['assignhavegrade'] = 'Graded';
$string['assignnotsubmitted'] = 'Not submitted';
$string['questionnairesubmitted'] = 'Submitted';
$string['questionnairenotsubmitted'] = 'Not started';
$string['hvphavegrade'] = 'Graded';
$string['hvpnotsubmitted'] = 'Not submitted';

// Enrolkey.
$string['studentsenrolkey'] = 'Enrol key: {$a}';
$string['enrolkey'] = 'Enrol key';
$string['enrolme_label'] = 'Enrol key';
$string['enrolme'] = 'Enrol me';
$string['enrolselfconfirm'] = 'Do you really want to enrol yourself to course "{$a}"?';
$string['getcoursekeytitle'] = 'Get enrol key';
$string['getkey'] = 'Get key';
$string['close'] = 'Close';
$string['scantoenrol'] = 'Scan to enrol';
$string['msgenrolkey1'] = 'The enrolment key is used for your students to enroll into this course.
<br><br>
The <span class="bold">unique</span> enrolment key for this course:
<span class="bold"> {$a} </span>
<br><br>
<span class="bold">Students who are already registered </span> to the PETEL website, 
can enroll into the course with the following link:
<a target="_blank" href="../enrol/self/enrolwithkey.php?enrolkey={$a}">Enrol into course</a>
<br>
Or scan the following QR code';
$string['msgenrolkey2'] = '
<span class="bold">Students who are not yet registered </span> to this PETEL website,
can register and enroll into the course with the following link:
<br>
<a href="../login/signup.php?key={$a}">Register and enrol to course</a>
<br><br>
Detailed explanation about student self registration and enrollment can be found at:
<a target="_blank" href="https://stwww1.weizmann.ac.il/petel/instructions/add-new-petel-user/">New user tutorial</a>
and also at:
<a target="_blank" href="https://stwww1.weizmann.ac.il/petel/instructions/studentsselfenroll/">Existing user tutorial</a>
';
$string['enrolkey_error'] = 'Enrolment key no found, please try to input a correct key';

//footer
$string['about'] = 'About PeTeL';
$string['abouturl'] = 'About url';
$string['abouturldesc'] = 'About url';
$string['initialize_tours'] = 'Restart page user tours';
$string['all_rights_reserved'] = 'All rights reserved to the Department of Science Teaching, Weizmann Institute of Science';
$string['weizmann_logo'] = 'Department of Science Teaching';
$string['tested'] = 'Tested';

//user menu pop Up
$string['user_completereport'] = 'Full course report';
$string['user_outlinereport'] = 'Basic course report';
$string['user_viewprofile'] = 'View profile';
$string['user_editprofile'] = 'Edit profile';
$string['user_sendmessage'] = 'Send a message';
$string['user_coursecompletion'] = 'Course completion';
$string['user_courselogs'] = 'Activity log';
$string['user_coursegrades'] = 'Course grades';
$string['user_loginas'] = 'Login as...';
$string['sendwhatsapp']='Send WhatsApp';
$string['resetpassword'] = 'Reset password';

//navbar
$string['shownotificationwindownonew'] = 'Show notification window with no new notifications';
$string['logo_petel'] = 'Logo Petel';
$string['siteadminquicklink'] = 'Site Administration';

// Course image.
$string['resolution_must'] = 'Resolution must be 1042 x 167';

$string['periodictable'] = 'Periodic table';
$string['closedialog'] = 'Close window';

$string['language_chooser'] = 'Change language';
$string['navigationmenu'] = 'Navigation Menu';

//Dark mode.
$string['dark_mode'] = 'Dark Mode';
$string['normal_mode'] = 'Normal Mode';

$string['mainmenu'] = 'Main Menu';

// Message menu
$string['togglemessagemenuopen'] = 'Messages menu is opened';
$string['togglemessagemenuclose'] = 'Messages menu is closed';

// Footer settings
$string['footersettings'] = 'Footer Settings';
$string['middlefooter'] = 'Middle';
$string['rightfooter'] = 'Right side';
$string['middlefooter_descr'] = 'Will appear below the About middle section';
$string['rightfooter_descr'] = 'Will appear below the right side Science Teaching logo';

$string['teammembers'] = 'Team members';
$string['currenttask'] = 'Current task';
$string['qsendmessage'] = 'Ask teacher';
$string['qmessageforteacher'] = 'I have trouble with {$a->qlink} in quiz: {$a->cmname} course: {$a->coursename}\n I would be happy for your help';
$string['question'] = 'question';

$string['quiz_student_question'] = 'User opened chat';

$string['switch_to_english'] = 'Switch to activity';

// Quiz attempt.
// Timer.
$string['remainingtime'] = 'remaining time:';
$string['return'] = 'return';
$string['stopwatchandalerts'] = 'Stopwatch and alerts';
$string['stopwatchisshown'] = 'Stopwatch is shown';
$string['stopwatchishidden'] = 'Stopwatch is hidden';
$string['alerts'] = 'alerts';
$string['alert'] = 'alert: ';
$string['every30minutes'] = 'Every 30 minutes';
$string['thirthyminutesbeforetheend'] = '30 minutes before the end';
$string['fifteenminutesbeforebnd'] = '15 minutes before the end';
$string['fiveminutesbeforetheend'] = '5 minute before the end';
$string['withoutwarnings'] = 'Without warnings';
$string['thirteenminutesleftuntiltheend'] = 'There are 30 minutes left until the end of the mission';
$string['minutesleftuntiltheend'] = 'There are {$a->timeleft} minutes left until the end of the mission';
$string['turnoffalerts'] = 'Turn off alerts';
$string['timeleft'] = 'Remaining';
$string['timeleftfrom'] = 'out of';
$string['answered'] = 'answered';
$string['answered_from'] = 'answered from';
$string['answered_from_full'] = 'questions answered from';
$string['minutesleft'] = 'left';
$string['questionnonav'] = '<span class="accesshide">Question </span><span class="text">{$a->number}</span> <span class="accesshide"> {$a->attributes}</span>';
$string['questionnonavinfo'] = '<span class="accesshide">Information </span><i class="fas fa-info"></i></i><span class="accesshide"> {$a->attributes}</span>';
$string['fullscreen'] = 'Fullscreen';
$string['message'] = 'Message';
$string['chapter'] = 'Chapter {$a->pagenum}';
$string['questionpointstext'] = '{$a->questionpoints} points';
$string['progresspage'] = '{$a->totalcomplinpage} of {$a->totalquestions} answered';
$string['stopwatchshowhide'] = 'Shown/Hidden Stopwatch';
$string['notflagged'] = 'Not flagged';
$string['timeisup'] = 'Time is up';
$string['advancedoverviewlink'] = 'Grades and extended feedback';
$string['assessmentdiscussionlink'] = 'Assessment discussion';
$string['gradingstudentslink'] = 'Examination by students';
$string['ministry_statement_title'] = ' ';
$string['ministry_statement_text'] = ' ';
$string['movetopage'] = 'Change page to: ';

// EC-219
$string['fixbuttonlabel'] = 'Fix';
$string['fixpopuplabel'] = 'Fix';
$string['fixlabel'] = 'Not in current category';
$string['fixpopupmessage'] = 'Are you sure you want recreated this question and unsigned old one';
$string['cancel'] = 'No';
$string['confirm'] = 'Yes';

// Cache.
$string['cachedef_instancecolors'] = 'Cache of instancecolors';

// Sign up.
$string['signuptitle'] = 'Welcome to the Petel environment';
$string['signuptext1'] = 'At this stage you are not registered in the system, in order to register, enter the course key';
$string['signuptext2'] = 'Please note, in this form there are fields that must be filled in and they are marked with a symbol';

// States for tooltip.
$string['tooltipopenquestion'] = 'Description question';
$string['tooltipnotyetanswered'] = 'Not yet answered';
$string['tooltipcorrect'] = 'Correct answer';
$string['tooltipincorrect'] = 'Not correct answer';
$string['tooltippartiallycorrect'] = 'Partial answer';
$string['tooltipnotanswered'] = 'Did not answer';
$string['tooltiprequiresgrading'] = 'Grading is required';