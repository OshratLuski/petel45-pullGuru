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
 * This page allows the teacher to annotate file of a particular question.
 *
 * @package    qtype_essayannotate
 * @copyright  2024 IIT Palakkad
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * @author Nideesh N, VM Sreeram,
 * Asha Jose, Parvathy S Kumar,
 * Tausif Iqbal, Vishal Rao (IIT Palakkad)
 * First version {@link https://github.com/TausifIqbal/moodle_quiz_annotator/blob/main/3.6/mod/quiz/annotator.php}
 * Second version {@link https://github.com/Parvathy-S-Kumar/Moodle_Quiz_PDF_Annotator/blob/main/src/common/mod/quiz/annotator.php}
 * This file is the third version, the changes from the previous version are as follows:
 * Changed code to follow security guidelines such as require_login, require_capability, escaping shell cmds before execution.
 * Updated the logic for checking filetype is PDF by using mimetype and extension.
 * Updated itemid as the step id of the first annotation step.
 * Added logic to create `essayPDF` directory within temp directory of moodledata to store temporary files.
 * Changed the name of dummy file to include attemptid and slot number to allow
 * concurrent annotation of different files by different users.
 * Used Mustache template to render HTML output.
 * Removed script tags and using js_call_amd to load javascript
 * Removed usage of mkdir to Moodle function make_temp_directory
 * Removed get_string usage in paths and changed to use helper functions
 */

require_once('../../../../config.php');
require_once('annotatormustacheconfig.php');
require_once('../../../../mod/quiz/locallib.php');
require_once('../classes/helper.php');
require_login();

global $USER, $PAGE;

// The $temppath is the path to the subdirectory essayPDF created in moodle's temp directory.
$temppath = $CFG->tempdir . '/' . qtype_essayannotate_helper::get_essaypdf_path();

$attemptid = required_param('attempt', PARAM_INT);
$slot = required_param('slot', PARAM_INT);
$fileno = required_param('fileno', PARAM_INT);
$cmid = optional_param('cmid', null, PARAM_INT);
$dummyfile = $temppath . '/' . qtype_essayannotate_helper::get_dummy_path() . $attemptid . "$" . $slot . "$" . $USER->id . ".pdf";
if ($cmid == null) {
    $result = qtype_essayannotate_helper::getcmid($attemptid);
    if ($result) {
        $cmid = $result->cmid;
    } else {
        throw new moodle_exception('generic_error', 'qtype_essayannotate');
    }
}

$PAGE->set_url('/question/type/essayannotate/annotator/annotator.php', ['attempt' => $attemptid, 'slot' => $slot, 'fileno' => $fileno]);
$PAGE->set_pagelayout('popup');

if (!empty($cmid)) {
    $cm = get_coursemodule_from_id('quiz', $cmid);
    $context = context_module::instance($cm->id);
    $PAGE->set_context($context);
    $PAGE->set_cm($cm);
} else {
    throw new moodle_exception('generic_error', 'qtype_essayannotate');
}

require_capability('mod/quiz:grade', $PAGE->context);

// Try to create the subdirectory essayPDF if not exists.
if (!is_dir($temppath) && !make_temp_directory(qtype_essayannotate_helper::get_essaypdf_path(), false)) {
    throw new moodle_exception('mkdir_fail', 'qtype_essayannotate');
}
$attemptobj = quiz_create_attempt_handling_errors($attemptid, $cmid);
$attemptobj->preload_all_attempt_step_users();
$attemptobj->render_question_for_commenting($slot);

// We need $qa and $options to get all files submitted by student.
$qa = $attemptobj->get_question_attempt($slot);
$options = $attemptobj->get_display_options(true);
$files = $qa->get_last_qt_files('attachments', $options->context->id);

// Select the "$fileno", the file about to be annotated.
$fileurl = "";
$currfileno = 0;
foreach ($files as $file) {
    $currfileno = $currfileno + 1;
    if ($currfileno == $fileno) {           // This is the file we want.
        $out = $qa->get_response_file_url($file);
        $url = (explode("?", $out))[0];     // Remove '?forcedownload=1' from the end of the url.
        $fileurl = $url;
        // Storing it; in case the file is not PDF, we need the original file to create PDF from it.
        $originalfile = $file;
        break;
    }
}

$attemptid = (string)$attemptid;
$contextid = $options->context->id;
$component = 'question';
$filearea = 'response_attachments';
$usageid = $qa->get_usage_id();
$filepath = '/';
[$filename, $format] = qtype_essayannotate_helper::get_filename_format($fileurl);

// The variable itemid is required for saving the file. Annotation step id is used as itemid so that it gets marked for backup.
$itemid = qtype_essayannotate_helper::get_first_annotation_comment_step($qa)->get_id();

// If an annotation step does not exist, itemid will be null.
if ($itemid == null) {
    $itemid = $attemptid;
}

// Checking if file is not PDF.
$ispdf = true;
// Copy file to the temp directory of moodledata.
$filetoconvertraw = $temppath . "/" . $originalfile->get_filename();
$filetoconvert = escapeshellcmd($filetoconvertraw);
$originalfile->copy_content_to($filetoconvert);

// Get the mime-type of the original file.
$tempmime = mime_content_type($filetoconvert);

if ($tempmime == false) {
    throw new moodle_exception('permission_denied', 'qtype_essayannotate');
}

$mime = (explode("/", $tempmime))[0];
if ($mime == "application") {
    $mime = (explode("/", $tempmime))[1];
}
if (($mime === "pdf" && $format !== "pdf") ||
    ($mime !== "pdf" && $format === "pdf")) {
    // Reject the file if there is inconsistency in mime and format for PDF.
    unlink($filetoconvert);
    throw new moodle_exception('unsupported_file', 'qtype_essayannotate');
}
if ($mime !== "pdf") {
    $ispdf = false;
    $filename = $filename . "_topdf.pdf";
}

$fs = get_file_storage();

// Check if the annotated PDF exists in database.
$doesexists = $fs->file_exists($contextid, $component, $filearea, $itemid, $filepath, $filename);

// If exists, then update $fileurl to the url of this file.
if ($doesexists === true) {
    unlink($filetoconvert);
    $file = $fs->get_file($contextid, $component, $filearea, $itemid, $filepath, $filename);
    $file->copy_content_to($dummyfile);
    $fileurl = qtype_essayannotate_helper::create_fileurl($qa, $file);
} else if ($ispdf == false) {
    // Annotated PDF doesn't exists and the original file is not a PDF file.
    // So we need to create PDF first and update fileurl to this PDF file.

    // Convert that file into PDF, based on mime type.
    $convert = get_config('qtype_essayannotate', 'imagemagickpath');

    if ($mime === "image") {
        $command = $convert." '" . $filetoconvertraw ."'  -page a4 " .$dummyfile;
    } else if ($mime == "text") {
        $command = $convert." TEXT:'" . $filetoconvertraw ."' " .$dummyfile;
    } else {
        unlink($filetoconvert);
        throw new moodle_exception('unsupported_file', 'qtype_essayannotate');
    }

    // Execute the commands of imagemagick(Convert texts and images to PDF).
    $safecommand = escapeshellcmd($command);
    $shelloutput = exec($safecommand, $cmdoutput, $retval);

    unlink($filetoconvert);

    if ($shelloutput === false || $retval !== 0) {
        throw new moodle_exception('imagemagick_fail', 'qtype_essayannotate');
    }
    // Create a PDF file in moodle database from the above created PDF file.
    $fileinfo = [
        'contextid' => $contextid,
        'component' => $component,
        'filearea' => $filearea,
        'usage' => $usageid,
        'slot' => $slot,
        'itemid' => $itemid,
        'filepath' => $filepath,
        'filename' => $filename];

    $fs->create_file_from_pathname($fileinfo, $dummyfile);

    // Now update fileurl to this newly created PDF file.
    $file = $fs->get_file($contextid, $component, $filearea, $itemid, $filepath, $filename);
    $fileurl = qtype_essayannotate_helper::create_fileurl($qa, $file);
} else {
    unlink($filetoconvert);
    $originalfile->copy_content_to($dummyfile);
}

// Checking if creation of dummyfile was unsuccessful.
if (!(file_exists($dummyfile))) {
    throw new moodle_exception('permission_denied', 'qtype_essayannotate');
}

$contextid = strval($contextid);
$slot = strval($slot);
// Render the annotator UI.
$data = qtype_essayannotate_annotator_mustache_config::get_annotator_mustache_config();

// Calling init function of pdfannotate.js and clickhandlers.js for setting the necessary parameters.
$PAGE->requires->js_call_amd('qtype_essayannotate/pdfannotate', 'init', [$contextid, $attemptid, $filename, $usageid, $slot]);
$PAGE->requires->js_call_amd('qtype_essayannotate/clickhandlers', 'init', [$fileurl]);

$output = $PAGE->get_renderer('qtype_essayannotate');
echo $output->header();
echo $output->render_from_template('qtype_essayannotate/annotator', $data);
echo $output->footer();

