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

$string['pluginname'] = 'שיתוף שאלות';
$string['taskssharequestion'] = 'Task share question';

$string['cancel'] = 'ביטול';
$string['copy'] = 'העתק';
$string['back'] = 'חזרה';
$string['end'] = 'סיום';
$string['sharingbutton'] = 'שיתוף';
$string['menupopuptitle'] = 'בחירת אופן שיתוף השאלה';
$string['copyquestionstoquiz'] = 'העתקה למשימה נוספת';
$string['copyquestionstoquizsuccess'] = 'שימו לב! פעולות מסוימות דורשות מספר רגעים להשלמה.';
$string['selectcourse'] = 'בחירת קורס ' . '<span style="font-size: 12px;">(ניתן להקליד את שם הקורס)</span>';
$string['selectquiz'] =
        'בחירת משימה ללא ניסיונות מענה ' . '<span style="font-size: 12px;">(הקלידו את שם המשימה או בחרו מהרשימה)</span>';
$string['selectquizerror'] = 'נא לבחור משימה';

$string['copyquestionstocategory'] = 'העתקה לקטגוריה אחרת';
$string['selectcategory'] = 'בחירת קטגוריה ' . '<span style="font-size: 12px;">(ניתן להקליד את שם הקטגוריה)</span>';
$string['settingsshowncompetencysection'] = 'הצגת מיומנויות לפי תת-נושא';
$string['settingssshowncompetencysectiondesc'] = 'הצגת מיומנויות השייכות לפעילויות ביחידת הוראה (נושא) אשר נבחרה';

// Events.
$string['eventcopytoquiz'] = 'העתקה למשימה אחרת';
$string['eventcopytocategory'] = 'העתקה לקטגוריה אחרת';
$string['eventcopytocatalog'] = 'העתקה למאגר';

// Access.
$string['sharequestion:questioncopy'] = 'Question Copy';

// Upload to catalog.
$string['advanced_catalog_options'] = 'אפשרויות קטלוג מתקדמות';
$string['share_national_shared'] = 'שיתוף למאגר המשותף';
$string['eventquestionupload'] = 'למאגר המשותף';
$string['question_upload_to_mr'] = 'השאלה נשלחה למאגר המשותף, ותהיה זמינה לכלל המורים בהקדם
תודה על השיתוף!';
$string['error'] = 'שגיאה';
$string['system_error_contact_administrator'] = 'שגיאת מערכת, יש לפנות למנהל';
$string['item_name'] = 'שם הפריט';
$string['theme_of_the_question'] = 'נושא הפעילות <span class="font-weight-normal">(לאיזה נושא מתאימה הפעילות?)</span>';
$string['error_quiz_category'] = 'מורה יקר/ה,<br>
לפחות חלק מהשאלות בבוחן שלך לא שייכות לקטגוריה "בררת מחדל של בוחן".
עליך לבדוק ותלקן את השיוך של השאלות האלה לפני העלאת הבוחן למאגר המשותף.
לשאלות/הבהרות ניתן לפנות ל: <strong>petel@weizmann.ac.il</strong>';
$string['error_courseid'] = 'שגיאה במספר קורס';
$string['sharing_content_materials_repository'] =
        'שימו לב! שיתוף פעילות זו, תאפשר גישה לתוכן הפעילות ללא ביצועי תלמידיכם לכלל המורים המשתמשים בסביבת PeTeL. אנא הקפידו על תקניות התכנים אשר מופיעים בפעילות';
$string['warning_qid_present'] = 'שימו לב! אתם עומדים לשתף שאלה אשר קייםת במאגר המשותף';
$string['share'] = 'שיתוף';
$string['share_item_error'] = 'יש למלא את ';
$string['settingsshownonrequieredfields'] = 'הצגת כפתור לשדות רשות';
$string['settingsshownonrequieredfieldsdesc'] = 'הצגה / הסתרה של כפתור המציג למורה שדות רשות נוספים, שאינם בהכרח נדרשים';
$string['settingsnumberofsection'] = 'העתקה למספר נושאים';
$string['settingsnumberofsectiondesc'] = 'מאפשר למורה להעתיק את הפריט למספר נושאים (קורסים/יחידות הוראה)';
$string['availabletocohort'] = 'כפתור שיתוף שאלה למאגר';
$string['availabletocohortdesc'] = 'זמינות כפתור שיתוף שאלה למאגר המשותף מוצג לחברי קבוצה מערכתית';
$string['selecting_the_topic_of_the_activity'] = 'בחירת נושא הפעילות';
$string['competencies_embedded_in_the_activity'] =
        'מיומנויות המוטמעות בפעילות. <span class="font-weight-normal">יש להתחיל להקליד את המושג ולבחור אותו מהרשימה. ניתן לבחור מספר מיומנויות:</span>';
$string['select_competency'] = 'בחירת מיומנות';
$string['assign_the_activity_to_another_learning_topic'] = 'שיוך הפעילות לנושא למידה נוסף (במידת הצורך)';
$string['no_copyright_of_activity_is_mine_only'] = 'לא. זכויות היוצרים של הפעילות הינן שלי בלבד';
$string['yes_i_processed_activity_based_on_another_activity'] = 'כן. עיבדתי/תרגמתי את הפעילות על בסיס פעילות אחרת';
$string['register_resource'] = 'רישמו מהיכן המשאב והוסיפו קישור אליו במידה ויש';
$string['enter_subject_name'] = 'נא להזין את שם הפריט';
$string['write_tags_here'] = "נא להקליד שם תג";

// Share to teacher.
$string['copyquestionstoteacher'] = "למורה";
$string['send'] = "שליחה";
$string['sharewithteacher'] = "שיתוף עם מורה";
$string['enterteacherhere'] = "יש להקליד כאן את שם המורה...";
$string['commenttoteacher'] = "כאן תכנס הערה שהמורה ישתף עם מי שבחר...";
$string['teachersyouvesent'] = "מורים ששלחת אליהם את הפריט";
$string['userfoto'] = "משתמש";
$string['nosharing'] = "עדיין לא נשלח";
$string['eventdublicatetoteacher'] = "העתקת שאלות";
$string['subject_message_for_teacher'] = 'המורה {$a->teachername} שיתף/פה איתך שאלות ';
$string['teachers_error'] = 'יש לבחור מורים';

// Copy questions from my courses.
$string['copyquestionsfrommycourses'] = "ייבוא שאלות";
$string['allcourses'] = "כל הקורסים";
$string['allcategories'] = "כל הקטגוריות";
$string['questionchecked'] = "שאלות נבחרו";
$string['popupmessagefailtitle'] = "תשומת הלב";
$string['popupmessagefailcontent'] = "אנא בחר שאלות";
$string['popupmessagesuccesstitle'] = "הודעה";
$string['popupmessagesuccesscontent'] = "שימו לב! כעת מתבצע תהליך העתקת השאלות שנבחרו. פעולה זו דורשות מספר דקות להשלמה.";
$string['popupbuttondisabled'] = "בתהליך...";
$string['popupbuttonenabled'] = "אישור";
$string['qshare'] = "ייבוא שאלות";
$string['qtype'] = "סוג";
$string['qcontent'] = "נושא שאלה";
$string['qcreatedby'] = "נוצר על ידי";
$string['qcreateddate'] = "תאריך שיתוף";
$string['qupdateddate'] = "תאריך עדכון";
$string['qsearch'] = "חיפוש שאלות";
$string['qcancel'] = 'ביטול';
$string['describe_copy_question'] = 'לחיפוש שאלות בקטגוריות ובקורסים וייבוא שלהן למשימה ניתן לחפש חיפוש חופשי או לאתר ברשימה מטה';
$string['course_name'] = 'שם הקורס: ';
$string['bank_questions'] = 'קטגוריות של שאלות';
$string['course_categories'] = 'קטגוריות של משימות';
$string['noquestions'] = 'אין שאלות';
$string['nocategories'] = 'אין קטגוריות';
$string['notificationmessage'] =
        'השאלות אשר בחרתם להוסיף למשימה {$a->name} זמינות כעת וניתנות לצפיה בעמוד <a href="{$a->url}">"עריכת שאלות"</a>';
$string['noresult'] = 'אין תוצאה';
