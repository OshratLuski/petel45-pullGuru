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
 * @package     community_sharewith
 * @category    string
 * @copyright   2018 Devlion <info@devlion.co>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['pluginname'] = 'Share With';
$string['sharemodule'] = 'Share With';

$string['wordcopy'] = 'Share';
$string['generalsectionname'] = 'General';

// Cron.
$string['tasksharewith'] = 'Task sharewith';

// Settings.
$string['settingscoursecopy'] = 'Copy course';
$string['settingscoursecopydesc'] = 'Enable/disable copy courses';
$string['settingssectioncopy'] = 'Copy topic';
$string['settingssectionscopy'] = 'Copy topics';
$string['settingssectioncopydesc'] = 'Enable/disable copy topics';
$string['settingsactivityteachercopy'] = 'Share activity to teacher';
$string['settingsactivityteachercopydesc'] = 'Enable/disable share activities to teacher';
$string['settingsactivitycopy'] = 'Copy activity';
$string['settingsactivitycopydesc'] = 'Enable/disable copy activities';
$string['settingsactivitysending'] = 'Send activity';
$string['settingsactivitysendingdesc'] = 'Enable/disable send activities';
$string['settingsshownonrequieredfields'] = 'Button for more options';
$string['settingsshownonrequieredfieldsdesc'] = 'Enable/disable button for more options';
$string['settingsnumberofsection'] = 'Number of sections to copy';
$string['settingsnumberofsectiondesc'] = 'Select number of sections to copy';
$string['settingscoursetag'] = 'Type course tag';
$string['settingscoursetagdesc'] = '';
$string['settingsrolesshareteacher'] = 'Roles to share teacher';
$string['settingsrolesshareteacherdesc'] = '';
$string['settingsaddcompetenciescohort'] = 'Can add competencies';
$string['settingsaddcompetenciescohortdesc'] =
        'Only members of a selected cohort can add competencies, if none selected: feature disabled.';
$string['settingsshowncompetencysection'] = 'Show only competency of section';
$string['settingssshowncompetencysectiondesc'] = '';
$string['settingsvisibilitytype'] = 'לבחור את visibility של פעילות';
$string['settingsvisibilitytypedesc'] = 'לבחור את visibility של פעילות אשר שותף למאגר';
$string['showforstudent'] = 'הצגה לסטודנטים';
$string['hideforstudent'] = 'מוסתר מסטודנטים';
$string['hideforstudentavailable'] = 'זמין לסטודנטים, אך אינו מוצג בעמוד הראשי של הקורס';

// Events.
$string['eventcoursecopy'] = 'Copy course';
$string['eventsectioncopy'] = 'Copy topic';
$string['eventactivitycopy'] = 'To my course';
$string['eventactivityupload'] = 'To oercatalog';
$string['eventactivitysharetoteacher'] = 'To teacher';
$string['eventcommunityshare'] = 'To community';

// Modals.
$string['sectionselection'] = 'Select topic';
$string['selectcourse'] = 'Select course';
$string['uploadactivity'] = 'Upload activity';
$string['selectcourse_and_section'] = 'Select course and topic';
$string['selecttopic'] = 'Select topic';
$string['close'] = 'Close';
$string['cancel'] = 'Cancel';
$string['submit'] = 'Submit';
$string['approve'] = 'Ok';
$string['finish'] = 'Ok';
$string['finish2'] = 'Close...';
$string['redirectmessage'] = 'Yes';
$string['activity_copied_to_course'] = 'Activity is copied to the course';
$string['activity_copied_to_course_from_message'] =
        'האם תרצו לשאול את <a target="_blank" href="{$a->link}">{$a->userfirstname} {$a->userlastname}</a> לגבי הפריט?';
$string['section_copied_to_course'] = 'Topic is copied to the course';
$string['system_error_contact_administrator'] = 'System error, contact administrator';

$string['error_coursecopy'] = 'Course copy disabled on the plugin settings';
$string['error_sectioncopy'] = 'Section copy disabled on the plugin settings';
$string['error_activitycopy'] = 'Activity copy disabled on the plugin settings';
$string['error_permission_allow_copy'] = 'Not enough permissions to copy, contact administrator';

$string['eventcopytomaagar'] = "Copy to Database";
$string['eventcopytoteacher'] = "Share to teachers";
$string['eventdownloadtoteacher'] = "Download activity";
$string['eventdublicatetoteacher'] = "Copy activity";
$string['eventcoursemodulevisibilitychanged'] = "Course module visibility changed";

$string['menu_popup_title'] = "Choose how you want to share";
$string['menu_popup_maagar'] = "Post in the Shared Database";
$string['menu_popup_send_teacher'] = "Send to Teacher";
$string['back'] = "Back";
$string['send'] = "Send";
$string['share_with_teacher'] = "Share with a teacher";
$string['communityselection'] = "Community selection";
$string['teachers_youve_sent'] = "Teachers you've sent the item to";
$string['enter_teacher_here'] = "Enter the name of the teacher here ...";
$string['comment_to_teacher'] = "Here a comment will be made that the teacher will share with the person who has chosen ...";
$string['enter_course_here'] = "Select the name of the course here ...";
$string['user_foto'] = "User foto";
$string['nosharing'] = "No one sent yet";
$string['error_share_to_teacher'] = "Please select teachers";
$string['error_share_to_community'] = "Please select course";

$string['activity_upload_to_mr'] = 'Activity {$a->activitytitle} has been sent to the shared repository and will be available to all teachers as soon as possible
thanks for sharing!';
$string['subject_message_for_teacher'] = 'Teacher {$a->teachername} share to you activity {$a->activityname}';
$string['info_message_for_teacher'] = 'Message from Sharing Activity';
$string['enter_subject_name'] = 'Enter the name of the subject';
$string['succesfullyshared'] = 'The request was successfully updated. It will copied after number of minutes. Thank you!';
$string['succesfullycopied'] = 'The request was successfully updated. It will copied after number of minutes. Thank you!';

$string['activitycopy_title'] = 'Activity';
$string['activityshare_title'] = 'Activity';
$string['sectioncopy_title'] = 'Topic';
$string['coursecopy_title'] = 'Course';
$string['notification_smallmessage_copied'] = 'Successfully copied!';
$string['activitycopy_fullmessage'] = 'Your activity was successfully copied to the <a href="{$a->link}">{$a->coursename}</a>';
$string['activityshare_fullmessage'] = 'Your activity was successfully saved to the <a href="{$a->link}">{$a->coursename}</a>';
$string['sectioncopy_fullmessage'] = 'Your topic was successfully copied to the <a href="{$a->link}">{$a->coursename}</a>';
$string['coursecopy_fullmessage'] = 'Your course was successfully copied to the <a href="{$a->link}">{$a->coursename}</a>';

$string['copysection'] = 'Copy section';
$string['share'] = 'Share';
$string['copy'] = 'Copy';
$string['save'] = 'Save';
$string['how_to_share'] = 'How to share ?';
$string['share_national_shared'] = 'Share the national shared database';
$string['send_to_teacher'] = 'Send to the teacher';
$string['transfer_another_course'] = 'Transfer to another course';
$string['no_courses_for_send'] = 'No communities were found in which you are a member';

// Sharing popup.
$string['choose'] = 'Choose...';
$string['reduce_catalog_options'] = 'Reduce catalog options';
$string['advanced_catalog_options'] = 'Advanced catalog options';
$string['advanced_catalog_options_2'] = 'Advanced Catalog';
$string['advanced_catalog_options_3'] = 'Permission fields to help locate the item in the shared repository ';
$string['please_enter_item_name'] = 'Please enter the item name';
$string['warning_cmid_present'] = 'Pay attention! You are about to share an item that exists in the shared repository';
$string['warning_label_select'] = 'A similar activity is in the database, is it about:';
$string['warning_select_option_1'] = 'Translation of the activity';
$string['warning_select_option_2'] = 'Repair or improvement of the activity';
$string['warning_select_option_3'] = 'A new pedagogical activity';
$string['error'] = 'Error';
$string['please_select_course_section'] = 'Please select course and topic';
$string['sent'] = 'Sent';
$string['fails'] = 'Fails';
$string['sharing_sent_successfully'] = 'A sharing invitation has been sent successfully';
$string['studysection'] = 'Study Topic';
$string['loading'] = 'Loading...';

$string['selectteacher'] = 'Select Teacher';
$string['selectcommunity'] = 'Share to community';
$string['activitydeleted'] = 'This activity was deleted by author.';
$string['sendingnotallowed'] = 'Share activities disabled by administrator.';

$string['insert_mails'] = 'Administration users';
$string['insert_mails_desc'] = 'Example: email1@google.com,email2@google.com';

$string['course_count_label'] = 'Number of Courses to show';
$string['search_label'] = 'Search:';
$string['searchcourses:addinstance'] = 'Add Search Courses block';
$string['searchcourses:myaddinstance'] = 'Add Search Courses block to My Home';
$string['setting_inserticonswithlinks'] = 'Insert icons with links';
$string['setting_inserticonswithlinks_desc'] = 'Setup the menu (only text), each item in a new line.';

$string['system_error'] = 'System error';
$string['course_error'] = 'Dear teachers, activity can not be shared';

$string['category_error'] = 'Dear teacher,<br>
At least some of the questions in your exam do not belong to the "Default examiner" category.<br>
You must check and categorize these questions before uploading the examiner to the shared repository.<br>
For questions / clarifications, please contact: petel@weizmann.ac.il';

$string['category_error_teacher'] = 'Dear teacher,<br>
At least some of the questions in your exam do not belong to the "Default examiner" category.<br>
You must check and categorize these questions before uploading the examiner to the shared repository.<br>
For questions / clarifications, please contact: petel@weizmann.ac.il';

$string['sharing_content_materials_repository'] =
        'Pay attention! By sharing this activity, you will be able to access the activity content without the performance of your students to all physics teachers who use the PeTeL environment. Please ensure that the content that appears in the activity is standardized';
$string['share_item_error'] = 'Need fill ';
$string['item_name'] = 'Item name:';
$string['instructions_for_teachers_on_the_activity'] = 'Instructions for teachers on the activity';
$string['please_tell_other_teachers_a_few_words_about_the_activity'] = 'Please tell other teachers a few words about the activity, insights from the session with students and your recommendations for the session
Successful. The information will appear below the item title in the shared repository';
$string['theme_of_the_activity'] =
        'Theme of the activity <span class="font-weight-normal">(select the third level from the drop-down menu)</span>';
$string['selecting_the_topic_of_the_activity'] = 'Select the topic of the activity';
$string['mark_the_recommended_uses_for_this_activity'] =
        'Mark the recommended uses for this activity <span class="font-weight-normal">(more than one use can be marked)</span>';
$string['competencies_embedded_in_the_activity'] =
        'Competencies embedded in the activity. <span class="font-weight-normal">You have to start typing the concept and select it from the list. You can select several competencies:</span>';
$string['select_competency'] = 'Select competency';
$string['level_of_activity_difficulty'] = 'Level of activity difficulty';

$string['did_you_rely_on_other_activity_development_activities'] = 'Did you rely on other activity development activities?';
$string['no_copyright_of_activity_is_mine_only'] = 'No. The copyright of the activity is mine only';
$string['yes_i_processed_activity_based_on_another_activity'] =
        'Yes. I processed / translated the activity based on another activity';
$string['how_long_doesthe_activity_last'] = 'How long does the activity last?';
$string['min'] = 'min';
$string['in_what_language_is_the_activity_conducted'] = 'In what language is the activity conducted?';
$string['what_is_nature_of_feedback_in_this_activity'] = 'What is the nature of the feedback in this activity?';
$string['assign_the_activity_to_another_learning_topic'] = 'Assign the activity to another learning topic (if necessary)';
$string['agree_to_copyright'] = 'קראתי ואני מסכים/ה לתנאי זכויות יוצרים';

$string['availability_describe'] = 'שימו לב! פעילות זו היא חלק מרצף הוראה. ברצונכם לשתף את כל שאר הפריטים ברצף למאגר המשוותף?';
$string['glossary_describe'] = 'האם ברצונך ליבא נתונים לפעולות הזאת?';
$string['database_describe'] = 'האם ברצונך ליבא נתונים לפעולות הזאת?';
$string['define_item_cataloged'] = 'Define where the item will be cataloged in the shareport';
$string['select_main_topic'] = 'Select Main Topic';
$string['assignment_appropriate_topics'] = 'Assignment to appropriate topics';
$string['select_sub_topic'] = 'Select a sub-topic';
$string['add_association'] = '+ Add association';
$string['remove_association'] = '- Remove association';
$string['mark_recommended'] = 'Check what are the recommended uses for this activity ?';
$string['difficulty_of_activity'] = 'Activity difficulty *';
$string['language'] = 'Language';
$string['duration_of_activity'] = 'Duration of activity';
$string['rely_other_activity'] = 'Did you rely on other activity development activities';
$string['rely_other_activity_no'] = 'No. The copyright of the activity is only mine';
$string['rely_other_activity_yes'] = 'Yes. I translated / translated the activity on the basis of another activity';
$string['register_resource'] = 'Record where the resource is from and add a link to it if there is';
$string['summary'] = 'Summary / Purpose of Activity';
$string['summary_of_activity'] = 'Record a summary here about the activity';
$string['teacherremarks'] = 'Teacher remarks';
$string['tag_item'] = 'Tag the item to enable quick detection in the repository';
$string['first_tag'] = 'first tag';
$string['add_tag'] = 'Add tag';
$string['technical_evaluations'] = 'If technical evaluations are required, please mark it here';
$string['mobile_and_desktop'] = 'Mobile and Computer';
$string['only_desktop'] = 'Computer only';
$string['feedback_activity'] = 'What is the feedback in this activity';
$string['feedback_during_activity'] = 'Feedback during activity';
$string['includes_hints'] = 'Includes hints';
$string['includes_example'] = 'Includes example of solution';
$string['validation'] = 'Validation';
$string['general_comments'] = 'General Comments';
$string['add_image'] = 'Add an image to represent the activity';
$string['select_image'] = 'Select image to upload';
$string['quick_drum'] = "Quick Share";
$string['write_tags_here'] = "Type a tag name";
$string['mail_subject_add_activity'] = "New activity added to repository";
$string['mail_subject_duplicate_mid'] = "In new activity from repository mid duplicated";
$string['mail_subject_shared_teacher'] = "Share activity";

$string['subject_message_for_teacher_by'] = 'Activity {$a->activity_name} added by {$a->teacher_name}';

$string['settingscatalogcategoryid'] = 'Catalog category for upload';
$string['settingscatalogcategoryiddesc'] = 'Catalog category for upload';
$string['succesfullyrecieved'] = 'Succesfully recieved';

// Sharewithbutton.
$string['use_activity'] = 'Use activity';
$string['sharewithpopuptitle'] = 'Choosing the sharing method';
$string['messageprovider:sharewith_notification'] = 'Share with';
$string['messageprovider:shared_notification'] = 'Shared';

$string['ask_question_before_copying'] =
        'Hi! I got a link to copy the activity {$a->modname}, And I have a question about the item. I wanted to ask...';
$string['word_copy'] = 'copy';
$string['selectchain'] = 'Select sequence';
$string['continue'] = 'Continue';

$string['info'] = 'Info';
$string['no_matching_courses_found'] = 'No matching courses found';
$string['copy_activity_chain'] = 'This activity depends on other activities, do you want to copy the activity chain?';

$string['error_quiz_category'] = 'Dear teacher,<br>
At least some of the questions in your exam do not belong to the "Default examiner" category.
You must check and categorize these questions before uploading the examiner to the shared repository.
For questions / clarifications, please contact: <strong>petel@weizmann.ac.il</strong>';
$string['error_courseid'] = 'Wrong course id';

// Mails and notifications.
$string['mail_subject_to_teacher_activity'] = 'PeTeL system - A new activity is ready for you.';
$string['mail_subject_to_teacher_course'] = 'PeTeL system - A new course is ready for you';
$string['notification_course_to_teacher'] = '<p>Hello {$a->user_fname} {$a->user_lname}</p>
<p>Your Course "{$a->coursename}" have been duplicated</p><p><a href="{$a->url}">view course</a></p>';
$string['notification_activity_to_teacher'] = '<p>Hello {$a->user_fname} {$a->user_lname}</p>
<p>Your Activity "{$a->activityname}" have been shared to "{$a->coursename}" section "{$a->sectionname}"</p>';
$string['notification_section_to_teacher'] = '<p>Hello {$a->user_fname} {$a->user_lname}</p>
<p>Your Section "{$a->sectionname}" have been shared to "{$a->coursename}"</p>';
$string['notification_activity_to_banksharing'] = '<p>Hello {$a->user_fname} {$a->user_lname}</p>
<p>Your Activity "{$a->activityname}" have been shared to bank</p>';
$string['notification_activity_to_bankdownload'] = '<p>Hello {$a->user_fname} {$a->user_lname}</p>
<p>Your Activity "{$a->activityname}" have been shared to "{$a->coursename}" section "{$a->sectionname}"</p>';
$string['mail_activity_to_banksharing'] = '<p>Hello {$a->user_fname} {$a->user_lname}</p>
<p>The Activity "{$a->activityname}" have been shared to bank</p>';
$string['copy_all_sub'] = "Do you want to copy all sub topics and activities?";

$string['select_competencies'] = 'Competencies';
$string['write_competencies_here'] = 'Selected competencies';
$string['rights_management'] = 'More info about rights management';

$string['towhatsapp'] = 'To whatsapp';
$string['petelmessage'] = 'Petel message';
$string['copytoclipboard'] = 'Copy to clipboard';

// Role capabilities.
$string['sharewith:copyactivity'] = 'Copy activity';
$string['sharewith:copycourse'] = 'Copy course';
$string['sharewith:copysection'] = 'Copy section';
$string['sharewith:shareactivity'] = 'Share activity';

// Alert.
$string['thelinkhasbeencopied'] = 'The link has been copied';

// Copy section link clipboard.
$string['copylinksection'] = 'Copy the link to the section';
$string['copylinksectionsuccess'] = 'The link has been copied to the clipboard';
