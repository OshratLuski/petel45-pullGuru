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
 * @package     local_petel
 * @category    string
 * @copyright   2017 nadavkav@gmail.com
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['pluginname'] = 'הגדרות מותאמות לפטל';
$string['setting_smssecurtitynumber'] = 'SMS Security number';
$string['setting_smssecurtitynumber_desc'] = 'SMS Security number';
$string['setting_smssecurtitytimereset'] = 'SMS Security reset time';
$string['setting_smssecurtitytimereset_desc'] = 'SMS Security reset time in seconds';
$string['settings_defaultcourse'] = 'בחר קורס ברירת מחדל';
$string['settings_adminemail'] = 'דוא"ל מנהל';
$string['settings_countviewpopupcoursechangemetadata'] = 'כמה פעמים להציג את החלונית "פרטים נוספים על הקורס"';

$string['policy_yes'] = 'קראתי, אני מסכים עם תנאי השימוש.';
$string['policy_no'] = 'אינני מסכים עם תנאי השימוש.';

// Access.
$string['petel:studentview'] = 'הצג רק לסטודנטים';

// Special gradebook categories for the Chemistry instance courses.
$string['activitieswithgrade'] = 'פעילויות עם ציון';
$string['activitieswithoutgrade'] = 'פעילויות ללא ציון';

// Auth/email.
$string['signupthankyou'] = '<p>תודה על ההרשמה למערכת פטל להוראת המדעים </p><p><a href="{$a}">כניסה למערכת</a></p>';

// Register form.
$string['mustgiveemailorphone'] = 'יש להזין דואל או מספר טלפון סלולרי';
$string['onlyhebrewletters'] = 'ניתן להזין רק שמות בעברית הכוללים סימן "-"';
$string['successfulyregisterd'] = 'נרשמתם בהצלחה למערכת! אתם מועברים לעמוד הקורסים שלכם...';
$string['createaccount'] = 'כאן ניתן ליצור חשבון חדש.';
$string['signup'] = 'רישום למערכת פטל';
$string['idnumber'] = 'מספר תעודת הזהות';
$string['idnumberexists'] = 'מספר זהות קיים במערכת';
$string['idnumbernotvalid'] = 'מספר תעודת זהות לא תקין';
$string['phone1exists'] = 'מספר טלפון קיים במערכת';
$string['missingidnumber'] = 'יש להזין מספר תעודות זהות';

// Login.
$string['searchbyphone'] = 'חיפוש לפי טלפון';
$string['usernameoremailorphone'] = 'הכנס אחד משניים שם משתמש או כתובת דוא"ל או טלפון';
$string['phonenotexists'] = 'הטלפון אינו קיים';
$string['wrongphone'] = 'טלפון לא נכון';
$string['textforsmscode'] = 'אימות הקוד שלך: ';
$string['smsvalidation'] = 'אימות  SMS';
$string['varificationcode'] = 'אימות קוד';
$string['sendcode'] = 'שלח';
$string['emptycodesms'] = 'קוד ריק';
$string['wrongcodesms'] = 'קוד שגוי';
$string['passwordforgotteninstructions2'] =
        'אם מספר הטלפון מצוי במערכת, נשלח אליך קוד SMS. את הקוד יש להזין אותו פה. אם לא התקבל קוד תוך דקה, אנא נסו שוב.';

// User profile page.
$string['welcome_to_profile_page'] = 'ברוך הבא לעמוד הפרופיל שלך';
$string['firstname_and_lastname'] = 'שם פרטי + משפחה';
$string['personal_information'] = 'פרטים אישיים';
$string['username'] = 'שם משתמש';
$string['password'] = 'סיסמה';
$string['email'] = 'דואל';
$string['identity_card'] = 'תעודת זהות';
$string['phone'] = 'טלפון';
$string['save'] = 'שמירה';
$string['edit_properties'] = 'עריכת המאפיינים שלי';
$string['account_settings'] = 'העדפות';

$string['enterfullname'] = 'נא להזין שם פרטי + משפחה';
$string['enterusername'] = 'נא להזין שם משתמש';
$string['enteridnumber'] = 'נא להזין ת.ז.';
$string['idnumbernotnumerical'] = 'ת.ז. לא מספריים';
$string['idnumberwrong'] = 'ת.ז.  שגוי';
$string['enterphone'] = 'נא להזין טלפון';
$string['phonenotnumerical'] = 'מספר טלפון לא תקין';
$string['enteremail'] = 'נא להזין דואר אֶלֶקטרוֹנִי';
$string['enterproperemail'] = 'נא להזין דואר אֶלֶקטרוֹנִי';
$string['saving'] = 'שומר ...';
$string['detailssavedsuccessfullycustom'] = 'פרטים נשמרו בהצלחה!';
$string['wrongpassword'] = 'הסיסמה צריכה להיות מינימום 8 תווים';

$string['usezerobeforeidnumber'] = 'משתמשים בעלי שם משתמש שהוא מספר זהות מתבקשים להזין תשע ספרות';
$string['studentsenrolkey'] = 'מפתח רישום: {$a}';
$string['enrolkey'] = 'מפתח רישום';
$string['enrolme_label'] = 'רישום לקורס נוסף';
$string['enrolme'] = 'רשום אותי';
$string['enrolselfconfirm'] = 'אנא אשרו את הרישום כתלמיד לקורס "{$a}"?';
$string['getcoursekeytitle'] = 'מפתח רישום לקורס';
$string['getkey'] = 'תודה';
$string['close'] = 'סגירה';
$string['enrolkey_error'] = 'לא נמצא קורס התואם למפתח אשר הוזן, אנא נסו להזין מפתח תקין';

$string['messageprovider:attemptgraded'] = 'הסתימה בדיקה של שאלות פתוחות בבוחן';

$string['question_graded_subject'] = 'ניתן ציון ומשוב ניסיון המענה שלך בבוחן';
$string['question_graded_body'] = 'שלום {fullname},<br>ניתן ציון ({grade}) ומשוב למענה שלך בבוחן "{activityname}" ,
 הערות המורה, המשוב והציון ניתנים לצפיה בקישור: {link} <br>';

// Participiant popup.
$string['buttoncreatecourse'] = 'יצירת קורסים חדשים למורים';
$string['buttonaddsystemgroups'] = 'שיוך לקבוצות מערכתיות';
$string['titlecreatecourse'] = 'יצירת קורסים ל {$a} מורים';
$string['titlecreatecourse1'] = 'יצירת קורסים למורה';
$string['titleaddsystemgroups'] = 'שיוך לקבוצות מערכתיות ל {$a} מורים';
$string['titleaddsystemgroups1'] = 'שיוך לקבוצות מערכתיות למורה';
$string['selectmaincategory'] = 'בחירת קטגוריה ראשית';
$string['selectrole'] = 'בחירת תפקיד';
$string['selectgroups'] = 'בחירת קבוצות';
$string['keynull'] = 'האם לאפס מפתח הרשמה';
$string['selecttemplatecourse'] = 'בחירת קורס תבנית';
$string['coursescreated'] = 'בקשת הפתיחה של הקורס נקלטה בהצלחה. הודעה על זמינות הקורס החדש תשלח אליכם';
$string['subjectmailcoursescreated'] = 'רשימת קטגוריות וקורסים שנוצרו';
$string['htmlcategorycreated'] = '<p>קטגוריות שנוצרו</p>';
$string['htmlcategorynotcreated'] = '<p>קטגוריות שלא נוצרו</p>';
$string['htmlmailcoursescreated'] =
        '<p>בקטגוריה "{$a->category_name}" נוצר קורס <a href="{$a->course_url}">"{$a->course_name}"</a></p>';

// Create course popup.
$string['createcourseerror'] = 'לא ניתן לפתוח קורס, אנא העזרו ב"אוזניות" לבקשת קורס';
$string['createcourseteacher'] = 'יצירת קורס חדש למורה';
$string['createcoursesubmit'] = 'אישור';
$string['coursename'] = 'שם של קורס חדש';
$string['waitcoursecreate'] = 'בקשת הפתיחה של הקורס נקלטה בהצלחה. הודעה על זמינות הקורס החדש תשלח אליכם וגם תוצג ב״פעמון״';
$string['messagecoursectreate'] =
        '<p>הקורס "{$a->course_name}" נוצר בהצלחה! להלן קישור לקורס <a href="{$a->course_url}">"{$a->course_name}"</a></p>';
$string['subjectmailcoursecreated'] = 'קורס שנוצר';

$string['questionhintdefault1'] = 'שימו לב לשגיאה ונסו שוב';
$string['questionhintdefault2'] = 'שימו לב לשגיאה ונסו שוב';

// Edit course.
$string['editcoursetitle'] = 'אזהרה';
$string['editcoursebody'] =
        'זהירות! שינוי של תצוגת יחידות ההוראה בקורס, לא ישמור את ההגדרות והעיצוב הנוכחי, במידה ותרצו לחזור אליהם בעתיד';
$string['editcourseapprove'] = 'אישור';

// Recommendations.
$string['questionchooserrecommendations'] = 'שאלות מומלצות';
$string['cacheoercatalog'] = 'מטמונים של מאגר משותף';

// Page copy metadata to activity.
$string['copymetadataactivity'] = 'העתקת ערכי MD לפעילויות';
$string['chatclickevent'] = 'לחץ על CHAT';
$string['notificationclickevent'] = 'לחץ על הודעה';
$string['cmasourcecmid'] = 'הזן את מספר פעילות המקור';
$string['cmatargetcmids'] = 'הזן את מספרים הפעילויות היעד באמצעות פסיק';
$string['cmaheadermdfields'] = 'בחר שדות להעתקה';
$string['cmaerrorsourcecmid'] = 'נא להזין את מספר פעילות המקור';
$string['cmaerrortargetcmids'] = 'נא להזין את מספרים הפעילויות היעד באמצעות פסיק';
$string['cmaerrormdfields'] = 'אנא בחר שדות להעתקה';
$string['cmasubmitlabel'] = 'להעתיק';
$string['cmasuccess'] = 'הועתק בהצלחה';

// Settings admin.
$string['excludedemails'] = 'כתובות דוא"ל אשר לא ישלח אליהם דוא"ל';
$string['excludedemails_desc'] = 'להזין כתובות דוא"ל באופן מלא או חלקי, אשר לא ישלח אליהם דוא"ל.';

// Page participiants.
$string['ppactivestudents'] = 'תלמידים פעילים';
$string['ppallstudents'] = 'כל התלמידים (כולל מושהים)';
$string['ppsuspendedusers'] = 'משתמשים מושהים';
$string['ppfellowteachers'] = 'מורים עמיתים';
$string['ppteacherspayoff'] = 'מורים משתלמים';
$string['ppteacherdoesnotedit'] = 'מורה לא עורך';
$string['ppnopersonalcategory'] = 'משתתפים שאין להם קטגוריה אישית';
$string['ppactivestudentsandteachers'] = 'תלמידים ומורים פעילים';
$string['ppall'] = 'הכל';
$string['searchplaceholder'] = 'חיפוש משתתפים';
$string['filterlabel'] = 'ברשימה מוצגים';
$string['settings_participiant_filter'] = 'ברירת מחדל סינון משתתפים';
$string['pageparticipiants'] = 'משתתפים';

// Page session timeout.
$string['catcustomsettings'] = 'הגדרות מותאמות אישית';
$string['sessiontimeout'] = 'משך זמן חיבור למערכת';
$string['defaulttimeout'] = 'ברירת המחדל של Session timeout Moodle';
$string['sessiontimeouttitle'] = 'הגדרת משך זמן חיבור למערכת';
$string['selectsessiontimeout'] = 'בחירת משך זמן חיבור למערכת';
$string['twohours'] = 'שעתיים';
$string['const'] = 'קבוע';
$string['sessiontimeoutwarning'] = 'משך זמני חיבור קצרים, בטוחים יותר להגנה על החשבון שלכם, בעת חיבור ממחשב ציבורי';

$string['democaptchaheader'] = 'התנסות בפטל DEMO';
$string['democaptchadesc'] = 'לתשומת לבך, עליך לאשר התחברות למערכת התנסות של פטל.
ההתנסות תהיה זמינה למשך {$a} שעות. אנא אישורך ל"אני לא רובוט".';
$string['democaptcha'] = 'Type the sequence from the picture into this field';
$string['demosubmitlabel'] = 'המשך';
$string['enabledemo'] = 'להפעיל מצב דאמו';
$string['demo_copied'] = 'הועתק';
$string['linktodemoactivity'] = 'קישור להדגמה';
$string['linktodemo'] = 'קישור להדגמה';
$string['demomodalhdr'] = 'העתק קישור';
$string['demorole'] = 'תפקיד למשתמשי דאמו';
$string['demorole_desc'] = 'Sitewide role the user using demo link will be enrolled as';

$string['errordemonokey'] = 'קישור לא תקין';
$string['errordemonoenrol'] = 'Error occured during your enrolment: course enrol is not valid. Please contact your administrator';
$string['errordemonoenrolmethod'] =
        'Error occured during your enrolment: enrolment method is not callable. Please contact your administrator';
$string['errordemocoursefull'] = 'Error occured during your enrolment: course is full. Please contact your administrator';
$string['errordemoenrol'] =
        'Error occured during your enrolment: system was not able to enrol you. Please contact your administrator';

$string['aftercontent'] = 'לאחר "{$a}"';
$string['beforecontent'] = 'לפני "{$a}"';

// Comments A11Y.
$string['blankcannotbesaved'] = 'לא ניתן לשמור הערה ריקה, אנא הזן טקסט כאן';
$string['currentview'] = 'תצוגה נוכחית :';
$string['currentfolder'] = 'תיקיה נוכחית: ';
$string['moemyprofile'] = 'פרופיל המשתמש שלך במשרד החינוך';
$string['periodictable'] = 'הטבלה המחזורית';
$string['closedialog'] = 'סגירת חלון';

//Strings for Question edit page
$string['saveinnewversion'] = 'שמירת גרסה חדשה';
$string['saveinnewversionandcontinueediting'] = 'שמירת גרסה חדשה והמשך עריכה';

// Recaptch V3
$string['configrecaptchav3enable'] = 'הפעל או בטל  Google reCAPTCHA V3 עבור דף התחברות.';
$string['configrecaptchav3url'] = 'כתובת עבור Google reCAPTCHA V3 API script.';
$string['configrecaptchav3sitekey'] = 'קוד אתר המסופק על ידי Google reCAPTCHA V3.';
$string['configrecaptchav3privatekey'] = 'קוד סודי המסופק על ידי Google reReCAPTCHA V3.';
$string['configrecaptchav3failed'] = 'reCAPTCHA נכשל, אנא נסו שנית.';
$string['configfailedlogins_without_recaptcha'] = 'מספר נסיונות כושלים לפני הפעלת מנגנון reCAPTCHA';

$string['recaptchav3enable'] = 'הפעל ReCAPTCHAv V3';
$string['recaptchav3url'] = 'ReCAPTCHA V3 API כתובת';
$string['recaptchav3sitekey'] = 'ReCAPTCHA V3 קוד אתר';
$string['recaptchav3privatekey'] = 'ReCAPTCHA V3 קוד סודי';
$string['failedlogins_without_recaptcha'] = 'ניסיונות כושלים.';

$string['configrecaptchav2enable'] = 'הפעל או בטל Google reCAPTCHA V2 עבור דף התחברות.';
$string['configrecaptchav2url'] = 'URL to the Google reCAPTCHA V2 API script.';
$string['configrecaptchav2sitekey'] = 'קוד אתר המסופק על ידי Google reCAPTCHA V2.';
$string['configrecaptchav2privatekey'] = 'קוד סודי המסופק על ידי Google reReCAPTCHA V2.';
$string['configrecaptchav2failed'] = 'reCAPTCHA נכשל, אנא נסו שנית.';

$string['recaptchav3demoenable'] = 'הפעל ReCAPTCHAv V3 ל Demo';
$string['recaptchav2enable'] = 'הפעל ReCAPTCHAv V2';
$string['recaptchav2url'] = 'ReCAPTCHA V2 API URL';
$string['recaptchav2sitekey'] = 'ReCAPTCHA V2 קוד אתר';
$string['recaptchav2privatekey'] = 'ReCAPTCHA V2 קוד סודי';

$string['invalididnumber'] = 'מספר זיהוי לא תקין';

// Grade export.
$string['gradeexportcheckbox'] = 'סימון פעילויות בהן יש משיבים עם ציון';
$string['autosort'] = 'מיון אוטומטי';

// Popup update course metadata.
$string['titlepopupupdatecoursemetadata'] = 'פרטים נוספים על הקורס';
$string['approve'] = 'אישור';
$string['coursemetadata'] = 'אנא פרטו את הנתונים הבאים על קורס זה:';
$string['no_teacher_permission'] = 'למשתמש {$a->username} חסרה הרשאה מורה פטל, נא לפנות לצוות תמיכה.';
