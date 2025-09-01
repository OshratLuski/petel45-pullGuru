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
 * Strings for component 'qtype_diagnosticadvai', language 'he', branch 'MOODLE_20_STABLE'
 *
 * @package    qtype_diagnosticadvai
 * @copyright  1999 onwards Martin Dougiamas  {@link http://moodle.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['informationtext'] = 'טקסט מידע';
$string['pluginname'] = 'שיח עם AI';
$string['pluginname_help'] = 'אבחון מתקדם AI אינו סוג של שאלה. הוא פשוט מאפשר להציג טקסט מבלי לדרוש תשובות, בדומה לתווית בעמוד הקורס.

טקסט השאלה מוצג הן במהלך הניסיון והן בעמוד הסקירה. כל משוב כללי מוצג רק בעמוד הסקירה.';
$string['pluginnameadding'] = 'הוספת תיאור';
$string['pluginnameediting'] = 'עריכת אבחון מתקדם AI';
$string['pluginnamesummary'] = 'זה אינו שאלה בפועל. במקום זאת, זוהי דרך להוסיף הנחיות, מדריך הערכה או תוכן אחר לפעילות. זה דומה לאופן שבו ניתן להשתמש בתוויות כדי להוסיף תוכן לעמוד הקורס.';
$string['privacy:metadata'] = 'תוסף סוג השאלה של אבחון מתקדם AI אינו שומר נתונים אישיים.';
$string['question_label'] = 'AI צ׳אט';
$string['ask_your_question'] = 'הקלדת הנחייה ל- AI...';
$string['send_message'] = 'שליחה';
$string['answer_label'] = 'תשובה';

$string['settingstitle'] = 'הגדרות אבחון AI';

$string['systemprompt'] = 'הנחיית מערכת';
$string['systemprompt_help'] = 'הנחיית מערכת ברירת מחדל לאינטראקציות עם AI.';
$string['systemprompt_default'] = 'זוהי הנחיית המערכת ברירת המחדל.';

$string['prompttemaplate'] = 'תבנית הנחיה';
$string['prompttemaplate_help'] = 'תבנית להנחיות שנוצרו על ידי AI.';
$string['prompttemaplate_dafault'] = 'אתה עוזר הוראה ועליך לספק תשובות לתלמידים עם תוצאות
תוצאת התלמיד {LOG}
היסטוריית השיחה איתך היא {CONVERSATION} 
הודעה של בסטודנט {STUDENTTEXT}';

$string['disclaimer'] = 'הצהרת אחריות';
$string['disclaimer_help'] = 'טקסט המוצג כהצהרת אחריות לסטודנטים.';
$string['disclaimer_default'] = 'ייתכן שתשובות שנוצרו על ידי AI לא יהיו מדויקות תמיד.';

$string['temperature'] = 'טמפרטורה';
$string['temperature_help'] = 'שולט באקראיות של תגובות ה-AI. ערך נמוך יותר הופך את התגובות לדטרמיניסטיות יותר, בעוד שערך גבוה יותר הופך אותן לאקראיות יותר.';
$string['teacherprompt'] = 'הנחיית מורה';
$string['runbutton'] = 'התחל שיח עם AI';
