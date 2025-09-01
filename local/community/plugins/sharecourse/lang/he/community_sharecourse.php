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
 * @package     community_sharecourse
 * @category    string
 * @copyright   2019 Devlion <info@devlion.co>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['pluginname'] = 'שיתוף קורסים';
$string['taskscopycourse'] = 'Task copy course';

// Access.
$string['sharecourse:coursecopy'] = 'Course Copy';

// Settings.
$string['availabletocohort'] = 'כפתור שיתוף קורס למאגר';
$string['availabletocohortdesc'] = 'זמינות כפתור שיתוף קורס למאגר המשותף מוצג לחברי קבוצה מערכתית';
$string['settingsshownonrequieredfields'] = 'הצגת כפתור לשדות רשות';
$string['settingsshownonrequieredfieldsdesc'] = 'הצגה / הסתרה של כפתור המציג למורה שדות רשות נוספים, שאינם בהכרח נדרשים';
$string['oercoursecohortrole'] = 'בחר תפקיד לקורס משותף';
$string['oercoursecohortroledesc'] = 'בחר תפקיד לקורס משותף';
$string['oercoursecohort'] = 'בחר קבוצה לקורס משותף';
$string['oercoursecohortdesc'] = 'בחר קבוצה לקורס משותף';
$string['oercoursesharevisible'] = 'קורס ישותף גלוי?';
$string['oercoursesharevisibledesc'] = 'האם קורס ישותף למאגר המשותף במצב גלוי או מוסתר.';

// Events.
$string['course_share'] = 'שיתוף קורס למאגר משותף';
$string['course_unshare'] = 'ביטול שיתוף קורס ממאגר המשותף';
$string['eventcoursecopy'] = 'העתקת קורס';

// Main popup.
$string['menupopuptitle'] = 'בחירת פעולה רצויה';
$string['cancel'] = 'ביטול';
$string['buttonshare'] = 'שיתוף קורס';
$string['menucoursenode'] = 'שיכפול הקורס';
$string['courseuploadtocatalog'] = 'למאגר המשותף';
$string['sharecoursepombim'] = 'לסביבת העמיתים';
$string['coursereuploadtocatalog'] = 'שכפול קורס';
$string['copycoursetoteacher'] = 'צירוף מורה עמית';
$string['sharecommunityteachers'] = 'שיתוף עם מורי הקהילה';
$string['sharecoursecommunity'] = 'שיתוף לקהילה';
$string['copycoursetocategory'] = 'לסביבה שלי';
$string['couse_copied_from_catalog'] =
        'אנא שים לב, קורס זה שותף למאגר. האם ברצונך לשתף את הקורס החדש שיוצר, במקום הקורס הישן הנוכחי?';

// Disable popup.
$string['buttonsharedcourse'] = 'שותף למאגר';
$string['disablepopuptitle'] = 'ביטול שיתוף קורס ממאגר המשותף';
$string['disablepopupbody'] = 'האם ברצונך לבטל את השיתוף?';
$string['disablepopupsubmit'] = 'אישור';

// Upload catalog.
$string['share_course_catalog_title'] = 'שיתוף קורס למאגר המשותף';
$string['share'] = 'שיתוף';
$string['sharing_content_materials_repository'] = 'אנא שים לב! לאחר האישור כל מי שמתאים לקהל היעד יוכל להעתיק את הקורס במלואו!';
$string['advanced_catalog_options'] = 'אפשרויות קטלוג מתקדמות';
$string['coursenameinput'] = "שם קורס";
$string['coursedescriptioninput'] = "תיאור קורס";
$string['coursedescriptionlabel'] =
        "אנא ספרו למורים אחרים במספר מילים על רצף ההוראה, תובנות מהפעלה עם תלמידים והמלצות שלכם להפעלה מוצלחת. המידע יופיע בתיאור הרצף במאגר המשותף";
$string['course_upload_to_mr'] = 'הקורס נשלחה למאגר המשותף, ותהיה זמינה לכלל המורים בהקדם
תודה על השיתוף!';
$string['eventcourseupload'] = 'שיתוף למאגר המשותף';
$string['theme_of_the_course'] = 'נושא הקורס <span class="font-weight-normal">(אילו נושאי לימוד מופיעים בקורס?)</span>';

// Share social.
$string['titlesharecoursepombim'] = "שיתוף קורס לסביבת העמיתים";
$string['approval'] = "אישור";
$string['sharecoursesdesc'] = "נא לאשר את שיתוף הקורס לסביבת העמיתים.";

// Share to teacher.
$string['send'] = "שליחה";
$string['back'] = 'חזרה';
$string['end'] = 'סיום';
$string['copycoursesuccess'] = 'שימו לב! פעולות מסוימות דורשות מספר רגעים להשלמה.';
$string['eventdublicatetoteacher'] = "העתקת קורס";
$string['subject_message_for_teacher'] = 'המורה {$a->teachername} שיתף/פה איתך קורס ';

// Copy course.
$string['selectioncategories'] = 'בחר קטגוריה';
$string['eventcoursecopy'] = 'העתקת קורס';
$string['course_copied_to_category'] = 'הקורס משוכפל כעת, ויהיה זמין עוד רגע בסביבה שלך';
$string['finish'] = 'סיום';
$string['sure_duplicate_course_without_students'] = 'האם ברצונך להעתיק את הקורס בשלמותו לסביבתך האישית?';
$string['close'] = 'סגירה';
$string['copy'] = 'העתקה';
$string['wordcopy'] = 'העתקה';

// Messages.
$string['notificationmessage'] = '{$a->user} שיתף קורס למאגר המשותף. קישור לצפיה <a href="{$a->url}">"{$a->coursename}"</a>';
$string['subjectmail'] = '{$a->user} שיתף קורס למאגר המשותף.';
$string['community_sharecourse_copy_course_to_teacher'] = 'העתק קורס למורה';

// Enrol.
$string['enrolname'] = 'קבוצה־מערכתית מאגר קורסים';
