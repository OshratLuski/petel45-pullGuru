<?php
// This file is part of Stack - http://stack.maths.ed.ac.uk/
//
// Stack is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Stack is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Stack.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Duplicate a question and optionally copy it into a new question category.
 *
 * @package     moodlecore
 * @subpackage  questionbank
 * @author      Nadav Kavalerchik <nadav.kavalerchik@weizmann.ac.il>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');

require_once($CFG->libdir . '/questionlib.php');
require_once($CFG->dirroot . '/question/format/xml/format.php');

/**
 * Duplicate a question and optionally copy it into a new question category.
 *
 * Based on XML question export/import and export single question
 * MDL-63738 https://github.com/moodle/moodle/commit/08a3564f93205cca22367819b2750a01445bd145
 *
 * @param int $questionid Question id.
 * @param int $intoquestioncategory Destination question category id.
 *
 */
function question_duplicate_single_question($questionid, $cmid, $intoquestioncategory = null) {
    global $DB, $CFG, $PAGE;

    $questiondata = question_bank::load_question_data($questionid);

    $cm = get_coursemodule_from_id(null, $cmid);
    if (!$course = $DB->get_record('course', array('id' => $cm->course))) {
        throw new \moodle_exception('missingcourseid', 'question');
    }

    $thiscontext = context_module::instance($cmid);
    $PAGE->set_context($thiscontext);
    // Load the necessary data.
    $contexts = new \core_question\local\bank\question_edit_contexts($thiscontext);

    // Check permissions.
    question_require_capability_on($questiondata, 'view');

    // Update name with prefix.
    $questiondata->name = get_string('duplicate') . ' ' . $questiondata->name;

    // Set up the export format.
    $qformat = new qformat_xml();
    $filename = question_default_export_filename($course, $questiondata) .
            $qformat->export_file_extension();
    $qformat->setContexts($contexts->having_one_edit_tab_cap('export'));
    $qformat->setCourse($course);
    $qformat->setQuestions([$questiondata]);
    $qformat->setCattofile(false);
    $qformat->setContexttofile(false);

    // Do the export.
    if (!$qformat->exportpreprocess()) {
        throw new \moodle_exception('error_exportpreprocess', 'question');
    }
    if (!$content = $qformat->exportprocess()) {
        throw new \moodle_exception('error_exportprocess', 'question');
    }
    // Download XML file.
    //send_file($content, $filename, 0, 0, true, true, $qformat->mime_type());

    // Save question data as a temporary XML file.
    $importfile = "{$CFG->tempdir}/questionimport/{$filename}";
    make_temp_directory('questionimport');
    $ok = file_put_contents($importfile, $content);

    // TODO: Figure out a way to skip saving and opening a question data XML file.

    // Import temporary XML question file.

    $formatfile = $CFG->dirroot . '/question/format/xml/format.php';
    if (!is_readable($formatfile)) {
        throw new moodle_exception('formatnotfound', 'question', '', 'xml');
    }
    require_once($formatfile);

    $classname = 'qformat_xml';
    $qformat = new $classname();

    if ($intoquestioncategory) {
        $destinationcategory = $intoquestioncategory;
    } else {
        $destinationcategory = $questiondata->category;
    }
    if (!$category = $DB->get_record('question_categories', array('id' => $destinationcategory))) {
        throw new \moodle_exception('missingquestioncategoryid', 'question');
    }

    $qformat->setCategory($category);
    $qformat->setContexts($contexts->having_one_edit_tab_cap('import'));
    $qformat->setCourse($course);
    $qformat->setFilename($importfile);
    $qformat->setRealfilename($filename);
    //$qformat->setMatchgrades($form->matchgrades);
    //$qformat->setCatfromfile(!empty($form->catfromfile));
    //$qformat->setContextfromfile(!empty($form->contextfromfile));
    //$qformat->setStoponerror($form->stoponerror);

    // Suppress redundant output.
    ob_start();

    // Do anything before that we need to
    if (!$qformat->importpreprocess()) {
        throw new \moodle_exception('cannotimport', 'question');
    }

    // Process the uploaded file
    if (!$qformat->importprocess($category)) {
        throw new \moodle_exception('cannotimport', 'question');
    }

    // In case anything needs to be done after
    if (!$qformat->importpostprocess()) {
        throw new \moodle_exception('cannotimport', 'question');
    }

    $value = ob_get_contents();
    ob_end_clean();

    // Remove temp XML question file.
    unlink($importfile);

    // New Question ID.
    $targetquestionid = $qformat->questionids[0];

    // Copy old stamp and history.
    $obj = $DB->get_record('question', ['id' => $questionid]);
    $objnew = $DB->get_record('question', ['id' => $targetquestionid]);
    $objnew->stamp = $obj->stamp;
    $DB->update_record('question', $objnew);

    // Copy unit for numerical.
    $newquestiondata = question_bank::load_question_data($targetquestionid);
    if ($newquestiondata->qtype == 'numerical') {
        $units = [];
        foreach ($questiondata->options->answers as $q) {
            $units[] = $q->unitvalue;
        }

        $answerids = [];
        foreach ($newquestiondata->options->answers as $q) {
            $answerids[] = $q->id;
        }

        if (count($units) == count($answerids)) {
            foreach ($answerids as $key => $answerid) {
                if ($obj = $DB->get_record('question_numerical', ['question' => $targetquestionid, 'answer' => $answerid])) {
                    $obj->unit = $units[$key];
                    $DB->update_record('question_numerical', $obj);
                }
            }
        }
    }

    //complete duplication of specific questions configurations
    \community_sharequestion\duplicate_question::after_duplicate_question($questiondata, $newquestiondata);

    // Copy question dataset.
    foreach ($DB->get_records('question_datasets', ['question' => $questionid]) as $dataset) {
        foreach ($DB->get_records('question_dataset_definitions', ['id' => $dataset->datasetdefinition]) as $definition) {
            $definitionid = $definition->id;

            unset($definition->id);
            $newdefinitionid = $DB->insert_record('question_dataset_definitions', $definition);

            foreach ($DB->get_records('question_dataset_items', ['definition' => $definitionid]) as $item) {
                unset($item->id);
                $item->definition = $newdefinitionid;
                $DB->insert_record('question_dataset_items', $item);
            }
        }

        if (isset($newdefinitionid) && $newdefinitionid > 0) {
            unset($dataset->id);
            $dataset->datasetdefinition = $newdefinitionid;
            $dataset->question = $targetquestionid;

            $DB->insert_record('question_datasets', $dataset);
        }
    }


    // Copy all metadata.
    \local_metadata\mcontext::question()->copy_all_metadata($questionid, $targetquestionid);

    // Save qid metadata.
    \local_metadata\mcontext::question()->save($targetquestionid, 'qid', $questionid);

    return $targetquestionid;
}

// Get the parameters from the URL.
$questionid = required_param('id', PARAM_INT);
$cmid = required_param('cmid', PARAM_INT);
$addonpage = optional_param('addonpage', 0, PARAM_INT);

// Security.
require_login();
require_sesskey();

// Test question_duplicate_single_question function.
$newquestionid = question_duplicate_single_question($questionid, $cmid);
$url = new \moodle_url('/mod/quiz/edit.php', ['addquestion' => $newquestionid, 'sesskey' => sesskey()
    , 'cmid' => $cmid, 'addonpage' => $addonpage]);

$PAGE->set_context(context_module::instance($cmid));
$PAGE->set_url($url);

redirect(new \moodle_url('/mod/quiz/edit.php', ['addquestion' => $newquestionid, 'sesskey' => sesskey()
    , 'cmid' => $cmid, 'addonpage' => $addonpage]));
