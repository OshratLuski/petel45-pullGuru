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
 * @package     community_oer
 * @category    string
 * @copyright   2018 Devlion <info@devlion.co>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['pluginname'] = 'Oer catalog';
$string['tasksoer'] = 'Creating and cleaning the Oer catalog';
$string['cachedef_oer_cache'] = 'Oer catalog';
$string['cachedef_oer_activity_cache'] = 'Oer catalog - activity';
$string['cachedef_oer_question_cache'] = 'Oer catalog - question';
$string['cachedef_oer_sequence_cache'] = 'Oer catalog - section';
$string['cachedef_oer_course_cache'] = 'Oer catalog - course';
$string['messageprovider:oer_notification'] = 'Oer catalog';
$string['cacheoercatalog'] = 'Cache oercatalog';

// Main.
$string['searchplaceholder'] = 'שם, תיאור או יוצר';
$string['menuactivity'] = 'פעילויות';
$string['menuquestion'] = 'שאלות';
$string['menusequence'] = 'רצפי הוראה';
$string['menucourse'] = 'קורסים';
$string['mylearningspace'] = 'My learning space';
$string['oerrepository'] = 'Materials repository';
$string['changeiconcategory'] = 'Change icon category';
$string['changedefaultimage'] = 'Change default image in oercatalog';
$string['link'] = 'link';

// Activity.
$string['titleaside'] = 'נושאי הלימוד';
$string['activitycopied'] = 'הועתק לסביבה';
$string['selectactivity'] = 'Select';
$string['copyactivityadress'] = 'Copy activity link';
$string['copysequenceadress'] = 'Copy sequence link';
$string['copycourseadress'] = 'Copy course link';
$string['linkcopiedtoclipboard'] = 'Link copied to clipboard';
$string['activitycopy'] = 'העתק לסביבה שלי';
$string['mycourses'] = 'הקורסים שלי';
$string['submitcopy'] = 'הוספה לכאן';
$string['sortby'] = 'הצג תוצאות לפי';
$string['items'] = 'פריטים';
$string['resultsearch'] = 'תוצאות החיפוש';
$string['resultselected'] = 'נבחרו ';
$string['successcopybody'] = 'בקשתכם התקבלה ומתבצעת כעת. שימו לב! פעולות מסוימות דורשות מספר רגעים להשלמה';
$string['approve'] = 'אישור';
$string['defaultuser'] = 'פטל';

$string['sorting1'] = 'יוצרי תוכן';
$string['sorting2'] = 'מספר שימושים';
$string['sorting3'] = 'תאריך עדכון';
$string['sorting4'] = 'סדר א-ב';
$string['sorting5'] = 'המלצות הצוות';
$string['sorting6'] = 'מספר נסיונות מענה';

$string['filter1title'] = 'סוג פריט';
$string['modquiz'] = 'משימות אינטראקטיביות';
$string['modassign'] = 'מטלות';
$string['modquestionnaire'] = 'שאלונים';
$string['moddata'] = 'בסיס־נתונים';
$string['modglossary'] = 'אגרון';
$string['modlesson'] = 'שיעור מסתעף';
$string['modhvp'] = 'H5P';
$string['modgame'] = 'משחקים';
$string['modworkshop'] = 'הערכת־עמיתים';
$string['modresource'] = 'קבצים';
$string['modurl'] = 'קישורים';
$string['modpage'] = 'דפי תוכן';
$string['modother'] = 'אחר';
$string['otherteachers'] = 'מורים מציעים';
$string['mycontent'] = 'תוכן שלי';

$string['filter2title'] = 'שימושים מומלצים';
$string['filter21title'] = 'יוצרי התוכן';
$string['filter22title'] = 'תוכן הפעילות';

$string['filter3title'] = 'סינונים נוספים';
$string['removeallpills'] = 'נקה הכל';

$string['activity_whatsnew'] = 'Something new!';
$string['activity_notvisible'] = 'Hidden';
$string['comments_box'] = 'Comments';
$string['teachers_recommendations'] = 'Recommended by teachers';
$string['base_on_activity'] = 'Based on:';
$string['teachers_offer'] = 'Teachers suggest';
$string['tested_by_petel'] = 'Tested by PETEL team';
$string['tested_by_teachers'] = 'Tested by teachers';
$string['student_response'] = 'Students who submitted';
$string['used_activity'] = 'Teachers downloaded';
$string['image_title_with_stamp'] = 'Type activity {$a->modname}, name activity {$a->cm_name}, recommendation {$a->stamp}';
$string['image_title_without_stamp'] = 'Type activity {$a->modname}, name activity {$a->cm_name}';
$string['createddate'] = 'Created date';

// Question.
$string['filter1titlequestion'] = 'Question type';
$string['the_subject_of_the_question'] = 'Topic question';
$string['type'] = 'Type';
$string['shared_by'] = 'Shared by';
$string['creation_date'] = 'Creation date';
$string['copy'] = 'Copy';
$string['hide_question_content'] = 'Hide questions content';
$string['show_questions_content'] = 'Show questions content';
$string['copy_for_my_environment'] = 'Copy for my environment';
$string['itemshidden'] = 'Hidden item';
$string['viewmetadata'] = 'Question metadata';
$string['enable_hidden_questions'] = 'Edit';
$string['disable_hidden_questions'] = 'View';
$string['enable_view_only_hidden_questions'] = 'Show hidden questions';
$string['disable_view_only_hidden_questions'] = 'Show all questions';
$string['base_on_question'] = 'Based on:';
$string['edit_question'] = 'Edit question';
$string['edit_question_metadata'] = 'Question metadata';
$string['delete_question'] = 'Delete';
$string['qpopupdeletetitle'] = 'Approve';
$string['qpopupdeletebody'] = 'האם הינך בטוח כי ברצונך למחוק את שאלות נבחרות?';
$string['view_selected_questions'] = 'הצגת שאלות שנבחרו';
$string['qitems'] = 'שאלות';

// Sequence.
$string['sequence_activity'] = 'פעילות';
$string['sequence_describe'] = 'תיאור';
$string['sequencecopied'] = 'הועתק לסביבה';
$string['sequencecopy'] = 'העתק לסביבה שלי';

// Course.
$string['section_on_course'] = 'נושאים בקורס';
$string['course_shared_disabled'] = 'שימו לב, קורס זה לא ניתן להעתיק בשלמותו
אך ניתן להעתקה בחלקים נפרדים
(פעילויות ויחידות הוראה)';
$string['error_category'] = 'לא ניתן לבצע את הפעולה, אנא פנו לתמיכה';
$string['title_copy_success'] = 'העתקת קורס';
$string['body_copy_success'] = 'מתחילה העתקה ומורה מקבל התראה בגמר התהליך.';
$string['body_copy_question'] = 'האם להעתיק את הקורס לסביבה שלך?';

// Review.
$string['responses_to_activity'] = '{$a->count} תגובות לפעילות: {$a->activityName}';
$string['responses_to_course'] = '{$a->count} תגובות לקורס: {$a->courseName}';
$string['responses_to_question'] = '{$a->count} תגובות לשאלה: {$a->questionName}';
$string['responses_to_sequence'] = '{$a->count} תגובות לרצף הוראה: {$a->sequenceName}';
$string['deleted'] = 'נמחק';
$string['askforfeedback'] = 'הי {$a->userName},<br>ראינו שהשתמשת לאחרונה בפעילות <a href="{$a->activityUrl}" target="_blank">"<strong>{$a->activityName}</strong>"</a>. נשמח לקבל את חווית הדעת שלך על השימוש בפעילות.<br>
עזרו לנו לדעת האם להמליץ על פעילות זו למורים אחרים.<br><br>
<span style="border: 2px solid; border-radius: 4px; padding: 7px; margin: 0 5%; display: block;">רוצים להשפיע יותר?<br> מעכשיו המשוב שלכם יהיה זמין למורים גם בפעילות המקורית במאגר וגם במשבצת "המלצות" בעמוד הסביבה שלי</span>
<br>';
$string['requiredfield'] = 'שדה נדרש';
$string['give_feedback'] = 'השאירו משוב על פריט: ';
$string['feedbacks_count'] = 'עד היום מלאתם {$a->sofar} משובים מתוך 5, נותרו עוד {$a->left} משובים ליעד!';
$string['dont_wont_comment_activity'] = 'לא תודה';
$string['dont_wont_comment_activity_popup'] = 'ביטול';
$string['to_write_comment'] = 'שליחת תגובה';
$string['remind_me_later'] = 'כן. תזכרו אותי';
$string['your_review_submitted'] = 'חוות דעתך נשלחה בהצלחה! ';
$string['close'] = 'סגור';
$string['add_activity_review'] = 'הוספת חוות דעת לפעילות:';
$string['timing_moving_activity'] = 'מה היה תזמון העברת הפעילות במהלך החומר?';
$string['share_your_experience'] = 'אנא שתפו את שאר המורים עם החוויה שלכם בהעברת הפעילות.';
$string['preparation_for_topic'] = 'הכנה לנושא';
$string['topic_start'] = 'תחילת נושא';
$string['during_subject'] = 'במהלך הנושא';
$string['when_topic_over'] = 'בסיום הנושא';
$string['inpreparation_for_test'] = 'כהכנה למבחן';
$string['open_activity'] = 'פתיחת פעילות';
$string['open_course'] = 'פתיחת קורס';
$string['open_question'] = 'פתיחת שאלה';
$string['open_sequence'] = 'פתיחת רצף הוראה';
$string['mode_transferring_activity'] = 'מה היה אופן העברת הפעילות?';
$string['can_choose_more_than_one_option'] = ' (ניתן לבחור יותר מאפשרות אחת)';
$string['classroom'] = 'בכיתה';
$string['home'] = 'בבית';
$string['smartphone'] = 'בסמארטפון';
$string['computer'] = 'במחשב';
$string['report_error'] = ' דיווח על טעות / תקלה';
$string['please_inform_of_error'] = 'אנא דווחו לנו על כל טעות או תקלה שמצאתם בפעילות (טעות בחישוב, בעיה בהרצה או כל נושא אחר)';
$string['report_bug'] = 'אני רוצה לדווח על תקלה';
$string['report_mistake'] = 'אני רוצה לדווח על טעות';
$string['describe_location'] = 'נסו לתאר את מיקום הטעות, אופיה וכיצד יש לתקנה';
$string['problem_details'] = 'פירוט התקלה...';
$string['add_picture'] = 'הוספת תמונה';
$string['provide_information'] = 'אנא פרטו בכתב לגבי הנושאים הבאים';
$string['timing_activity'] = 'אופן ותזמון הפעילות<span> (תזמון העברת הפעילות, כיצד בחרתם להעביר את הפעילות וכד) </span>';
$string['insights_experiences'] =
        'תובנות וחוויות מההפעלה עם תלמידים<span> (מה בפעילות היה אפקטיבי? קשיים שנתקלו בהם התלמידים?...) </span>';
$string['recommendations_teachers'] = 'המלצות למורים אחרים<span> (דגשים, נקודות למחשבה...) </span>';
$string['how_transfer_activity'] = 'אופן העברת הפעילות ';
$string['experience_transfer_activity'] = 'חווית העברת הפעילות ';
$string['recommendations_transfer_activity'] = 'המלצות להעברת הפעילות ';
$string['recommend_activity_to_teachers'] =
        'האם הייתם ממליצים על פעילות זו למורים אחרים?<span> (התשובה לסעיף זה לא תוצג לכלל המורים) </span>';
$string['recommend'] = 'ממליצים';
$string['dontrecommend'] = 'לא ממליצים';
$string['cancel'] = 'ביטול';
$string['send'] = 'שליחה';
$string['petel_group'] = 'צוות פטל';
$string['timing'] = ' תזמון: ';
$string['howtouse'] = ' אופן שימוש: ';
$string['howtotransfer'] = 'אופן ההעברה';
$string['experience'] = 'חוויה';
$string['recommendations'] = 'המלצות';
$string['addcomment'] = '+ הוספת תגובה';
$string['delete'] = 'מחק';
$string['edited'] = 'נערך';
$string['edit'] = 'ערוך';
$string['yes'] = 'כן';
$string['reallydelete'] = 'למחוק תגובה?';
$string['i_want_to_report_a_bug'] = 'אני רוצה לדווח על תקלה';
$string['i_want_to_report_a_contentbug'] = 'אני רוצה לדווח על תקלה בתוכן של פעילות';
$string['redmine_description'] = '*דיווח חדש*
Activity: {$a->activityname}
*שם*: {$a->name}
*דואל*: {$a->email}
*סוג*: {$a->question}
*תוכלו לספר לנו עוד?*
{$a->moreinfo}

PageUrl: {$a->pageurl}
';
$string['redmine_subject'] = '{$a->digest} - Support request ';
$string['text_confirm_reject'] = 'Do you want us to remember you later?';
$string['report_bug_btn'] = 'Report bug';
$string['reviewsheadertext1'] = 'Hi, {$a->userName}.';
$string['reviewsheadertext2'] = 'Do you want edit - "<a href="{$a->activityUrl}" target="_blank">{$a->activityName}</a>"?';
$string['reviewsheadertextdesc'] = 'Text in the popup header';
$string['reviewsquestiontext'] = 'Question text';
$string['reviewsquestiontextdesc'] = '';
$string['reviewstextarea'] = 'Textarea description';
$string['reviewstextareadesc'] = '';
$string['activity_update_notification'] = 'Notify teachers';
$string['review_description_error'] = 'Please fill in a field';
$string['review_activity_remind'] = 'שליחת עדכונים של פעילות: ';
$string['review_activity_remind_saved'] = 'שמירה עדכונים של פעילות: ';
$string['review_activity_remind_submit'] = 'שליחת הודעה למורים שהורידו את הפעילות';
$string['review_activity_remind_submit_save'] = 'שמירה בהיסטורית עדכונים בלבד';
$string['remind_saved'] = 'נשמר';
$string['remindtext'] = 'אנא שימו לב!
<br>
בפריט שהועתק מהמאגר המשותף <a target="_blank" href="{$a->url}">"{$a->name}"</a> בתאריך {$a->date} נערכו שינויים.';
$string['sent'] = 'Sent';
$string['cm_version_changed'] = 'שים לב! במאגר המשותף קיימת גרסה עדכנית של <a target="_blank" href="{$a->url}">"{$a->name}"</a>' .
        '<br>היסטוריה עדכונים:{$a->version_history}';
$string['review_show'] = 'Show';
$string['sub_comments'] = 'sub comments';
$string['hide_comments'] = 'Hide comments';
$string['add_text'] = 'כתיבת תגובה...';
$string['no_active_courses'] = 'The activity is in one of the currently inactive courses.';
$string['active_courses'] = 'Below is the list of active courses in which the activity is located:';

// Popup questions.
$string['copyquestionsfromoer'] = "Copy questions from my oer questions";
$string['qshare'] = "Share";
$string['popupmessagesuccesstitle'] = "Message";
$string['popupmessagesuccesscontent'] = "Pay attention! Some actions require a number of moments to complete.";
$string['popupbuttondisabled'] = "In process...";
$string['popupbuttonenabled'] = "Ok";
$string['popupmessagefailtitle'] = "Attention";
$string['popupmessagefailcontent'] = "Please select questions";

// Events.
$string['eventshare'] = 'Share link';
$string['oer_activity_filter'] = 'Oercatalog activity filter';
$string['oer_question_filter'] = 'Oercatalog question filter';
$string['oer_sequence_filter'] = 'Oercatalog sequence filter';
$string['oer_course_filter'] = 'Oercatalog course filter';
$string['oer_reviews_open'] = 'Oercatalog reviews open';
$string['oer_reviews_addmessage'] = 'Oercatalog reviews add message';
$string['oer_reviews_addcomment'] = 'Oercatalog reviews add comment';
$string['oer_reviews_deletecomment'] = 'Oercatalog reviews delete comment';
$string['oer_reviews_deletemessage'] = 'Oercatalog reviews delete message';
$string['oer_reviews_editcomment'] = 'Oercatalog reviews edit comment';
$string['oer_move_module'] = 'Move course module';
$string['oer_resort_course'] = 'Resort course';
$string['oer_resort_category'] = 'Resort category';

// Settings.
$string['community_oer_main_section'] = 'כללי';
$string['community_oer_activity_section'] = 'מאגר פעילויות';
$string['community_oer_question_section'] = 'מאגר שאלות';
$string['community_oer_sequence_section'] = 'מאגר רצפי הוראה';
$string['community_oer_course_section'] = 'מאגר קורסים';
$string['itemsonpage'] = 'פריטים בעמוד';
$string['itemsonpagedesc'] = 'פריטים בעמוד (0 - מנותק)';
$string['filter_modtypes'] = 'נא לבחור mods למסנן';
$string['filter_modtypes_desc'] = '';
$string['filter_qtypes'] = 'נא לבחור שאלות למסנן';
$string['filter_qtypes_desc'] = '';
$string['enablereviews'] = 'Enable reviews for activities';
$string['activityviewed'] = 'Activity views';
$string['activityvieweddesc'] = 'How many students should view the activity before starting the review';
$string['archiveoldrequest'] = 'Archive old requests';
$string['archiveoldrequestdesc'] = 'Archive review requests older then X weeks ';
$string['rejectreviewbutton'] = 'Button reject review';
$string['rejectreviewbuttondesc'] = 'After which number of views add the "Reject review" button.';
$string['reviewcohort'] = 'Special review cohort';
$string['reviewscountstartdate'] = 'Start date from start to count';
$string['reviewscountstartdatedesc'] = 'Simple text date, for example Dec 1, 2020';
$string['oertabactivity'] = 'Cohort for modules TAB';
$string['oertabquestion'] = 'Cohort for questions TAB';
$string['oertabsequence'] = 'Cohort for sequence TAB';
$string['oertabcourse'] = 'Cohort for course TAB';
$string['reviewrating'] = '"Recommended by teachers" in percent';
$string['reviewratingdesc'] = '"Recommended by teachers" in percent';
$string['minreviewcount'] = 'Please select the amount of positive feedback';
$string['minreviewcountdesc'] = 'Recommended by teachers only if at least X positive feedback has been received (0 - closed)';
$string['defaultlangactivity'] = 'Select default language filter';
$string['notselected'] = 'Not selected';
$string['defaultsortactivity'] = 'Default sort activity';
$string['defaultsortquestion'] = 'Default sort activity';
$string['defaultsortsequence'] = 'Default sort activity';
$string['defaultsortcourse'] = 'Default sort activity';
$string['minresponses'] = 'Min student responses';
$string['minresponsesdesc'] = 'Min student responses fro oer';

// Messages.
$string['reviewnotificationmessage'] =
        'התקבלה תגובה חדשה לפריט במאגר אליו כתבת משוב בעבר. קישור לצפיה <a href="{$a->url}">"{$a->name}"</a>';

$string['fulltext'] = 'Full info';
$string['trimtext'] = 'Short info';
$string['originalactivity'] = 'Link to the original activity';
$string['translationbasedon'] = 'Link to the translation activity';
$string['translatedby'] = ', translated by ';

// Page recache.
$string['recachebutton'] = 'Submit';
$string['recachemenu'] = 'Recache menu';
$string['recacheactivity'] = 'Recache activity';
$string['recachequestion'] = 'Recache question';
$string['recachesequence'] = 'Recache sequence';
$string['recachecourse'] = 'Recache course';
$string['recachebegin'] = 'Recache begin';
$string['recacheerror'] = 'Please select caches';
$string['oer:questioncopy'] = 'Copy question';

// Email: new activity in oer.
$string['settingsmailnewoeractivity'] = 'Sending an email about new activity in oer';
$string['settingsmailnewoeractivitydesc'] = 'Sending an email to cohort teachers about new activity in oer';
$string['settingssubjectmailnewoeractivity'] = 'message subject';
$string['settingssubjectmailnewoeractivitydesc'] = 'message subject';
$string['settingsmessagemailnewoeractivity'] = 'body of the message';
$string['settingsmessagemailnewoeractivitydesc'] = 'course name: {course} | activity name: {activity} | cm id : {cmid}';
$string['removefrommagarmaillist'] = '<br><a href="{$a->url}">To be removed from the mailing list</a>';
$string['oerremovemsg'] = 'Successfully removed from mailing list: Updates on new item';
$string['erroroerremovemsg'] = 'Unable to perform the operation.';

// Approve activity message.
$string['oeractivityapprovesuccess'] = '{$a->cmname} in course {$a->coursename} approved';
$string['oeractivityapprovefaild'] = 'Something went wrong with the activity confirmation. Please contact the system administrator';

// Grouper.
$string['grouper'] = 'Learning analytics';
$string['selectgrouper'] = 'Select grouper';

// Cache.
$string['cachedef_oer_is_repository_cache'] = 'Oer catalog';

$string['sequenceshare'] = 'Sequence page';
