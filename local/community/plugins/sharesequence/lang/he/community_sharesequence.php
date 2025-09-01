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
 * @package     community_sharesequence
 * @category    string
 * @copyright  2018 Devlion <info@devlion.co>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['pluginname'] = 'שיתוף רצפי פעילויות';
$string['taskssharesequence'] = 'Task share sequence';
$string['buttonshare'] = 'שיתוף רצפי פעילויות';

$string['cancel'] = 'ביטול';
$string['copy'] = 'העתק';
$string['back'] = 'חזרה';
$string['end'] = 'סיום';
$string['sharingbutton'] = 'שיתוף';
$string['menupopuptitle'] = 'בחירת פעולה רצויה';
$string['copyquestionstoquiz'] = 'העתקה למשימה אחרת';
$string['copyquestionstoquizsuccess'] = 'שימו לב! פעולות מסוימות דורשות מספר רגעים להשלמה.';
$string['selectcourse'] = 'בחירת קורס ' . '<span style="font-size: 12px;">(ניתן להקליד את שם הקורס)</span>';
$string['selectquiz'] =
        'בחירת משימה ללא ניסיונות מענה ' . '<span style="font-size: 12px;">(הקלידו את שם המשימה או בחרו מהרשימה)</span>';
$string['selectquizerror'] = 'נא לבחור משימה';

$string['copyquestionstocategory'] = 'העתקה לקטגוריה אחרת';
$string['selectcategory'] = 'בחירת קטגוריה ' . '<span style="font-size: 12px;">(ניתן להקליד את שם הקטגוריה)</span>';

// Events.
$string['eventcopytoquiz'] = 'העתקה למשימה אחרת';
$string['eventcopytocategory'] = 'העתקה לקטגוריה אחרת';
$string['eventcopytocatalog'] = 'העתקה למאגר';

// Access.
$string['sharesequence:questioncopy'] = 'Question Copy';

// Upload to catalog.
$string['advanced_catalog_options'] = 'אפשרויות קטלוג מתקדמות';
$string['share_national_shared'] = 'שיתוף רצף הוראה למאגר המשותף';
$string['eventquestionupload'] = 'שיתוף רצף הוראה למאגר המשותף';
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
$string['sharing_content_materials_repository'] = '.שימו לב! שיתוף רצף ההוראה יאפשר גישה לתוכן הפעילויות ללא ביצועי תלמידיכם לכלל המורים
.אנא הקפידו על תקינות התכנים אשר מופיעים ברצף הפעילויות';
$string['share_page1'] = 'בחירת פעילויות לרצף';
$string['share_page2'] = 'שיתוף הרצף';
$string['back_to_page1'] = 'חזרה לתיאור הרצף';
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
$string['sequencenameinput'] = "שם רצף ההוראה";
$string['sequencedescriptioninput'] = "תיאור רצף ההוראה";
$string['sequencedescriptionlabel'] =
        "אנא ספרו למורים אחרים במספר מילים על רצף ההוראה, תובנות מהפעלה עם תלמידים והמלצות שלכם להפעלה מוצלחת. המידע יופיע בתיאור הרצף במאגר המשותף";
$string['sequence_description'] = 'תיאור הרצף';
$string['selection_of_sequence_units'] = 'בחירת פעילויות ברצף';
$string['teaching_units'] = 'יחידות הוראה';
$string['teaching_units_subtitle'] =
        'סמנו את הפריטים או הקטגוריות על מנת להוסיפם לרצף ההוראה. <br/> ביטול הסימון יוריד את הפריט מהרצף.';
$string['teaching_sequence'] = 'רצף הוראה';
$string['teaching_sequence_subtitle'] = 'גררו את הפריטים על מנת לסדר אותם כרצונכם. <br/> לחיצה על ה-x תמחק את הפריט מהרצף.';
$string['delete_unit_from_sequence'] = 'מחק את היחידה מהרצף';
$string['rename_sequence_unit'] = 'שנה את שם יחידת הרצף';
$string['nothing_to_view'] = 'אין נתונים';
$string['nothing_selected'] = 'נא לבחור יחידות הרצף';
$string['popupmessagesuccesstitle'] = "הודעה";
$string['popupmessagesuccesscontent'] = "שימו לב! כעת מתבצע תהליך העתקת רצף ההוראה שנבחרו. פעולה זו דורשות מספר דקות להשלמה.";
$string['popupcheckavailabilitytitle'] = "התראה";
$string['popupcheckavailabilitycontent'] = "רשימת פעילויות מהגבלות גישה שחסרים ברשימה:";
$string['popupcheckavailabilitybuttonsave'] = "להמשיך בכל מקרה";
$string['hidden'] = "מוסתר";
