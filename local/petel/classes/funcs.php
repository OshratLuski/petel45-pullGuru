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
 * @package    local_petel
 * @copyright  2018 Devlion <info@devlion.co>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_petel;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/user/profile/lib.php');

class funcs {

    // Old function "local_petel_execute_counter".
    public static function execute_counter() {
        global $DB;

        $ip = getremoteaddr();
        $smssecurtitynumber = get_config('local_petel', 'sms_securtity_number');
        $smssecurtitytimereset = get_config('local_petel', 'sms_securtity_time_reset');

        $row = $DB->get_record('security_sms', array('ip' => $ip));

        if (!empty($row)) {
            $relevanttime = $row->timemodified + $smssecurtitytimereset;

            if ($row->count >= $smssecurtitynumber && $relevanttime >= time()) {
                $row->count = $row->count + 1;
                $row->timemodified = time();
                $DB->update_record('security_sms', $row);

                return true;
            }

            if ($row->count >= $smssecurtitynumber && $relevanttime < time()) {
                $DB->delete_records('security_sms', array('ip' => $ip));
            }

            if ($row->count < $smssecurtitynumber) {
                $row->count = $row->count + 1;
                $row->timemodified = time();
                $DB->update_record('security_sms', $row);
            }

        } else {
            $DB->insert_record('security_sms', array('ip' => $ip, 'count' => 0, 'timemodified' => time()));
        }

        return false;
    }

    // Old function "local_petel_enable_captcha".
    public static function enable_captcha() {
        global $DB;

        $ip = getremoteaddr();
        $smssecurtitynumber = get_config('local_petel', 'sms_securtity_number');
        $smssecurtitytimereset = get_config('local_petel', 'sms_securtity_time_reset');

        $row = $DB->get_record('security_sms', array('ip' => $ip));
        if (!empty($row)) {

            $relevanttime = $row->timemodified + $smssecurtitytimereset;
            if ($row->count >= $smssecurtitynumber && $relevanttime >= time()) {
                return true;
            }
        }

        return false;
    }

    // Old function "create_course_special_grade_categories".
    public static function create_course_special_grade_categories($courseid, $ifprint = false) {
        global $CFG;

        require_once($CFG->libdir . '/grade/constants.php');

        if ($ifprint) {
            echo "<br> [100%] grade cat - Done, ";
        }

        // Add a new "Zero grade" category, for activities without grading.
        $catnograde = [
                'courseid' => $courseid,
                'fullname' => get_string('activitieswithoutgrade', 'local_petel'),
                'aggregation' => GRADE_AGGREGATE_MEAN
        ];
        $returncat = self::create_grade_category($catnograde);

        // Set the grade type of the grade item associated to the grade category.
        $catitemnototalinnototal = $returncat->load_grade_item();
        $catitemnototalinnototal->grademax = 0;
        $catitemnototalinnototal->aggregationcoef = 1;
        $catitemnototalinnototal->update();

        if ($ifprint) {
            echo " [0%] grade cat - Done<br>";
        }
    }

    // Old function "create_grade_category".
    public static function create_grade_category($record = null) {
        global $CFG;

        $record = (array) $record;
        if (empty($record['courseid'])) {
            throw new \coding_exception('courseid must be present in testing::create_grade_category() $record');
        }
        if (!isset($record['fullname'])) {
            $record['fullname'] = 'Grade category ';
        }

        // For gradelib classes.
        require_once($CFG->libdir . '/gradelib.php');

        // Create new grading category in this course.
        $gradecategory = new \grade_category(array('courseid' => $record['courseid']), false);
        $gradecategory->apply_default_settings();
        \grade_category::set_properties($gradecategory, $record);
        $gradecategory->apply_forced_settings();
        $gradecategory->insert();

        // This creates a default grade item for the category.
        $gradeitem = $gradecategory->load_grade_item();
        $gradecategory->update_from_db();

        return $gradecategory;
    }

    // Old function "local_petel_copy_course_to_new_category".
    public static function copy_course_to_new_category($userid, $targetcategoryid, $targetcourseid, $coursename = null, $roleid = null) {
        global $DB;

        $user = \core_user::get_user($userid);
        $maincourse = get_course($targetcourseid);

        if (empty($user) || empty($maincourse)) {
            return false;
        }

        $categories = \core_course_category::get_all(['returnhidden' => true]);
        if (!isset($categories[$targetcategoryid])) {
            return false;
        }

        $maincategory = $categories[$targetcategoryid];

        profile_load_data($user);
        $idnumber = $user->idnumber;

        if (empty($idnumber)) {
            return false;
        }

        // Check category with idnumber.
        $categoryid = 0;
        foreach ($maincategory->get_all_children_ids() as $childrenid) {
            $children = \core_course_category::get($childrenid);
            if ($children->idnumber == $idnumber) {
                $categoryid = $children->id;
                break;
            }
        }

        // Check if idnumber present in another categoryid.
        if ($categoryid == 0) {
            $row = $DB->get_record('course_categories', array('idnumber' => $idnumber));
            if (!empty($row)) {
                $categoryid = $row->id;
            }
        }

        // Create category.
        $flagcreatecategory = false;
        if ($categoryid == 0) {
            $obj = new \StdClass();
            $obj->name = $user->firstname . ' ' . $user->lastname;
            $obj->idnumber = $idnumber;
            $obj->parent = $maincategory->id;
            $obj->visible = 1;

            $newcategory = \core_course_category::create($obj);
            $categoryid = $newcategory->id;
            $flagcreatecategory = true;
        }

        // Duplicate course.
        $newcourse = self::duplicate_course($targetcourseid, $categoryid, $coursename);

        // Remove enrol self method if not set in original course.
        if (!$DB->get_record('enrol', ['enrol' => 'self', 'courseid' => $targetcourseid])) {
            $DB->delete_records('enrol', ['enrol' => 'self', 'courseid' => $newcourse->id]);
        }

        // Set enrole to user.
        if ($roleid == null) {
            $namerole = 'editingteacher';
            $role = $DB->get_record('role', array('shortname' => $namerole));
        } else {
            $role = $DB->get_record('role', array('id' => $roleid));
        }

        if (!empty($role)) {
            enrol_try_internal_enrol($newcourse->id, $userid, $role->id);
        }

        $result = new \StdClass();
        $category = $DB->get_record('course_categories', array('id' => $categoryid));
        $result->category_id = $category->id;
        $result->category_name = $category->name;
        $result->flag_create_category = $flagcreatecategory;

        $result->course_id = $newcourse->id;
        $result->course_name = $newcourse->fullname;
        $url = new \moodle_url('/course/view.php', ['id' => $newcourse->id]);
        $result->course_url = $url->out();

        $result->user_id = $user->id;
        $result->user_fullname = $user->firstname . ' ' . $user->lastname;

        return $result;
    }

    // Old function "local_petel_duplicate_course".
    public static function duplicate_course($courseid, $categoryid, $coursename = null, $visible = 1, $options = array()) {
        global $CFG, $DB;

        require_once($CFG->dirroot . '/backup/util/includes/backup_includes.php');
        require_once($CFG->dirroot . '/backup/util/includes/restore_includes.php');

        if (!($course = $DB->get_record('course', array('id' => $courseid)))) {
            throw new \moodle_exception('invalidcourseid', 'error');
        }

        $adminid = isset($CFG->adminid) ? $CFG->adminid : 2;

        $fullname = $coursename == null ? $course->fullname : $coursename;

        // Build shortname.
        $flag = false;
        $counter = 0;
        $timing = time();

        do {
            $shortname = $course->shortname . ' ' . ($timing + $counter);

            $obj = $DB->get_record('course', array('shortname' => $shortname));
            if (empty($obj)) {
                $flag = true;
            } else {
                $counter++;
            }

        } while ($flag == false);

        $backupdefaults = array(
                'activities' => 1,
                'blocks' => 1,
                'filters' => 1,
                'users' => 0,
                'enrolments' => \backup::ENROL_NEVER,
                'role_assignments' => 0,
                'comments' => 0,
                'userscompletion' => 0,
                'logs' => 0,
                'grade_histories' => 0
        );

        $backupsettings = array();
        // Check for backup and restore options.
        if (!empty($options)) {
            foreach ($options as $option) {

                // Strict check for a correct value (allways 1 or 0, true or false).
                $value = clean_param($option['value'], PARAM_INT);

                if ($value !== 0 && $value !== 1) {
                    throw new \moodle_exception('invalidextparam', 'webservice', '', $option['name']);
                }

                if (!isset($backupdefaults[$option['name']])) {
                    throw new \moodle_exception('invalidextparam', 'webservice', '', $option['name']);
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
            throw new \moodle_exception('shortnametaken', '', '', $foundcoursenamestring);
        }

        // Backup the course.
        $bc = new \backup_controller(\backup::TYPE_1COURSE, $course->id, \backup::FORMAT_MOODLE,
                \backup::INTERACTIVE_NO, \backup::MODE_SAMESITE, $adminid);

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
        $newcourseid = \restore_dbops::create_new_course($fullname, $shortname, $categoryid);

        $rc = new \restore_controller($backupid, $newcourseid,
                \backup::INTERACTIVE_NO, \backup::MODE_HUB, $adminid, \backup::TARGET_NEW_COURSE);

        foreach ($backupsettings as $name => $value) {
            $setting = $rc->get_plan()->get_setting($name);
            if ($setting->get_status() == \backup_setting::NOT_LOCKED) {
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

                throw new \moodle_exception('backupprecheckerrors', 'webservice', '', $errorinfo);
            }
        }

        $rc->execute_plan();
        $rc->destroy();

        $course = $DB->get_record('course', array('id' => $newcourseid), '*', MUST_EXIST);
        $course->fullname = $fullname;
        $course->shortname = $shortname;
        $course->visible = $visible;

        $startdate = date('Y-m-d', strtotime(' - 1 days'));
        $enddate = date('Y-m-d', strtotime(' -1 days + 1 years'));

        $course->startdate = strtotime($startdate);
        $course->enddate = strtotime($enddate);

        // Set shortname and fullname back.
        $DB->update_record('course', $course);

        if (empty($CFG->keeptempdirectoriesonbackup)) {
            fulldelete($backupbasepath);
        }

        // Delete the course backup file created by this WebService. Originally located in the course backups area.
        $file->delete();

        // Change password for auth:self.
        if ($enrol = $DB->get_record('enrol', ['enrol' => 'self', 'courseid' => $course->id])) {
            if (!empty($enrol->password)) {
                $enrol->password = self::hash_password($course->id);
                $DB->update_record('enrol', $enrol);
            }
        }

        // Copy BADGES from source course.
        $newcourseid = $course->id;

        $context = \context_course::instance($courseid);
        $newcontext = \context_course::instance($newcourseid);
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

        return $course;
    }

    // Old function "local_petel_hash_password".
    public static function hash_password($courseid) {
        $len = 3;
        return substr(str_shuffle(str_repeat("123456789ABCDEFGHIJKLMNPQRSTUVWXYZ", $len)), 0, $len) . (string) $courseid .
                substr(str_shuffle(str_repeat("123456789ABCDEFGHIJKLMNPQRSTUVWXYZ", $len)), 0, $len);
    }

    // Old function "local_petel_send_message_to_teacher".
    public static function send_message_to_teacher($useridfrom, $useridto, $component, $eventtype, $smallmessage,
            $fullmessage, $customdata = array()) {
        global $DB;

        $time = time();

        $objinsert = new \stdClass();
        $objinsert->useridfrom = $useridfrom;
        $objinsert->useridto = $useridto;

        $objinsert->subject = $smallmessage;
        $objinsert->fullmessage = $smallmessage;
        $objinsert->fullmessageformat = 2;
        $objinsert->fullmessagehtml = $fullmessage;
        $objinsert->smallmessage = $smallmessage;
        $objinsert->component = $component;
        $objinsert->eventtype = $eventtype;
        $objinsert->timecreated = $time;
        $objinsert->customdata = json_encode($customdata);

        $objinsert->contexturl = isset($customdata['contexturl']) ? $customdata['contexturl'] : null;

        $notificationid = $DB->insert_record('notifications', $objinsert);

        $objinsert = new \stdClass();
        $objinsert->notificationid = $notificationid;
        $DB->insert_record('message_petel_notifications', $objinsert);

        return $notificationid;
    }

    // Old function "local_petel_user_admin_or_teacher".
    public static function user_admin_or_teacher() {
        global $CFG, $USER;

        $access = false;

        if (is_siteadmin()) {
            $access = true;
            return $access;
        } else if (!empty($CFG->defaultcohortscourserequest)) {
            $permitedcohorts = explode(',', $CFG->defaultcohortscourserequest);
            if ($permitedcohorts) {
                require_once($CFG->dirroot . '/cohort/lib.php');

                $cohorts = cohort_get_user_cohorts($USER->id);
                foreach ($cohorts as $cohort) {
                    if (in_array($cohort->idnumber, $permitedcohorts)) {
                        $access = true;
                        return $access;
                    }
                }
            }
        }

        return $access;
    }

    // Old function "local_petel_logout_by_session_timeout_per_user".
    public static function logout_by_session_timeout_per_user($userid, $withsessid = true, $redirect = true) {
        global $DB;

        $delta = self::get_session_timeout($userid);

        $isexpired = false;

        if (isset($userid) && $userid > 0) {
            $params = ['userid' => $userid];
            if ($withsessid) {
                $params['sid'] = session_id();
            }
            $sessions = $DB->get_records('sessions', $params);
            $session = array_pop($sessions);

            if ($delta && $session && time() > $session->timemodified + $delta) {
                $isexpired = true;
                \core\session\manager::kill_user_sessions($userid);
                if ($redirect) {
                    redirect(new \moodle_url('/'));
                }
            }
        }

        return $isexpired;
    }

    // Old function "local_petel_calculate_social_relationships".
    public static function calculate_social_relationships() {
        global $DB, $CFG;

        raise_memory_limit(MEMORY_UNLIMITED);

        // Get social users.
        $users = [];
        foreach ($DB->get_records('community_social_usr_dtls') as $item) {
            $users[] = $item->userid;
        }

        // Delete users form table social_relationships.
        if (empty($users)) {
            $DB->execute("TRUNCATE TABLE {social_relationships}");
        } else {
            $sql = "DELETE FROM {social_relationships} WHERE userid_watching NOT IN (" . implode(',', $users) . ") 
            OR userid_feedback NOT IN (" . implode(',', $users) . ")";

            $DB->execute($sql);
        }

        if (class_exists('\community_social\social')) {
            $social = new \community_social\social();

            // Fill table social_relationships.
            foreach ($users as $watchingid) {

                if ($CFG->debug) {
                    mtrace('Currently proccessing userid: ' . $watchingid);
                }

                foreach ($users as $feedbackid) {
                    if ($watchingid == $feedbackid) {
                        continue;
                    }

                    // Calculate relationships.
                    $points = 0;
                    if ($social->if_user_followers($watchingid, $feedbackid)) {
                        $points += 1;
                    }

                    if ($social->if_user_colleagues($watchingid, $feedbackid)) {
                        $points += 1;
                    }

                    $row = $DB->get_record('social_relationships', ['userid_watching' => $watchingid, 'userid_feedback' => $feedbackid]);
                    if (!empty($row)) {
                        $row->points = $points;
                        $DB->update_record('social_relationships', $row);
                    } else {
                        $ins = new \StdClass();
                        $ins->userid_watching = $watchingid;
                        $ins->userid_feedback = $feedbackid;
                        $ins->points = $points;

                        $DB->insert_record('social_relationships', $ins);
                    }
                }
            }
        }

        return true;
    }

    // Old function "local_petel_get_session_timeout".
    public static function get_session_timeout($userid = null) {

        $timeout = get_user_preferences('session_timeout', 0, $userid);

        switch ($timeout) {
            case 1:
                $delta = 2 * 60 * 60;
                break;
            case 2:
                $delta = 24 * 60 * 60;
                break;
            case 3:
                $delta = 7 * 24 * 60 * 60;
                break;
            case 4:
                $delta = 30 * 24 * 60 * 60;
                break;
            default:
                if (isset($CFG->sessiontimeout_user) && !empty($CFG->sessiontimeout_user)) {
                    $delta = $CFG->sessiontimeout_user;
                } else {
                    $delta = 2 * 60 * 60;
                }
        }

        return $delta;
    }

}
