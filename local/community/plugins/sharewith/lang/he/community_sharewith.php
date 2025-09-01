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

$string['pluginname'] = 'שיתוף פעילויות';

$string['wordcopy'] = 'העתקה';
$string['defaultsectionname'] = 'יחידת־הוראה';

// Cron.
$string['tasksharewith'] = 'פעילות שיתוף משימות';

// Settings.
$string['settingscoursecopy'] = 'העתקת קורס';
$string['settingscoursecopydesc'] = 'הפעלה / כיבוי מנגנון העתקת קורס';
$string['settingssectioncopy'] = 'העתקה מיחידה';
$string['settingssectionscopy'] = 'העתקה';
$string['settingssectioncopydesc'] = 'הפעלת / כיבוי העתקת יחידה';
$string['settingsactivityteachercopy'] = 'העתקת פעילות למורה';
$string['settingsactivityteachercopydesc'] = 'הפעלת / כיבוי העתקת פעילות למורה';
$string['settingsactivitycopy'] = 'העתקת פעילות לקורס אחר שלי';
$string['settingsactivitycopydesc'] = 'הפעלת / כיבוי מנגנון העתקת פעילות לקורס שלי';
$string['settingsactivitysending'] = 'שליחת פעילות למורה';
$string['settingsactivitysendingdesc'] = 'הפעלת / כיבוי מנגנון שליחת פעילויות למורה אחר';
$string['settingsshownonrequieredfields'] = 'הצגת כפתור לשדות רשות';
$string['settingsshownonrequieredfieldsdesc'] = 'הצגה / הסתרה של כפתור המציג למורה שדות רשות נוספים, שאינם בהכרח נדרשים';
$string['settingsnumberofsection'] = 'העתקה למספר נושאים';
$string['settingsnumberofsectiondesc'] = 'מאפשר למורה להעתיק את הפריט למספר נושאים (קורסים/יחידות הוראה)';
$string['settingscoursetag'] = 'תג לקורס קהילה';
$string['settingscoursetagdesc'] = 'תג זה יזהה קורסי קהילה, המאפשרים שיתוף של פעילות לכל חברי הקהילה/קורס';
$string['settingsrolesshareteacher'] = 'תפקידים לשיתוף למורה';
$string['settingsrolesshareteacherdesc'] = '';
$string['settingsaddcompetenciescohort'] = 'הזנת מיומנויות';
$string['settingsaddcompetenciescohortdesc'] =
        'רק חברי קבוצה מערכתית נבחרת יוכלו להזין מיומנויות בעת שיתוף רכיב קורס למאגר, כאשר לא נבחרת קבוצה מערכתית, לא מוצג מנגנון בחירת מיומנויות בממשק השיתוף.';
$string['settingsshowncompetencysection'] = 'הצגת מיומנויות לפי תת-נושא';
$string['settingssshowncompetencysectiondesc'] = 'הצגת מיומנויות השייכות לפעילויות ביחידת הוראה (נושא) אשר נבחרה';
$string['settingsvisibilitytype'] = 'לבחור את visibility של פעילות';
$string['settingsvisibilitytypedesc'] = 'לבחור את visibility של פעילות אשר שותף למאגר';
$string['showforstudent'] = 'הצגה לסטודנטים';
$string['hideforstudent'] = 'מוסתר מסטודנטים';
$string['hideforstudentavailable'] = 'זמין לסטודנטים, אך אינו מוצג בעמוד הראשי של הקורס';

// Events.
$string['eventcoursecopy'] = 'העתקת קורס';
$string['eventsectioncopy'] = 'העתקה';
$string['eventsectioncopy_tooltip'] = 'העתקת יחידת הוראה לקורס אחר שלי';
$string['eventactivitycopy'] = 'לקורס אחר שלי';
$string['eventactivityupload'] = 'למאגר המשותף';
$string['eventactivitysharetoteacher'] = 'למורה';
$string['eventcommunityshare'] = 'לקהילה';

// Modals.
$string['sectionselection'] = 'בחירת יחידת־הוראה';
$string['selectcourse'] = 'בחירת קורס';
$string['uploadactivity'] = 'העלאת פעילות';
$string['selectcourse_and_section'] = 'בחירת קורס ויחידה';
$string['selecttopic'] = 'בחירת יחידה';
$string['close'] = 'סגירה';
$string['cancel'] = 'ביטול';
$string['submit'] = 'העתקה';
$string['approve'] = 'אישור';
$string['finish'] = 'סיום';
$string['finish2'] = 'נראה לי שאוותר...';
$string['redirectmessage'] = 'כן, בשמחה!';
$string['activity_copied_to_course'] = 'שימו לב! פעולות מסוימות דורשות מספר רגעים להשלמה';
$string['activity_copied_to_course_from_message'] =
        'האם תרצו לשאול את <a target="_blank" href="{$a->link}">{$a->userfirstname} {$a->userlastname}</a> לגבי הפריט?';
$string['section_copied_to_course'] = 'יחידת־ההוראה הועתקה לקורס';
$string['system_error_contact_administrator'] = 'שגיאת מערכת, יש לפנות למנהל';
$string['mail_subject_shared_teacher'] = "שיתוף פעילות";

$string['error_coursecopy'] = 'העתקת הקורס לא זמינה כעת';
$string['error_sectioncopy'] = 'העתקת יחידת־הוראה לא זמינה כעת';
$string['error_activitycopy'] = 'העתקת הפעילות לא זמינה כעת';
$string['error_permission_allow_copy'] = 'אין הרשאות להעתקה, אנא פנו למנהלת המערכת';

$string['eventcopytomaagar'] = "העתקה למאגר";
$string['eventcopytoteacher'] = "שיתוף פעילות";
$string['eventdownloadtoteacher'] = "הורדת פעילות";
$string['eventdublicatetoteacher'] = "העתקת פעילות";
$string['eventcoursemodulevisibilitychanged'] = "נראות רכיב הקורס השתנתה";

$string['menu_popup_title'] = "בחירת אופן השיתוף";
$string['menu_popup_maagar'] = "פרסום במאגר המשותף";
$string['menu_popup_send_teacher'] = "שליחה למורה";
$string['send'] = "שליחה";

$string['back'] = "חזרה";
$string['share_with_teacher'] = "שיתוף עם מורה";
$string['communityselection'] = "בחירת קהילה";
$string['teachers_youve_sent'] = "מורים ששלחת אליהם את הפריט";
$string['enter_teacher_here'] = "יש להקליד כאן את שם המורה...";
$string['comment_to_teacher'] = "כאן תכנס הערה שהמורה ישתף עם מי שבחר...";
$string['enter_course_here'] = "יש לבחור כאן את שם הקורס...";
$string['user_foto'] = "משתמש";
$string['nosharing'] = "עדיין לא נשלח";
$string['error_share_to_teacher'] = "נא לבחור מורים";
$string['error_share_to_community'] = "נא לבחור קורס";

$string['activity_upload_to_mr'] = 'הפעילות {$a->activitytitle} נשלחה למאגר המשותף, ותהיה זמינה לכלל המורים בהקדם
תודה על השיתוף!';
$string['subject_message_for_teacher'] = 'המורה {$a->teachername} שיתף/פה איתך פעילות "{$a->activityname}"';
$string['info_message_for_teacher'] = 'הודעה מפעילות שיתוף';
$string['enter_subject_name'] = 'נא להזין את שם הפריט';

$string['copysection'] = 'העתקת היחידה';
$string['share'] = 'שיתוף';
$string['copy'] = 'העתק';
$string['save'] = 'שיכפול';
$string['how_to_share'] = 'כיצד לשתף?';
$string['share_national_shared'] = 'שיתוף למאגר המשותף';
$string['send_to_teacher'] = 'שליחה למורה';
$string['transfer_another_course'] = 'העתקה לקורס אחר שלי';
$string['no_courses_for_send'] = 'לא נמצאו קהילות בהן את/ה חבר/ה';

$string['succesfullyshared'] = 'הבקשה עודכנה בהצלחה. פריט זה מועתק כעת... תודה!';
$string['succesfullycopied'] = 'הבקשה עודכנה בהצלחה. פריט זה מועתק כעת... תודה!';

$string['activitycopy_title'] = 'פעילות';
$string['sectioncopy_title'] = 'יחידת־הוראה';
$string['coursecopy_title'] = 'קורס';
$string['notification_smallmessage_copied'] = 'הועתק בהצלחה!';
$string['activitycopy_fullmessage'] = 'Your activity was successfully copied to the <a href="{$a->link}">{$a->coursename}</a>';
$string['sectioncopy_fullmessage'] = 'Your section was successfully copied to the <a href="{$a->link}">{$a->coursename}</a>';
$string['coursecopy_fullmessage'] = 'Your course was successfully copied to the <a href="{$a->link}">{$a->coursename}</a>';

// Sharing popup.
$string['choose'] = 'יש לבחור...';
$string['reduce_catalog_options'] = 'צמצום אפשרויות קטלוג';
$string['advanced_catalog_options'] = 'אפשרויות קטלוג מתקדמות';
$string['advanced_catalog_options_2'] = 'קטלוג מתקדם';
$string['advanced_catalog_options_3'] = 'שדות רשות המסייעים באיתור מהיר של הפריט במאגר המשותף';
$string['please_enter_item_name'] = 'נא להזין את שם הפריט';
$string['instructions_for_teachers_on_the_activity'] = 'הנחיות למורים על הפעילות';
$string['please_tell_other_teachers_a_few_words_about_the_activity'] = 'אנא ספרו למורים אחרים במספר מילים על הפעילות, תובנות מהפעלה עם תלמידים והמלצות שלכם להפעלה
מוצלחת. המידע יופיע מתחת לכותרת הפריט במאגר המשותף';
$string['theme_of_the_activity'] = 'נושא הפעילות <span class="font-weight-normal">(לאיזה נושא מתאימה הפעילות?)</span>';
$string['selecting_the_topic_of_the_activity'] = 'בחירת נושא הפעילות';
$string['mark_the_recommended_uses_for_this_activity'] =
        'סמנו את השימושים המומלצים לפעילות זו <span class="font-weight-normal">(ניתן לסמן יותר משימוש אחד)</span>';
$string['competencies_embedded_in_the_activity'] =
        'מיומנויות המוטמעות בפעילות. <span class="font-weight-normal">יש להתחיל להקליד את המושג ולבחור אותו מהרשימה. ניתן לבחור מספר מיומנויות:</span>';
$string['level_of_activity_difficulty'] = 'רמת קושי הפעילות';
$string['select_competency'] = 'בחירת מיומנות';
$string['did_you_rely_on_other_activity_development_activities'] = 'האם הסתמכתם על פעילויות אחרות בפיתוח הפעילות?';
$string['no_copyright_of_activity_is_mine_only'] = 'לא. זכויות היוצרים של הפעילות הינן שלי בלבד';
$string['yes_i_processed_activity_based_on_another_activity'] = 'כן. עיבדתי/תרגמתי את הפעילות על בסיס פעילות אחרת';
$string['how_long_doesthe_activity_last'] = 'כמה זמן נמשכת הפעילות?';
$string['min'] = "'דק";
$string['in_what_language_is_the_activity_conducted'] = 'באיזה שפה נערכת הפעילות?';
$string['what_is_nature_of_feedback_in_this_activity'] = 'מה אופי המשוב בפעילות זו?';
$string['assign_the_activity_to_another_learning_topic'] = 'שיוך הפעילות לנושא למידה נוסף (במידת הצורך)';
$string['agree_to_copyright'] = 'קראתי את הכללים אודות שימוש בתכנים צד-שלישי במערכת פטל ואני מסכים/ה לפעול בהתאם';

$string['warning_cmid_present'] = 'שימו לב! אתם עומדים לשתף פריט אשר קיים במאגר המשותף';
$string['warning_label_select'] = 'פעילות דומה נמצאת במאגר, האם מדובר ב:';
$string['warning_select_option_1'] = 'תרגום של הפעילות';
$string['warning_select_option_2'] = 'תיקון או שיפור של הפעילות';
$string['warning_select_option_3'] = 'פעילות פדגוגית חדשה';
$string['error'] = 'שגיאה';
$string['please_select_course_section'] = 'נא לבחור קורס וסעיף';
$string['sent'] = 'נשלח';
$string['fails'] = 'נכשל';
$string['sharing_sent_successfully'] = 'הזמנה לשיתוף נשלחה בהצלחה';
$string['studysection'] = 'יחידת־הוראה ';
$string['select_sub_topic'] = 'בחירת סעיף בתת נושא';

$string['selectteacher'] = 'בחירת מורה';
$string['selectcommunity'] = 'שיתוף לקהילה';
$string['activitydeleted'] = 'הפעילות נמחקה על ידי היוצר שלה.';
$string['sendingnotallowed'] = 'הפעילות לא זמינה לשיתוף, יש לפנות למנהלת המאגר.';

$string['insert_mails'] = 'דוא"לים של מנהלי המערכת';
$string['insert_mails_desc'] = 'Example: email1@google.com,email2@google.com';

$string['course_count_label'] = 'מספר קורסים להצגה';
$string['search_label'] = 'חיפוש:';
$string['searchcourses:addinstance'] = 'Add Search Courses block';
$string['searchcourses:myaddinstance'] = 'Add Search Courses block to My Home';

$string['system_error'] = 'שגיאת מערכת';
$string['course_error'] = 'מורים יקרים, לא ניתן לשתף פעילות';

$string['category_error'] = 'מורה יקר/ה,<br>
חלק מהשאלות בבוחן שלך לא מצויות בקטגוריה "בררת מחדל של בוחן".<br>
עליך לבדוק ולתקן את השיוך של השאלות האלה לפני העלאת הבוחן למאגר המשותף.<br>
לשאלות והבהרות ניתן לפנות ל: petel@weizmann.ac.il';

$string['category_error_teacher'] = 'מורה יקר/ה,<br>
חלק מהשאלות בבוחן שלך לא מצויות לקטגוריה "בררת מחדל של בוחן".<br>
עליך לבדוק ולתקן את השיוך של השאלות האלה לפני העלאת הבוחן למאגר המשותף.<br>
לשאלות והבהרות ניתן לפנות ל: petel@weizmann.ac.il';

$string['sharing_content_materials_repository'] =
        'שימו לב! שיתוף פעילות זו, תאפשר גישה לתוכן הפעילות ללא ביצועי תלמידיכם לכלל המורים המשתמשים בסביבת PeTeL. אנא הקפידו על תקניות התכנים אשר מופיעים בפעילות';
$string['share_item_error'] = 'יש למלא את השדה: ';
$string['item_name'] = 'שם הפריט';
$string['availability_describe'] = 'שימו לב! פעילות זו היא חלק מרצף הוראה. ברצונכם לשתף את כל שאר הפריטים ברצף למאגר המשוותף?';
$string['glossary_describe'] = 'האם ברצונך ליבא נתונים לפעולות הזאת?';
$string['database_describe'] = 'האם ברצונך ליבא נתונים לפעולות הזאת?';
$string['define_item_cataloged'] = 'הגדירו היכן יקוטלג הפריט במאגר המשתוף';
$string['select_main_topic'] = 'בחירת נושא ראשי';
$string['assignment_appropriate_topics'] = 'בחירת תת נושא';
$string['add_association'] = 'הוספת שיוך +';
$string['remove_association'] = '- הסרת שיוך';
$string['mark_recommended'] = 'סמנו מהם השימושים המומלצים לפעילות זו ?';
$string['difficulty_of_activity'] = '* רמת קושי של הפעילות';
$string['language'] = 'באיזה שפה נערכת הפעילות?';
$string['duration_of_activity'] = 'משך זמן הפעילות';
$string['rely_other_activity'] = 'האם הסתמכת על פעילויות אחרות בפיתוח הפעילות';
$string['rely_other_activity_no'] = 'לא. זכויות היוצרים של הפעילות הינם רק שלי';
$string['rely_other_activity_yes'] = 'כן. עיבדתי/ תירגמתי את הפעילות על בסיס פעילות אחרת';
$string['register_resource'] = 'רישמו מהיכן המשאב והוסיפו קישור אליו במידה ויש';
$string['summary'] = 'תקציר / מטרת הפעילות';
$string['summary_of_activity'] = 'רשמו כאן תקציר אודות הפעילות';
$string['teacherremarks'] = 'המלצות הפעלת פעילות זו עם התלמידים';
$string['tag_item'] = 'תיגו את הפריט על מנת לאפשר איתור מהיר שלו במאגר';
$string['first_tag'] = 'תגית ראשונה';
$string['add_tag'] = 'הוספת תגית';
$string['technical_evaluations'] = 'במידה ונדרשת הערכות טכנית, יש לסמן אותה כאן';
$string['mobile_and_desktop'] = 'מובייל ומחשב';
$string['only_desktop'] = 'מחשב בלבד';
$string['feedback_activity'] = 'מה אופי המשוב בפעילות זו?';
$string['feedback_during_activity'] = 'משוב במהלך הפעילות';
$string['includes_hints'] = 'כולל רמזים';
$string['includes_example'] = 'כולל דוגמא לפתרון';
$string['validation'] = 'תיקוף';
$string['general_comments'] = 'הערות כלליות';
$string['add_image'] = 'הוספת תמונה שתייצג את הפעילות';
$string['select_image'] = 'בחירת תמונה להעלאה';
$string['quick_drum'] = "שיתוף מהיר";
$string['write_tags_here'] = "נא להקליד שם תג";

$string['loading'] = 'מבצע שליחה...';

$string['settingscatalogcategoryid'] = 'Catalog category for upload';
$string['settingscatalogcategoryiddesc'] = 'Catalog category for upload';
$string['succesfullyrecieved'] = 'התקבל בהצלחה';

// Sharewithbutton.
$string['use_activity'] = 'שיתוף פעילות';
$string['sharewithpopuptitle'] = 'בחירת אופן שיתוף הפעילות';

$string['ask_question_before_copying'] = 'היי! קיבלתי קישור להעתקת הפעילות {$a->modname}, ויש לי שאלה לגבי הפריט. רציתי לשאול...';
$string['word_copy'] = 'העתקה';
$string['selectchain'] = 'בחירת רצף';
$string['continue'] = 'המשך';

$string['info'] = 'מידע';
$string['no_matching_courses_found'] = 'לא נמצאו קורסים תואמים';
$string['copy_activity_chain'] = 'פעילות זו תלויה בפעילויות אחרות, האם ברצונכם להעתיק את רצף הפעילויות?';

$string['error_quiz_category'] = 'מורה יקר/ה,<br>
לפחות חלק מהשאלות בבוחן שלך לא שייכות לקטגוריה "בררת מחדל של בוחן".
עליך לבדוק ותלקן את השיוך של השאלות האלה לפני העלאת הבוחן למאגר המשותף.
לשאלות/הבהרות ניתן לפנות ל: <strong>petel@weizmann.ac.il</strong>';
$string['error_courseid'] = 'שגיאה במספר קורס';

// Mails and notifications.
$string['mail_subject_to_teacher_activity'] = 'מערכת פטל - פעילות חדשה זמינה כעת בקורס שלך';
$string['mail_subject_to_teacher_course'] = 'מערכת פטל - קורס חדש זמין כעת עבורך';
$string['notification_course_to_teacher'] = '<p>שלום {$a->user_fname} {$a->user_lname}</p>
<p>הקורס "{$a->coursename}" שוכפל, למעבר לקורס חדש הקליקו: </p><p><a href="{$a->url}">צפיה בקורס</a></p>';
$string['notification_activity_to_teacher'] = '<p>שלום {$a->user_fname} {$a->user_lname}</p>
<p>פעילות "{$a->activityname}" הועתקה ליחידה {$a->sectionname} לקורס {$a->coursename} שלכם להלן קישור לפעילות:</p>';
$string['notification_section_to_teacher'] = '<p>שלום {$a->user_fname} {$a->user_lname}</p>
<p>יחידת הוראה הועתקה מהקורס "{$a->coursename}" לקורס שלכם להלן קישור ליחידה: </p>';
$string['notification_activity_to_banksharing'] = '<p>שלום {$a->user_fname} {$a->user_lname}</p>
<p>פעילות "{$a->activityname}" הועתק למאגר בהצלחה!</p>';
$string['notification_activity_to_bankdownload'] = '<p>שלום {$a->user_fname} {$a->user_lname}</p>
<p>הפעילות "{$a->activityname}" אשר הועתקה מהמאגר לקורס "{$a->coursename}" ליחידה "{$a->sectionname}" שלכם, להלן קישור לפעילות:</p>';
$string['mail_activity_to_banksharing'] = '<p>שלום {$a->user_fname} {$a->user_lname}</p>
<p>פעילות "{$a->activityname}" הועתק למאגר בהצלחה!</p>';
$string['mail_subject_add_activity'] = "פעילות חדשה התווספה למאגר";
$string['mail_subject_duplicate_mid'] = "בפעילות החדשה ממאגר המשותף נמצע MID משוכפל";
$string['copy_all_sub'] = "האם להעתיק תתי יחידות הוראה והפעילויות המצויות בהן?";

$string['select_competencies'] =
        ' נושאים לימוד מיומנויות. יש להתחיל להקליד את המושג ולבחור אותו מהרשימה. ניתן לבחור כמה מיומנויות מהרשימה:';
$string['write_competencies_here'] = 'תיווג נושאי לימוד ומיומנויות';
$string['rights_management'] = 'אודות זכויות יוצרים';

$string['towhatsapp'] = 'לוואטסאפ';
$string['petelmessage'] = 'מסרים בפטל';
$string['copytoclipboard'] = 'העתקת קישור';

// Alert.
$string['thelinkhasbeencopied'] = 'הקישור הועתק';

// Copy section link clipboard.
$string['copylinksection'] = 'העתקת הקישור ליחידה';
$string['copylinksectionsuccess'] = 'הקישור הועתק ללוח הגזירים';
