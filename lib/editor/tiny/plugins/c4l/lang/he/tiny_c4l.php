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
 * Plugin C4L strings for language en.
 *
 * @package     tiny_c4l
 * @category    string
 * @copyright   2022 Marc Català <reskit@gmail.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['additionalhtml'] = 'דף ניהול HTML נוסף';
$string['aimedatstudents'] = 'מיועד לתלמידים';
$string['aimedatstudents_desc'] = 'כברירת מחדל, רק רכיבים נבחרים יהיו זמינים למשתמשים עם יכולות סטודנט בעת שימוש בעורך. כדי לשנות את הגדרת ברירת המחדל, פשוט סמנו או בטלו את הסימון של הבחירה המועדפת עליכם.';
$string['align-center'] = 'מרכז';
$string['align-left'] = 'יישור לשמאל';
$string['align-right'] = 'יישור לימין';
$string['allpurposecard'] = 'All-purpose card';
$string['attention'] = 'לתשומת ליבך';
$string['button_c4l'] = 'תבניות עיצוב';
$string['c4l:use'] = 'Use TinyMCE C4L';
$string['c4l:viewplugin'] = 'View C4L plugin';
$string['caption'] = 'כתובית';
$string['comfort-reading'] = 'קריאה נוחה';
$string['contextual'] = 'בהקשר';
$string['custom'] = 'מותאם אישית';
$string['customcompcode'] = 'קוד HTML של רכיב {$a}';
$string['customcompcodedesc'] = 'יש לכלול את המחלקה <code>{{CUSTOMCLASS}}</code> לצד מחלקות ה־CSS הראשיות של הרכיב שלך.<br />
דוגמה לקוד:
<pre>
&lt;div class="{{CUSTOMCLASS}} &lt;!-- הוסף כאן מחלקות CSS נוספות --&gt;"&gt;
    &lt;p&gt;{{PLACEHOLDER}}&lt;/p&gt;
&lt;/div&gt;
</pre>
לתשומת לבך: כל קוד JavaScript או CSS מוטמע יוסר לפני ההצגה.';
$string['customcompcount'] = 'מספר רכיבים מותאמים אישית';
$string['customcompcountdesc'] = 'כמה רכיבים מותאמים אישית ברצונך ליצור';
$string['customcompenable'] = 'הפעלת רכיב {$a}';
$string['customcompenabledesc'] = 'כאשר מסומן, רכיב זה יהיה זמין לשימוש.';
$string['customcompicon'] = 'אייקון לרכיב {$a}';
$string['customcompicondesc'] = 'אייקון אופציונלי. גודל מומלץ: 18x18 פיקסלים.';
$string['customcompname'] = 'טקסט לכפתור רכיב {$a}';
$string['customcompnamedesc'] = 'הטקסט שיוצג בתוך הכפתור.';
$string['customcompsortorder'] = 'סדר תצוגה לרכיב {$a}';
$string['customcompsortorderdesc'] = 'מגדיר את מיקום הרכיב בממשק המשתמש.';
$string['customcomptext'] = 'טקסט מציין מקום לרכיב {$a}';
$string['customcomptextdesc'] = 'הטקסט שיופיע בתוך הרכיב כמציין מקום. ודא שהמחרוזת <code>{{PLACEHOLDER}}</code> מופיעה בקוד.';
$string['customcomptitle'] = 'רכיב מותאם אישית {$a}';
$string['customcomponents'] = 'רכיבים מותאמים אישית';
$string['customcompvariant'] = 'אפשר וריאציות לרכיב {$a}';
$string['customcompvariantdesc'] = 'כאשר מסומן, תהיה זמינה וריאציה לרוחב מלא של רכיב זה.';
$string['customimagesbank'] = 'מאגר תמונות';
$string['customimagesbankdesc'] = 'כדי להוסיף אחת מהתמונות שהועלו, הוסף את השורה הבאה לקוד:<br />
<code>&lt;img src="{{filename.extension}}" alt="תמונה מותאמת"&gt;</code>';
$string['custompreviewcss'] = 'קוד CSS';
$string['custompreviewcssdesc'] = 'CSS המשמש לתצוגה מקדימה של רכיבים בתוך העורך.
<p>כל קוד CSS שתוסיף כאן חייב להיכלל גם בתבנית העיצוב שלך, או להיות עטוף בתגיות <code>&lt;style&gt;...&lt;/style&gt;</code> ולשמור אותו בהגדרה <strong>additionalhtmlhead</strong> תחת {$a};
אחרת, העיצובים שלך לא יחולו על הרכיבים בזמן הצגה.</p>';
$string['do-card'] = 'כרטיס "כן"';
$string['dodontcards'] = 'כרטיסי כן/לא';
$string['dont-card'] = 'כרטיס "לא"';
$string['dont-card-only'] = 'כרטיס "לא" בלבד';
$string['duedate'] = 'תאריך יעד';
$string['enablepreview'] = 'אפשר תצוגה מקדימה';
$string['enablepreview_desc'] = 'כאשר מסומן, תוצג תצוגה מקדימה בעת מעבר עם סמן העכבר על כל רכיב.';
$string['estimatedtime'] = 'זמן משוער';
$string['evaluative'] = 'הערכתי';
$string['example'] = 'דוגמה';
$string['expectedfeedback'] = 'משוב צפוי';
$string['figure'] = 'איור';
$string['full-width'] = 'רוחב מלא';
$string['generalsettings'] = 'הגדרות כלליות';
$string['gradingvalue'] = 'ערך ציון';
$string['helper'] = 'עוזר';
$string['helplinktext'] = 'עוזר C4L';
$string['inlinetag'] = 'תגית מוטמעת';
$string['keyconcept'] = 'מושג מפתח';
$string['learningoutcomes'] = 'תוצאות למידה';
$string['menuitem_c4l'] = 'רכיבי למידה (C4L)';
$string['min'] = 'דק\'';
$string['notintendedforstudents'] = 'לא מיועד לתלמידים';
$string['notintendedforstudents_desc'] = 'כברירת מחדל, רכיבים הערכתיים ותהליכיים אינם זמינים למשתמשים בעלי יכולות תלמיד בעורך. כדי לשנות זאת, סמנו את הרכיבים שברצונכם לאפשר.';
$string['ordered-list'] = 'רשימה ממוספרת';
$string['pluginname'] = 'רכיבים ללמידה (C4L)';
$string['preview'] = 'תצוגה מקדימה';
$string['previewdefault'] = 'הנח את הסמן על רכיב כדי לראות תצוגה מקדימה שלו.';
$string['privacy:preference:components_variants'] = 'וריאציות מועדפות של כל רכיב';
$string['procedural'] = 'תהליכי';
$string['proceduralcontext'] = 'הקשר תהליכי';
$string['quote'] = 'ציטוט';
$string['readingcontext'] = 'הקשר קריאה';
$string['reminder'] = 'תזכורת';
$string['tag'] = 'תגית';
$string['textplaceholder'] = 'כאן יופיע טקסט לדוגמה';
$string['tip'] = 'טיפ';