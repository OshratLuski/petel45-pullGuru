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
 * @package    community_social
 * @copyright  2018 Devlion <info@devlion.co>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace community_social;

defined('MOODLE_INTERNAL') || die();

class funcs {

    // Old function "social_filter_userid".
    public static function filter_userid($var) {
        return ($var !== null && $var !== false && $var !== '');
    }

    // Old function "social_has_permission".
    public static function has_permission($userid) {
        global $DB, $CFG, $USER;

        if (!empty($CFG->defaultcohortscourserequest)) {
            $permitedcohorts = explode(',', $CFG->defaultcohortscourserequest);
            if ($permitedcohorts) {
                require_once($CFG->dirroot . '/cohort/lib.php');
                $cohorts = cohort_get_user_cohorts($USER->id);
                foreach ($cohorts as $cohort) {
                    if (in_array($cohort->idnumber, $permitedcohorts)) {
                        return true;
                    }
                }
            }
        }

        if (\community_oer\main_oer::get_instancename() === 'physics') {
            $rolespermitted = ['manager', 'coursecreator', 'editingteacher', 'teacher'];
        } else {
            // Hide from chemistry & biology.
            $rolespermitted = ['manager', 'coursecreator', 'editingteacher'];
        }

        $sql = "
        SELECT DISTINCT (shortname) 
        FROM {role} 
        LEFT JOIN {role_assignments} ON ({role}.id = {role_assignments}.roleid) 
        WHERE userid=?
    ";

        $roles = $DB->get_records_sql($sql, [$userid]);

        if (!empty($roles)) {
            foreach ($roles as $role) {
                if (in_array($role->shortname, $rolespermitted)) {
                    return true;
                }
            }
        }

        return false;
    }

    // Old function "social_get_course_image".
    public static function get_course_image($courseid) {
        global $CFG;

        // Default.
        $imgurl = $CFG->wwwroot . '/local/community/plugins/social/pix/defaultbg.jpg';

        $course = get_course($courseid);
        if ($course instanceof \stdClass) {
            $course = new \core_course_list_element($course);
        }

        foreach ($course->get_course_overviewfiles() as $file) {
            $isimage = $file->is_valid_image();
            $imgurl = file_encode_url("$CFG->wwwroot/pluginfile.php",
                    '/' . $file->get_contextid() . '/' . $file->get_component() . '/' .
                    $file->get_filearea() . $file->get_filepath() . $file->get_filename(), !$isimage);
        }

        return $imgurl;
    }

    // Old function "social_get_users_courses".
    public static function get_users_courses($userid) {
        $result = [];
        if ($userid) {

            list($oercategories, $oercourses, $oeractivities) = \community_oer\main_oer::get_main_structure_elements();

            $courses = enrol_get_users_courses($userid, true);
            if (!empty($courses)) {
                foreach ($courses as $course) {
                    $context = \context_course::instance($course->id);
                    $roles = get_user_roles($context, $userid);

                    $flagpermission = true;
                    foreach ($roles as $role) {
                        if ($role->shortname == 'teachercolleague') {
                            $flagpermission = false;
                        }
                    }

                    if (!in_array($course->id, $oercourses) && $flagpermission) {
                        $result[] = $course->id;
                    }
                }
            }
        }

        return $result;
    }

    // Old function "social_open_permission_course".
    public static function open_permission_course($userid, $courseid) {
        global $DB;

        $namerole = 'teachercolleague';

        $role = $DB->get_record('role', ['shortname' => $namerole]);
        if (!empty($role)) {
            enrol_try_internal_enrol($courseid, $userid, $role->id);
        }
    }

    // Old function "social_close_permission_course".
    public static function close_permission_course($userid, $courseid) {
        global $DB;

        $namerole = 'teachercolleague';
        $role = $DB->get_record('role', ['shortname' => $namerole]);

        $course = $DB->get_record('course', ['id' => $courseid]);
        if (!empty($course)) {
            $context = \context_course::instance($courseid);
            if (!empty($role)) {
                if ($DB->count_records('role_assignments', ['contextid' => $context->id, "userid" => $userid]) > 1) {
                    role_unassign($role->id, $userid, $context->id);
                } else {
                    role_unassign($role->id, $userid, $context->id);
                    $sql = "
                    SELECT uen.id as id
                    FROM {user_enrolments} uen
                    LEFT JOIN {enrol} en ON (uen.enrolid = en.id)
                    WHERE uen.userid=? AND en.courseid=?
                ";
                    $enrollments = $DB->get_record_sql($sql, [$userid, $courseid]);
                    if (!empty($enrollments)) {
                        $DB->delete_records('user_enrolments', ['id' => $enrollments->id]);
                    }
                }
            }
        }
    }

    // Old function "social_approve_courses_to_user".
    public static function approve_courses_to_user($userid, $sharedcoursesids) {
        global $DB, $USER;

        if (!empty($sharedcoursesids)) {

            $arrcourses = explode(',', $sharedcoursesids);
            foreach ($arrcourses as $courseid) {
                $res = $DB->get_record('community_social_shrd_crss', ['id' => $courseid, 'userid' => $USER->id]);
                if (!empty($res)) {

                    // Save to community_social_collegues.
                    $row = $DB->get_record('community_social_collegues',
                            ['userid' => $userid, 'social_shared_courses_id' => $courseid]);
                    if (!empty($row)) {
                        $row->approved = 1;
                        $DB->update_record('community_social_collegues', $row, $bulk = false);
                    } else {
                        $dataobject = new \stdClass();
                        $dataobject->userid = $userid;
                        $dataobject->social_shared_courses_id = $courseid;
                        $dataobject->approved = 1;
                        $dataobject->timecreated = time();
                        $dataobject->timemodified = time();
                        $DB->insert_record('community_social_collegues', $dataobject);
                    }

                    // Update community_social_requests.
                    $req = $DB->get_record('community_social_requests',
                            ['userid' => $USER->id, 'usersendid' => $userid, 'social_shared_courses_ids' => $sharedcoursesids]);
                    if (!empty($req)) {
                        $req->status = 1;
                        $DB->update_record('community_social_requests', $req, $bulk = false);
                    }

                    \community_social\funcs::open_permission_course($userid, $res->courseid);
                }
            }

        }
    }

    // Teachers.

    // Old function "social_teachers_get_followers".
    public static function teachers_get_followers($userid = null, $search = '') {
        global $USER;

        if ($userid == null) {
            $userid = $USER->id;
        }

        $social = new \community_social\social();
        $obj = $social->query()->compare('active', '1')->notIn('userid', $userid);

        $search = trim($search);

        if (!empty($search)) {
            $obj = $social->query($obj->get());
            $obj = $obj->like('firstname', $search)->orLike('lastname', $search)->orLike('fullname', $search);
        }

        $obj = $obj->orderNumber('colleagues_count', 'desc');

        $obj = $social->calculate_data_online($obj)->get();
        $relevantusers = array_values($obj);

        $result = [];
        foreach ($relevantusers as $user) {
            if (self::has_permission($user->userid) && $user->if_followers) {
                $result[] = $user;
            }
        }

        return $result;
    }

    // Cohort functions.

    // Old function "social_if_user_in_cohort".
    public static function if_user_in_cohort($cohortid, $userid = null) {
        global $DB, $USER;

        if ($userid == null) {
            $userid = $USER->id;
        }

        $sql = "
        SELECT *
        FROM {cohort} c
        LEFT JOIN {cohort_members} cm ON (cm.cohortid = c.id)
        WHERE c.id=? AND cm.userid=? AND c.visible=1     
    ";
        $row = $DB->get_record_sql($sql, [$cohortid, $userid]);

        if (!empty($row)) {
            return true;
        }
        return false;
    }

    // Old function "social_get_cohorts_by_user".
    public static function get_cohorts_by_user($userid = null) {
        global $DB, $USER;

        if ($userid == null) {
            $userid = $USER->id;
        }

        $sql = "
        SELECT c.*
        FROM {cohort_members} cm
        LEFT JOIN {cohort} c ON (cm.cohortid = c.id)
        WHERE cm.userid=? AND c.visible=1    
    ";
        $rows = $DB->get_records_sql($sql, [$userid]);

        return $rows;
    }

    // Old function "social_cohorts_via_settings".
    public static function cohorts_via_settings($cohorts) {

        $result = [];
        $cohortcategoryid = \community_oer\main_oer::get_oer_category();

        if (!isset($cohortcategoryid) || empty($cohortcategoryid)) {
            return $result;
        }

        foreach ($cohorts as $key => $cohort) {
            if ($cohort->contextid == $cohortcategoryid) {
                $result[$key] = $cohort;
            }
        }

        return $result;
    }

    // Old function "social_migrate_course".
    public static function migrate_course($oldcourseid, $newcourseid, $ifcopy) {
        global $DB;

        // Shared courses.
        foreach ($DB->get_records('community_social_shrd_crss', ['courseid' => $oldcourseid]) as $shared) {
            $usersrecache = [];
            $userspermission = [];

            // Users for recache.
            $usersrecache[] = $shared->userid;

            $shared->courseid = $newcourseid;
            $shared->ifcopy = $ifcopy ? 1 : 0;
            $DB->update_record('community_social_shrd_crss', $shared);

            // Collegues.
            $colleques = $DB->get_records('community_social_collegues', ['social_shared_courses_id' => $shared->id]);

            foreach ($colleques as $colleque) {

                // Users for change permission.
                if ($colleque->approved) {
                    $userspermission[] = $colleque->userid;
                }

                // Users for recache.
                $usersrecache[] = $colleque->userid;
            }

            // Request.
            $requests = $DB->get_records('community_social_requests', ['social_shared_courses_ids' => $shared->id]);

            foreach ($requests as $request) {

                // Delete row.
                if (!$request->status) {
                    $DB->delete_records('community_social_requests', ['id' => $request->id]);

                    // Remove messages.
                    $DB->delete_records('message_petel_notifications', ['notificationid' => $request->messageid]);

                    continue;
                }

                // Users for recache.
                $usersrecache[] = $request->userid;
                $usersrecache[] = $request->usersendid;
            }

            // Set new permissions.
            foreach (array_unique($userspermission) as $userid) {
                \community_social\funcs::close_permission_course($userid, $oldcourseid);
                \community_social\funcs::open_permission_course($userid, $newcourseid);
            }

            // Rechache.
            $social = new \community_social\social();
            foreach (array_unique($usersrecache) as $userid) {
                // Recache user.
                $social->refreshUser($userid);
            }
        }
    }
}
