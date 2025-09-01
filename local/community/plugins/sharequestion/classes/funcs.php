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
 * @package    community_sharequestion
 * @copyright  2018 Devlion <info@devlion.co>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace community_sharequestion;

defined('MOODLE_INTERNAL') || die();

class funcs {

    // Old function "community_sharequestion_get_courses_for_quiz".
    public static function get_courses_for_quiz() {
        global $DB, $USER;

        $result = [];
        $courseids = [];
        $mycourses = enrol_get_my_courses('*', 'id DESC');
        foreach ($mycourses as $course) {

            $context = \context_course::instance($course->id);
            foreach (get_user_roles($context, $USER->id, false) as $role) {
                if ($role->shortname == 'editingteacher') {
                    $courseids[] = $course->id;
                }
            }
        }

        if (!empty($courseids)) {
            $sql = "
            SELECT cm.id as cmid, c.id AS courseid, c.fullname AS fullname, q.id AS qid
            FROM {course_modules} cm
            LEFT JOIN {modules} m ON (m.id = cm.module)
            LEFT JOIN {quiz} q ON (q.id = cm.instance)
            LEFT JOIN {course} c ON (c.id = cm.course)
            WHERE m.name = 'quiz' AND c.id IN(" . implode(',', $courseids) . ")                 
        ";

            foreach ($DB->get_records_sql($sql) as $item) {
                if (!$DB->record_exists('quiz_attempts', array('quiz' => $item->qid, 'preview' => 0))) {
                    $result[$item->courseid] = $item;
                }
            }
        }

        return array_values($result);
    }

    // Old function "community_sharequestion_get_quiz_by_course".
    public static function get_quiz_by_course($courseid) {
        global $DB;

        if (empty($courseid)) {
            return [];
        }

        $result = [];

        $sql = "
                SELECT cm.id as cmid, q.name as name, q.id AS qid
                FROM {course_modules} cm
                LEFT JOIN {modules} m ON (m.id = cm.module)
                LEFT JOIN {quiz} q ON (q.id = cm.instance)
                WHERE m.name = 'quiz' AND cm.course = ?
            ";

        foreach ($DB->get_records_sql($sql, [$courseid]) as $item) {
            if (!$DB->record_exists('quiz_attempts', array('quiz' => $item->qid, 'preview' => 0))) {
                $result[] = $item;
            }
        }

        return array_values($result);
    }

    // Old function "community_sharequestion_get_courses_for_category".
    public static function get_courses_for_category() {
        global $DB, $USER;

        $result = [];

        $mycourses = enrol_get_my_courses('*', 'id DESC');
        foreach ($mycourses as $course) {
            $context = \context_course::instance($course->id);

            $sql = "
                SELECT qc.id as id, qc.name as name
                FROM {question_categories} qc             
                WHERE qc.name != 'top' AND qc.contextid = ?
            ";

            $obj = $DB->get_records_sql($sql, [$context->id]);

            if (!empty($obj)) {
                foreach (get_user_roles($context, $USER->id, false) as $role) {
                    if ($role->shortname == 'editingteacher') {
                        $result[] = $course;
                    }
                }
            }
        }

        return $result;
    }

    // Old function "community_sharequestion_get_categories_by_course".
    public static function get_categories_by_course($courseid) {
        global $DB;

        if (empty($courseid)) {
            return [];
        }

        $context = \context_course::instance($courseid);

        $sql = "
                SELECT qc.id as id, qc.name as name
                FROM {question_categories} qc             
                WHERE qc.name != 'top' AND qc.contextid = ?
            ";

        return array_values($DB->get_records_sql($sql, [$context->id]));
    }

    // Old function "community_sharequestion_autocomplete_teachers".
    public static function autocomplete_teachers($searchstring) {
        global $USER, $DB;

        $result = '';
        if (!empty($searchstring)) {
            $sql = "
            SELECT
                DISTINCT u.id AS teacher_id,
                c.id AS course_id,
                c.fullname AS full_name,
                u.username AS user_name,
                u.firstname AS firstname,
                u.lastname AS lastname,
                CONCAT(u.firstname, ' ', u.lastname) AS teacher_name,
                CONCAT('/user/pix.php/', u.id ,'/f1.jpg') AS teacher_url,
                u.email AS teacher_mail
            FROM {course} c,
                 {role_assignments} ra,
                 {user} u, 
                 {context} ct
            WHERE c.id = ct.instanceid
                AND ra.roleid IN(1,2,3,4)
                AND ra.userid = u.id
                AND ct.id = ra.contextid
                AND ( u.email LIKE(?)
                    OR u.lastname LIKE(?)
                    OR u.firstname LIKE(?)
                    OR u.username LIKE(?)
                    OR CONCAT(u.firstname, ' ', u.lastname) LIKE(?))
            GROUP BY u.id;
        ";

            $searchstrquery = '%' . $searchstring . '%';
            $teachers = $DB->get_records_sql($sql, array($searchstrquery, $searchstrquery,
                    $searchstrquery, $searchstrquery, $searchstrquery));
            $result = json_encode(array_values($teachers));
        }

        return $result;
    }

    // Old function "community_sharequestion_submit_teachers".
    public static function submit_teachers($questionids, $teachersid, $message) {
        global $USER, $DB, $CFG;

        $teachersid = json_decode($teachersid);

        if (!empty($teachersid) && !empty($questionids)) {

            $arrteachers = $DB->get_records_sql("
            SELECT DISTINCT u.id AS teacher_id
            FROM {course} c,
                 {role_assignments} ra,
                 {user} u, 
                 {context} ct
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

                // Check if present teacher.
                if (in_array($teacherid, $arrfortest)) {

                    // Prepare message for user.
                    $eventtype = 'copy_question_to_teacher';
                    $customdata = array('message' => $message);
                    $messageid = self::send_message_to_teacher($USER->id, $teacherid, $questionids,
                            'community_sharequestion', $eventtype, $customdata);

                    if (!empty($questionids)) {
                        foreach (json_decode($questionids) as $questionid) {
                            $DB->insert_record('community_sharequestion_shr', [
                                    'type' => 'copy_question_to_teacher',
                                    'qid' => $questionid,
                                    'useridto' => $teacherid,
                                    'useridfrom' => $USER->id,
                                    'messageid' => $messageid,
                                    'timecreated' => time(),
                            ]);
                        }
                    }
                }
            }
        }

        return $messageid;
    }

    // Old function "community_sharequestion_send_message_to_teacher".
    private static function send_message_to_teacher($useridfrom, $useridto, $questionids, $component, $eventtype,
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
        if (!empty($questionids)) {
            $customdata['questionids'] = $questionids;

            $a = new \stdClass;
            $a->teachername = $userfrom->firstname . ' ' . $userfrom->lastname;
            $customdata['content'] = get_string('subject_message_for_teacher', 'community_sharequestion', $a);

            $questions = [];
            foreach (json_decode($questionids) as $questionid) {
                $row = $DB->get_record('question', ['id' => $questionid]);

                if (!empty($row)) {
                    $questions[] = ['questionname' => $row->name];
                }
            }
            $customdata['questions'] = $questions;
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
