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
 * This page saves the annotated PDF file to database
 *
 * @package    qtype_essayannotate
 * @copyright  2024 IIT Palakkad
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * @author Nideesh N, VM Sreeram,
 * Asha Jose, Parvathy S Kumar,
 * Tausif Iqbal, Vishal Rao (IIT Palakkad)
 * First version {@link https://github.com/TausifIqbal/moodle_quiz_annotator/blob/main/3.6/mod/quiz/upload.php}
 * Second version {@link https://github.com/Parvathy-S-Kumar/Moodle_Quiz_PDF_Annotator/blob/main/src/common/mod/quiz/upload.php}
 * This file is the third version, the changes from the previous version are as follows:
 * Followed security guidelines such as require_login, require_capability, required_param
 * instead of $_POST, escaping shell cmds before execution.
 * Added the logic to add steps to question_attempt_step after each annotation.
 * Modified the itemid of the file saved in the database to be the step_id of first annotation step we add.
 * Updated fileinfo array to fix unexpected annotations to different files with same filename within a quiz bug.
 *
 * This page saves annotated pdf to database.
 *
 * It gets the annotation data from pdfannotate.js as parameters. Then annotate the file using FPDI and FPDF
 * Then save it temporarily in 'essayPDF' subdirectory in Moodle's temp directory.
 *
 * Then create new file in databse using this temporary file.
 * Removed get_string usage in paths and changed to use helper functions
 * Moved the functions to helper.php
 */

require_once('../../../../config.php');
require_once('../../../../mod/quiz/locallib.php');
require_once(__DIR__ . '/annotatedfilebuilder.php');
require_once(__DIR__ . '/parser.php');
require_once(__DIR__ . '/alphapdf.php');
require_once('../classes/helper.php');

require_login();
// Getting all the data from pdfannotate.js.
$annotations = required_param('data', PARAM_RAW);                 // Changed id to data.
$contextid = required_param('contextid', PARAM_INT);
$attemptid = required_param('attemptid', PARAM_INT);
$filename = required_param('filename', PARAM_FILE);
$usageid = required_param('usageid', PARAM_INT);
$slot = required_param('slot', PARAM_INT);
$component = 'question';
$filearea = 'response_attachments';
$filepath = '/';

global $USER, $DB;

$result = qtype_essayannotate_helper::getcmid($attemptid);

if ($result) {
    $cmid = $result->cmid;
} else {
    throw new moodle_exception('generic_error', 'qtype_essayannotate');
}

if (!empty($cmid)) {
    $cm = get_coursemodule_from_id('quiz', $cmid);
    $context = context_module::instance($cm->id);
    $PAGE->set_context($context);
}

require_capability('mod/quiz:grade', $PAGE->context);


// Get the serialisepdf value contents and convert into php arrays.
$json = json_decode($annotations, true);

// Referencing the file from the temp directory.
$essaypdfpath = $CFG->tempdir . '/' . qtype_essayannotate_helper::get_essaypdf_path();
$file = $essaypdfpath . '/' . qtype_essayannotate_helper::get_dummy_path() . $attemptid . "$" . $slot . "$" . $USER->id . ".pdf";
$tempfile = $essaypdfpath . '/outputmoodle' . $attemptid . "$" . $slot . "$" . $USER->id . ".pdf";

if (file_exists($file)) {
    // Calling function to convert the PDF version above 1.4 to 1.4 for compatibility with fpdf.
    $file = qtype_essayannotate_helper::convert_pdf_version($file, $essaypdfpath, $attemptid, $slot);

    // Using FPDF and FPDI to annotate.
    if (file_exists($file)) {
        $pdf = qtype_essayannotate_annotatedfilebuilder::build_annotated_file($file, $json);
        // Deleting dummy.pdf .
        unlink($file);
        // Creating output moodle file for loading into database.
        $pdf->Output('F', $tempfile);
    } else {
        throw new moodle_exception('pdf_version_error', 'qtype_essayannotate');
    }
} else {
    throw new moodle_exception('pdf_source_error', 'qtype_essayannotate');
}

if (file_exists($tempfile)) {
    $fsize = filesize($tempfile);       // File size of annotated file.
    $maxbytes = qtype_essayannotate_helper::get_max_bytes();

    if (($fsize > 0) && ($maxbytes > 0) && ($fsize < $maxbytes)) {
        // Changes by Nideesh N and VM Sreeram for marking file for backup.
        $quba = question_engine::load_questions_usage_by_activity($usageid);
        $qa = $quba->get_question_attempt($slot);
        // Adding the annotation step to keep track of annotations in Response History.
        // In Response History, a new entry is added.
        $submitteddata = ["-comment" => get_string('annotated_file', 'qtype_essayannotate') . $filename];
        qtype_essayannotate_helper::add_question_attempt_step($quba, $slot, $submitteddata);

        // Updating $qa after the step is saved.
        $quba = question_engine::load_questions_usage_by_activity($usageid);
        $qa = $quba->get_question_attempt($slot);

        // Saving the annotated file with $itemid as stepid of annotation step so that it gets marked for backup.
        $itemid = qtype_essayannotate_helper::get_first_annotation_comment_step($qa)->get_id();
        $fs = get_file_storage();
        $fileinfo = [
            'contextid' => $contextid,
            'component' => $component,
            'filearea' => $filearea,
            'usage' => $usageid,
            'slot' => $slot,
            'itemid' => $itemid,
            'filepath' => $filepath,
            'filename' => $filename, ];

        // Check if the annotated file exists in the file system and delete if so.
        $doesexists = $fs->file_exists($contextid, $component, $filearea, $itemid, $filepath, $filename);
        if ($doesexists === true) {
            $storedfile = $fs->get_file($contextid, $component, $filearea, $itemid, $filepath, $filename);
            $storedfile->delete();
        }
        // Saving the new annotated file to the file system.
        $fs->create_file_from_pathname($fileinfo, $tempfile);

        // Getting the last feedback comment by teacher, and adding it once more
        // so that the teacher's last manual comment is shown to students.
        $markstep = $qa->get_last_step_with_qt_var("-mark");
        $submitteddata = qtype_essayannotate_helper::get_annotation_stepdata($markstep);
        qtype_essayannotate_helper::add_question_attempt_step($quba, $slot, $submitteddata);
    } else {
        throw new moodle_exception('file_too_big', 'qtype_essayannotate');
    }
    // Deleting outputmoodle file.
    unlink($tempfile);
} else {
    throw new moodle_exception('output_file_failed', 'qtype_essayannotate');
}
