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
 * Strings for component 'qtype_essayannotate', language 'en', branch 'MOODLE_41_STABLE'
 *
 * @package    qtype_essayannotate
 * @copyright  2024 IIT Palakkad
 * @copyright  based on work by Martin Dougiamas  {@link http://moodle.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * Updated By  Nideesh N, VM Sreeram (IIT Palakkad)
 * Added language string for alt text of PDF icon
 * Removed usage of paths from language file
 */

$string['acceptedfiletypes'] = 'Accepted file types';
$string['acceptedfiletypes_help'] = 'Accepted file types can be restricted by entering a list of file extensions. If the field is left empty, then all file types are allowed.';
$string['allowattachments'] = 'Allow attachments';
$string['annotate_button_label'] = 'Annotate';
$string['annotated_file'] = 'Annotated file: ';
$string['annotationstep_default_comment'] = 'Teacher has started grading';
$string['annotator'] = 'Annotator';
$string['answerfiles'] = 'Answer files';
$string['answertext'] = 'Answer text';
$string['attachedfiles'] = 'Attachments: {$a}';
$string['attachmentsoptional'] = 'Attachments are optional';
$string['attachmentsrequired'] = 'Require attachments';
$string['attachmentsrequired_help'] = 'This option specifies the minimum number of attachments required for a response to be considered gradable.';
$string['corrected_documents'] = 'Corrected Documents';
$string['err_maxminmismatch'] = 'Maximum word limit must be greater than minimum word limit';
$string['err_maxwordlimit'] = 'Maximum word limit is enabled but is not set';
$string['err_maxwordlimitnegative'] = 'Maximum word limit cannot be a negative number';
$string['err_minwordlimit'] = 'Minimum word limit is enabled but is not set';
$string['err_minwordlimitnegative'] = 'Minimum word limit cannot be a negative number';
$string['file_not_saved_message'] = 'Not able to save the file';
$string['file_saved_message'] = 'File has been saved';
$string['file_too_big'] = 'Too big file';
$string['font_size'] = 'Font size';
$string['formateditor'] = 'HTML editor';
$string['formateditorfilepicker'] = 'HTML editor with file picker';
$string['formatmonospaced'] = 'Plain text, monospaced font';
$string['formatnoinline'] = 'No online text';
$string['formatplain'] = 'Plain text';
$string['generic_error'] = 'Some error occurred.';
$string['ghostscriptpath'] = 'Ghostscript Path';
$string['ghostscriptpath_desc'] = 'Enter the path to your Ghostscript installation.';
$string['graderinfo'] = 'Information for graders';
$string['graderinfoheader'] = 'Grader information';
$string['gs_fail'] = 'PDF conversion using Ghostscript failed';
$string['imagemagick_fail'] = 'PDF conversion using ImageMagick failed';
$string['imagemagickpath'] = 'ImageMagick Path';
$string['imagemagickpath_desc'] = 'Enter the path to your ImageMagick installation.<br><br><u><b>Note:</b></u> Alter policy.xml file of imagemagick, change <br>    &lt;policy domain="coder" rights="none" pattern="PDF" /&gt; <br>    to <br>    &lt;policy domain="coder" rights="read|write" pattern="PDF" /&gt;<br><br>';
$string['maxbytes'] = 'Maximum file size';
$string['maxwordlimit'] = 'Maximum word limit';
$string['maxwordlimit_help'] = 'If the response requires that students enter text, this is the maximum number of words that each student will be allowed to submit.';
$string['maxwordlimitboundary'] = 'The word limit for this question is {$a->limit} words and you are attempting to submit {$a->count} words. Please shorten your response and try again.';
$string['minwordlimit'] = 'Minimum word limit';
$string['minwordlimit_help'] = 'If the response requires that students enter text, this is the minimum number of words that each student will be allowed to submit.';
$string['minwordlimitboundary'] = 'This question requires a response of at least {$a->limit} words and you are attempting to submit {$a->count} words. Please expand your response and try again.';
$string['mkdir_fail'] = 'Cannot create directory';
$string['mustattach'] = 'When "No online text" is selected, or responses are optional, you must allow at least one attachment.';
$string['mustrequire'] = 'When "No online text" is selected, or responses are optional, you must require at least one attachment.';
$string['mustrequirefewer'] = 'You cannot require more attachments than you allow.';
$string['nlines'] = '{$a} lines';
$string['nonexistentfiletypes'] = 'The following file types were not recognised: {$a}';
$string['output_file_failed'] = 'Failed to create output file';
$string['pdf_icon_alt_txt'] = 'PDF document';
$string['pdf_source_error'] = 'Source PDF not found!';
$string['pdf_version_error'] = 'PDF Version incompatible';
$string['permission_denied'] = 'Cannot read file. Either the file does not exist or there is a permission problem.';
$string['pluginname'] = 'Essay annotate';
$string['pluginname_help'] = 'In response to a question, the respondent may upload one or more files and/or enter text online. A response template may be provided. Responses must be graded manually.';
$string['pluginname_link'] = 'question/type/essayannotate';
$string['pluginnameadding'] = 'Adding an Essay annotate question';
$string['pluginnameediting'] = 'Editing an Essay annotate question';
$string['pluginnamesummary'] = 'Allows a response of a file upload and/or online text. This must then be graded manually. Supports annotation of the uploaded files.';
$string['privacy:metadata'] = 'essayannotate question type plugin allows question authors to set default options as user preferences.';
$string['privacy:preference:attachments'] = 'Number of allowed attachments.';
$string['privacy:preference:attachmentsrequired'] = 'Number of required attachments.';
$string['privacy:preference:defaultmark'] = 'The default mark set for a given question.';
$string['privacy:preference:maxbytes'] = 'Maximum file size.';
$string['privacy:preference:responsefieldlines'] = 'Number of lines indicating the size of the input box (textarea).';
$string['privacy:preference:responseformat'] = 'What is the response format (HTML editor, plain text, etc.)?';
$string['privacy:preference:responserequired'] = 'Whether the student is required to enter text or the text input is optional.';
$string['responsefieldlines'] = 'Input box size';
$string['responseformat'] = 'Response format';
$string['responseisrequired'] = 'Require the student to enter text';
$string['responsenotrequired'] = 'Text input is optional';
$string['responseoptions'] = 'Response options';
$string['responserequired'] = 'Require text';
$string['responsetemplate'] = 'Response template';
$string['responsetemplate_help'] = 'Any text entered here will be displayed in the response input box when a new attempt at the question starts.';
$string['responsetemplateheader'] = 'Response template';
$string['save_exit'] = 'Save and Exit';
$string['time'] = ', time: ';
$string['unsupported_file'] = 'Unsupported file';
$string['user'] = ', user: ';
$string['wordcount'] = 'Word count: {$a}';
$string['wordcounttoofew'] = 'Word count: {$a->count}, less than the required {$a->limit} words.';
$string['wordcounttoomuch'] = 'Word count: {$a->count}, more than the limit of {$a->limit} words.';
