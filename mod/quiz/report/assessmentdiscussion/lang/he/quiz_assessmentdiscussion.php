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
 * @package     quiz_assessmentdiscussion
 * @category    string
 * @copyright   2022 Devlion <info@devlion.co>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['pluginname'] = 'בדיקה רוחבית של שאלות ודיון כיתתי';
$string['assessmentdiscussion'] = 'בדיקה רוחבית של שאלות ודיון כיתתי';

$string['questionswaitingtobegraded'] = 'שאלות ממתינות לציון';
$string['allquestions'] = 'כל השאלות';
$string['forclassdiscussion'] = 'לדיון הכיתתי';
$string['anonymousmodeisoff'] = 'מצב אנונימי כבוי';
$string['anonymousmodeison'] = 'מצב אנונימי מופעל';
$string['saveandupdatetheclass'] = 'שמירה ועדכון הכיתה';
$string['buttonenablegrades'] = 'חשיפת ציונים';
$string['buttondisablegrades'] = 'הסתרת ציונים';
$string['sortby'] = 'מיון לפי:';
$string['mosterrors'] = 'מירב השגיאות';
$string['vieworderinquiz'] = 'סדר תצוגה במשימה';
$string['waitingforthegrade'] = 'בהמתנה לציון';
$string['fullcontentdisplay'] = 'תצוגת תוכן מלאה';
$string['smallcontentdisplay'] = 'תצוגת תוכן מצומצמת';
$string['awaitinggrade'] = 'בהמתנה לציון';
$string['allanswers'] = 'כל התשובות';
$string['answersforclassdiscussion'] = 'תשובות לדיון כיתתי';
$string['distributionofstudentsanswers'] = 'התפלגות תשובות התלמידים על פי מסיחים';
$string['emptyquestions'] = 'אין שאלות להצגה';
$string['emptyanswersarea'] = 'אין מידע על שאלה להצגה';
$string['attemptswaitingtobegraded'] = 'בהמתנה לציון';
$string['allattempts'] = 'כל התשובות';
$string['attemptsdiscussion'] = 'תשובות לדיון כיתתי';
$string['lastattempt'] = 'ניסיון אחרון';
$string['firstattempt'] = 'ניסיון ראשון';
$string['points'] = 'נקודות';
$string['anonuser'] = 'תלמיד/ה';
$string['attempt'] = 'ניסיון';
$string['noanswers'] = 'אין תשובות';
$string['selectgroup'] = 'קבוצות נפרדות';
$string['allusers'] = 'כל המשתתפים';
$string['moreattempts'] = 'ניסיונות נוספים';
$string['updateacommentorgrade'] = 'עדכון הערה או ציון';
$string['return'] = 'חזרה';
$string['classdiscussion'] = 'דיון כיתתי';
$string['answer'] = 'תשובה';
$string['hideanswers'] = 'הסתרת תשובות';
$string['showanswers'] = 'הצגת תשובות';
$string['next'] = 'הַבָּא';
$string['previous'] = 'קודם';
$string['tooltipcarousel'] = 'תצוגת קרוסלה';
$string['tooltiplist'] = 'תצוגת רשימה';
$string['changedisplaymodemodaltitle'] = 'שינוי מצב תצוגה';
$string['changedisplaymodemodaltext'] = 'במצב התצוגה הרגיל יוצגו שמות התלמידים.
האם בוודאות ברצונך לצאת ממצב נראות אנונימי?';
$string['ok'] = 'אישור';
$string['score'] = 'ניקוד:';
$string['outof'] = 'מתוך';
$string['saveandupdatethestudent'] = 'שמירה ועדכון התלמיד/ה';
$string['notpossibletoenterascore'] = 'לא ניתן להזין ניקוד שחורג מהטווח שהוגדר בשאלה';
$string['renderevent'] = 'Render event';
$string['changediscussionevent'] = 'Change discussion event';
$string['renderoverlayevent'] = 'Render overlay event';
$string['savegradesevent'] = 'save grades event';
$string['staticticgrades'] = '{$a->grade} נקודות מתוך {$a->maxgrade}';
$string['markthequestionforclassdiscussion'] = 'סימון השאלה עבור
דיון כיתתי';
$string['beyondtheclassdiscussiondisplay'] = 'מעבר לתצוגת דיון כיתתי';
$string['evaluationinthepreviousinterface'] = 'הערכה בממשק הקודם';
$string['assessmentdiscussionreport'] = 'בדיקה רוחבית של שאלות ודיון כיתתי';
$string['filter_qtypes'] = 'נא לבחור שאלות למסנן';
$string['filter_qtypes_desc'] = '';
$string['torevealtheanswerspleaseuse'] = 'לחשיפת התשובות 
 נא להשתמש בכפתור “הצגת תשובות”';
$string['accesscohort'] = 'גישה באמצעות קבוצה מערכתית';
$string['accesscapability'] = 'גישה באמצעות הרשאה';
$string['assessmentdiscussion:viewassessmentdiscussion'] = 'View assessmentdiscussion';

// Cache.
$string['cachedef_assessmentdiscussion_all_attempts'] = 'דיון הערכה: מטמון של כל הניסיונות';
$string['cachedef_assessmentdiscussion_user_attempts_grade'] = 'דיון בהערכה: מטמון של ניסיונות משתמשים וציון';
$string['cachedef_assessmentdiscussion_questions'] = 'דיון הערכה: מטמון של שאלות';
$string['cacheenable'] = 'הפעלת מטמון';
