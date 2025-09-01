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
 * @package     theme_petel
 * @category    string
 * @copyright   2023 Devlion <info@devlion.co>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

$string['pluginname'] = 'PeTel';
$string['choosereadme'] = '';

$string['siteadminquicklink'] = 'ניהול המערכת';

// Edit Button Text.
$string['editon'] = 'הפעלת עריכה';
$string['editoff'] = 'סיום עריכה';

$string['back_to_course'] = 'לעמוד הקורס';

// General Settings.
$string['generalsettings'] = 'General Settings';
$string['configtitle'] = 'PeTel';

// Instance Settings.
$string['instancesettings'] = 'Instance Settings';
$string['instancename'] = 'Instance name';
$string['instancenamedesc'] = 'Instance name';
$string['instancename_physics'] = 'פיזיקה';
$string['instancename_chemistry'] = 'כימיה';
$string['instancename_biology'] = 'ביולוגיה';
$string['instancename_math'] = 'מתמטיקה';
$string['instancename_sciences'] = 'מדעים';
$string['instancename_feinberg'] = 'מדרשת פיינברג';
$string['instancename_computerscience'] = 'מדעי המחשב';
$string['instancename_tutorials'] = 'הדרכות';
$string['instancename_demo'] = 'הדגמה';
$string['instancename_learnmed'] = 'LearnMed';
$string['blockexpanded'] = 'זכור את מצב התצוגה של הבלוקים';
$string['blockexpanded_desc'] = 'שומר עבור כל משתמש אם סרגל הבלוקים פתוח או סגור.';
$string['search_label'] = 'חיפוש :';
$string['searchcourse'] = "חיפוש בקורס";
$string['noresults'] = 'לא נמצאו תוצאות';
$string['foundxresults'] = ' תוצאות נמצאו ,נא להשתמש במקשי החצים למעלה ולמטה כדי לנווט ';

/* custom css */
$string['customcss'] = 'CSS מותאם אישית';
$string['customcssdesc'] = 'באפשרותך להגדיר קוד CSS מותאם אישית בתיבת הטקסט שלמעלה. השינויים יחולו על כל דפי האתר.';

/* custom accessibility policy */
$string['accessibility_policy'] = 'הצהרת נגישות';
$string['accessibility_policy_link_descr'] = 'קישור להצגת נגישות';

/* terms link */
$string['terms_of_use'] = 'תנאי שימוש';
$string['privacy_policy'] = 'מדיניות פרטיות';

// Login page.
$string['forgotten'] = 'שחזור סיסמה';
$string['showless'] = 'הצג פחות';
$string['showmore'] = 'הצג יותר';
$string['sectionactivities'] = 'פעילויות';

$string['checked'] = 'הושלם ({$a})';
$string['cancel'] = 'ביטול';
$string['logintitle'] = ' התחברות <br> לפטל {$a}!';
$string['logintitle_petel'] = 'התחברות <br> לפטל - {$a}!';
$string['logintitle_wiz'] = 'התחברות ל {$a}!';
$string['loginpolicy'] = 'מדיניות פרטיות';
$string['loginterms'] = 'תנאי שימוש';
$string['logo_department_of_science_teaching'] = 'לוגו מחלקה הוראת למדעים מותאם אישית';

// Support popup.
$string['messageprovider:support_request'] = 'בקשת תמיכה';
$string['messageprovider:shared_notification'] = 'פעילות הועתקה';
$string['messageprovider:sharewith_notification'] = 'פעילות שותפה';

$string['my_dashboard'] = 'הסביבה שלי';
$string['quickaccess'] = 'גישה מהירה';
$string['disablequickaccess'] = 'ביטול גישה מהירה';

$string['topblocks'] = 'אזור משבצות עליון';
$string['region-topblocks'] = 'אזור משבצות עליון';

$string['viewallcourses'] = 'צפיה בכל הקורסים';

$string['region-side-pre'] = 'צד שמאל';

$string['ask_details'] = 'בקשת פרטים';
$string['copy'] = "העתקה";
$string['copy_environment'] = 'העתקה לסביבה שלי';
$string['copy_section'] = 'העתקת יחידת הלימוד';
$string['how_to_copy_collegue'] = "Copy";

$string['backgroundimage_desc'] = 'תמונת בדף התחברות';
$string['backgroundimage']      = 'תמונת בדף התחברות';

// Privacy & terms.
$string['privacy'] = 'מדיניות פרטיות';
$string['privacyurl'] = 'מסמך מדיניות פרטיות';
$string['privacyurldesc'] = '';

$string['terms'] = 'תנאי שימוש';
$string['termsurl'] = 'מסמך תנאי שימוש';
$string['termsurldesc'] = '';

// Register form
$string['phonenotnumerical'] = 'מספר טלפון לא תקין';
$string['mustgiveemailorphone'] = '<span style="color:red;">חובה</span> למלא את אחד מהשדות: טלפון או דוא"ל, ללא מילוי של אחד משדות אלו לא ניתן יהיה לשלוח את הטופס בהצלחה';
$string['idnumbernotvalid'] = 'מספר תעודת זהות לא תקין';
$string['onlyenglishletters'] = 'ניתן להזין רק שמות באנגלית';
$string['onlyhebrewletters'] = 'ניתן להזין רק שמות בעברית הכוללים סימן "-"';
$string['onlyarabicletters'] = 'ניתן להזין רק שמות בערבית';
$string['successfulyregisterd'] = 'נרשמתם בהצלחה למערכת! אתם מועברים לעמוד הקורסים שלכם...';
$string['idnumber'] = 'מספר תעודת הזהות';
$string['idnumberexists'] = 'מספר זהות קיים במערכת';
$string['phone1exists'] = 'מספר טלפון קיים במערכת';
$string['missingidnumber'] = 'יש להזין מספר תעודות זהות';
$string['longerusername'] = 'בשם המשתמש נדרשים לפחות 7 תווים';
$string['noidnumberinusername'] = 'אין להשתמש במספר זהות בשם משתמש';
$string['usernamerestrictions'] = 'בשם המשתמש נדרשים לפחות 7 תווים של אותיות קטנות באנגלית ומספרים';

// Forgot password.
$string['searchbyphone'] = 'חיפוש לפי טלפון';
$string['usernameoremailorphone'] = 'יש להזין פרט מזהה אחד: שם משתמש או כתובת דוא"ל או טלפון';
$string['phonenotexists'] = 'הטלפון אינו קיים';
$string['wrongphone'] = 'טלפון לא נכון';
$string['textforsmscode'] = 'אימות הקוד שלך: ';
$string['smsvalidation'] = 'אימות  SMS';
$string['varificationcode'] = 'אימות קוד';
$string['sendcode'] = 'שליחה';
$string['emptycodesms'] = 'קוד ריק';
$string['wrongcodesms'] = 'קוד שגוי';
$string['passwordforgotteninstructions2']='אם מספר הטלפון מצוי במערכת, נשלח אליך קוד SMS
                                . את הקוד יש להזין אותו פה. אם לא התקבל קוד תוך דקה, אנא נסו שוב.';

// Course page
$string['to_submission'] = 'להגשה';
$string['cut_of_date'] = 'לא הוגש';
$string['cut_of_date_label'] = 'להגיש עד {$a->date}';
$string['cut_of_date_less_days_label'] = '<span>להגיש תוך {$a} <i class="fa fa-exclamation-circle red" style="color: red" aria-hidden="true"></i> </span>';
$string['and'] = ' ו-';
$string['no_submission_date'] = 'ללא תאריך הגשה';
$string['wait_for_submit'] = 'מחכה להגשה';
$string['complete'] = 'הושלם';
$string['of'] = 'מתוך';
$string['waitgrade'] = 'הוגש וטרם נבדק';
$string['complited'] = 'הושלם';
$string['waiting_to_grade'] = 'ממתין לבדיקה';
$string['share'] = 'שיתוף';
$string['quizinprogress'] = 'בתהליך';
$string['quizwithgrades'] = 'הוגש וניתן ציון';
$string['quizsubmittedwitgrades'] = 'הוגש וניתן ציון';
$string['quizsubmitted'] = 'הוגש';
$string['quizwithoutgrades'] = 'ממתין לבדיקה';
$string['quiznosubmit'] = 'טרם הוגש';
$string['quizwithoutstarted'] = 'טרם הגיש';
$string['assignsubmitted'] = 'ממתין לבדיקה';
$string['assignhavegrade'] = 'הוגש וניתן ציון';
$string['assignnotsubmitted'] = 'טרם הוגש';
$string['questionnairesubmitted'] = 'הגישו';
$string['questionnairenotsubmitted'] = 'טרם התחילו';
$string['hvphavegrade'] = 'הוגש וניתן ציון';
$string['hvpnotsubmitted'] = 'טרם הוגש';

// Enrolkey.
$string['studentsenrolkey'] = 'מפתח רישום: {$a}';
$string['enrolkey'] = 'מפתח רישום';
$string['enrolme_label'] = 'רישום לקורס נוסף';
$string['enrolme'] = 'רשום אותי';
$string['enrolselfconfirm'] = 'אנא אשרו את הרישום כתלמיד לקורס "{$a}"?';
$string['getcoursekeytitle'] = 'מפתח רישום לקורס';
$string['getkey'] = 'תודה';
$string['close'] = 'סגירה';
$string['scantoenrol'] = 'סריקה לרישום לקורס';
$string['msgenrolkey1'] = 'מפתח רישום לקורס מיועד להרשמה עצמית של תלמידיך לקורס זה בסביבת פטל.
<br><br>
מפתח ההרשמה 
<span class="bold">הייחודי</span>
לקורס זה הוא:
<span class="bold"> {$a} </span>
<br><br>
<span class="bold">תלמידים אשר רשומים כבר </span>
למערכת פטל, יוכלו להירשם לקורס הזה על ידי הקישור:
<a target="_blank" href="../enrol/self/enrolwithkey.php?enrolkey={$a}">רישום לקורס</a>
או על ידי QR קוד:';
$string['msgenrolkey2'] = '
<span class="bold">תלמידים אשר עדיין לא רשומים</span>
למערכת פטל, יוכלו להירשם למערכת ולקורס הזה על ידי הקישור:
<a href="../login/signup.php?key={$a}">רישום למערכת ולקורס</a>
<br><br>
 הסבר מפורט על אופן הרשמה עצמית של התלמידים ניתן לקרוא ב
<a target="_blank" href="https://stwww1.weizmann.ac.il/petel/instructions/add-new-petel-user/">תדריך רישום תלמידים חדשים</a>
 וגם ב
<a target="_blank" href="https://stwww1.weizmann.ac.il/petel/instructions/studentsselfenroll/">תדריך רישום תלמידים קיימים</a>
';
$string['enrolkey_error'] = 'לא נמצא קורס התואם למפתח אשר הוזן, אנא נסו להזין מפתח תקין';

//footer
$string['about']='אודות מיזם פטל';
$string['abouturl'] = 'אודות מיזם פטל';
$string['abouturldesc'] = 'אודות מיזם פטל';
$string['initialize_tours']='אתחול סיורים בעמוד זה';
$string['all_rights_reserved'] = 'כל הזכויות שמורות למכון ויצמן למדע, המחלקה להוראת המדעים';
$string['weizmann_logo'] = 'המחלקה הוראת המדעים';
$string['tested'] = 'נבדק';

// user menu pop Up
$string['user_completereport'] = 'דוח קורס מלא';
$string['user_outlinereport'] = 'דוח צפיה בקורס';
$string['user_viewprofile'] = 'צפיה בפרופיל';
$string['user_editprofile'] = 'עריכת פרופיל';
$string['user_sendmessage'] = 'שליחת הודעה';
$string['user_coursecompletion'] = 'השלמת קורס';
$string['user_courselogs'] = 'ניטור פעילות';
$string['user_coursegrades'] = 'ציונים בקורס';
$string['user_loginas'] = 'התחברות כתלמיד זה';
$string['sendwhatsapp']='שליחת וואטסאפ';
$string['resetpassword'] = 'אתחול סיסמה';

//navbar
$string['shownotificationwindownonew'] = 'הצגת/הסתרת תפריט הודעות';
$string['logo_petel'] = 'לוגו פטל';
$string['siteadminquicklink'] = 'ניהול המערכת';

// Course image.
$string['resolution_must'] = 'רוחב: 1042px גובה: 167px';

$string['periodictable'] = 'הטבלה המחזורית';
$string['closedialog'] = 'סגירת חלון';

$string['language_chooser'] = 'החלפת שפת הממשק';
$string['navigationmenu'] = 'תפריט ניווט';

//Dark mode.
$string['dark_mode'] = 'מצב לילה';
$string['normal_mode'] = 'מצב רגיל';

$string['mainmenu'] = 'תפריט ראשי';

// Message menu
$string['togglemessagemenuopen'] = 'תפריט מסרים פתוח';
$string['togglemessagemenuclose'] = 'שליחת מסרים';

// Footer settings
$string['footersettings'] = 'Footer Settings';
$string['middlefooter'] = 'Middle';
$string['rightfooter'] = 'Right side';
$string['middlefooter_descr'] = 'Will appear below the About middle section';
$string['rightfooter_descr'] = 'Will appear below the right side Science Teaching logo';

$string['teammembers'] = 'חברי צוות';
$string['currenttask'] = 'משימה נוכחית';
$string['qsendmessage'] = 'שאלה למורה';
$string['qmessageforteacher'] = 'אני מתקשה ב{$a->qlink} משימה: {$a->cmname} קורס: {$a->coursename}\n אשמח לעזרתך';
$string['question'] ='שאלה';

$string['quiz_student_question'] = 'המשתמש פתח את הציאט';

$string['switch_to_english'] = 'מעבר לפעילות';

// Quiz attempt.
// Timer.
$string['remainingtime'] = 'זמן שנותר:';
$string['return'] = 'חזרה';
$string['stopwatchandalerts'] = 'שעון עצר והתראות';
$string['stopwatchisshown'] = 'שעון עצר מוצג';
$string['stopwatchishidden'] = 'שעון העצר מוסתר';
$string['alerts'] = 'התראות';
$string['alert'] = 'התראה: ';
$string['every30minutes'] = 'כל 30 דקות';
$string['thirthyminutesbeforetheend'] = '30 דקות לפני סיום';
$string['fifteenminutesbeforebnd'] = '15 דקות לפני הסוף';
$string['fiveminutesbeforetheend'] = '5 דקות לפני הסוף';
$string['withoutwarnings'] = 'ללא התראות';
$string['thirteenminutesleftuntiltheend'] = 'נותרו 30 דקות לסיום המשימה';
$string['minutesleftuntiltheend'] = 'נותרו {$a->timeleft} דקות לסיום המשימה';
$string['turnoffalerts'] = 'כיבוי התראות';
$string['timeleft'] = 'נותרו';
$string['timeleftfrom'] = 'מתוך';
$string['answered'] = 'נענו';
$string['answered_from'] = 'נענו מתוך';
$string['answered_from_full'] = 'שאלות נענו מתוך';
$string['minutesleft'] = 'נותרו';
$string['questionnonav'] = '<span class="accesshide">שאלה </span><span class="text">{$a->number}</span> <span class="accesshide"> {$a->attributes}</span>';
$string['questionnonavinfo'] = '<span class="accesshide">מידע </span><i class="fas fa-info"></i></i><span class="accesshide"> {$a->attributes}</span>';
$string['fullscreen'] = 'הצגה במסך מלא';
$string['message'] = 'שאלה למורה';
$string['chapter'] = 'פרק {$a->pagenum}';
$string['questionpointstext'] = '{$a->questionpoints} נקודות';
$string['progresspage'] = '{$a->totalcomplinpage} מתוך {$a->totalquestions} נענו';
$string['stopwatchshowhide'] = 'הצגת/הסתרת שעון עצר';
$string['notflagged'] = 'לא מסומן';
$string['timeisup'] = 'זמן תם';
$string['advancedoverviewlink'] = 'ציונים ומשוב מורחב';
$string['assessmentdiscussionlink'] = 'בדיקה ודיון';
$string['gradingstudentslink'] = 'בדיקה לפי תלמידים';
$string['ministry_statement_title'] = 'סביבת פטל {$a} מאושרת על-ידי משרד החינוך.';
$string['ministry_statement_text'] = '
סביבת פטל {$a}, שאושרה על ידי משרד החינוך, הוערכה מדגמית על ידי האגף לאישור ספרים וחומרי למידה.
סביבת פטל {$a} מופעלת על ידי המחלקה להוראת המדעים במכון ויצמן למדע בהתאם ל<a tabindex="-1" target="_blank" href="https://petel.stweizmann.org.il/chemistry/theme/petel/docs/he/petel_policy.pdf">תנאי השימוש</a> הקבועים בה. התכנים פותחו על ידי מומחי המחלקה ("בדיקת צוות פטל") ועל ידי מורים ("בדיקת עמיתים"). 
בנוסף, סביבת פטל {$a} כוללת הצעות של מורים לפעילויות שונות ("מורים מציעים") וקישורים לאתרים חיצוניים נבחרים. 
האחריות על התכנים בסביבת פטל {$a} היא של המחלקה להוראת המדעים ו/או של כותבי התכנים, ו/או של האתר החיצוני, לפי העניין, כמפורט ב<a tabindex="-1" target="_blank" href="https://petel.stweizmann.org.il/chemistry/theme/petel/docs/he/petel_policy.pdf">תנאי השימוש</a>. 
מומלץ להפעיל שיקול דעת בהחלטה כיצד לעשות שימוש בתכנים השונים ולקרוא בעיון את <a tabindex="-1" target="_blank" href="https://petel.stweizmann.org.il/chemistry/theme/petel/docs/he/petel_policy.pdf">תנאי השימוש</a> באתר.
לצורך קידום הוראת המדעים בישראל, חוקרי המחלקה להוראת המדעים עושים שימוש מחקרי בנתונים המצטברים בסביבת פטל {$a} והכל בהתאם לכללי אתיקה רלוונטיים ו<a tabindex="-1" target="_blank" href="https://petel.stweizmann.org.il/chemistry/theme/petel/docs/he/petel_privacy_policy.pdf">מדיניות הפרטיות</a> של האתר.
';
$string['movetopage'] = 'בחירת עמוד תצוגה: ';

// EC-219
$string['fixbuttonlabel'] = 'לסדר קטגוריה לשאלה';
$string['fixpopuplabel'] = 'לסדר קטגוריה לשאלה';
$string['fixlabel'] = 'לא בקטגוריה הנכונה';
$string['fixpopupmessage'] = 'האם לשכפל שאלה קיימת ולהוריד שאלה הישנה?';
$string['cancel'] = 'לא';
$string['confirm'] = 'כן';

// Cache.
$string['cachedef_instancecolors'] = 'צבעי ערכת העיצוב';

// Sign up.
$string['signuptitle'] = 'טופס רישום למערכת פטל';
$string['signuptext1'] = " במידה ואתם לא רשומים למערכת, ניתן להירשם למערכת בעזרת טופס זה על ידי הזנת 'מפתח הקורס' אותו קיבלתם מהמורה, והזנת הפרטים האישיים";
$string['signuptext2'] = 'אנא שימו לב, בטופס זה ישנם שדות שחובה למלא והם מסומנים בסמליל';

// States for tooltip.
$string['tooltipopenquestion'] = 'שאלת תיאור';
$string['tooltipnotyetanswered'] = 'טרם נענתה';
$string['tooltipcorrect'] = 'תשובה נכונה';
$string['tooltipincorrect'] = 'תשובה לא נכונה';
$string['tooltippartiallycorrect'] = 'תשובה חלקית';
$string['tooltipnotanswered'] = 'לא נענתה';
$string['tooltiprequiresgrading'] = 'נדרש מתן ציון';