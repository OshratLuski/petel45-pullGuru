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
 * @package    community_sharecourse
 * @copyright  2018 Devlion <info@devlion.co>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace community_sharecourse;

defined('MOODLE_INTERNAL') || die();

class funcs {

    // Old function "community_sharecourse_submit_teachers".
    public static function submit_teachers($courseid, $teachersid, $coursesid, $message) {
        global $USER, $DB;

        $teachers = [];

        $teachersid = json_decode($teachersid);
        $coursesid = json_decode($coursesid);

        if (!empty($courseid) && (!empty($teachersid) || !empty($coursesid))) {

            // Share to enroled users course.
            $config = get_config('community_sharewith', 'roles_share_teacher');
            $roles = explode(',', $config);

            foreach ($coursesid as $cid) {
                $context = \context_course::instance($cid);
                foreach (get_enrolled_users($context) as $u) {
                    foreach (get_user_roles($context, $u->id, false) as $role) {
                        if (in_array($role->shortname, $roles) && $u->id != $USER->id) {
                            $teachers[] = $u->id;
                            break;
                        }
                    }
                }
            }

            $arrteachers = $DB->get_records_sql("
            SELECT DISTINCT u.id AS teacher_id
            FROM {course} c,
                 {role_assignments} ra,
                 {user} u, {context} ct
            WHERE
                c.id = ct.instanceid
                AND ra.roleid IN(1,2,3,4)
                AND ra.userid = u.id
                AND ct.id = ra.contextid
            GROUP BY u.id;
        ");

            $arrfortest = [];
            foreach ($arrteachers as $item) {
                $arrfortest[] = $item->teacher_id;
            }

            foreach ($teachersid as $teacherid) {
                if (in_array($teacherid, $arrfortest)) {
                    $teachers[] = $teacherid;
                }
            }

            foreach (array_unique($teachers) as $teacherid) {

                // Prepare message for user.
                $eventtype = 'copy_course_to_teacher';
                $customdata = array('message' => $message);
                $messageid = self::send_message_to_teacher($USER->id, $teacherid, $courseid,
                        'community_sharecourse', $eventtype, $customdata);

                if (!empty($courseid)) {
                    $DB->insert_record('community_sharecourse_shr', [
                            'type' => 'copy_course_to_teacher',
                            'courseid' => $courseid,
                            'useridto' => $teacherid,
                            'useridfrom' => $USER->id,
                            'messageid' => $messageid,
                            'timecreated' => time(),
                    ]);
                }

            }
        }

        return true;
    }

    // Old function "community_sharecourse_get_categories".
    public static function get_categories($courseid = null) {
        global $DB, $USER;

        if (!is_siteadmin($USER)) {
            $sql = "SELECT * FROM {course_categories} WHERE id IN (SELECT category from {course} WHERE id=?)";
            $categories = $DB->get_records_sql($sql, array($courseid));
        } else {
            // Get all categories without categories from oer catalog.
            $categories = $DB->get_records('course_categories', array('visible' => 1));

            list($oercategories, $oercourses, $oeractivities) = \community_oer\main_oer::get_main_structure_elements();
            foreach ($categories as $key => $item) {
                if (in_array($item->id, $oercategories)) {
                    unset($categories[$key]);
                }
            }
        }

        return array_values($categories);
    }

    // Old function "community_sharecourse_add_task".
    public static function add_task($type, $userid, $courseid, $categoryid, $metadata = []) {
        global $DB;

        $obj = new \stdClass();
        $obj->type = $type;
        $obj->userid = $userid;
        $obj->courseid = $courseid;
        $obj->categoryid = $categoryid;
        $obj->status = 0;
        $obj->timemodified = time();

        $obj->metadata = json_encode($metadata);

        $result = $DB->insert_record('community_sharecourse_task', $obj);

        return $result;
    }

    // Old function "community_sharecourse_send_message_to_teacher".
    private static function send_message_to_teacher($useridfrom, $useridto, $courseid, $component, $eventtype,
            $customdata = array()) {
        global $DB, $CFG;

        $smallmessage = get_string($component . '_' . $eventtype, 'message_petel');

        $time = time();
        $userfrom = $DB->get_record("user", array('id' => $useridfrom));

        $customdata['custom'] = true;
        $customdata[$eventtype] = true;
        $customdata['firstname'] = $userfrom->firstname;
        $customdata['lastname'] = $userfrom->lastname;
        $customdata['teacher_image'] = $CFG->wwwroot . '/user/pix.php/' . $useridfrom . '/f1.jpg';
        $customdata['dateformat'] = date("d.m.Y", $time);
        $customdata['timeformat'] = date("H:i", $time);

        // Prepare questions.
        if (!empty($courseid)) {

            $a = new \stdClass;
            $a->teachername = $userfrom->firstname . ' ' . $userfrom->lastname;
            $customdata['content'] = get_string('subject_message_for_teacher', 'community_sharecourse', $a);
            $customdata['courseid'] = $courseid;

            if ($course = $DB->get_record('course', ['id' => $courseid])) {
                $customdata['course_url'] = $CFG->wwwroot . '/course/view.php?id=' . $courseid;
                $customdata['course_name'] = $course->fullname;
            } else {
                $customdata['course_url'] = 'javascript:void(0)';
                $customdata['course_name'] = '';
            }
        }

        $objinsert = new \stdClass();
        $objinsert->useridfrom = $useridfrom;
        $objinsert->useridto = $useridto;

        $objinsert->subject = $smallmessage;
        $objinsert->fullmessage = $smallmessage;
        $objinsert->fullmessageformat = 2;
        $objinsert->fullmessagehtml = '';
        $objinsert->smallmessage = $smallmessage;
        $objinsert->component = $component;
        $objinsert->eventtype = $eventtype;
        $objinsert->timecreated = $time;
        $objinsert->customdata = json_encode($customdata);

        $notificationid = $DB->insert_record('notifications', $objinsert);

        $objinsert = new \stdClass();
        $objinsert->notificationid = $notificationid;
        $DB->insert_record('message_petel_notifications', $objinsert);

        return $notificationid;
    }
}
