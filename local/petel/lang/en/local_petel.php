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
 * @package     local_petel
 * @category    string
 * @copyright   2017 nadavkav@gmail.com
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['pluginname'] = 'Petel config';
$string['setting_smssecurtitynumber'] = 'SMS Security number';
$string['setting_smssecurtitynumber_desc'] = 'SMS Security number';
$string['setting_smssecurtitytimereset'] = 'SMS Security reset time';
$string['setting_smssecurtitytimereset_desc'] = 'SMS Security reset time in seconds';
$string['settings_defaultcourse'] = 'Select default course';
$string['settings_adminemail'] = 'Admin email';
$string['settings_countviewpopupcoursechangemetadata'] = 'How many times to show the panel "Additional details about the course"';

$string['policy_yes'] = 'Yes, I agree.';
$string['policy_no'] = 'No, I do not agree.';

// Access.
$string['petel:studentview'] = 'View a only for students';

// Special gradebook categories for the Chemistry instance courses.
$string['activitieswithgrade'] = 'Activities with grade';
$string['activitieswithoutgrade'] = 'Activities without grade';

// Auth/email.
$string['signupthankyou'] = '<p>Thank you for registering to the PeTeL website.</p><p><a href="{$a}">Click here to login</a></p>';

// Register form.
$string['mustgiveemailorphone'] = 'Must give email or phone';
$string['onlyhebrewletters'] = 'Only Hebrew letters and "minus" char';
$string['successfulyregisterd'] = 'You have successfully registered to the PETEL system, and now redirected to your course page...';
$string['createaccount'] = 'Here you can create a new account.';
$string['signup'] = 'Sign Up';
$string['idnumber'] = 'ID number';
$string['idnumberexists'] = 'ID number exists';
$string['idnumbernotvalid'] = 'Invalid ';
$string['phone1exists'] = 'Phone number exists';
$string['missingidnumber'] = 'ID number missing';

// Login.
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
$string['passwordforgotteninstructions2'] =
        'If the phone number is in the system, we will send you an SMS code. The code should be entered here. If no code has been received in a minute, please try again.';

// User profile page.
$string['welcome_to_profile_page'] = 'Welcome to your profile page';
$string['firstname_and_lastname'] = 'First name and last name';
$string['personal_information'] = 'Personal Information';
$string['username'] = 'Username';
$string['password'] = 'Password';
$string['email'] = 'Email';
$string['identity_card'] = 'Identity card';
$string['phone'] = 'Phone';
$string['save'] = 'Save';
$string['edit_properties'] = 'Edit My Properties';
$string['account_settings'] = 'Account Settings';

$string['enterfullname'] = 'Enter Full Name';
$string['enterusername'] = 'Enter Username';
$string['enteridnumber'] = 'Enter Teudat Zeut';
$string['idnumbernotnumerical'] = 'Teudat Zeut not numerical';
$string['idnumberwrong'] = 'Teudat Zeut Wrong';
$string['enterphone'] = 'Enter Phone';
$string['phonenotnumerical'] = 'Phone not numerical';
$string['enteremail'] = 'Please enter your Email';
$string['enterproperemail'] = 'Please enter your Email';
$string['saving'] = 'Saving ...';
$string['detailssavedsuccessfullycustom'] = 'Details Saved Successfully!';
$string['wrongpassword'] = 'Password needed minimum 8 chars';

$string['usezerobeforeidnumber'] = 'Please use leading ZERO before your id number';
$string['studentsenrolkey'] = 'Enrol key: {$a}';
$string['enrolkey'] = 'Enrol key';
$string['enrolme_label'] = 'Enrol key';
$string['enrolme'] = 'Enrol me';
$string['enrolselfconfirm'] = 'Do you really want to enrol yourself to course "{$a}"?';
$string['getcoursekeytitle'] = 'Get enrol key';
$string['getkey'] = 'Get key';
$string['close'] = 'Close';
$string['enrolkey_error'] = 'Enrolment key no found, please try to input a correct key';

$string['messageprovider:attemptgraded'] = 'Quiz attempt open questions were graded';
$string['question_graded_subject'] = 'Quiz attempt was graded and ready for you to review';
$string['question_graded_body'] = 'Hello {fullname},<br>The quiz {activityname} was graded (final grade: {grade} ) by the teacher,
 and now ready to be review by you.<br>You can use the following link to review it: {link} <br>';

$string['taskspetelbbb'] = 'Update remote BBB statistics';
$string['taskspetelupdatestats'] = 'Update user engagement statistics';
$string['taskabuseip'] = 'Get abuse ip information';

// Participiant popup.
$string['buttoncreatecourse'] = 'Create new courses for teachers';
$string['buttonaddsystemgroups'] = 'Belonging to system groups';
$string['titlecreatecourse'] = 'Create new courses for {$a} teachers';
$string['titlecreatecourse1'] = 'Create new courses for 1 teacher';
$string['titleaddsystemgroups'] = 'Belonging to system groups for {$a} teachers';
$string['titleaddsystemgroups1'] = 'Belonging to system groups for 1 teacher';
$string['selectmaincategory'] = 'Select main category';
$string['selectrole'] = 'Select role';
$string['selectgroups'] = 'Select groups';
$string['keynull'] = 'Do reset registration key';
$string['selecttemplatecourse'] = 'Select template course';
$string['coursescreated'] = 'Courses created';
$string['subjectmailcoursescreated'] = 'List of categories and courses which created';
$string['htmlcategorycreated'] = '<p>Category created</p>';
$string['htmlcategorynotcreated'] = '<p>Category not created</p>';
$string['htmlmailcoursescreated'] =
        '<p>In category "{$a->category_name}" created course <a href="{$a->course_url}">"{$a->course_name}"</a></p>';

// Create course popup.
$string['createcourseerror'] = 'It is not possible to open a course, please use "headphones" to request a course';
$string['createcourseteacher'] = 'Create course for teacher';
$string['createcoursesubmit'] = 'OK';
$string['coursename'] = 'Course name';
$string['waitcoursecreate'] =
        'The opening application of the course was successfully received. Notice of the availability of the new course will be sent to you and will also be displayed on the "Bell"';
$string['messagecoursectreate'] =
        '<p>הקורס "{$a->course_name}" נוצר בהצלחה! להלן קישור לקורס <a href="{$a->course_url}">"{$a->course_name}"</a></p>';
$string['subjectmailcoursecreated'] = 'קורס שנוצר';

$string['questionhintdefault1'] = 'Please note the error and try again';
$string['questionhintdefault2'] = 'Please note the error and try again';

// Feinberg web services.
$string['wsnoinstance'] = 'Manual enrolment doesn\'t exist or is disabled for role {$a->rolename} in courseid = {$a->courseid}';

// Edit course.
$string['editcoursetitle'] = 'Warning';
$string['editcoursebody'] = 'Attention! changing the course format will not allow you to go back to the old setting and design';
$string['editcourseapprove'] = 'Approve';

// Recommendations.
$string['questionchooserrecommendations'] = 'Questions recomendation';
$string['cacheoercatalog'] = 'Cache oercatalog';

// Page copy metadata to activity.
$string['copymetadataactivity'] = 'Copy metadata to activity';
$string['chatclickevent'] = 'Click on chat';
$string['notificationclickevent'] = 'Click on notification';
$string['cmasourcecmid'] = 'Enter source activity id';
$string['cmatargetcmids'] = 'Enter target activities id via comma';
$string['cmaheadermdfields'] = 'Select fields for copy';
$string['cmaerrorsourcecmid'] = 'Please enter source activity id';
$string['cmaerrortargetcmids'] = 'Please enter target activities id via comma';
$string['cmaerrormdfields'] = 'Please select fields for copy';
$string['cmasubmitlabel'] = 'Copy';
$string['cmasuccess'] = 'Copied successfully';

$string['eventsecurityaudit'] = 'Security audit';
$string['autoconfirmuserstask'] = 'Auto confirm users';

// Settings admin.
$string['excludedemails'] = 'Email addresses that will not send email to them';
$string['excludedemails_desc'] = 'Enter email addresses in full or in part, which will not send email to them.';

// Page participiants.
$string['ppactivestudents'] = 'Active students';
$string['ppallstudents'] = 'All students (including suspended)';
$string['ppsuspendedusers'] = 'Suspended users';
$string['ppfellowteachers'] = 'Fellow teachers';
$string['ppteacherspayoff'] = 'Teachers pay off';
$string['ppteacherdoesnotedit'] = 'A teacher does not edit';
$string['ppnopersonalcategory'] = 'Participants who do not have a personal category';
$string['ppactivestudentsandteachers'] = 'Active students and teachers';
$string['ppall'] = 'All';
$string['searchplaceholder'] = 'Search for participants';
$string['filterlabel'] = 'The list shows';
$string['settings_participiant_filter'] = 'Default participant filtering';
$string['pageparticipiants'] = 'Participants';

// Page session timeout.
$string['catcustomsettings'] = 'Custom settings';
$string['sessiontimeout'] = 'Session timeout';
$string['defaulttimeout'] = 'Moodle default session timeout';
$string['sessiontimeouttitle'] = 'Setting of session timeout';
$string['selectsessiontimeout'] = 'Select session timeout';
$string['twohours'] = 'Two hours';
$string['const'] = 'Without session timeout';
$string['sessiontimeoutwarning'] =
        'Short session timeout are better are better for securing your account on public computers logins';

// Demo.
$string['democaptchaheader'] = 'Please let us be sure you are not a robot first';
$string['democaptchadesc'] = 'Please note that you must confirm connection to the Patel experience system.
The experience will be available for {$a} hours. Please confirm "I\'m not a robot".';
$string['democaptcha'] = 'Type the sequence from the picture into this field';
$string['demosubmitlabel'] = 'Proceed';
$string['enabledemo'] = 'Enable demo mode';
$string['demo_copied'] = 'Copied';
$string['linktodemoactivity'] = 'Link to demonstration';
$string['linktodemo'] = 'Link to demo';
$string['demomodalhdr'] = 'Copy link to demo';
$string['demorole'] = 'Demo role';
$string['demorole_desc'] = 'Sitewide role the user using demo link will be enrolled as';
$string['democleanuptask'] = 'Demo users cleanup';
$string['calculatesocialrelationships'] = 'Calculate social relationships';

$string['errordemonokey'] = 'This link is invalid. Please contact your administrator';
$string['errordemonoenrol'] = 'Error occured during your enrolment: course enrol is not valid. Please contact your administrator';
$string['errordemonoenrolmethod'] =
        'Error occured during your enrolment: enrolment method is not callable. Please contact your administrator';
$string['errordemocoursefull'] = 'Error occured during your enrolment: course is full. Please contact your administrator';
$string['errordemoenrol'] =
        'Error occured during your enrolment: system was not able to enrol you. Please contact your administrator';

$string['aftercontent'] = 'After item "{$a}"';
$string['beforecontent'] = 'Before item "{$a}"';

// Comments A11Y.
$string['blankcannotbesaved'] = 'A blank comment cannot be saved, please enter text here';
$string['currentview'] = 'Current view:';
$string['currentfolder'] = 'Current folder: ';
$string['moemyprofile'] = 'MOE user profile';
$string['periodictable'] = 'Periodic table';
$string['closedialog'] = 'Close window';
//Strings for Question edit page
$string['saveinnewversion'] = 'Save in new version';
$string['saveinnewversionandcontinueediting'] = 'Save in new version and continue editing';


// Recaptch V3
$string['configrecaptchav3enable'] = 'Enable or disable Google reCAPTCHA V3 for user login.';
$string['configrecaptchav3url'] = 'URL to the Google reCAPTCHA V3 API script.';
$string['configrecaptchav3sitekey'] = 'Your site key provided by Google reCAPTCHA V3.';
$string['configrecaptchav3privatekey'] = 'Your secret key provided by Google reReCAPTCHA V3.';
$string['configrecaptchav3failed'] = 'reCAPTCHA verification failed. Please try again.';
$string['configfailedlogins_without_recaptcha'] = 'Number of failed logins attempts before reCAPTCHA activation.';


$string['recaptchav3enable'] = 'Enable ReCAPTCHAv V3';
$string['recaptchav3url'] = 'ReCAPTCHA V3 API URL';
$string['recaptchav3sitekey'] = 'ReCAPTCHA V3 site key';
$string['recaptchav3privatekey'] = 'ReCAPTCHA V3 secret key';
$string['failedlogins_without_recaptcha'] = 'Failed attempts';

$string['configrecaptchav2enable'] = 'Enable or disable Google reCAPTCHA V2 for user login.';
$string['configrecaptchav2url'] = 'URL to the Google reCAPTCHA V2 API script.';
$string['configrecaptchav2sitekey'] = 'Your site key provided by Google reCAPTCHA V2.';
$string['configrecaptchav2privatekey'] = 'Your secret key provided by Google reReCAPTCHA V2.';
$string['configrecaptchav2failed'] = 'reCAPTCHA verification failed. Please try again.';


$string['recaptchav3demoenable'] = 'Enable ReCAPTCHAv V3 for Demo';
$string['recaptchav2enable'] = 'Enable ReCAPTCHAv V2';
$string['recaptchav2url'] = 'ReCAPTCHA V2 API URL';
$string['recaptchav2sitekey'] = 'ReCAPTCHA V2 site key';
$string['recaptchav2privatekey'] = 'ReCAPTCHA V2 secret key';


$string['invalididnumber'] = 'Invalid ID number';

// Grade export.
$string['gradeexportcheckbox'] = 'Marking activities in which there are respondents with a score';
$string['autosort'] = 'Auto sort';

// Popup update course metadata.
$string['titlepopupupdatecoursemetadata'] = 'More details about the course';
$string['approve'] = 'Approve';
$string['coursemetadata'] = 'Please provide the following information about this course:';
$string['no_teacher_permission'] = 'User {$a->username} lacks Petel Teacher permission, please contact support.';
