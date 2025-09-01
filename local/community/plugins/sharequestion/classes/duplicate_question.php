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
 * @package     community_sharequestion
 * @copyright  2018 Devlion <info@devlion.co>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace community_sharequestion;

use mod_quiz\quiz_settings;

/**
 * Local community event handler.
 */
class duplicate_question {

    public static function duplicate_single_question($questionid, $intoquestioncategory = null, $changename = true) {
        global $DB, $CFG;

        require_once($CFG->libdir . '/questionlib.php');
        require_once($CFG->dirroot . '/question/format/xml/format.php');

        $questiondata = \question_bank::load_question_data($questionid);

        $context = \context::instance_by_id($questiondata->contextid);
        switch ($context->contextlevel) {
            case CONTEXT_MODULE:
                $cmid = $context->instanceid;
                $cm = get_coursemodule_from_id(null, $cmid);

                if (!$course = $DB->get_record('course', array('id' => $cm->course))) {
                    throw new \moodle_exception('missingcourseid', 'question');
                }

                $thiscontext = \context_module::instance($cmid);
                break;
            case CONTEXT_COURSE:
                $courseid = $context->instanceid;

                $thiscontext = \context_course::instance($courseid);
                if (!$course = $DB->get_record('course', array('id' => $courseid))) {
                    throw new \moodle_exception('missingcourseid', 'question');
                }
                break;
            default:
                $courseid = SITEID;
        }

        // Load the necessary data.
        $contexts = new \core_question\local\bank\question_edit_contexts($thiscontext);

        // Check permissions.
        question_require_capability_on($questiondata, 'view');

        // Update name with prefix.
        if ($changename) {
            $questiondata->name = get_string('duplicate') . ' ' . $questiondata->name;
        }

        // Set up the export format.
        $qformat = new \qformat_xml();
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
            throw new \moodle_exception('formatnotfound', 'question', '', 'xml');
        }
        require_once($formatfile);

        $classname = 'qformat_xml';
        $qformat = new $classname();

        $qformat->set_display_progress(false);

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

        // Do anything before that we need to.
        if (!$qformat->importpreprocess()) {
            throw new \moodle_exception('cannotimport', 'question');
        }

        // Process the uploaded file.
        if (!$qformat->importprocess()) {
            throw new \moodle_exception('cannotimport', 'question');
        }

        // In case anything needs to be done after.
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
        $newquestiondata = \question_bank::load_question_data($targetquestionid);
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

        // Complete duplication of specific questions configurations.
        self::after_duplicate_question($questiondata, $newquestiondata);

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

        // Save qid metadata.
        \local_metadata\mcontext::question()->save($targetquestionid, 'qid', $questionid);

        // New Question ID.
        return $targetquestionid;
    }

    public static function add_question_to_quiz($cmid, $addquestion) {
        global $DB, $CFG;

        require_once($CFG->dirroot . '/mod/quiz/locallib.php');
        require_once($CFG->dirroot . '/question/editlib.php');

        $cm = $DB->get_record('course_modules', ['id' => $cmid]);
        if (empty($cm)) {
            return false;
        }

        $mod = $DB->get_record('modules', ['id' => $cm->module]);
        if (empty($mod) || $mod->name != 'quiz') {
            return false;
        }

        $quiz = $DB->get_record($mod->name, array('id' => $cm->instance));
        if (empty($quiz)) {
            return false;
        }

        // Get the course object and related bits.
        $course = $DB->get_record('course', array('id' => $quiz->course), '*', MUST_EXIST);
        $quizobj = new quiz_settings($quiz, $cm, $course);
        $structure = $quizobj->get_structure();

        // Add a single question to the current quiz.
        $structure->check_can_be_edited();
        quiz_require_question_use($addquestion);
        $addonpage = 1;
        quiz_add_quiz_question($addquestion, $quiz, $addonpage);
        quiz_delete_previews($quiz);
        quiz_settings::create($quiz->id)->get_grade_calculator()->recompute_quiz_sumgrades();

        return true;
    }

    public static function copy_question_metadata($sourcequestionid, $targetquestionid) {

        // Copy all metadata.
        \local_metadata\mcontext::question()->copy_all_metadata($sourcequestionid, $targetquestionid);

        // Save qid.
        \local_metadata\mcontext::question()->save($targetquestionid, 'qid', $sourcequestionid);

        // Save qid history.
        \local_metadata\mcontext::question()->save($targetquestionid, 'qidhistory', $sourcequestionid);

        return true;
    }

    public static function add_metadata_to_question($targetquestionid, $sourcequestionid, $data = []) {
        global $DB;

        $sql = "
            SELECT lmf.id, lmf.shortname
            FROM {local_metadata_field} lmf 
            LEFT JOIN {local_metadata_category} lmc ON (lmf.categoryid = lmc.id)
            WHERE lmc.contextlevel = ?        
        ";
        $fieldids = $DB->get_records_sql($sql, [\local_metadata\mcontext::question()->get_contextid()]);

        foreach ($fieldids as $field) {
            foreach ($data as $shortname => $value) {
                if ($field->shortname == $shortname) {
                    if ($existing = $DB->get_record('local_metadata', ['instanceid' => $targetquestionid, 'fieldid' => $field->id])) {
                        $existing->data = $value;
                        $existing->dataformat = 0;
                        $DB->update_record('local_metadata', $existing);
                    } else {
                        $obj = new \StdClass();
                        $obj->instanceid = $targetquestionid;
                        $obj->fieldid = $field->id;
                        $obj->data = $value;
                        $obj->dataformat = 0;
                        $DB->insert_record('local_metadata', $obj);
                    }
                }
            }
        }

        // Save qid.
        \local_metadata\mcontext::question()->save($targetquestionid, 'qid', $sourcequestionid);

        return false;
    }

    public static function after_duplicate_question($q, $neq) {

        // Copy all poodllrecording recordings for qtype_poodllrecording.
        if ($q->qtype == 'poodllrecording') {
            $fs = get_file_storage();
            $files = $fs->get_area_files($q->contextid, \qtype_poodllrecording\constants::M_COMP,
                    \qtype_poodllrecording\constants::FILEAREA_QRESOURCE,
                    $q->id);
            foreach ($files as $file) {
                if ($file->get_filename() != '.') {
                    $filerecord = array(
                            'contextid' => $neq->contextid,
                            'component' => \qtype_poodllrecording\constants::M_COMP,
                            'filearea' => \qtype_poodllrecording\constants::FILEAREA_QRESOURCE,
                            'itemid' => $neq->id,
                            'timemodified' => time()
                    );
                    $fs->create_file_from_storedfile($filerecord, $file);
                }
            }
        }

        // PTL_7328 backup & restore custom parameter (will work just on the same instance).
        if ($q->qtype == 'essay') {
            $allowcheck = get_config('qtype_essay', 'allowcheck_' . $q->id);
            if ($allowcheck) {
                set_config('allowcheck_' . $neq->id, $allowcheck, 'qtype_essay');
            }
        }

        // EC-260 Copy question competencies.
        self::copy_question_competencies($q, $neq);

    }

    /**
     * Copy question competencies from one question to another.
     *
     * @param object $q The source question object.
     * @param object $neq The target question object.
     */
    public static function copy_question_competencies($q, $neq) {
        global $DB, $USER;

        $sourcecompetencies = $DB->get_records('competency_questioncomp', ['qid' => $q->id]);

        foreach ($sourcecompetencies as $key => $sourcecompetency) {
            $sourcecompetency->qid = $neq->id;
            $sourcecompetency->userid = $USER->id;
            $sourcecompetency->timecreated = time();
            $sourcecompetency->timemodified = $sourcecompetency->timecreated;
            $sourcecompetencies[$key] = $sourcecompetency;

            $existing = $DB->get_record('competency_questioncomp', [
                'competencyid' => $sourcecompetency->competencyid,
                'qid' => $neq->id,
            ]);

            if ($existing) {
                $sourcecompetency->id = $existing->id;
                $DB->update_record('competency_questioncomp', $sourcecompetency);
            } else {
                $DB->insert_record('competency_questioncomp', $sourcecompetency);
            }
        }
    }

}
