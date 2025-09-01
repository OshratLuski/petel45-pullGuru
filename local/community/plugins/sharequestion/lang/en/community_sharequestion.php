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
 * @package     community_sharequestion
 * @category    string
 * @copyright   2019 Devlion <info@devlion.co>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['pluginname'] = 'Sharing questions';
$string['taskssharequestion'] = 'Task share question';

$string['cancel'] = 'Cancel';
$string['copy'] = 'Copy';
$string['back'] = 'Back';
$string['end'] = 'End';
$string['sharingbutton'] = 'Sharing';
$string['menupopuptitle'] = 'Select the desired action';
$string['copyquestionstoquiz'] = 'Copy questions to quiz';
$string['copyquestionstoquizsuccess'] = 'Pay attention! Some actions require a number of moments to complete.';
$string['selectcourse'] = 'Select course';
$string['selectquiz'] = 'Select quiz (without attempts)';
$string['selectquizerror'] = 'Please select quiz';

$string['copyquestionstocategory'] = 'Copy questions to category';
$string['selectcategory'] = 'Select category';
$string['settingsshowncompetencysection'] = 'Show only competency of section';
$string['settingssshowncompetencysectiondesc'] = '';

// Events.
$string['eventcopytoquiz'] = 'Copy to quiz';
$string['eventcopytocategory'] = 'Copy to category';
$string['eventcopytocatalog'] = 'Copy to catalog';

// Access.
$string['sharequestion:questioncopy'] = 'Question Copy';

// Upload to catalog.
$string['advanced_catalog_options'] = 'Advanced catalog options';
$string['share_national_shared'] = 'Share the national shared database';
$string['eventquestionupload'] = 'Upload question';
$string['question_upload_to_mr'] = 'Question has been sent to the shared repository and will be available to all teachers as soon as possible
thanks for sharing!';
$string['error'] = 'Error';
$string['system_error_contact_administrator'] = 'System error, contact administrator';
$string['item_name'] = 'Item name:';
$string['theme_of_the_question'] = 'Theme of the activity <span class="font-weight-normal">(select the third level from the drop-down menu)</span>';
$string['error_quiz_category'] = 'Dear teacher,<br>
At least some of the questions in your exam do not belong to the "Default examiner" category.
You must check and categorize these questions before uploading the examiner to the shared repository.
For questions / clarifications, please contact: <strong>petel@weizmann.ac.il</strong>';
$string['error_courseid'] = 'Wrong course id';
$string['sharing_content_materials_repository'] = 'Pay attention! By sharing this activity, you will be able to access the activity content without the performance of your students to all physics teachers who use the PeTeL environment. Please ensure that the content that appears in the activity is standardized';
$string['warning_qid_present'] = 'Pay attention! You are about to share an question that exists in the shared repository';
$string['share'] = 'Share';
$string['share_item_error'] = 'Need fill ';
$string['settingsshownonrequieredfields'] = 'Button for more options';
$string['settingsshownonrequieredfieldsdesc'] = 'Enable/disable button for more options';
$string['settingsnumberofsection'] = 'Number of sections to copy';
$string['settingsnumberofsectiondesc'] = 'Select number of sections to copy';
$string['availabletocohort'] = 'Share question to OER catalog';
$string['availabletocohortdesc'] = 'Share question to OER catalog available to cohort members';
$string['selecting_the_topic_of_the_activity'] = 'Select the topic of the activity';
$string['competencies_embedded_in_the_activity'] = 'Competencies embedded in the activity. <span class="font-weight-normal">You have to start typing the concept and select it from the list. You can select several competencies:</span>';
$string['select_competency'] = 'Select competency';
$string['assign_the_activity_to_another_learning_topic'] = 'Assign the activity to another learning topic (if necessary)';
$string['no_copyright_of_activity_is_mine_only'] = 'No. The copyright of the activity is mine only';
$string['yes_i_processed_activity_based_on_another_activity'] = 'Yes. I processed / translated the activity based on another activity';
$string['register_resource'] = 'Record where the resource is from and add a link to it if there is';
$string['enter_subject_name'] = 'Enter the name of the subject';
$string['write_tags_here'] = "Type a tag name";

// Share to teacher.
$string['copyquestionstoteacher'] = "Share to teacher";
$string['send'] = "Send";
$string['sharewithteacher'] = "Share with a teacher";
$string['enterteacherhere'] = "Enter the name of the teacher here ...";
$string['commenttoteacher'] = "Here a comment will be made that the teacher will share with the person who has chosen ...";
$string['teachersyouvesent'] = "Teachers you've sent the item to";
$string['userfoto'] = "User foto";
$string['nosharing'] = "No one sent yet";
$string['eventdublicatetoteacher'] = "Copy questions";
$string['subject_message_for_teacher'] = 'Teacher {$a->teachername} share to you questions ';
$string['teachers_error'] = 'יש לבחור מורים';

// Copy questions from my courses.
$string['copyquestionsfrommycourses'] = "Copy questions from my courses";
$string['allcourses'] = "All courses";
$string['allcategories'] = "All categories";
$string['questionchecked'] = "Questions checked";
$string['popupmessagefailtitle'] = "Attention";
$string['popupmessagefailcontent'] = "Please select questions";
$string['popupmessagesuccesstitle'] = "Message";
$string['popupmessagesuccesscontent'] = "Pay attention! Some actions require a number of moments to complete.";
$string['popupbuttondisabled'] = "In process...";
$string['popupbuttonenabled'] = "Ok";
$string['qshare'] = "Share";
$string['qtype'] = "Question type";
$string['qcontent'] = "Question content";
$string['qcreatedby'] = "Created by";
$string['qcreateddate'] = "Created date";
$string['qupdateddate'] = "Updated date";
$string['qsearch'] = "Search questions";
$string['qcancel'] = 'Cancel';
$string['describe_copy_question'] = 'To search for questions in categories and courses and import them into the task, you can search for a free search or find a list below.';
$string['course_name'] = 'Course name: ';
$string['bank_questions'] = 'Bank questions';
$string['course_categories'] = 'Course categories';
$string['noquestions'] = 'No questions';
$string['nocategories'] = 'No categories';
$string['notificationmessage'] = 'השאלות אשר בחרתם להוסיף למשימה {$a->name} זמינות כעת וניתנות לצפיה בעמוד <a href="{$a->url}">"עריכת שאלות"</a>';
$string['noresult'] = 'No result';
