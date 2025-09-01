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
 * Helper functions for qtype_essayannotate plugin.
 *
 * @package    qtype_essayannotate
 * @copyright  2024 IIT Palakkad
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author     Nideesh N, VM Sreeram
 *   Removed usage of deprecated function file_encode_url
 *   Added helper functions for retrieving hardcoded repeated paths
 */

/**
 * Class with helper functions for qtype_essayannotate plugin
 */
class qtype_essayannotate_helper {
    /**
     * Fetches course module id (cmid) associated with given attempt id from the database
     *
     * @param int $attemptid the attempt id whose cmid is to be found
     * @return object $result the record containing cmid
     */
    public static function getcmid($attemptid) {
        global $DB;

        $sql = "SELECT cm.id AS cmid
                FROM {quiz_attempts} qa
                JOIN {course_modules} cm ON qa.quiz = cm.instance AND
                    cm.module = (SELECT id FROM {modules} WHERE name = 'quiz')
                WHERE qa.id = :attemptid";

        $params = ['attemptid' => $attemptid];

        $result = $DB->get_record_sql($sql, $params);

        return $result;
    }

    /**
     * Gets the step of first annotation made for the question attempt
     *
     * @param question_attempt $qa the question attempt whose first annotation step is to be found
     * @return question_attempt_step $step the question attempt step of first annotation made for the question attempt if exists,
     *  otherwise, a readonly step is returned
     */
    public static function get_first_annotation_comment_step($qa) {
        foreach ($qa->get_step_iterator() as $step) {
            // The annotation step does not have qt_var "-mark".
            if ($step->has_qt_var("-comment") && !$step->has_qt_var("-mark")) {
                return $step;
            }
        }
        return new question_attempt_step_read_only();
    }

    /**
     * Create URL of the file based on the provided question attempt and file object.
     *
     * @param question_attempt $qa The question attempt object.
     * @param stored_file $file The file object.
     * @return string $url The URL of the file.
     */
    public static function create_fileurl($qa, $file) {
        // Create url of this file.
        $urlpath = '/' . implode('/', [
            $file->get_contextid(),
            $file->get_component(),
            $file->get_filearea(),
            $qa->get_usage_id(),
            $qa->get_slot(),
            $file->get_itemid()]) .
            $file->get_filepath() . $file->get_filename();
        $url = moodle_url::make_file_url(new moodle_url('/pluginfile.php'), $urlpath)->__toString();
        return $url;
    }

    /**
     * Retrieves the filename and format from a given file URL.
     *
     * @param string $fileurl The URL of the file.
     * @return array An array containing the filename and format.
     */
    public static function get_filename_format($fileurl) {
        $filename = explode("/", $fileurl);
        $filename = end($filename);
        $filename = urldecode($filename);
        $format = explode(".", $filename);
        $format = end($format);
        return [$filename, $format];
    }

    /**
     * Retrieves the dummy path.
     *
     * @return string The dummy path.
     */
    public static function get_dummy_path() {
        return 'dummy';
    }

    /**
     * Retrieves the essayPDF path.
     *
     * @return string The essayPDF path.
     */
    public static function get_essaypdf_path() {
        return 'essayPDF';
    }

    /**
     * To convert PDF versions to 1.4 if the version is above it
     * since FPDI parser will only work for PDF versions upto 1.4.
     * Given a file and its path, the file converted to version 1.4 is returned,
     * if version is above it else, the original file is retured.
     *
     * @param string $file the path to the pdf file
     * @param string $essaypdfpath the path where the file exists
     * @param int $attemptid the attempt id
     * @param int $slot the slot id
     * @return string $file the path to thepdf file after conversion (if necessary)
     */
    public static function convert_pdf_version($file, $essaypdfpath, $attemptid, $slot) {
        global $USER;
        $filepdf = fopen($file, "r");
        if ($filepdf) {
            $linefirst = fgets($filepdf);
            preg_match_all('!\d+!', $linefirst, $matches);
            // Save that number in a variable.
            $pdfversion = implode('.', $matches[0]);
            if ($pdfversion > "1.4") {
                // Filename contains attemptid, slot, userid so that multiple files can be annotated simultaneously.
                $srcfilenew = $essaypdfpath . "/newdummy" . $attemptid . "$" . $slot . "$" . $USER->id . ".pdf";
                $srcfile = $file;
                // Using Ghostscript convert the pdf version to 1.4.
                // Getting Ghostscript path from settings page of plugin.
                $gspath = get_config('qtype_essayannotate', 'ghostscriptpath');
                $command = $gspath . ' -sDEVICE=pdfwrite -dCompatibilityLevel=1.4 -dNOPAUSE -dBATCH -sOutputFile="' . $srcfilenew . '" "' . $srcfile . '"'.'  2>&1';
                $safecommand = escapeshellcmd($command);
                $shelloutput = exec($safecommand);

                if ($shelloutput === false) {
                    throw new moodle_exception('gs_fail', 'qtype_essayannotate');
                }
                $file = $srcfilenew;
                unlink($srcfile);          // To remove original dummy file.
            }
            fclose($filepdf);
        }

        return $file;
    }

    /**
     * Get the annotation step data based on the provided $markstep object.
     *
     * @param object $markstep The markstep object to retrieve data from.
     * @return array The array containing the submitted data.
     */
    public static function get_annotation_stepdata($markstep) {
        $submitteddata = [];
        if ($markstep->get_state() != question_state::$unprocessed) {
            // So that the teacher's last manual comment is shown to students.
            $submitteddata["-maxmark"] = $markstep->get_qt_var("-maxmark");
            $submitteddata["-mark"] = $markstep->get_qt_var("-mark");
            $submitteddata["-commentformat"] = $markstep->get_qt_var("-commentformat");
            $submitteddata["-comment"] = $markstep->get_qt_var("-comment");
        } else {
            // So that the annotated step comment does not get revealed to students.
            $submitteddata["-comment"] = get_string('annotationstep_default_comment', 'qtype_essayannotate');
            $submitteddata["-mark"] = '';
        }
        return $submitteddata;
    }

    /**
     * Get the maximum number of bytes allowed for file uploads, considering various limits.
     * @return int The maximum number of bytes allowed for file uploads.
     */
    public static function get_max_bytes() {
        global $CFG;
        $maxupload = (int)(ini_get('upload_max_filesize'));
        $maxpost = (int)(ini_get('post_max_size'));
        $memorylimit = (int)(ini_get('memory_limit'));
        $maxmb = 0;

        // If memory limit is -1, there is no limit
        if ($memorylimit != -1) {
            $maxmb = min($maxupload, $maxpost, $memorylimit);
        } else {
            $maxmb = min($maxupload, $maxpost);
        }

        $maxbytes = $maxmb * 1024 * 1024;

        $mdlmaxbytes = $CFG->maxbytes;
        if ($mdlmaxbytes > 0) {
            $maxbytes = min($maxbytes, $mdlmaxbytes);
        }
        return $maxbytes;
    }

    /**
     * Processes a question attempt step and saves it to the database.
     *
     * @param question_usage_by_activity $quba The question usage by activity object.
     * @param int $slot The slot where the question attempt is being made.
     * @param array $submitteddata The data submitted for the question attempt.
     */
    public static function add_question_attempt_step($quba, $slot, $submitteddata) {
        global $DB;
        $quba->process_action($slot, $submitteddata);

        // Saving the step to database.
        $transaction = $DB->start_delegated_transaction();
        question_engine::save_questions_usage_by_activity($quba);
        $transaction->allow_commit();
    }
}
