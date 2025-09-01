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
 * Strings for component 'qtype_mlnlpessay', language 'en', branch 'MOODLE_20_STABLE'
 *
 * @package    qtype
 * @subpackage mlnlpessay
 * @copyright  Dor Herbesman  - Devlion
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
$string['pluginname'] = 'MLNLPEssay';
$string['pluginname_help'] =
        'In response to a question, the respondent may upload one or more files and/or enter text online. A response template may be provided. Responses must be graded manually.';
$string['pluginname_link'] = 'question/type/mlnlpessay';
$string['pluginnameadding'] = 'Adding an MLNLP Essay question';
$string['pluginnameediting'] = 'Editing an MLNLP Essay question';
$string['pluginnamesummary'] = 'Allows a response of a file upload and/or online text. This must then be graded manually and some strings i added .';
$string['privacy:metadata'] = 'The MLNLP Essay question type plugin allows question authors to set default options as user preferences.';
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

//mlnlp essay
$string['rubiccategorytable'] = 'Rubic Category';
$string['rubiccategoryheader'] = 'Rubic Category';
$string['rubiccategorychoose'] = 'Choose category';
$string['numberofcategories'] = 'How many categories?';
$string['numberofcategoriesdesc'] = 'Choose the number of categories inside mlnlp essay';
$string['modelid'] = 'Model type';
$string['modelname'] = 'Model name';
$string['modelnamedesc'] = 'The name of the model';
$string['categoryname'] = 'Category name';
$string['categorynamedesc'] = 'The name of the category';
$string['categorytag'] = 'Category tag';
$string['categorytagdesc'] = 'Some tag for the catgeory';
$string['modelname'] = 'Model name';
$string['modelnamedesc'] = 'Please insert model name';
$string['filename'] = 'File name';
$string['filenamedesc'] = 'Please insert the name of the file';
$string['weighterror'] = 'Error! weight value has to be numeric value!';
$string['categoryerror'] = 'Error! At least one category should be selected';
$string['true'] = 'True';
$string['false'] = 'False';
$string['pleaseselectananswer'] = 'Please write an answer.';
$string['weightforfeedback'] = 'Please wait for the system to analyze your answer to recive a grade.';
$string['numberofmodels'] = 'Number Of Models';
$string['numberofmodelsdesc'] = 'Number Of Models to use';
$string['categoryblock'] = '    ';
$string['categoryblockinfo'] = 'Category definition {$a}';
$string['svgfeedbacktemplatename'] = 'Feedback template (SVG)';
$string['svgfeedbacktemplatenamedesc'] = 'Placeholders {{cat1}} {{cat2}} ... {{cat13}} would be replaced by categories name';
$string['categorytype'] = "Please enter category type";
$string['categorytypes'] = "Please enter category types, one per row";
$string['svgfeedbacktemplate'] = 'SVG feedback template';
$string['type1'] = 'Type 1';
$string['type2'] = 'Type 2';
$string['graderesponse'] = 'Grade response';
$string['indextitle'] = '<h2><b>Index {$a} </b></h2>';
$string['descriptioncategory'] = 'Decscripton ';
$string['descriptioncategorydesc'] = 'View in responce table';
$string['nameresponce'] = 'Category Name';
$string['typeresponce'] = 'Type';
$string['resultresponce'] = 'Result';

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
$string['cachedef_quizlambdawarmup'] = 'Quiz MLNLP question lambda warmup';

$string['questionreport'] = 'Qestion Report';
$string['qid'] = 'Question ID';
$string['quizattquiz'] = 'Quiz attepmtp ID';
$string['qattquestionsummary'] = 'Qestion summary';
$string['download_csv'] = 'Download CSV';
$string['qattid'] = 'Question attempt ID';
$string['quizattuserid'] = 'User ID';
$string['quizattattempt'] = 'Quiz attempt ID';
$string['qattresponsesummary'] = 'Response';
$string['nopermissiontoaccesspage'] = 'You don\'t have permission to access this page.';
$string['correcntess'] = 'Correctness';
$string['executechunks'] = 'Number of requests (chunks)';
$string['executechunks_desc'] = 'The number of requests to LAMBDA to split the proccess for chunks';
$string['errorsrequests'] = 'Number of times for error request';
$string['errorsrequests_desc'] = 'The number of times requests should be resent to Lambda if it fails';
$string['allquestionreport'] = 'All question report';
$string['attempttime'] = 'Attempt time';
$string['coursename'] = 'Course name';
$string['searchquestionplaceholder'] = 'Search question by Idnumber';
$string['searchcategories'] = 'Search categories...';

$string['submitbtn'] = 'Submit';
$string['active'] = 'Active';
$string['saveerror'] = 'Error occured while saving data';
$string['catid'] = 'ID #';
$string['catlang'] = 'Language';
$string['cattopic'] = 'Topic';
$string['catsubtopic'] = 'Subtopic';
$string['catstatus'] = 'Status';
$string['catactive'] = 'Active';
$string['catdisabled'] = 'Disabled';
$string['catactions'] = 'Actions';
$string['catadd'] = 'Add category';

$string['langid'] = 'ID';
$string['langcode'] = 'Code';
$string['langname'] = 'Language';
$string['langactive'] = 'Active';
$string['langactions'] = 'Actions';
$string['langadd'] = 'Add Language';
$string['modallangstitle'] = 'Add\edit Language';

$string['topicid'] = 'ID';
$string['topicname'] = 'Topic';
$string['topicactive'] = 'Active';
$string['topicactions'] = 'Actions';
$string['topicadd'] = 'Add Topic';
$string['modaltopicstitle'] = 'Add\edit Topic';

$string['subtopicid'] = 'ID';
$string['subtopicname'] = 'Subtopic';
$string['subtopicactive'] = 'Active';
$string['subtopicactions'] = 'Actions';
$string['subtopicadd'] = 'Add Subtopic';
$string['modalsubtopicstitle'] = 'Add\edit Subtopic';

$string['select'] = 'Select...';
$string['modalcategoriestitle'] = 'Add\edit Category';
$string['deleteconfirm'] = 'Delete setting';
$string['deletewarning'] = 'Are you sure you want to delete this setting?';
$string['saveconfirm'] = 'Save category';
$string['savewarning'] = 'Are you sure you want to save changes?';
$string['csvconfirm'] = 'Manual upload';
$string['csvwarning'] = 'Are you sure you want to proceed with manual categories upload?';
$string['csvproceed'] = 'Proceed';
$string['csvupload'] = 'CSV manual upload';
$string['modalcsvuploadtitle'] = 'CSV manual upload';
$string['categories'] = 'Categories';
$string['csvprocess'] = 'Process csv';
$string['csvuploadresult'] = 'CSV Upload Result';
$string['csvcreated'] = 'Rows created';
$string['csvupdated'] = 'Rows updated';
$string['csvprecheckstep'] = 'Preview CSV';
$string['csvresultstep'] = 'Import Results';
$string['csvrowcount'] = 'Rows detected';
$string['csvskippedrows'] = 'Skipped rows';
$string['csvuploaderror'] = 'CSV upload failed or file is empty. Please check your file and try again.';
$string['csvundo'] = 'Undo last import';
$string['csvimportdetails'] = 'Detailed import results';
$string['row'] = 'Row';
$string['action'] = 'Action';
$string['name'] = 'Name';
$string['tag'] = 'Tag';
$string['reason'] = 'Reason';
$string['csvundodone'] = 'Bulk import undone.';
$string['savesuccess'] = 'Category saved successfully';
$string['categoryexists'] = 'Category name already exists';
$string['failedaddcategory'] = 'Failed to save category';