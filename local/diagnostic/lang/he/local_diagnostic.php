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
 * @package local_diagnostic
 * @copyright 2021 Devlion.co
 * @author Evgeniy Voevodin
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['pluginname'] = 'אבחון התלמידים על פי תוצאותיהם';
$string['analytics'] = 'אנליטיקות למידה';
$string['teachers'] = 'קבוצות מערכתיות';
$string['activitiesselected'] = 'נבחרו {$a} פעילויות';
$string['noselection'] = 'בחירת פעילויות';
$string['submittext'] = 'הקצאה';
$string['daterange'] = 'טווח תאריכים';
$string['events'] = 'בחירת פעילויות';
$string['required'] = 'כדי להתקדם חובה לבחור פעילוית';
//$string['submit'] = 'הצגת אבחון';
$string['popuptip'] = 'אנא הגדירו את הפרמטרים ליצירת קבוצות התלמידים (בתפריטים הנפתחים אשר בראש העמוד)
לאחר סיום ההגדרות, יווצרו על מסך זה הקבוצות';
$string['commontitle'] = 'מידע והקצאת פעילויות לכלל הכיתה';
$string['rectangletitle'] = 'תלמידים שאין מספיק נתונים במערכת כדי לאבחן אותם';
$string['cluster'] = 'כיתה';
$string['clustername'] = 'אשכול {$a}';
$string['avggrade'] = 'ציון ממוצע {$a}%';
$string['avggradetotal'] = 'ציון ממוצע כיתתי: {$a->total} % (ארצי {$a->totalall} %)';
$string['questionstitle'] = 'כותרת';
$string['slidertitle'] = 'סטטיסטיקה';
$string['tabletitle'] = 'תיאור קבוצות לפי שאלות';
$string['red'] = 'רמה נמוכה';
$string['yellow'] = 'רמה בינונית';
$string['green'] = 'רמה גבוהה';
$string['question'] = 'שאלה {$a}';
$string['usertooltiptitle'] = 'שמות התלמידים';
$string['userlisttitle'] = 'שמות התלמידים';
$string['gapestimate'] = 'Gap estimated number of clusters';

$string['excelcluster'] = 'אשכול {$a}';
$string['clusterinfo1'] = '</div>{$a}% ציון ממוצע<div>
<div>&nbsp;מתמודדים בהצלחה עם יישום עקרונות פיזיקליים במגוון רחב של תרחישים, יותר סטנדרטיים
 (2-11), או פחות סטנדרטיים (14-12). לעתים מחמיצים תרחיש נוסף
  בשאלות בהן יש מספר תרחישים אפשריים (1, 17) 
  או מחמיצים תשובה נוספת</strong> בשאלות לא סטנדרטיות בהן יש שתי תשובות נכונות (15, 16).</div>';
$string['clusterinfo2'] = '</div>{$a} ציון ממוצע<div>
<p>מתמודדים בהצלחה עם יישום עקרונות פיזיקליים במגוון רחב של תרחישים סטנדרטיים
 (2-4, 9-11), לעתים מתבטאת תפישה חלופית של &quot;כוח הנורמל שווה תמיד למשקל&quot; (5-8).
  מחמיצים תרחיש נוסף בשאלות בהן יש מספר תרחישים אפשריים (1, 8), לא מצליחים להתמודד בתרחיש לא סטנדרטי
   של החלקה אפשרית בכיוונים שונים (12-13, 15-17), 
  אלא אם ניתן להסתייע באינטואיציה (14).</p>';
$string['clusterinfo3'] = '</div>{$a} ציון ממוצע<div>
<p>מתמודדים בהצלחה עם יישום עקרונות פיזיקליים במגוון צר של תרחישים סטנדרטיים
 (2-4). לעתים מתבטאת תפישה חלופית של &quot;כוח הנורמל שווה תמיד למשקל&quot;>
  (5-8). <>לא מצליחים להתמודד עם תרחיש של מערכת גופים
   (10-11), או עם תרחיש לא סטנדרטי של החלקה אפשרית בכיוונים שונים (12-17). 
   מחמיצים תרחיש נוסף בשאלות בהן יש מספר תרחישים אפשריים (1, 8)</p>';
$string['clusterinfo4'] = '</div>{$a} ציון ממוצע<div>
<p>מתמודדים באופן חלקי בעיקר עם שאלות בהן ניתן להסתייע באינטואיציה
 (9,11) או בידע קודם שאינו נוגע לחיכוך> (10), 
 אינם מצליחים ליישם עקרונות פיזיקליים לניתוח מרבית התרחישים. 
מפגינים כבר בשאלות סטנדרטיות תפישות חלופיות נפוצות
  דוגמת &quot;כוח הנורמל שווה תמיד למשקל&quot; &quot;
  כוח חיכוך סטטי שווה תמיד לערכו המקסימלי&quot; &quot;כוח הנורמל שווה תמיד למשקל&quot; (5-8, 1-3). 
  מחמיצים תרחיש נוסף בשאלות בהן יש מספר תרחישים אפשריים (1, 8).</p>';
$string['clusterinfo5'] = '</div>{$a} ציון ממוצע<div>
<p>אינם מצליחים ליישם עקרונות פיזיקליים לניתוח המצבים השונים.</p>';

$string['cluster_info'] = 'תיאור הקבוצות';

$string['share'] = 'לשתף';
$string['shareactivitycourse'] = 'הקצאת פעילות מהקורסים שלי';
$string['shareactivityrepo'] = 'הקצאת פעילות ממאגר';

$string['enabletags'] = 'להפעיל תגיות';
$string['allowedtags'] = 'בחירת תגים';
$string['allowedtags_desc'] = 'הצגת משימות הכוללות רק תגים שנבחרו';

$string['clusteractivities'] = 'לאשכולות זו הוקצאו {$a} פעילויות';

$string['globalview'] = 'קבוצות ארציות';
$string['noclusterdata'] = 'לא נמצאו נתונים מתאימים להצגה, אנא בחרו פעילויות חדשו';

$string['export_excel'] = 'יצוא ל - Excel';
$string['firstname_excelhdr'] = 'שם משפחה';
$string['lastname_excelhdr'] = 'שם פרטי';
$string['group_excelhdr'] = 'אשכולות';
$string['grade_excelhdr'] = 'ציון במשימה';
$string['gradecluster_excelhdr'] = 'ציון חישוב לפי ניסיון מענה ראשון';
$string['id_excelhdr'] = 'מספר זיהוי';
$string['email_excelhdr'] = 'דוא"ל';
$string['status_excelhdr'] = 'מצב';
$string['started_excelhdr'] = 'התחיל ב:';
$string['finished_excelhdr'] = 'הושלם';
$string['duration_excelhdr'] = 'משך הזמן שלקח לענות';
$string['activities'] = 'פעילויות';
$string['average_percent_of_success'] = 'אחוזי הצלחה ממוצעים';

$string['numberofactivities'] = 'מספר פעילויות להתאים';
$string['numberofactivitiesdesc'] = 'בחרו את מספר הפעילויות כדי להתאים את הגדרותיהן';
$string['CMID_title'] = 'הכנס CMID לפעילות מספר {$a}';
$string['CMID_desc'] = ' הכנס CMID לפעילות מספר {$a} כדי להתאים את הפעילות המבוקשת';
$string['cluster_text_area'] = 'אשכול {$a}';
$string['cluster_text_area_desc'] = 'הכנס תיאור לאשכולות {$a} בפעילות';
$string['activity_yellow'] = 'פעילות {$a} - רמה בינונית';
$string['activity_green'] = 'פעילות {$a} - רמה גבוהה';
$string['activity_excluded_cmids'] = 'פעילות {$a} - CMID שאינם נכללים';
$string['activity_excluded_cmids_desc'] = 'פעילות {$a} - הכנס רשימת CMID שאינם נכללים';
$string['activity_cutoff'] = 'פעילות {$a} - חתך ציון להצלחה';
$string['activity_cutoff_desc'] = 'פעילות {$a} - הכנס חתך ציון להצלחה בין 0 ל- 1';
$string['activity_startdate'] = 'פעילות {$a} - תאריך התחלה';
$string['activity_startdate_desc'] = 'פעילות {$a} - הכנס תאריך התחלת ניתוח נתונים, בתסדיר DD-MM-YYYY';
$string['activity_enddate'] = 'פעילות {$a} - הכנס תאריך סיום';
$string['activity_enddate_desc'] = 'פעילות {$a} - הכנס תאריך סיום ניתוח נתונים, בתסדיר DD-MM-YYYY';
$string['circle_empty'] = 'ריק';

$string['submit'] = 'הקצאה';
$string['cancel'] = 'ביטול';
$string['selected_from_my_courses'] = 'נבחרו {$a} פעילויות מהקורסים שלי';
$string['selected_from_repository'] = 'נבחרו {$a} פעילויות מהמאגר';
$string['share_activities_course_title'] = 'הקצאת פעילות המשך מהקורס שלי';
$string['share_activities_course_title_all'] = 'הקצאת פעילות המשך מהקורס שלי';
$string['all_clusters'] = 'כל האשכולות';
$string['selected_from_my_courses'] = 'נבחרו {$a} פעיליות מהקורס';
$string['selected_from_repository'] = 'נבחרו {$a} פעיליות ממאגר';
$string['selected_previously'] = 'נבחרו {$a} פעיליות';
$string['description_title'] = 'מידע על פעילות המשך';
$string['description_text'] = 'כדי לסייע למורים אחרים, נשמח אם תוכלו להסביר מדוע הפעילות מתאימה לאשכול זה';
$string['recommend_text'] = 'האם תאפשרו למורה אחר להשתמש בפעילות הקנייה אשר בחרת מתוך הקורס הפרטי שלך?';
$string['recommend_repo_text'] = 'האם תאפשרו למורה אחר להשתמש בפעילות הקנייה אשר בחרת מתוך הקורס הפרטי שלך?';
$string['saved_successfully'] = 'נשמר בהצלחה';
$string['server_error'] = 'שגיאת שרת';

$string['shareactivityrecom'] = 'הקצאת פעילות לפי המלצות מורים';

$string['activityname'] = 'שם הפעילות';
$string['source'] = 'מקור הפעילות';

$string['descriptionheader'] = 'מידע על התלמידים ב';

$string['activitiespopuptitle'] = 'הקצאת פעילות המשך מהקורסים שלי';
$string['activitiestoptitle'] = 'הקצאת פעילות המשך מהקורס שלי עבור';

$string['recomandpopuptitle'] = 'הקצאת פעילות לפי המלצות מורים';
$string['recomandtoptitle'] = 'המלצות מורים עבור';

$string['repositorypopuptitle'] = 'הקצאת פעילות ממאגר';
$string['repositorytoptitle'] = 'הקצאת פעילות ממאגר עבור';

$string['mlnpmenutext'] = '{$a->quizname}(MLNP {$a->questionname})';
$string['rebuildlimit'] = 'Rebuild limit';
$string['rebuildlimitdesc'] = 'Number of attempts starting from which the script will not rebuild centroids';

$string['allquestions'] = 'סטטיסטיקה כללית';
$string['requiredquestions'] = 'שאלות חשובות ברמה כללית';
$string['importantquestions'] = 'Enable Important questions functionality';
$string['importantnum'] = 'מס. שאלות חשובות';
$string['importantnumdesc'] = 'How many ofss top heatmapped questions will be rendered as important in grouper table';
$string['oercatalogtooltip'] = 'מותאם לגרופר - כלי לאבחון מתקדם';

$string['midurl'] = 'Custom url for MID {$a}';
$string['midurldesc'] = 'This URL will be used in question table for this MID. Url has higher priority over file setting below.';
$string['midfile'] = 'Custom file for MID {$a}';
$string['midfiledesc'] = 'Link to this file will be used in question table for this MID. File has lower priority over url setting above.';
$string['custommidlink'] = 'קובץ ניתוח שאלות';

$string['recommendedbyusers'] = 'המלצות משתמשים מועדפים בהקצאת פעילות המשך';
$string['recommendedbyusersdesc'] = 'Show Recommended users first in the top of the list, example 122,455,...';
$string['usertableevent'] = 'User open analytics table';
$string['rebuildqueued'] = 'התהליך ירוץ בצורה אסינכרונית, אפשר לראות את התוצאה בעוד 1-5 דקות.';