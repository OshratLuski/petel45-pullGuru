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
 * @package     local_redmine
 * @category    support
 * @copyright   2021 <nadav.kavalerchik@weizmann.ac.il>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['pluginname'] = 'Redmine';
$string['myissues'] = 'דיווחים שלי';
$string['plugintitle'] = 'הפניות שלי';

// Settings.
$string['redmineurl'] = 'Redmine url';
$string['redmineurldesc'] = 'Redmine server url';
$string['redminestatus'] = 'Redmine status';
$string['redminestatusdesc'] = 'Turn on/off redmine support';
$string['redmineusername'] = 'Redmine user name';
$string['redmineusernamedesc'] = '';
$string['redminepassword'] = 'Redmine password';
$string['redminepassworddesc'] = '';
$string['redmineadminusername'] = 'Redmine admin user name';
$string['redmineadminusernamedesc'] = '';
$string['redmineadminpassword'] = 'Redmine admin password';
$string['redmineadminpassworddesc'] = '';
$string['redminereporterid'] = 'Redmine reporter user ID';
$string['redminereporteriddesc'] = '';
$string['redminewatcherbugid'] = 'Redmine watcher bug user ID';
$string['redminewatcherbugiddesc'] = '';
$string['redmine_leadoercatalog'] = 'Redmine leadoercatalog user ID';
$string['redmine_leadoercatalogdesc'] = '';
$string['redmine_technopedagogical'] = 'Redmine techno-pedagogical user ID';
$string['redmine_technopedagogicaldesc'] = '';
$string['redminesearchwords'] = 'מספר המילים';
$string['redminesearchwordsdesc'] = 'מספר המילים בחיפוש (0 לא עובד)';
$string['redmineshowresults'] = 'מספר התוצאות';
$string['redmineshowresultsdesc'] = 'מספר התוצאות המוצגות (0 לא עובד)';
$string['allmightymentor'] = 'מנטור ראשי';
$string['allmightymentordesc'] = 'פרטים מלאים של מנטר ראשי, אשר יצורפו לדיווח';
$string['settingssupportcourse'] = 'קורס תמיכה';
$string['settingssupportcoursedesc'] = 'אנא בחר קורס תמיכה';
$string['enabled'] = 'מופעל';
$string['disabled'] = 'מנותק';

// Events.
$string['eventsupportrequestaudit'] = 'Support request audit';

// Popups.
$string['asq_questions_and_report_everything'] = 'כאן תוכלו לדווח לנו על תקלות ולשאול את צוות PeTeL שאלות. אנו נדאג לחזור אליכם בהקדם עם תשובה או פתרון לבעיה.';
$string['how_can_we_help_you'] = 'במה נוכל לעזור לך?';
$string['i_want_to_ask_a_question'] = 'תמיכה טכנית';
$string['i_want_to_get_pedagogical_help'] = 'הנחיה פדגוגית';
$string['i_want_to_suggest_improvement'] = 'הצעה לשיפור';
$string['i_want_to_report_a_contentbug'] = 'בעיה בתוכן של פעילות במאגר המשותף';
$string['i_want_to_report_a_bug'] = 'דיווח על בעיה טכנית';
$string['question_type_error'] = 'נא לבחור';
$string['can_you_tell_more'] = 'תוכלו לספר לנו עוד?';
$string['can_you_tell_more_desc'] = 'ניתן להתחיל להקליד את השאלה/בקשה. ברשימת הנושאים שתוצג, ניתן לבחור בתדריך הקיים. הוא ייפתח בלשונית חדשה.
במידה ולא נמצא נושא מתאים, נשמח לקבל הסבר מפורט על מנת לייעל את עזרתינו.';
$string['we_have_found_something'] = 'מצאנו תדריכים שיכולים לעזור לך:';
$string['more_info_error'] = 'אנא תאר את הבעיה';
$string['were_almost_done'] = 'כמעט סיימנו!';
$string['do_you_have_question_to_teacher'] = 'יש לכם שאלה למורה שלכם?';
$string['here_you_can_ask_your_teacher'] = 'כאן תוכלו לשאול את המורה שלכם שאלה בקשר לאחד הנושאים המופיעים בפטל. השאלה תגיע למורה בתוספת קישור לדף ממנו נשלחה ההודעה.';
$string['my_question'] = 'השאלה שלי:';
$string['screenshot_question'] = 'צילום מסך שיבהיר למורה את השאלה:';
$string['have_you_a_question'] = 'יש לכם שאלה או בעיה טכנית?';
$string['send'] = 'שליחה';
$string['question_to_teacher'] = 'שאלה למורה';
$string['redmine_description'] = '*דיווח חדש*
*שם*: {$a->name}
*דואל*: {$a->email}
*סוג*: {$a->question}
*תוכלו לספר לנו עוד?*
{$a->moreinfo}


IP: {$a->userip}
Browser: {$a->userbrowser}
Resolution: {$a->resolution}
PageUrl: {$a->pageurl}
';
$string['redmine_subject'] = 'מורה מדווח: {$a->digest}';
$string['supportconfirmsmall'] = 'בקשת התמיכה שלכם התקבלה בהצלחה.';
$string['supportconfirmbody'] = 'נציג מצוות התמיכה יצור אתכם קשר בהקדם לקבלת מידע נוסף, תודה רבה, צוות פטל.<br> קוד מעקב: {$a}';
$string['supportmoreinfo'] = 'מידע אודות מצב הטיפול ניתן לקבל מ: הסרגל העליון > תפריט משתמש > דיווחים שלי ';
$string['supportconfirmsubject'] = 'אישור בקשת תמיכה: {$a}';
$string['supportstudent_description'] = 'שלום {$a->teacher_name}<br>
התלמיד {$a->name} מעוניין בתמיכה. <br>
להלן מצורפת הפניה שלו<br><br>
<hr>
{$a->moreinfo}<br>
<hr>
דואל: {$a->email}<br>
IP: {$a->userip}<br>
Browser: {$a->userbrowser}<br>
Resolution: {$a->resolution}<br>
עמוד: {$a->pageurl}<br>
';
$string['supportstudent_description_notification'] = 'שלום {$a->teacher_name}<br>
התלמיד <a href="{$a->userprofileurl}">{$a->name}</a> מעוניין בתמיכה. <br>
להלן מצורפת הפניה שלו<br><br>
<hr>
{$a->moreinfo}<br>
<hr>
דואל: <a href="mailto:{$a->email}">{$a->email}</a><br>
עמוד: <a href="{$a->pageurl}">{$a->pageurl}</a><br>
';
$string['support_subject']='בקשת תמיכה מתלמיד פטל: {$a->digest}';
$string['supporturl'] = 'קישור לעמוד ממנו דווחה בקשת התמיכה';
$string['supportsuccesssendtitle'] = 'הודעה';
$string['supportsuccesssendcontent'] = 'הפניה שלך התקבלה בהצלחה';
$string['supportstudentsuccesssendtitle'] = 'הודעה';
$string['supportstudentsuccesssendcontent'] = 'ההודעה שלך נשלחה למנהל';

// Issues main.
$string['periodhalfyear'] = 'חצי שנה אחרונה';
$string['periodmonth'] = 'חודש נוכחי';
$string['periodlastyear'] = 'שנה אחרונה';
$string['activemyissues'] = 'הפניות שלי';
$string['searchplaceholder'] = "סוג, תוכן או מספר הפנייה";
$string['titlehistory'] = 'היסטוריה';
$string['issues'] = 'פניות';
$string['of'] = 'מתוך';
$string['showing'] = 'מציג';
$string['typeissue'] = 'סוג';
$string['contentissue'] = 'תוכן הפנייה';
$string['dateissue'] = 'מועד הפנייה';
$string['closingdate'] = 'מועד הסגירה';
$string['numberissue'] = 'מספר פנייה';
$string['statusissue'] = 'מצב דיווח';
$string['authornameissue'] = 'יוצר הנושא';
$string['noissueshistory'] = ', אין לך היסטוריה כרגע';
$string['noissuesactive'] = 'מורה יקר/ה אין לך כרגע פניות פעילות';
$string['activeissues'] = 'פניות פעילות';
$string['attentioninfotext'] = 'נותרו {$a} ימים למענה, במידה ולא יתקבל ממך מענה הפנייה תיסגר';
$string['statusnew'] = 'חדש';
$string['statusyouranswer'] = 'ממתין למענה שלך';
$string['statusyouranswer'] = 'ממתין למענה שלך';
$string['statustreatment'] = 'בטיפול';
$string['statusclosed'] = 'הושלם';

// Page single issue.
$string['back'] = 'חזרה לעמוד "הפניות שלי"';
$string['issuenumber'] = 'פנייה מס';
$string['issuedetails'] = 'פרטי הפנייה';
$string['issuelastchanges'] = 'עדכון אחרון';
$string['issuepage'] = 'העמוד ממנו דיווחת';
$string['chatmessages'] = 'תוכן הפנייה';
$string['chatplaceholder'] = 'כתבו את תשובתכם…';
$string['chatnow'] = 'היום';
$string['alertnote'] = 'נותרו {$a->days} ימים למענה, במידה ולא יתקבל מענה הפנייה תיסגר';

// Chat page.
$string['deletefile'] = 'מחק קובץ';
$string['addfile'] = 'הוסף קובץ';
$string['filealreadyadded'] = 'הקובץ כבר נוסף';
$string['wrongfileformat'] = "פורמט קובץ שגוי. נא הוסף קובץ בפורמט '.jpg', '.jpeg' או '.png'.";
$string['textneeded'] = "אנא שימו לב, ניתן לצרף רק תמונה אחת לכל תגובה. אנא הקפידו לצרף הסבר לתמונה";
$string['teacherresponse'] = "תגובה למורה";
$string['responsefrom'] = "תגובה של";

// Support button and menu.
$string['support'] = 'תמיכה';
$string['support_menu_newappeal'] = 'פתיחת פנייה חדשה';
$string['support_menu_myappeals'] = 'כל הפניות שלי';
$string['support_menu_petelguides'] = 'תדריכים לעבודה עם פטל';
$string['support_menu_activeissues'] = 'פניות ממתינות להתייחסותך';

// Email.
$string['mailsubject'] = 'הודעה ממערכת הפניות';
