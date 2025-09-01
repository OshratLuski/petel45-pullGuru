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
 * Strings for component 'qtype_essayrubric', language 'en', branch 'MOODLE_20_STABLE'
 *
 * @package    qtype
 * @subpackage essayrubric
 * @copyright  1999 onwards Martin Dougiamas  {@link http://moodle.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

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
$string['pluginname'] = 'Essay with rubric';
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

//Indicator essay
$string['rubriccategorytable'] = 'Rubric Category';
$string['rubriccategoryheader'] = 'Rubric Category';
$string['rubriccategorychoose'] = 'Choose category';
$string['numberofcategories'] = 'How many categories?';
$string['numberofcategoriesdesc'] = 'Choose the number of categories inside Indicator essay';
$string['categorynameen'] = 'Category name in English';
$string['categorynameendesc'] = 'The name of the category in English';
$string['categorynamehe'] = 'Category name in Hebrew';
$string['categorynamehedesc'] = 'The name of the category in Hebrew';
$string['categorytag'] = 'Category tag';
$string['categorytagdesc'] = 'Some tag for the catgeory';
$string['modelname'] = 'Model name';
$string['modelnamedesc'] = 'Please insert model name';
$string['filename'] = 'File name';
$string['filenamedesc'] = 'Please insert the name of the file';
$string['weighterror'] = 'Error! weight value has to be numeric value!';
$string['true'] = 'True';
$string['false'] = 'False';
$string['pleaseselectananswer'] = 'Please write an answer.';
$string['weightforfeedback'] = 'Please wait for the system to analyze your answer to recive a grade.';
$string['numberofmodels'] = 'Number Of Models';
$string['numberofmodelsdesc'] = 'Number Of Models to use';
$string['categoryblock'] = '    ';
$string['categoryblockinfo'] = '<h2><b> Category definition {$a} </b></h2>';
$string['svgfeedbacktemplatename'] = 'Feedback template (SVG)';
$string['svgfeedbacktemplatenamedesc'] = 'Placeholders {{cat1}} {{cat2}} ... {{cat13}} would be replaced by categories name';
$string['categorytype'] = "Please enter category type";
$string['categorytypes'] = "Please enter category types, one per row";
$string['svgfeedbacktemplate'] = 'SVG feedback template';
$string['type1'] = 'causal relationship';
$string['type2'] = 'use of the concept';
$string['type3'] = 'element in the chain of events';
$string['type4'] = 'Claim';
$string['type5'] = 'Evidence';
$string['type6'] = 'Reasoning';
$string['type7'] = 'Definition';
$string['type8'] = 'Counter claim';
$string['type9'] = 'Rebuttal';
$string['graderesponse'] = 'Grade response';
$string['indextitle'] = '<h2><b>Category {$a} </b></h2>';
$string['descriptioncategory'] = 'Decscripton ';
$string['descriptioncategorydesc'] = 'View in response table';
$string['nameresponse'] = 'Indicator Name';
$string['typeresponse'] = 'Type';
$string['resultresponse'] = 'Result';

$string['processing_mode'] = 'Processing mode';
$string['processing_mode_desc'] = 'Processing mode';
$string['processing_mode_random'] = 'Random (for testing)';
$string['processing_mode_local'] = 'Local';
$string['processing_mode_labmda'] = 'AWS Lambda';
$string['aws_labmda_key'] = 'AWS Lambda Key';
$string['aws_labmda_key_desc'] = 'AWS Lambda Key';
$string['aws_labmda_secret'] = 'AWS Lambda Secret';
$string['aws_labmda_secret_desc'] = 'AWS Lambda Secret';
$string['aws_labmda_region'] = 'AWS Lambda Region';
$string['aws_labmda_region_desc'] = 'AWS Lambda Region';
$string['aws_labmda_functionname'] = 'AWS Lambda Function Name';
$string['aws_labmda_functionname_desc'] = 'AWS Lambda Function Name';

$string['override'] = 'Override';
$string['cachedef_quizlambdawarmup'] = 'Quiz Indicator question lambda warmup';


// INDICATORS

$string['list_all_indicators'] = 'List of indicators to use in questions';
$string['list_all_indicators_desc'] = 'You can create a new measure to be added to the list.';
$string['igrade'] = 'Indicator grade';

$string['scalar_grade_0'] = 'grade 0';
$string['scalar_grade_1'] = 'grade 1';
$string['scalar_grade_2'] = 'grade 2';
$string['scalar_grade_3'] = 'grade 3';
$string['scalar_grade_4'] = 'grade 4';
$string['scalar_grade_5'] = 'grade 5';

$string['no_access'] = 'No access';

$string['studentanswer'] = 'Student answer:';

$string['questionindicatorheader'] = 'Question indicators';
$string['questionindicatortext'] = 'You can add a measure to the indicator from an existing pool of measures or create a new measure. Clicking on the add button will open a line at the bottom of the table where free text must be typed';
$string['weightstyle'] = 'Slider style';

$string['scalar'] = 'Scalar';
$string['binary'] = 'Binary';

$string['updated'] = 'Updated';
$string['researchquestion'] = 'Research question';
$string['essayrubricsettings'] = 'Essayrubric settings';
$string['indicatorssettings'] = 'Indicators';
$string['questionindicatortable'] = 'Question indicators';
$string['allowcheck'] = 'Allow check';

$string['selectall'] = 'Select all';
$string['indicatorid'] = 'Indicatorid';
$string['name'] = 'Name';
$string['category'] = 'Tag name';
$string['model'] = 'Description';
$string['research'] = 'Research';
$string['visible'] = 'Visible';

$string['type'] = 'Type';
$string['weight'] = 'Weight';
$string['totalweight'] = 'Total weight';

$string['add'] = 'Add';
$string['submit'] = 'Submit';

$string['search'] = 'Search';

$string['questionreport'] = 'Question Report';
$string['qid'] = 'Question ID';
$string['quizattquiz'] = 'Quiz attepmtp ID';
$string['qattquestionsummary'] = 'Qestion summary';
$string['download_csv'] = 'Download CSV';
$string['qattid'] = 'Question attempt ID';
$string['quizattuserid'] = 'User ID';
$string['quizattattempt'] = 'Quiz attempt ID';
$string['qattresponsesummary'] = 'Response';
$string['nopermissiontoaccesspage'] = 'You don\'t have permission to access this page.';

$string['labeldeleteindicators'] = 'Deleting an indicator for an indicator';
$string['areyoushure'] = 'Are you shure?';
$string['success'] = 'Confirmation';
