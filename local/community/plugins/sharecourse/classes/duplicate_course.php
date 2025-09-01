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
 * @package    community_sharecourse
 * @copyright  2018 Devlion <info@devlion.co>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

require_once($CFG->libdir . "/externallib.php");

/**
 * Course external functions
 *
 * @package    core_course
 * @category   external
 * @copyright  2011 Jerome Mouneyrac
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @since Moodle 2.2
 */
class duplicate_course extends \external_api {

    public function __construct() {
        global $CFG;

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

        $oldcontext = context_course::instance($courseid);
        $newcontext = context_course::instance($newcourseid);

        $fs = get_file_storage();

        // Copy BADGES from source course.
        $badges = $DB->get_records('badge', array('courseid' => $courseid));
        foreach ($badges as $badge) {
            $newbadge = clone $badge;

            // Insert new badge.
            unset($newbadge->id);
            $newbadge->courseid = $newcourseid;
            $newbadgeid = $DB->insert_record('badge', $newbadge);

            // Copy badge file.
            $files = $fs->get_area_files($oldcontext->id, 'badges', 'badgeimage', $badge->id);

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
}
