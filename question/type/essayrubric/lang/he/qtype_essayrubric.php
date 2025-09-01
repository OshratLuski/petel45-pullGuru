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
 * Strings for component 'qtype_essayrubric', language 'he', branch 'MOODLE_39_STABLE'
 *
 * @package    qtype
 * @subpackage essayrubric
 * @copyright  1999 onwards Martin Dougiamas  {@link http://moodle.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['allowcheck'] = 'להציג כפתור "הגשת תשובה"';
$string['waitteacherreview'] = 'תשובתך ממתינה לבדיקת מורה';
$string['combinedcontrolnameessay'] = 'נדרש מענה על סעיף שאלה פתוחה';
$string['pleaseenterananswer'] = 'יש להזין תשובה.';
$string['studentanswer'] = 'תשובת התלמיד:';
//Indicator essay
$string['rubriccategoryheader'] = 'Rubric Category';
$string['rubriccategorytable'] = 'Rubric Category';
$string['rubriccategorychoose'] = 'בחר קטגוריות';
$string['numberofcategories'] = 'כמה קטגוריות?';
$string['categoryblockinfo'] = '<h2><b> הגדרת קטגוריה {$a} </b></h2>';
$string['numberofcategoriesdesc'] = 'בחר כמה קטגוריות ברצונך שיהיו במחוון';
$string['categorynameen'] = 'שם הקטגוריה באנגלית';
$string['categorynameendesc'] = 'הכנס שם לקטגוריה באנגלית';
$string['categorynamehe'] = 'שם הקטגוריה בעברית';
$string['categorynamehedesc'] = 'הכנס שם לקטגוריה בעברית';
$string['categorytag'] = 'תג קטגוריה';
$string['categorytagdesc'] = 'הכנס תג לקטגוריה';
$string['modelname'] = 'שם המודל';
$string['modelnamedesc'] = 'אנא הכנס את שם המודל';
$string['filename'] = 'שם הקובץ';
$string['filenamedesc'] = 'אנא הכנס את שם הקובץ';
$string['weighterror'] = 'שגיאה! משקל חייב לכלול ערך מספרי בלבד!';
$string['pleaseselectananswer'] = 'אנא הזן תשובה.';
$string['weightforfeedback'] = 'אנא המתן עד שהמערכת תבדוק את הפתרון שלך על מנת לקבל ציון';
$string['categorytype'] = "אנא הכנס את סוג הקטגוריה";
$string['categorytypes'] = "Please enter category types, one per row";
$string['type1'] = 'קשר סיבתי';
$string['type2'] = 'שימוש במושג';
$string['type3'] = 'מרכיב בשרשרת האירועים';
$string['type4'] = 'טענה';
$string['type5'] = 'עדויות/נתונים';
$string['type6'] = 'הנמקה';
$string['type7'] = 'הגדרה';
$string['type8'] = 'טענה נגדית';
$string['type9'] = 'הפרכת טענה';
$string['graderesponse'] = 'Grade response';
$string['indextitle'] = '<h2><b>קטגוריה {$a} </b></h2>';
$string['descriptioncategory'] = 'Decscripton ';
$string['descriptioncategorydesc'] = 'View in response table';
$string['nameresponse'] = 'מרכיב בתשובה';
$string['typeresponse'] = 'סוג המרכיב';
$string['resultresponse'] = 'תוצאה';

$string['processing_mode'] = 'שיטת חישוב';
$string['processing_mode_desc'] = 'שיטת חישוב לבחירה';
$string['processing_mode_random'] = 'אקראי (רק לבדיקה)';
$string['processing_mode_local'] = 'לוקלית לפי סקריפט לוקלי';
$string['processing_mode_labmda'] = 'שימוש בפונקציה LAMBDA';
$string['aws_labmda_key'] = 'AWS Lambda Key';
$string['aws_labmda_key_desc'] = 'AWS Lambda Key';
$string['aws_labmda_secret'] = 'AWS Lambda Secret';
$string['aws_labmda_secret_desc'] = 'AWS Lambda Secret';
$string['aws_labmda_region'] = 'AWS Lambda Region';
$string['aws_labmda_region_desc'] = 'AWS Lambda Region';
$string['aws_labmda_functionname'] = 'AWS Lambda Function Name';
$string['aws_labmda_functionname_desc'] = 'AWS Lambda Function Name';

$string['override'] = 'תיקון תוצאה';

$string['acceptedfiletypes'] = 'Accepted file types';
$string['acceptedfiletypes_help'] = 'Accepted file types can be restricted by entering a list of file extensions. If the field is left empty, then all file types are allowed.';
$string['allowattachments'] = 'Allow attachments';
$string['answerfiles'] = 'Answer files';
$string['answertext'] = 'Answer text';
$string['attachedfiles'] = 'Attachments: {$a}';
$string['attachmentsoptional'] = 'Attachments are optional';
$string['attachmentsrequired'] = 'Require attachments';
$string['attachmentsrequired_help'] = 'This option specifies the minimum number of attachments required for a response to be considered gradable.';
$string['err_maxminmismatch'] = 'Maximum word limit must be greater than minimum word limit';
$string['err_maxwordlimit'] = 'Maximum word limit is enabled but is not set';
$string['err_maxwordlimitnegative'] = 'Maximum word limit cannot be a negative number';
$string['err_minwordlimit'] = 'Minimum word limit is enabled but is not set';
$string['err_minwordlimitnegative'] = 'Minimum word limit cannot be a negative number';
$string['formateditor'] = 'HTML editor';
$string['formateditorfilepicker'] = 'HTML editor with file picker';
$string['formatmonospaced'] = 'Plain text, monospaced font';
$string['formatnoinline'] = 'No online text';
$string['formatplain'] = 'Plain text';
$string['graderinfo'] = 'Information for graders';
$string['graderinfoheader'] = 'Grader information';
$string['maxbytes'] = 'Maximum file size';
$string['maxwordlimit'] = 'Maximum word limit';
$string['maxwordlimit_help'] = 'If the response requires that students enter text, this is the maximum number of words that each student will be allowed to submit.';
$string['maxwordlimitboundary'] = 'The word limit for this question is {$a->limit} words and you are attempting to submit {$a->count} words. Please shorten your response and try again.';
$string['minwordlimit'] = 'Minimum word limit';
$string['minwordlimit_help'] = 'If the response requires that students enter text, this is the minimum number of words that each student will be allowed to submit.';
$string['minwordlimitboundary'] = 'This question requires a response of at least {$a->limit} words and you are attempting to submit {$a->count} words. Please expand your response and try again.';
$string['mustattach'] = 'When "No online text" is selected, or responses are optional, you must allow at least one attachment.';
$string['mustrequire'] = 'When "No online text" is selected, or responses are optional, you must require at least one attachment.';
$string['mustrequirefewer'] = 'You cannot require more attachments than you allow.';
$string['nlines'] = '{$a} lines';
$string['nonexistentfiletypes'] = 'The following file types were not recognised: {$a}';
$string['pluginname'] = 'שאלה פתוחה עם מחוון';
$string['pluginname_help'] =
    'In response to a question, the respondent may upload one or more files and/or enter text online. A response template may be provided. Responses must be graded manually.';
$string['pluginname_link'] = 'question/type/essayrubric';
$string['pluginnameadding'] = 'Adding an Indicator Essay question';
$string['pluginnameediting'] = 'Editing an Indicator Essay question';
$string['pluginnamesummary'] = 'Allows a response of a file upload and/or online text. This must then be graded manually and some strings i added .';
$string['privacy:metadata'] = 'The Indicator Essay question type plugin allows question authors to set default options as user preferences.';
$string['privacy:preference:defaultmark'] = 'The default mark set for a given question.';
$string['privacy:preference:responseformat'] = 'What is the response format (HTML editor, plain text, etc.)?';
$string['privacy:preference:responserequired'] = 'Whether the student is required to enter text or the text input is optional.';
$string['privacy:preference:responsefieldlines'] = 'Number of lines indicating the size of the input box (textarea).';
$string['privacy:preference:attachments'] = 'Number of allowed attachments.';
$string['privacy:preference:attachmentsrequired'] = 'Number of required attachments.';
$string['privacy:preference:maxbytes'] = 'Maximum file size.';
$string['responsefieldlines'] = 'Input box size';
$string['responseformat'] = 'Response format';
$string['responseoptions'] = 'Response options';
$string['responserequired'] = 'Require text';
$string['responsenotrequired'] = 'Text input is optional';
$string['responseisrequired'] = 'Require the student to enter text';
$string['responsetemplate'] = 'Response template';
$string['responsetemplateheader'] = 'Response template';
$string['responsetemplate_help'] = 'Any text entered here will be displayed in the response input box when a new attempt at the question starts.';
$string['wordcount'] = 'Word count: {$a}';
$string['wordcounttoofew'] = 'Word count: {$a->count}, less than the required {$a->limit} words.';
$string['wordcounttoomuch'] = 'Word count: {$a->count}, more than the limit of {$a->limit} words.';


// INDICATORS

$string['list_all_indicators'] = 'רשימת מדדים לשימוש בשאלה';
$string['list_all_indicators_desc'] = 'ניתן להוסיף מדדים לרשימה.';
$string['igrade'] = 'קיים בתשובה';

$string['scalar_grade_0'] = 'כלל לא קיים';
$string['scalar_grade_1'] = 'במידה מעטה';
$string['scalar_grade_2'] = 'במידה מספקת';
$string['scalar_grade_3'] = 'במידה רבה';
$string['scalar_grade_4'] = 'במידה רבה מאוד';
$string['scalar_grade_5'] = 'במידה מלאה';

$string['no_access'] = 'אין גישה';

$string['questionindicatorheader'] = 'מדדי השאלה';
$string['questionindicatortext'] = 'ניתן להוסיף מדד למחוון מתוך מאגר מדדים קיים או ליצור מדד חדש. לחיצה על כפתור ההוספה תפתח שורה בתחתית הטבלה שבה יש להקליד טקסט חופשי';
$string['weightstyle'] = 'צורת דירוג מחוון';

$string['scalar'] = 'מדורג';
$string['binary'] = 'בינארי';

$string['updated'] = 'מעודכן';
$string['researchquestion'] = 'שאלה למחקר';
$string['essayrubricsettings'] = 'Indicator Essay הגדרות';
$string['indicatorssettings'] = 'מחוונים';
$string['questionindicatortable'] = 'מדדי השאלה';

$string['selectall'] = 'בחר הכל';
$string['indicatorid'] = 'מספר המדד';
$string['name'] = 'שם המדד';
$string['category'] = 'תג קטגוריה';
$string['model'] = 'תיאור';
$string['research'] = 'למחקר';
$string['visible'] = 'הצג\הסתר';

$string['type'] = 'סוג המרכיב';
$string['weight'] = 'משקל';
$string['totalweight'] = 'סך כל המשקלים';

$string['add'] = 'הוספה';
$string['submit'] = 'שמירה';

$string['search'] = 'חיפוש';

$string['questionreport'] = 'דוח שאלות';
$string['qid'] = 'ID שאלה';
$string['quizattquiz'] = 'ID נסיון';
$string['qattquestionsummary'] = 'תקציר שאלה';
$string['download_csv'] = 'הורדת CSV';
$string['qattid'] = 'ID נסיון שאלה';
$string['quizattuserid'] = 'ID משתמש';
$string['quizattattempt'] = 'ID נסיון בוחן';
$string['qattresponsesummary'] = 'תשובה';
$string['nopermissiontoaccesspage'] = 'You don\'t have permission to access this page.';

$string['labeldeleteindicators'] = 'מחיקה מדד למחוון';
$string['areyoushure'] = 'האם אתה בטוח?';
$string['success'] = 'אישור';
