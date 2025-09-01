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
 * Event observers supported by this module
 *
 * @package    local_petel
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/local/petel/locallib.php');

/**
 * Event observers supported by this module
 *
 * @package    local_petel
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class local_petel_observer {

    /**
     * Observer for \core\event\course_created event.
     *
     * @param \core\event\course_created $event
     * @return void
     */
    public static function course_created(\core\event\course_created $event) {
        global $CFG;

        if (!empty($CFG->instancename) && $CFG->instancename === 'chemistry') {
            \local_petel\funcs::create_course_special_grade_categories($event->objectid);
        }
    }

    /**
     * Observer for \mod_quiz\event\question_manually_graded event.
     *
     * @param \mod_quiz\event\question_manually_graded $event
     * @return void
     */
    public static function question_manually_graded(\mod_quiz\event\question_manually_graded $event) {
        global $CFG;

        // PTL-2902 Disable automatic email with feedback and final grade of open questions, that is sent to students.
        return;

        require_once($CFG->dirroot . '/question/engine/datalib.php');

        $attemptobj = quiz_attempt::create($event->other['attemptid']);

        $dm = new question_engine_data_mapper();

        $cm = $attemptobj->get_cm();
        $context = context_module::instance($cm->id);

        $usages = $dm->load_questions_usages_question_state_summary(
                static::get_qubaids_condition($context, $cm), $attemptobj->get_slots());

        $send = true;
        foreach ($usages as $usage) {
            if ($usage->autograded == 0 && $usage->manuallygraded == 0) {
                $send = false;
            }
        }
        if ($send && $attemptobj->get_attempt()->state == "finished") {
            $quiz = $attemptobj->get_quiz();
            $course = get_course($quiz->course);

            $user = \core_user::get_user($attemptobj->get_attempt()->userid);
            $grade = static::get_quiz_grade($quiz, $user->id);
            if ($grade != '-') {
                $data = [
                        'grade' => $grade,
                        'fullname' => fullname($user),
                        'activityname' => $quiz->name,
                        'coursename' => $course->fullname,
                        'link' => (new \moodle_url('/mod/quiz/review.php', ['attempt' => $attemptobj->get_attempt()->id]))->out()
                ];

                $body = get_string('question_graded_body', 'local_petel');
                $subject = get_string('question_graded_subject', 'local_petel');
                foreach ($data as $fieldname => $value) {
                    $body = str_replace('{' . $fieldname . '}', $value, $body);
                    $subject = str_replace('{' . $fieldname . '}', $value, $subject);
                }

                static::send_message($attemptobj->get_attempt()->userid, $subject, $body);
            }
        }

    }

    protected static function get_quiz_grade($quiz, $userid) {
        global $CFG;
        require_once($CFG->libdir . '/gradelib.php');

        $gradinginfo = grade_get_grades($quiz->course, 'mod', 'quiz', $quiz->id, $userid);
        if (!empty($gradinginfo->items)) {
            $item = $gradinginfo->items[0];
            if (isset($item->grades[$userid])) {
                return $item->grades[$userid]->str_long_grade;
            }
        }

        return null;
    }

    protected static function send_message($userid, $subject, $body) {
        $userobj = \core_user::get_user($userid);

        $eventdata = new \core\message\message();
        $eventdata->courseid = SITEID;
        $eventdata->component = 'local_petel';
        $eventdata->name = 'attemptgraded';
        $eventdata->notification = 1;

        $eventdata->userfrom = \core_user::get_noreply_user();

        $eventdata->fullmessageformat = FORMAT_HTML;

        $eventdata->fullmessagehtml = $body;
        $eventdata->fullmessage = html_to_text($body);
        $eventdata->subject = $subject;

        $eventdata->userto = $userobj;

        if (!message_send($eventdata)) {
            throw new \Exception('False returned when message queued with userid: ' . $userid);
        }
    }

    protected static function get_qubaids_condition($context, $cm) {

        $where = "quiza.quiz = :mangrquizid AND
                quiza.preview = 0 AND
                quiza.state = :statefinished";
        $params = array('mangrquizid' => $cm->instance, 'statefinished' => quiz_attempt::FINISHED);

        $usersjoin = '';
        $currentgroup = groups_get_activity_group($cm, true);
        $enrolleduserscount = count_enrolled_users($context,
                array('mod/quiz:reviewmyattempts', 'mod/quiz:attempt'), $currentgroup);
        if ($currentgroup) {
            $userssql = get_enrolled_sql($context,
                    array('mod/quiz:reviewmyattempts', 'mod/quiz:attempt'), $currentgroup);
            if ($enrolleduserscount < 1) {
                $where .= ' AND quiza.userid = 0';
            } else {
                $usersjoin = "JOIN ({$userssql[0]}) AS enr ON quiza.userid = enr.id";
                $params += $userssql[1];
            }
        }

        return new qubaid_join("{quiz_attempts} quiza $usersjoin ", 'quiza.uniqueid', $where, $params);
    }

    /**
     * Observer for \core\event\course_module_created event.
     *
     * @param \core\event\course_module_created $event
     * @return void
     */
    public static function course_module_created(\core\event\course_module_created $event) {
        global $DB;

        if ($event->contextinstanceid) {
            $sql = "SELECT *
                    FROM {local_metadata_field}
                    where shortname = ?";
            $result = $DB->get_record_sql($sql, ['ID']);
            if (!empty($result->id)) {
                $fieldid = $result->id;
                $sql = "SELECT *
                        FROM {local_metadata}
                        WHERE instanceid = :instanceid
                        AND fieldid = :fieldid";
                $srcmetadatafieldid = $DB->get_record_sql($sql, array(
                        'instanceid' => $event->contextinstanceid,
                        'fieldid' => $fieldid
                ));
                if (empty($srcmetadatafieldid)) {
                    $item = new stdClass;
                    $item->instanceid = $event->contextinstanceid;
                    $item->fieldid = $fieldid;
                    $item->data = $event->contextinstanceid;
                    $DB->insert_record('local_metadata', $item);
                }
            }
        }
    }

    public static function user_loggedin(\core\event\user_loggedin $event) {
        global $DB;

        $sql = "
            DELETE FROM {user_preferences}
            WHERE userid = ? AND name LIKE('%participant_filter_%')
        ";

        $DB->execute($sql, [$event->objectid]);
    }
}
