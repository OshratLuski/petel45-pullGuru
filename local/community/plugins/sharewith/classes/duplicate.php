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
 * External course API
 *
 * @package    core_course
 * @category   external
 * @copyright  2009 Petr Skodak
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use mod_quiz\quiz_settings;

defined('MOODLE_INTERNAL') || die;

require_once($CFG->libdir . "/externallib.php");
require_once($CFG->libdir . '/questionlib.php');
require_once($CFG->dirroot . '/question/format/xml/format.php');

/**
 * Course external functions
 *
 * @package    core_course
 * @category   external
 * @copyright  2011 Jerome Mouneyrac
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @since Moodle 2.2
 */
class duplicate extends \external_api {

    private $modulemetadata;
    private $glossarycopyusers;
    private $databasecopyusers;
    private $copytype;
    private $objduplicate;

    public function __construct() {
        global $CFG;

        $this->modulemetadata = 70;
        $this->glossarycopyusers = false;
        $this->databasecopyusers = false;
        $this->copytype = '';
        $this->objduplicate = null;

    }

    public function enable_glossary_copy_users() {
        $this->glossarycopyusers = true;
    }

    public function enable_database_copy_users() {
        $this->databasecopyusers = true;
    }

    public function set_copy_type($str) {
        $this->copytype = $str;
    }

    public function get_copy_type() {
        return $this->copytype;
    }

    public function set_obj_duplicate($obj) {
        $this->objduplicate = $obj;
    }

    public function get_obj_duplicate() {
        return $this->objduplicate;
    }

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     * @since Moodle 2.3
     */
    public static function duplicate_course_parameters() {
        return new external_function_parameters(
                array(
                        'courseid' => new external_value(PARAM_INT, 'course to duplicate id'),
                        'fullname' => new external_value(PARAM_TEXT, 'duplicated course full name'),
                        'shortname' => new external_value(PARAM_TEXT, 'duplicated course short name'),
                        'categoryid' => new external_value(PARAM_INT, 'duplicated course category parent'),
                        'visible' => new external_value(PARAM_INT, 'duplicated course visible, default to yes', VALUE_DEFAULT, 1),
                        'options' => new external_multiple_structure(
                                new external_single_structure(
                                        array(
                                                'name' => new external_value(PARAM_ALPHAEXT, 'The backup option name:
                                            "activities" (int) Include course activites (default to 1 that is equal to yes),
                                            "blocks" (int) Include course blocks (default to 1 that is equal to yes),
                                            "filters" (int) Include course filters  (default to 1 that is equal to yes),
                                            "users" (int) Include users (default to 0 that is equal to no),
                                            "enrolments" (int) Include enrolment methods (default to 1 - restore only with users),
                                            "role_assignments" (int) Include role assignments  (default to 0 that is equal to no),
                                            "comments" (int) Include user comments  (default to 0 that is equal to no),
                                            "userscompletion" (int) Include user course completion information  (default to 0 that is equal to no),
                                            "logs" (int) Include course logs  (default to 0 that is equal to no),
                                            "grade_histories" (int) Include histories  (default to 0 that is equal to no)'
                                                ),
                                                'value' => new external_value(PARAM_RAW,
                                                        'the value for the option 1 (yes) or 0 (no)'
                                                )
                                        )
                                ), VALUE_DEFAULT, array()
                        ),
                )
        );
    }

    /**
     * Duplicate a course
     *
     * @param int $courseid
     * @param string $fullname Duplicated course fullname
     * @param string $shortname Duplicated course shortname
     * @param int $categoryid Duplicated course parent category id
     * @param int $visible Duplicated course availability
     * @param array $options List of backup options
     * @return array New course info
     * @since Moodle 2.3
     */
    public static function duplicate_course($userid, $courseid, $fullname, $shortname, $categoryid, $visible = 1,
            $options = array()) {
        global $CFG, $DB;

        require_once($CFG->dirroot . '/backup/util/includes/backup_includes.php');
        require_once($CFG->dirroot . '/backup/util/includes/restore_includes.php');

        // Parameter validation.
        $params = self::validate_parameters(
                self::duplicate_course_parameters(),
                array(
                        'courseid' => $courseid,
                        'fullname' => $fullname,
                        'shortname' => $shortname,
                        'categoryid' => $categoryid,
                        'visible' => $visible,
                        'options' => $options
                )
        );

        // Context validation.
        if (!($course = $DB->get_record('course', array('id' => $params['courseid'])))) {
            throw new moodle_exception('invalidcourseid', 'error');
        }

        // Category where duplicated course is going to be created.
        $categorycontext = context_coursecat::instance($params['categoryid']);
        self::validate_context($categorycontext);

        // Course to be duplicated.
        $coursecontext = context_course::instance($course->id);
        self::validate_context($coursecontext);

        //'enrolments' => backup::ENROL_WITHUSERS - default
        //ENROL_NEVER - Backup a course with enrolment methods and restore it without user data
        //ENROL_WITHUSERS - Backup a course with enrolment methods and restore it with user data with enrolment methods
        //ENROL_ALWAYS - Backup a course with enrolment methods and restore it without user data with enrolment methods

        $backupdefaults = array(
                'activities' => 1,
                'blocks' => 1,
                'filters' => 1,
                'users' => 0,
                'enrolments' => backup::ENROL_NEVER,
                'role_assignments' => 0,
                'comments' => 0,
                'userscompletion' => 0,
                'logs' => 0,
                'grade_histories' => 0
        );

        $backupsettings = array();
        // Check for backup and restore options.
        if (!empty($params['options'])) {
            foreach ($params['options'] as $option) {

                // Strict check for a correct value (allways 1 or 0, true or false).
                $value = clean_param($option['value'], PARAM_INT);

                if ($value !== 0 && $value !== 1) {
                    throw new moodle_exception('invalidextparam', 'webservice', '', $option['name']);
                }

                if (!isset($backupdefaults[$option['name']])) {
                    throw new moodle_exception('invalidextparam', 'webservice', '', $option['name']);
                }

                $backupsettings[$option['name']] = $value;
            }
        }

        // Check if the shortname is used.
        if ($foundcourses = $DB->get_records('course', array('shortname' => $shortname))) {
            foreach ($foundcourses as $foundcourse) {
                $foundcoursenames[] = $foundcourse->fullname;
            }

            $foundcoursenamestring = implode(',', $foundcoursenames);
            throw new moodle_exception('shortnametaken', '', '', $foundcoursenamestring);
        }

        // Backup the course.

        $bc = new backup_controller(backup::TYPE_1COURSE, $course->id, backup::FORMAT_MOODLE,
                backup::INTERACTIVE_NO, backup::MODE_SAMESITE, $userid);

        foreach ($backupsettings as $name => $value) {
            if ($setting = $bc->get_plan()->get_setting($name)) {
                $bc->get_plan()->get_setting($name)->set_value($value);
            }
        }

        $backupid = $bc->get_backupid();
        $backupbasepath = $bc->get_plan()->get_basepath();

        $bc->execute_plan();
        $results = $bc->get_results();
        $file = $results['backup_destination'];

        $bc->destroy();

        // Restore the backup immediately.

        // Check if we need to unzip the file because the backup temp dir does not contains backup files.
        if (!file_exists($backupbasepath . "/moodle_backup.xml")) {
            $file->extract_to_pathname(get_file_packer('application/vnd.moodle.backup'), $backupbasepath);
        }

        // Create new course.
        $newcourseid = restore_dbops::create_new_course($params['fullname'], $params['shortname'], $params['categoryid']);

        $rc = new restore_controller($backupid, $newcourseid,
                backup::INTERACTIVE_NO, backup::MODE_HUB, $userid, backup::TARGET_NEW_COURSE);

        foreach ($backupsettings as $name => $value) {
            $setting = $rc->get_plan()->get_setting($name);
            if ($setting->get_status() == backup_setting::NOT_LOCKED) {
                $setting->set_value($value);
            }
        }

        if (!$rc->execute_precheck()) {
            $precheckresults = $rc->get_precheck_results();
            if (is_array($precheckresults) && !empty($precheckresults['errors'])) {
                if (empty($CFG->keeptempdirectoriesonbackup)) {
                    fulldelete($backupbasepath);
                }

                $errorinfo = '';
                foreach ($precheckresults['errors'] as $error) {
                    $errorinfo .= $error;
                }

                if (array_key_exists('warnings', $precheckresults)) {
                    foreach ($precheckresults['warnings'] as $warning) {
                        $errorinfo .= $warning;
                    }
                }

                throw new moodle_exception('backupprecheckerrors', 'webservice', '', $errorinfo);
            }
        }

        $rc->execute_plan();
        $rc->destroy();

        $course = $DB->get_record('course', array('id' => $newcourseid), '*', MUST_EXIST);
        $course->fullname = $params['fullname'];
        $course->shortname = $params['shortname'];
        $course->visible = $params['visible'];

        // Set shortname and fullname back.
        $DB->update_record('course', $course);

        if (empty($CFG->keeptempdirectoriesonbackup)) {
            fulldelete($backupbasepath);
        }

        // Delete the course backup file created by this WebService. Originally located in the course backups area.
        $file->delete();

        // Copy BADGES from source course.
        $newcourseid = $course->id;

        $context = context_course::instance($courseid);
        $newcontext = context_course::instance($newcourseid);
        $badges = $DB->get_records('badge', array('courseid' => $courseid));
        foreach ($badges as $badge) {
            $newbadge = clone $badge;

            // Insert new badge.
            unset($newbadge->id);
            $newbadge->courseid = $newcourseid;
            $newbadgeid = $DB->insert_record('badge', $newbadge);

            // Copy badge file.
            $fs = get_file_storage();
            $files = $fs->get_area_files($context->id, 'badges', 'badgeimage', $badge->id);

            // Create files.
            foreach ($files as $f) {
                if ($f->get_filesize() != 0 || $f->get_filename() != '.') {
                    $fileinfo = array(
                            'contextid' => $newcontext->id,
                            'component' => $f->get_component(),
                            'filearea' => $f->get_filearea(),
                            'itemid' => $newbadgeid,
                            'filepath' => $f->get_filepath(),
                            'filename' => $f->get_filename()
                    );

                    // Save file.
                    $fs->create_file_from_string($fileinfo, $f->get_content());
                }
            }

            $criterias = $DB->get_records('badge_criteria', array('badgeid' => $badge->id));
            foreach ($criterias as $criteria) {
                $newcriteria = clone $criteria;

                // Insert new criteria.
                unset($newcriteria->id);
                $newcriteria->badgeid = $newbadgeid;
                $newcriteriaid = $DB->insert_record('badge_criteria', $newcriteria);

                $criteriaparams = $DB->get_records('badge_criteria_param', array('critid' => $criteria->id));
                foreach ($criteriaparams as $criteriaparam) {
                    $newcriteriaparam = clone $criteriaparam;

                    // Insert new criteria param.
                    unset($newcriteriaparam->id);
                    $newcriteriaparam->critid = $newcriteriaid;

                    if ($newcriteriaparam->name == 'course_' . $courseid) {
                        $newcriteriaparam->name = 'course_' . $newcourseid;
                        $newcriteriaparam->value = $newcourseid;
                    }

                    $newcriteriaparamid = $DB->insert_record('badge_criteria_param', $newcriteriaparam);
                }
            }
        }

        return array('id' => $course->id, 'shortname' => $course->shortname);
    }

    /**
     * Duplicates activity
     *
     * @param int $sourceactivityid source
     * @param int $courseid target
     * @param int $sectionid target
     * @return cm_info|null cminfo object if we sucessfully duplicated the mod and found the new cm.
     */
    public function duplicate_activity_source($sourceactivityid, $courseid, $sectionid, &$newactivityid) {
        global $USER, $DB, $CFG;
        require_once($CFG->dirroot . '/backup/util/includes/backup_includes.php');
        require_once($CFG->dirroot . '/backup/util/includes/restore_includes.php');

        // Copy competencies to target course.
        \community_sharewith\funcs::copy_competencies($sourceactivityid, $courseid);

        $bc = new \backup_controller(backup::TYPE_1ACTIVITY, $sourceactivityid, backup::FORMAT_MOODLE, backup::INTERACTIVE_NO,
                backup::MODE_IMPORT, $USER->id);
        $backupid = $bc->get_backupid();
        $backupbasepath = $bc->get_plan()->get_basepath();
        $bc->execute_plan();
        $bc->destroy();

        $rc = new \restore_controller($backupid, $courseid, backup::INTERACTIVE_NO, backup::MODE_IMPORT, $USER->id,
                backup::TARGET_CURRENT_ADDING);
        $cmcontext = context_module::instance($sourceactivityid);
        if (!$rc->execute_precheck()) {
            $precheckresults = $rc->get_precheck_results();
            if (is_array($precheckresults) && !empty($precheckresults['errors'])) {
                if (empty($CFG->keeptempdirectoriesonbackup)) {
                    fulldelete($backupbasepath);
                }
            }
        }

        $rc->execute_plan();

        $newcmid = null;
        $tasks = $rc->get_plan()->get_tasks();
        foreach ($tasks as $task) {
            if (is_subclass_of($task, 'restore_activity_task')) {
                if ($task->get_old_contextid() == $cmcontext->id) {
                    $newcmid = $task->get_moduleid();
                    break;
                }
            }
        }

        if ($newcmid) {
            $course = get_course($courseid);
            $info = get_fast_modinfo($course);
            $newcm = $info->get_cm($newcmid);
            $section = $DB->get_record('course_sections', array('id' => $sectionid, 'course' => $courseid));
            moveto_module($newcm, $section);
        }

        // Copy users for mod_database, mod_glossary, mod_game.
        if ($this->databasecopyusers) {
            \community_sharewith\funcs::copy_users_mod_database($sourceactivityid, $newcmid);
        }

        // PTL-1253 Multiple backup and restores (nadavkav).
        if ($this->glossarycopyusers) {
            \community_sharewith\funcs::copy_users_mod_glossary($sourceactivityid, $newcmid);
        }

        \community_sharewith\funcs::copy_entries_mod_glossary($sourceactivityid, $newcmid);

        $objgame = $this->copy_mod_game($sourceactivityid, $newcm, $courseid, $sectionid);
        if (!empty($objgame)) {
            $newactivityid[] = $objgame;
        }

        // PTL-2115 Insert history_cmid and history_authors.
        \community_sharewith\funcs::update_meta_history($sourceactivityid, $newcmid, $this->objduplicate);

        $rc->destroy();
        if (empty($CFG->keeptempdirectoriesonbackup)) {
            fulldelete($backupbasepath);
        }

        // EC-260 Copy question competencies.
        $targetactivityid = $newcmid;

        $isquiz = duplicate::check_object_is_module($sourceactivityid, 'quiz');
        if ($isquiz) {
            list($sourcecourse, $sourcecm) = get_course_and_cm_from_cmid($sourceactivityid);
            $sourcequizobj = quiz_settings::create($sourcecm->instance);

            $sourcequizobj->preload_questions();
            $sourcequizobj->load_questions();
            $sourcequizquestions = $sourcequizobj->get_questions();

            $isquiz_target = duplicate::check_object_is_module($targetactivityid, 'quiz');
            if ($isquiz_target) {
                list($targetcourse, $targetcm) = get_course_and_cm_from_cmid($targetactivityid);
                $targetquizobj = quiz_settings::create($targetcm->instance);
                $targetquizobj->preload_questions();
                $targetquizobj->load_questions();
                $targetquizquestions = $targetquizobj->get_questions();

                foreach ($sourcequizquestions as $sourceqkey => $sourcequestion) {
                    $targetquestion = duplicate::find_corresponding_target_question($sourcequestion, $targetquizquestions);

                    if ($targetquestion) {
                        community_sharequestion\duplicate_question::copy_question_competencies($sourcequestion, $targetquestion);

                        // EC-219 Check question for default category.
                        $questioncategoryok = duplicate::check_question_for_default_category($targetquestion, $targetquizobj);

                        // EC-219 Fix question if not in default category.
                        //PTL-12993 - random question is always not in default category, points to other category
                        if (!$questioncategoryok && $targetquestion->qtype != 'random') {
                            $newquestionid = duplicate::fix_question_default_category($targetquestion, $targetquizobj);
                        }

                    }
                }
            }
        }

        // Rebuild cm and course cache.
        \course_modinfo::purge_course_module_cache($newcm->course, $newcm->id);
        rebuild_course_cache($newcm->course, true, true);

        $newactivityid[] = $newcm;

        return isset($newcmid) ? $newcmid : null;
    }

    function find_corresponding_target_question($sourcequestion, $targetquizquestions) {
        foreach ($targetquizquestions as $targetqkey => $targetquestion) {
            if ($sourcequestion->slot == $targetquestion->slot) {
                return $targetquestion;
            }
        }
        return null;
    }

    // EC-219
    public static function check_question_for_default_category($question, $quizobj) {
        global $DB;
        $response = false;

        $quizcontextid = context_module::instance($quizobj->get_cm()->id)->id;

        $categorydefault = $DB->get_records('question_categories', array('contextid' => $quizcontextid), 'sortorder DESC');

        if (key_exists($question->category, $categorydefault)) {
            $response = true;
        }

        return $response;
    }

    // EC-219
    public static function fix_question_default_category($question, $quizobj) {
        global $DB, $PAGE;
        $response = false;

        $contextmodule = context_module::instance($quizobj->get_cm()->id);
        $categorydefault = question_make_default_categories([$contextmodule]);
        $newquestionid = duplicate::question_duplicate_single_question($question->id, $quizobj->get_cm()->id, $categorydefault->id);
        $addonpage = 0;
        $PAGE->set_context($contextmodule);
        $structure = $quizobj->get_structure();
        $quiz = $quizobj->get_quiz();

        // Add a single question to the current quiz.
        $structure->check_can_be_edited();
        quiz_require_question_use($newquestionid);
        quiz_add_quiz_question($newquestionid, $quiz, $addonpage);
        quiz_delete_previews($quiz);
        quiz_settings::create($quiz->id)->get_grade_calculator()->recompute_quiz_sumgrades();

        // Flip slots.
        $sourceactivityid = $quizobj->get_cm()->id;
        list($sourcecourse, $sourcecm) = get_course_and_cm_from_cmid($sourceactivityid);
        $sourcequizobj = quiz_settings::create($sourcecm->instance);
        $sourcequizobj->preload_questions();
        $sourcequizobj->load_questions();
        $newslot = $sourcequizobj->get_question($newquestionid);
        $oldslot = $sourcequizobj->get_question($question->id);

        // Remove question_references.
        $DB->delete_records('question_references', ['itemid' => $oldslot->slotid]);

        // Flip slots quiz_slots.
        $DB->delete_records('quiz_slots', ['id' => $oldslot->slotid]);

        $DB->execute("
                        UPDATE {quiz_slots}
                        SET slot = ? , page = ?
                        WHERE id = ?
            ", array($oldslot->slot, $oldslot->page, $newslot->slotid));

        quiz_delete_previews($quiz);
        quiz_settings::create($quiz->id)->get_grade_calculator()->recompute_quiz_sumgrades();
        $result = array('newsummarks' => quiz_format_grade($quiz, $quiz->sumgrades),
            'deleted' => true, 'newnumquestions' => $structure->get_question_count());

        return $response;
    }

    public static function question_duplicate_single_question($questionid, $cmid, $intoquestioncategory = null) {
        global $DB, $CFG, $PAGE;

        $questiondata = question_bank::load_question_data($questionid);

        $cm = get_coursemodule_from_id(null, $cmid);
        if (!$course = $DB->get_record('course', array('id' => $cm->course))) {
            throw new \moodle_exception('missingcourseid', 'question');
        }

        $thiscontext = context_module::instance($cmid);

        // Load the necessary data.
        $contexts = new \core_question\local\bank\question_edit_contexts($thiscontext);

        // Check permissions.
        question_require_capability_on($questiondata, 'view');

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
        require_once ($formatfile);

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

        // Save qid metadata.
        \local_metadata\mcontext::question()->save($targetquestionid, 'qid', $questionid);

        // Copy question files
        $fs = get_file_storage();
        $files = $fs->get_area_files($thiscontext->id, 'question', 'questiontext', $questionid);

        foreach ($files as $file) {
            if ($file->is_directory()) {
                continue;
            }

            $fileinfo = array(
                'contextid' => $thiscontext->id,
                'component' => 'question',
                'filearea' => 'questiontext',
                'itemid' => $targetquestionid,
                'filepath' => $file->get_filepath(),
                'filename' => $file->get_filename()
            );

            $fs->create_file_from_storedfile($fileinfo, $file);
        }

        return $targetquestionid;

    }

    public static function check_object_is_module($cmid, $module = '') {
        global $DB, $CFG;
        $response = false;

        if ($module == '') {
            return false;
        }

        $sql = "SELECT
                    cm.id,
                    m.name
                FROM
                    {course_modules} cm
                    LEFT JOIN {modules} m ON m.id = cm.module
                WHERE
                    cm.id = ?
                    AND m.name = ?";

        if ($ret = $DB->get_record_sql($sql, [$cmid, $module])) {
            $response = true;
        }

        return $response;
    }

    /**
     * Duplicates activity
     *
     * @param int $sourceactivityid source
     * @param int $courseid target
     * @param int $sectionid target
     * @return array
     */
    public function duplicate_activity($sourceactivityid, $courseid, $sectionid, &$newactivityid, $activitysequence = false) {
        global $DB;

        $newcmids = array();

        if (!empty($activitysequence) && is_array($activitysequence)) {
            $activitysequence = array_reverse($activitysequence);
            $chain = array();

            foreach ($activitysequence as $cmid) {
            $newcmid = $this->duplicate_activity_source($cmid, $courseid, $sectionid, $newactivityid);
                $newcmids[$cmid] = $newcmid;
                $chain[$cmid] = $newcmid;

                $newcm = $DB->get_record('course_modules', array('id' => $newcmid));
                $cm = $DB->get_record('course_modules', array('id' => $cmid));

                if (!empty($cm->availability)) {
                    $availability = json_decode($cm->availability);
                    foreach ($availability->c as $key => $item) {
                        if (array_key_exists($item->cm, $chain)) {
                            $availability->c[$key]->cm = (int) $chain[$item->cm];
                        } else {
                            unset($availability->c[$key]);
                            unset($availability->showc[$key]);
                        }
                    }
                    $availability->c = array_values($availability->c);
                    $availability->showc = array_values($availability->showc);
                    $newcm->availability = json_encode($availability);
                    $res = $DB->update_record('course_modules', $newcm);
                }

                // Rebuild cm cache.
                \course_modinfo::purge_course_module_cache($courseid, $newcm->id);
            }
        } else {
            $row = $DB->get_record('course_modules', array('id' => $sourceactivityid));
            $newcmids[$sourceactivityid] =
                    $this->duplicate_activity_source($sourceactivityid, $courseid, $sectionid, $newactivityid);
            $DB->update_record('course_modules', $row);

            // Rebuild cm cache.
            \course_modinfo::purge_course_module_cache($courseid, $sourceactivityid);
        }

        // Rebuild course cache.
        rebuild_course_cache($courseid, true, true);

        return $newcmids;
    }

    /**
     * Duplicates activity
     *
     * @param int $sourcesectionid source
     * @param int $courseid target
     * @return section_info|null section object if we sucessfully duplicated the section and found the new cm.
     */
    public function duplicate_section($sourcesectionid, $courseid) {
        global $DB, $CFG;

        require_once($CFG->dirroot . '/backup/util/includes/backup_includes.php');
        require_once($CFG->dirroot . '/backup/util/includes/restore_includes.php');

        $sourcesection = $DB->get_record('course_sections', array('id' => $sourcesectionid));
        $newsection = course_create_section($courseid, 0);

        // Add default name and summary for section.
        if ($sourcesection->name != null || $sourcesection->summary != null) {
            $newsection->name = $sourcesection->name;
            $newsection->summary = $sourcesection->summary;
            $DB->update_record('course_sections', $newsection);
        }

        // Copy files.
        $oldcontext = \context_course::instance($sourcesection->course);
        $newcontext = \context_course::instance($newsection->course);

        $fs = get_file_storage();
        $files = $fs->get_area_files($oldcontext->id, 'course', 'section', $sourcesection->id);

        // Create files.
        foreach ($files as $f) {
            if ($f->get_filesize() != 0 || $f->get_filename() != '.') {
                $fileinfo = array(
                        'contextid' => $newcontext->id,
                        'component' => $f->get_component(),
                        'filearea' => $f->get_filearea(),
                        'itemid' => $newsection->id,
                        'filepath' => $f->get_filepath(),
                        'filename' => $f->get_filename()
                );

                // Save file.
                $fs->create_file_from_string($fileinfo, $f->get_content());
            }
        }

        // Copy flexsections image.
        $files = $fs->get_area_files($oldcontext->id, 'format_flexsections', 'image', $sourcesection->id);
        foreach ($files as $f) {
            if ($f->get_filesize() != 0 || $f->get_filename() != '.') {
                $filename = str_replace(' ', '_', $f->get_filename());
                $fileinfo = array(
                    'contextid' => $newcontext->id,
                    'component' => $f->get_component(),
                    'filearea' => $f->get_filearea(),
                    'itemid' => $newsection->id,
                    'filepath' => $f->get_filepath(),
                    'filename' => $filename
                );

                // Save file.
                $fs->create_file_from_string($fileinfo, $f->get_content());
            }
        }

        $arrcmids = [];
        $activities = explode(',', $sourcesection->sequence);
        foreach ($activities as $key => $activity) {
            $row = $DB->get_record('course_modules', array('id' => $activity));
            if (!empty($row) && $row->deletioninprogress == 0) {
                $newactivities = array();
                $newcm = $this->duplicate_activity($activity, $courseid, $newsection->id, $newactivities);
                $DB->update_record('course_modules', $row);

                $arrcmids[$activity] = $newcm[$activity];
                \local_metadata\mcontext::module()->save($newcm[$activity], 'ID', $activity);
            }
        }

        // Mod learningmap.
        $this->copy_mod_learningmap_in_section($arrcmids, $courseid, $newsection->id);

        // Availability.
        foreach ($arrcmids as $oldcmid => $newcmid) {
            $rowold = $DB->get_record('course_modules', array('id' => $oldcmid));
            $rownew = $DB->get_record('course_modules', array('id' => $newcmid));

            if (!empty($rowold->availability) && $rownew) {
                $res = \community_sharewith\funcs::change_availability(json_decode($rowold->availability), $newcmid, $arrcmids);

                if ($res != null) {
                    $rownew->availability = json_encode($res, JSON_NUMERIC_CHECK);
                } else {
                    $rownew->availability = null;
                }

                $DB->update_record('course_modules', $rownew);
            }

            // Rebuild cm cache.
            \course_modinfo::purge_course_module_cache($courseid, $newcmid);
        }

        // Rebuild course cache.
        rebuild_course_cache($courseid, true, true);

        return isset($newsection) ? $newsection : null;
    }

    public function copy_mod_game($sourceactivityid, $targetactivity, $courseid, $sectionid) {
        global $DB;

        $sql = '
        SELECT m.name, cm.instance, cm.course
        FROM {course_modules} cm
        LEFT JOIN {modules} m ON(cm.module = m.id)
        WHERE cm.id = ?
    ';

        $source = $DB->get_record_sql($sql, array($sourceactivityid));

        if ($source->name == 'game') {

            if ($game = $DB->get_record('game', array('id' => $source->instance))) {

                if (in_array($game->sourcemodule, array('quiz', 'glossary'))) {
                    $cmactivity = $DB->get_record('course_modules', array('id' => $targetactivity->id));
                    $gameupdate = $DB->get_record('game', array('id' => $cmactivity->instance));
                }

                switch ($game->sourcemodule) {
                    case 'quiz':
                        $quiz = $DB->get_record('quiz', array('id' => $game->quizid));

                        if (!empty($quiz)) {
                            $cm = $DB->get_record('course_modules', array('course' => $quiz->course, 'instance' => $quiz->id));

                            $newactivities = array();
                            $activitysequence = false;

                            // PTL-1253.
                            //$availability = $this->get_activities_chain($cm->id, $cm->course);
                            //if (count($availability)) {
                            //    $activitysequence = array_column($availability, 'id');
                            //}
                            $newactivity =
                                    $this->duplicate_activity($cm->id, $courseid, $sectionid, $newactivities, $activitysequence);

                            $newcm = $DB->get_record('course_modules', array('id' => end($newactivity)));
                            $gameupdate->quizid = $newcm->instance;
                            $DB->update_record('game', $gameupdate);

                            // Copy availability.
                            // PTL-1253.
                            //$replace1 = array_keys($newactivity);
                            //$replace2 = array_values($newactivity);
                            //$newcm->availability = str_replace($replace1, $replace2, $cm->availability);
                            $newcm->availability = '';

                            $DB->update_record('course_modules', $newcm);

                            if ($this->copytype == 'banksharing') {
                                \community_sharewith\funcs::update_meta_id($newactivity);
                            }

                            return $newactivity;
                        } else {
                            return null;
                        }
                        break;

                    case 'glossary':
                        $glossary = $DB->get_record('glossary', array('id' => $game->glossaryid));

                        if (!empty($glossary) && $glossary->course == $source->course) {
                            $cm = $DB->get_record('course_modules',
                                    array('course' => $glossary->course, 'instance' => $glossary->id));

                            $this->enable_glossary_copy_users();

                            $newactivities = array();
                            $activitysequence = false;

                            // PTL-1253.
                            //$availability = $this->get_activities_chain($cm->id, $cm->course);
                            //if (count($availability)) {
                            //    $activitysequence = array_column($availability, 'id');
                            //}

                            $newactivity =
                                    $this->duplicate_activity($cm->id, $courseid, $sectionid, $newactivities, $activitysequence);

                            $newcm = $DB->get_record('course_modules', array('id' => end($newactivity)));
                            $gameupdate->glossaryid = $newcm->instance;
                            $DB->update_record('game', $gameupdate);

                            // Copy availability.
                            // PTL-1253.
                            //$replace1 = array_keys($newactivity);
                            //$replace2 = array_values($newactivity);
                            //$newcm->availability = str_replace($replace1, $replace2, $cm->availability);
                            $newcm->availability = '';

                            $DB->update_record('course_modules', $newcm);

                            if ($this->copytype == 'banksharing') {
                                \community_sharewith\funcs::update_meta_id(array('id' => end($newactivity)));
                            }

                            return $newactivity;
                        } else {
                            return null;
                        }
                        break;
                }
            }
        }

        return null;
    }

    public function get_types_content_metadata($shortname) {
        global $DB;

        $obj = $DB->get_record_sql("SELECT * FROM {local_metadata_field} WHERE contextlevel=? AND shortname=?",
                [$this->modulemetadata, $shortname]);

        $res = preg_split('/\R/', $obj->param1);
        $res = array_unique($res);

        $result = array();
        if (!empty($res)) {
            $countchecked = 0;
            foreach ($res as $str) {
                $arr = explode('|', $str);

                if (isset($arr[1]) && !empty($arr[1])) {
                    $icon = 'involve__button-image--' . $arr[1];
                } else {
                    $icon = '';
                }

                $checked = ($countchecked == 0) ? 'checked' : '';

                $result[] = array(
                        'metadata_name' => $arr[0],
                        'metadata_icon' => $icon,
                        'metadata_value' => $str,
                        'metadata_checked' => $checked
                );

                $countchecked++;
            }
        }

        return $result;
    }

    public function get_activities_chain($chain, $courseid) {
        global $CFG, $DB;
        require_once($CFG->dirroot . "/course/lib.php");

        if (!is_array($chain)) {
            $tempmod = new Stdclass();
            $tempmod->id = $chain;
            $chain = array($tempmod);
        }

        $mod = end($chain);
        $cms = get_fast_modinfo($courseid);
        $cminfo = $cms->get_cm($mod->id);

        $mod->course = $cminfo->course;
        $mod->visible = $cminfo->visible;
        $mod->availability = $cminfo->availability;
        if (!empty($mod->availability)) {

            $availability = json_decode($mod->availability);

            if (isset($availability->c) && !empty($availability->c)) {
                foreach ($availability->c as $item) {
                    if (empty($cms->cms[$item->cm])) {
                        return $chain;
                    }
                    $chainmod = new Stdclass();
                    $chainmod->id = $item->cm;
                    $chain[] = $chainmod;

                    $chain = $this->get_activities_chain($chain, $courseid);
                }
            }
        }

        return $chain;
    }

    public function copy_mod_learningmap_in_section($arrcmids, $courseid, $sectionid) {
        global $DB;

        foreach ($arrcmids as $oldcmid => $newcmid) {
            list($notneeded, $cmold) = get_course_and_cm_from_cmid($oldcmid);

            if ($cmold->modname == 'learningmap') {
                if ($learningmap = $DB->get_record('learningmap', ['id' => $cmold->instance])) {
                    $data = json_decode($learningmap->placestore);

                    if (isset($data->places) && !empty($data->places)) {
                        foreach ($data->places as $key => $place) {
                            if ($place->selectedLinkType == 'linkedActivity') {

                                if (isset($arrcmids[$place->linkedActivity])) {
                                    $data->places[$key]->linkedActivity = $arrcmids[$place->linkedActivity];
                                } else {
                                    try {
                                        $newactivities = [];
                                        $newcmids = $this->duplicate_activity($place->linkedActivity, $courseid, $sectionid, $newactivities,
                                                []);

                                        $data->places[$key]->linkedActivity = $newcmids[$place->linkedActivity];
                                    }
                                    catch(Exception $e) {}
                                }
                            }
                        }
                    }

                    list($notneeded, $cmnew) = get_course_and_cm_from_cmid($newcmid);

                    if ($cmnew->modname == 'learningmap') {
                        if ($obj = $DB->get_record('learningmap', ['id' => $cmnew->instance])) {
                            $obj->placestore = json_encode($data);
                            $DB->update_record('learningmap', $obj);
                        }
                    }
                }
            }
        }
    }

    public function copy_single_mod_learningmap($sourceactivityid, $targetactivityid, $courseid, $sectionid) {
        global $DB;

        list($notneeded, $cmsourse) = get_course_and_cm_from_cmid($sourceactivityid);

        if ($cmsourse->modname == 'learningmap') {
            if ($learningmap = $DB->get_record('learningmap', ['id' => $cmsourse->instance])) {
                $data = json_decode($learningmap->placestore);

                if (isset($data->places) && !empty($data->places)) {
                    foreach ($data->places as $key => $place) {
                        if ($place->selectedLinkType == 'linkedActivity') {
                            try {
                                $newactivities = [];
                                $newcmids = $this->duplicate_activity($place->linkedActivity, $courseid, $sectionid, $newactivities,
                                        []);

                                $data->places[$key]->linkedActivity = $newcmids[$place->linkedActivity];
                            }
                            catch(Exception $e) {}
                        }
                    }
                }

                list($notneeded, $cmtarget) = get_course_and_cm_from_cmid($targetactivityid);

                if ($cmtarget->modname == 'learningmap') {
                    if ($obj = $DB->get_record('learningmap', ['id' => $cmtarget->instance])) {
                        $obj->placestore = json_encode($data);
                        $DB->update_record('learningmap', $obj);
                    }
                }
            }
        }

        return null;
    }
}
