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

$string['pluginname'] = 'המאגר המשותף';
$string['tasksoer'] = 'יצירת וניקוי המאגר המשותף';
$string['cachedef_oer_cache'] = 'המאגר המשותף';
$string['cachedef_oer_activity_cache'] = 'המאגר המשותף - פעילויות';
$string['cachedef_oer_question_cache'] = 'המאגר המשותף - שאלות';
$string['cachedef_oer_sequence_cache'] = 'המאגר המשותף - רצפי הוראה';
$string['messageprovider:oer_notification'] = 'המאגר המשותף';
$string['cacheoercatalog'] = 'מטמונים של מאגר משותף';

// Main.
$string['searchplaceholder'] = 'שם, תיאור או יוצר';
$string['menuactivity'] = 'פעילויות';
$string['menuquestion'] = 'שאלות';
$string['menusequence'] = 'רצפי הוראה';
$string['menucourse'] = 'קורסים';
$string['mylearningspace'] = 'הסביבה שלי';
$string['oerrepository'] = 'המאגר המשותף';
$string['changeiconcategory'] = 'שנה אייקון של קטגורייה';
$string['changedefaultimage'] = 'שנה את תמונת ברירת המחדל במאגר המשותף';
$string['link'] = 'קישור למקור';

// Activity.
$string['titleaside'] = 'נושאי הלימוד';
$string['activitycopied'] = 'הועתק לסביבה';
$string['selectactivity'] = 'בחירה';
$string['copyactivityadress'] = 'העתקת כתובת הפעילות';
$string['copysequenceadress'] = 'העתקת כתובת הרצף';
$string['copycourseadress'] = 'העתקת כתובת הקורס';
$string['linkcopiedtoclipboard'] = 'הקישור הועתק ללוח';
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

$string['activity_whatsnew'] = 'חדש במאגר';
$string['activity_notvisible'] = 'מוסתר';
$string['comments_box'] = 'תגובות';
$string['teachers_recommendations'] = 'מומלץ ע"י מורים';
$string['base_on_activity'] = 'מתבסס על:';
$string['teachers_offer'] = 'מורים מציעים';
$string['tested_by_petel'] = 'בדיקת צוות פטל';
$string['tested_by_teachers'] = 'בדיקת עמיתים';
$string['student_response'] = 'מספר תלמידים שהגישו';
$string['used_activity'] = 'מורים שהפעילו בכיתה';
$string['image_title_with_stamp'] = 'סוג פעילות {$a->modname} ,שם פעילות {$a->cm_name} ,המלצה {$a->stamp}';
$string['image_title_without_stamp'] = 'סוג פעילות {$a->modname} ,שם פעילות {$a->cm_name}';
$string['createddate'] = 'תאריך היצירה';

// Question.
$string['filter1titlequestion'] = 'סוג שאלה';
$string['the_subject_of_the_question'] = 'נושא השאלה';
$string['type'] = 'סוג';
$string['shared_by'] = 'שותף על ידי';
$string['creation_date'] = 'תאריך שיתוף';
$string['copy'] = 'העתקה';
$string['hide_question_content'] = 'הסתרת תוכן שאלה/ות';
$string['show_questions_content'] = 'הצגת תוכן השאלה';
$string['copy_for_my_environment'] = 'העתקה לסביבה שלי';
$string['itemshidden'] = 'פריטים מוסתרים';
$string['viewmetadata'] = 'מידע־מורחב לשאלות';
$string['enable_hidden_questions'] = 'הפעלת עריכה';
$string['disable_hidden_questions'] = 'סיום עריכה';
$string['enable_view_only_hidden_questions'] = 'הצגת שאלות מוסתרות';
$string['disable_view_only_hidden_questions'] = 'הצגת כל השאלות';
$string['base_on_question'] = 'מתבסס על:';
$string['edit_question'] = 'עריכת שאלה';
$string['edit_question_metadata'] = 'מידע מורחב לשאלה';
$string['delete_question'] = 'מחיקה';
$string['qpopupdeletetitle'] = 'אישור';
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
$string['how_transfer_activity'] = 'אופן העברת הפעילות ...';
$string['experience_transfer_activity'] = 'חווית העברת הפעילות ...';
$string['recommendations_transfer_activity'] = 'המלצות להעברת הפעילות ...';
$string['recommend_activity_to_teachers'] =
        'האם הייתם ממליצים על פעילות זו למורים אחרים?<span> (התשובה לסעיף זה לא תוצג לכלל המורים) </span>';
$string['recommend'] = 'ממליצים';
$string['dontrecommend'] = 'לא ממליצים';
$string['cancel'] = 'ביטול';
$string['send'] = 'שליחה';
$string['petel_group'] = 'צוות פטל';
$string['open_activity'] = 'פתיחת פעילות';
$string['timing'] = ' תזמון: ';
$string['howtouse'] = ' אופן שימוש: ';
$string['howtotransfer'] = 'אופן ההעברה';
$string['experience'] = 'חוויה';
$string['recommendations'] = 'המלצות';
$string['addcomment'] = 'הוספת תגובה';
$string['delete'] = 'מחק';
$string['edited'] = 'נערך';
$string['edit'] = 'ערוך';
$string['yes'] = 'כן';
$string['reallydelete'] = 'למחוק תגובה?';
$string['preparation_for_topic'] = 'הכנה לנושא';
$string['topic_start'] = 'תחילת נושא';
$string['during_subject'] = 'במהלך הנושא';
$string['when_topic_over'] = 'בסיום הנושא';
$string['inpreparation_for_test'] = 'כהכנה למבחן';
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
$string['redmine_subject'] = '{$a->digest} - בקשת תמיכה ';
$string['text_confirm_reject'] = 'האם ברצונך שנתזכר אותך מאוחר יותר?';
$string['report_bug_btn'] = 'דיווח על בעיה בפעילות';
$string['reviewsheadertext1'] = 'הי, {$a->userName}';
$string['reviewsheadertext2'] =
        'ראינו שהשתמשת לאחרונה בפעילות: "<a href="{$a->activityUrl}" target="_blank">{$a->activityName}</a>"?';
$string['reviewsheadertextdesc'] = 'Text in the popup header';
$string['activity_update_notification'] = 'עדכון המורים';
$string['review_description_error'] = 'נא למלא שדה';
$string['review_activity_remind'] = 'שליחת הודעה אודות עדכונים בפעילות: ';
$string['review_activity_remind_saved'] = 'שמירה עדכונים של פעילות: ';
$string['review_activity_remind_submit'] = 'שליחת הודעה למורים שהורידו את הפעילות';
$string['review_activity_remind_submit_save'] = 'שמירה בהיסטורית עדכונים בלבד';
$string['remind_saved'] = 'נשמר';
$string['remindtext'] = 'אנא שימו לב!
<br>
בפריט שהועתק מהמאגר המשותף <a target="_blank" href="{$a->url}">"{$a->name}"</a> נערכו שינויים בתאריך {$a->date}.<br>
פירוט השינויים:<br>';
$string['sent'] = 'נשלח';
$string['cm_version_changed'] = 'שים לב! במאגר המשותף קיימת גרסה עדכנית של <a target="_blank" href="{$a->url}">"{$a->name}"</a>' .
        '<br>היסטוריה עדכונים:{$a->version_history}';
$string['review_show'] = 'הצגת';
$string['sub_comments'] = ' תגובות';
$string['hide_comments'] = 'סגירת תגובות';
$string['add_text'] = 'כתיבת תגובה...';
$string['no_active_courses'] = 'הפעילות נמצאת באחד הקורסים הלא פעילים כעת.';
$string['active_courses'] = 'להלן רשימת הקורסים הפעילים בהם נמצאת הפעילות:';


// Popup questions.
$string['copyquestionsfromoer'] = "ייבוא שאלות ממאגר שאלות";
$string['qshare'] = "ייבוא שאלות";
$string['popupmessagesuccesstitle'] = "הודעה";
$string['popupmessagesuccesscontent'] = "שימו לב! כעת מתבצע תהליך העתקת השאלות שנבחרו. פעולה זו דורשות מספר דקות להשלמה.";
$string['popupbuttondisabled'] = "בתהליך...";
$string['popupbuttonenabled'] = "אישור";
$string['popupmessagefailtitle'] = "תשומת הלב";
$string['popupmessagefailcontent'] = "אנא בחר שאלות";

// Events.
$string['eventshare'] = 'Share link';
$string['oer_activity_filter'] = 'סינון מאגר פעילויות';
$string['oer_question_filter'] = 'סינון מאגר שאלות';
$string['oer_sequence_filter'] = 'סינון מאגר רצפי הוראה';
$string['oer_course_filter'] = 'סינון מאגר קורסים';
$string['oer_reviews_open'] = 'נפתח מאגר תגובות';
$string['oer_reviews_addmessage'] = 'הוסף תגובה';
$string['oer_reviews_addcomment'] = 'הוסף תת תגובה';
$string['oer_reviews_deletecomment'] = 'מחיקת תת תגובה';
$string['oer_reviews_deletemessage'] = 'מחיקת תגובה';
$string['oer_reviews_editcomment'] = 'ערוך תת תגובה';
$string['oer_move_module'] = 'העברה פעילות';
$string['oer_resort_course'] = 'מיון קורס';
$string['oer_resort_category'] = 'מיון קטגוריה';

// Settings.
$string['community_oer_main_section'] = 'כללי';
$string['community_oer_activity_section'] = 'מאגר פעילויות';
$string['community_oer_question_section'] = 'מאגר שאלות';
$string['community_oer_sequence_section'] = 'מאגר רצפי הוראה';
$string['community_oer_course_section'] = 'מאגר קורסים';
$string['itemsonpage'] = 'פריטים בעמוד';
$string['itemsonpagedesc'] = 'פריטים בעמוד (0 - מנותק)';
$string['filter_modtypes'] = 'נא לבחור רכיבים למסנן';
$string['filter_modtypes_desc'] = '';
$string['filter_qtypes'] = 'נא לבחור שאלות למסנן';
$string['filter_qtypes_desc'] = '';
$string['enablereviews'] = 'מנגנון בקשת משוב וביקורות עבור פעילויות';
$string['activityviewed'] = 'נפח פעילות תלמידים';
$string['activityvieweddesc'] = 'כמה תלמידים צריכים לצפות בפעילות לפני תחילת הסקר';
$string['archiveoldrequest'] = 'הסרה מרשימת הבקשות';
$string['archiveoldrequestdesc'] = 'בקשות עבור פעילויות אשר בוצעו לפני יותר מ X שבועות, יוסרו מהרשימה של מורה ';
$string['rejectreviewbutton'] = 'אינני מעוניין להוסיף תגובה לפעילות זו';
$string['rejectreviewbuttondesc'] = 'לאחר איזה מספר צפיות הוסף את כפתור "אינני מעוניין להוסיף תגובה לפעילות זו".';
$string['reviewcohort'] = 'קבוצת מחקר - רואה משבצת מורים ממליצים';
$string['reviewscountstartdate'] = 'תאריך התחלה מההתחלה לספירה';
$string['reviewscountstartdatedesc'] = 'תאריך טקסט פשוט, למשל (Dec 1, 2020)';
$string['reviewsquestiontext'] = 'תבנית לשאלה המורה';
$string['reviewsquestiontextdesc'] = '';
$string['reviewstextarea'] = 'תבנית למשוב/סקירת המורה';
$string['reviewstextareadesc'] = '';
$string['oertabactivity'] = 'קבוצה מערכתית למאגר פעילוית';
$string['oertabquestion'] = 'קבוצה מערכתית למאגר שאלות';
$string['oertabsequence'] = 'קבוצה מערכתית לרצפי הוראה';
$string['oertabcourse'] = 'קבוצה מערכתית לקורסים';
$string['reviewrating'] = '"מומלץ על ידי מורים" באחוזים';
$string['reviewratingdesc'] = '"מומלץ על ידי מורים" באחוזים';
$string['minreviewcount'] = 'נא לבחור כמות משובים חיוביים';
$string['minreviewcountdesc'] = 'מומלץ על ידי מורים רק אם התקבלו לפחות X משובים חיוביים (0 - מנגנון מנותק)';
$string['defaultlangactivity'] = 'נא לבחור מסנן שפה ברירת מחדל';
$string['notselected'] = 'לא נבחר';
$string['defaultsortactivity'] = 'נא לבחור מיון ברירת מחדל';
$string['defaultsortquestion'] = 'נא לבחור מיון ברירת מחדל';
$string['defaultsortsequence'] = 'נא לבחור מיון ברירת מחדל';
$string['defaultsortcourse'] = 'נא לבחור מיון ברירת מחדל';
$string['minresponses'] = 'מספר מענות מינמלי';
$string['minresponsesdesc'] = 'מספר מענה מינמלי לקביעת הורדת מורה פעילה';
// Messages.
$string['reviewnotificationmessage'] =
        'התקבלה תגובה חדשה לפריט המאגר המשותף אותו העתקת לסביבה שלך או אליו כתבת משוב בעבר. קישור לצפיה <a href="{$a->url}">"{$a->name}"</a>';

$string['fulltext'] = 'תיאור מלא';
$string['trimtext'] = 'תיאור קצר';
$string['originalactivity'] = 'קישור לפעילות המקורית';
$string['translationbasedon'] = 'קישור לתרגום הפעילות';
$string['translatedby'] = ', שתורגמה על ידי ';

// Page recache.
$string['recachebutton'] = 'אישור';
$string['recachemenu'] = 'מטמון של תפריט';
$string['recacheactivity'] = 'מטמון של פעילויות';
$string['recachequestion'] = 'מטמון של שאלות';
$string['recachesequence'] = 'מטמון של רצפי הוראה';
$string['recachecourse'] = 'מטמון של קורסים';
$string['recachebegin'] = 'מתחיל מטמון מחדש';
$string['recacheerror'] = 'נא לבחור מטמונים';

// Email: new activity in oer.
$string['settingsmailnewoeractivity'] = 'שליחת אימיל על פעילות חדשה במאגר המשותף';
$string['settingsmailnewoeractivitydesc'] = 'שליחת הודעה לקבוצה מערכתית מורים על פעילות חדשה במאגר המשותף';
$string['settingssubjectmailnewoeractivity'] = 'נושא ההודעה';
$string['settingssubjectmailnewoeractivitydesc'] = 'נושא ההודעה';
$string['settingsmessagemailnewoeractivity'] = 'גוף ההודעה';
$string['settingsmessagemailnewoeractivitydesc'] = 'שם הקורס: {course} | שם הפעילות: {activity} | מס זיהוי : {cmid}';
$string['removefrommagarmaillist'] = '<br><a href="{$a->url}">להסרה מרשימת התפוצה</a>';;
$string['oerremovemsg'] = 'הוסרת בהצלחה מרשימת התפוצה: עדכונים על פריט חדש';
$string['erroroerremovemsg'] = 'לא ניתן לבצע את הפעולה. ניתן לפתוח קריאה בכפתור האוזניות';

// Approve activity message.
$string['oeractivityapprovesuccess'] = '{$a->cmname} בקורס {$a->coursename} אושרה בהצלחה';
$string['oeractivityapprovefaild'] = 'משהו השתבש באישור הפעילות, אנא פנה למנהל המערכת';

// Grouper.
$string['grouper'] = 'אנליטיקות למידה';
$string['selectgrouper'] = 'מותאם לגרופר';

$string['sequenceshare'] = 'רצף הוראה';
