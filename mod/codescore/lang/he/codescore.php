<?php
// This file is part of Moodle - https://moodle.org/
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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Plugin strings are defined here.
 *
 * @package     mod_codescore
 * @category    string
 * @copyright   2024 Devlion <info@devlion.co>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['missingidandcmid'] = 'Missing id and cmid';
$string['modulename'] = 'Codescore';
$string['modulename_help'] = 'Codescore מאפשר למורה לקבוע ציון עבור מטלות קוד באמצעות AI';
$string['timing'] = 'תזמון';
$string['codescoreopenclose'] = 'תאריכי התחלה וסיום';
$string['codescoreopenclose_help'] = 'תלמידים יכולים להתחיל את ניסיונותיהם רק לאחר שעת תחילת ההגשה ועליהם להשלים את הניסיונות שלהם לפני שעת הסיום.';
$string['codescoreopen'] = 'פתח את הפעילות';
$string['codescoreclose'] = 'סגור את הפעילות';
$string['codescoreclose_help'] = 'תלמידים יכולים להתחיל את ניסיונותיהם רק לאחר שעת תחילת ההגשה ועליהם להשלים את הניסיונות שלהם לפני שעת הסיום.';
$string['feedbacklang'] = 'שפת המשוב';

$string['modulenameplural'] = 'Codescore\'s';
$string['newmodulesettings'] = 'הגדרות';
$string['nonewmodules'] = 'אין מודולים חדשים';
$string['pluginadministration'] = 'Codescore ניהול תוסף';
$string['pluginname'] = 'Code Score';
$string['task'] = 'כתוב מטלת קוד';
$string['codescorefieldset'] = 'Fieldset';
$string['codescoresettings'] = 'הגדרות';
$string['codescorename'] = 'כותרת';
$string['codescorename_help'] = 'עזרה';
$string['privacy:metadata'] = 'Code Score אינו שומר נתונים אישיים כלשהם';
$string['view'] = 'תצוגה';
$string['grading'] = 'אפשרויות ניקוד';
$string['syntaxgrade'] = 'ניקוד על סינטקס';
$string['syntaxgrade_help'] = "ציון מקסימלי עובר סינטקס נכון";
$string['correctoutput'] = 'ניקוד על פלט נכון';
$string['correctoutput_help'] = "ניקוד מקסימלי עבור פלט נכון";
$string['correctsolution'] = 'ניקוד עבור תוצאה נכונה';
$string['correctsolution_help'] = "ציון מקסימלי עבור אופטימיזציית/תבניות הקוד של התלמיד";
$string['allcases'] = 'ניקוד עבור התייחסות כל המקרים האפשריים';
$string['allcases_help'] = "ציון מקסימלי עבור קוד המכסה את כל המקרים האפשריים (לדוגמה, הפונקציה פועלת כראוי עם כל סט של פרמטרים)";
$string['programminglang'] = 'בחר שפת תכנות';
$string['programminglangview'] = 'שפת תכנות: ';
$string['autograde'] = 'הגדר ציון אוטומטי על ידי AI';
$string['alowedlanguages'] = 'בחר שפות תכנות שיהיו בשימוש';
$string['multiattempts'] = 'ריבוי ניסיונות הגשה';
$string['python'] = 'Python';
$string['filltask'] = 'הזן תיאור משימה';
$string['selectlang'] = 'בחר שפת תכנות';
$string['yes'] = 'כן';
$string['no'] = 'לא';
$string['showfeedback'] = 'הצג משוב בינה מלאכותית לתלמידים';
$string['aifeedback'] = 'AI משוב:';
$string['noinstances'] = 'אין מופעי CodeScore';

// Admin settings
$string['apikey'] = 'מפתח API';
$string['apikeyexplain'] = 'המפתח לגישה ל-API של הבינה המלאכותית. הגרסה החינמית מאפשרת 10 הרצות בשבוע. לקבלת גרסת ה-Pro, צרו קשר עם <a href="mailto:info@devlion.co">info@devlion.co</a>';
$string['apiurl'] = 'כתובת URL של API';
$string['apiurlexplain'] = 'כתובת ה-URL לגישה ל-API של הבינה המלאכותית';

// View
$string['viewreportsbtn'] = 'צפה בהגשות';
$string['taskview'] = 'המטלה הינה:';
$string['submissionstatus'] = 'סטטוס הגשה: ';
$string['submitted'] = 'הוגש';
$string['notsubmitted'] = 'לא הוגש';
$string['yoursubmissions'] = 'הניסיון שלך: ';
$string['waitingforgrade'] = 'מחכה לציון';
$string['graded'] = 'ניתן ציון';
$string['notgraded'] = 'טרם ניתן ציון';
$string['viewdategraded'] = 'תאריך ציון: ';
$string['gradestatus'] = 'סטטוס:';
$string['unavaibleactivity'] = 'פעילות זו אינה זמינה לעת עתה';
$string['startattempt'] = 'התחל ניסיון';

// Reports
$string['studentsreports'] = "הגשות תלמיד: ";
$string['noreports'] = "עדין לא בוצעו הגשות";
$string['deleteBtnText'] = 'מחק הגשה';
$string['areyousure'] = 'האם אתה בטוח שאתה רוצה למחוק את ההגשה?';
$string['irreversible'] = 'פעולה זו תהיה בלתי הפיכה';
$string['regrade'] = 'דרג ניסיונות מחדש';

// Attempt
$string['taskheader'] = 'המטלה הינה: ';
$string['saveandexit'] = "שמור וצא";
$string['notesplaceholder'] = 'אתה יכול לכתוב הערות למורים כאן';

// Attempts table
$string['tablename'] = "שם";
$string['startedat'] = "התחיל בתאריך";
$string['timefinish'] = "הוגש בתאריך";
$string['timegraded'] = "ניתן ציון בתאריך";
$string['syntaxgradetable'] = "ניקוד עבור סינטקס";
$string['outputgradetable'] = "ניקוד עבור פלט";
$string['problemgradetable'] = "ניקוד עבור פתרון";
$string['casesgradetable'] = "ניקוד עבור כיסוי כל המקרים האפשריים";
$string['tableaigrade'] = "ניקוד AI";
$string['tableteachergrade'] = "ניקוד סופי";
$string['statusattempt'] = "סטטוס";
$string['finishedstatus'] = "גָמוּר";
$string['pendingstatus'] = "ממתין ל";
$string['submittedstatus'] = "הוגש";

// Report
$string['taskoverview'] = 'צפיה במטלה';
$string['reportlang'] = 'שפת תכנות: ';
$string['taskreport'] = 'מטלה: ';
$string['maxgradesyntax'] = 'ניקוד מקסימלי עבור סינטקס: ';
$string['maxrightoutput'] = 'ניקוד מקסימלי עבור פלט: ';
$string['maxproblemsolution'] = 'ניקוד מקסימלי עבור פתרון נכון: ';
$string['maxcovering'] = 'ניקוד מקסימלי עבור כיסוי כל המקרים האפשריים: ';
$string['reportname'] = 'דוח';
$string['reportgrade'] = 'ציון: ';
$string['aigradereport'] = 'ניקוד AI:';
$string['syntaxreport'] = 'ניקוד סינטקס: ';
$string['outputreport'] = 'ניקוד פלט: ';
$string['problemreport'] = 'ניקוד פתרון: ';
$string['casesreport'] = 'ניקוד כיסוי כל המקרים: ';
$string['notesreport'] = 'הערות: ';
$string['codediffreport'] = 'פער בין הקודים';
$string['studentcodereport'] = "קוד תלמיד";
$string['correctcodereport'] = 'קוד מתוקן';
$string['reportgradebtn'] = 'ציון';

// Privacy
$string['privacy:metadata:codescore_attempts'] = 'הטבלה הזו מכילה מידע על נסיונות המשתמש';
$string['privacy:metadata:codescore_attempts:userid'] = 'מזהה המשתמש ששמר דוח זה.';
$string['privacy:metadata:codescore_attempts:attempt'] = 'מספרי ניסיונותיו של סטודנט זה לפי מספרים סידוריים.';
$string['privacy:metadata:codescore_attempts:timestart'] = 'זמן שבו המשתמש התחיל בהגשה.';
$string['privacy:metadata:codescore_attempts:timefinish'] = 'זמן בו הסתיימה ההגשה של המשתמש.';
$string['privacy:metadata:codescore_attempts:timemodified'] = 'זמן שבו המשתמש שינה את ההגשה.';
$string['privacy:metadata:codescore_attempts:timemodifiedoffline'] = 'הזמן שבו המשתמש שינה את ההגשה במצב לא מקוון';
$string['privacy:metadata:codescore_attempts:timegraded'] = 'זמן בו ניתן ציון על ההגשה של המשתמש.';
$string['privacy:metadata:codescore_attempts:code'] = 'קוד שהוגש בהגשה זו.';
$string['privacy:metadata:codescore_attempts:studentnotes'] = 'הערות להגשה זו.';
$string['privacy:metadata:codescore_attempts:grade'] = 'ציון להגשה זו.';

$string['privacy:metadata:codescore'] = 'טבלה זו מכילה מידע שנשלח ל-API כדי לקבל משוב על קוד התלמיד';
$string['privacy:metadata:codescore:task'] = 'משימת התכנות של המטלה';
$string['privacy:metadata:codescore:programminglang'] = 'שפת תכנות שצריך להשתמש בה במטלה';
$string['privacy:metadata:codescore:syntaxgrading'] = 'ציון מרבי עבור תקינות תחביר הקוד התלמיד';
$string['privacy:metadata:codescore:outputgrading'] = 'ציון מרבי עבור פלט הקוד/פלט הפונקציה של התלמיד';
$string['privacy:metadata:codescore:problemsolutiongrading'] = 'ציון מרבי עבור אופטימיזציה/תבניות בקוד התלמיד';
$string['privacy:metadata:codescore:allcasesgrading'] = 'ציון מרבי עבור כיסוי כל המקרים בקוד של התלמיד (למשל, הפונקציה שעובדת כראוי עם כל סט של פרמטרים)';