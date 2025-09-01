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
 * Serve question type files
 *
 * @since      Moodle 2.0
 * @package    qtype_diagnosticadv
 * @copyright  Devlion.ltd <info@devlion.co>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();


/**
 * Checks file access for diagnostic ADV questions.
 * @package  qtype_diagnosticadv
 * @category files
 * @param stdClass $course course object
 * @param stdClass $cm course module object
 * @param stdClass $context context object
 * @param string $filearea file area
 * @param array $args extra arguments
 * @param bool $forcedownload whether or not force download
 * @param array $options additional options affecting the file serving
 * @return bool
 */
function qtype_diagnosticadv_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, array $options=array()) {
    global $DB, $CFG;
    require_once($CFG->libdir . '/questionlib.php');
    question_pluginfile($course, $context, 'qtype_diagnosticadv', $filearea, $args, $forcedownload, $options);
}


/**
 * @throws dml_exception
 */
function get_summary_data($attempts, $slot) {
    global $CFG;
    require_once($CFG->dirroot . '/mod/quiz/locallib.php');
    $data = [];
    $questions = [];
    foreach ($attempts as $attempt) {
        $attemptobj = \mod_quiz\quiz_attempt::create($attempt->id);
        $lastattempt = $attemptobj->get_question_attempt($slot)->get_last_qt_data();

        if (empty($questions)) {
            $questions = $attemptobj->get_question_attempt($slot)->get_question()->answers;
            foreach ($questions as $key => $question) {
                if ($question->custom) {
                    $questions['custom'] = $question;
                }
            }
        }

        if (isset($lastattempt['comment'])) {
            $lastattempt['comment'] = strip_tags($lastattempt['comment']);
            $lastattempt['comment'] = str_replace('&nbsp;', '', $lastattempt['comment']);
        }

        if (isset($lastattempt['security'])) {
            $lastattempt['security'] = strip_tags($lastattempt['security']);
            $lastattempt['security'] = str_replace('&nbsp;', '', $lastattempt['security']);
        }

        if (isset($lastattempt['customanswer'])) {
            $lastattempt['customanswer'] = strip_tags($lastattempt['customanswer']);
            $lastattempt['customanswer'] = str_replace('&nbsp;', '', $lastattempt['customanswer']);
        }

        $lastattempt['answer'] = $questions[$lastattempt['answer']];
        $lastattempt['userid'] = $attemptobj->get_userid();
        $user = \core_user::get_user($attemptobj->get_userid());
        $lastattempt['name'] = fullname($user);
        $data[] = $lastattempt;
    }
    return $data;
}

function get_diagnosticadv_attempts_data($courseid, $relatedqid, $quizid) {
    global $DB;

    $sql = "SELECT cm.*, quizslots.slot
            FROM {quiz_slots} quizslots
            JOIN {question_references} qr ON qr.itemid = quizslots.id
            JOIN {question_bank_entries} qbe ON qbe.id = qr.questionbankentryid
            JOIN {question_versions} qv ON qv.questionbankentryid = qbe.id
            JOIN {question} q ON q.id = qv.questionid
            JOIN {question_categories} qc ON qc.id = qbe.questioncategoryid
            JOIN {course_modules} cm ON cm.instance = quizslots.quizid
            JOIN {modules} m ON m.id = cm.module
            WHERE cm.course = :courseid 
            AND q.id = :relatedqid 
            AND quizslots.quizid = :quizid
            AND m.name = 'quiz'";

    $quizcms = $DB->get_record_sql($sql, [
        'courseid' => $courseid,
        'relatedqid' => $relatedqid,
        'quizid' => $quizid
    ]);
    $attempts = get_quiz_attempts($quizcms);
    $data = get_summary_data($attempts,$quizcms->slot);
    $users = [];
    foreach ($data as $key => $value) {
        if (isset($value['securitysure'])) {
            $value['securitysure'] = $value['securitysure'] == 'yes' ? 1 : 0;
        } else {
            $value['securitysure'] = '';
        }
        $users[$value['userid']]['studentname'] = "user" . $value['userid'];
        if ($value['answer']->custom) {
            $users[$value['userid']]['answer'] = $value['customanswer'];
        } else {
            $users[$value['userid']]['answer'] = $value['answer']->answer;
        }
        $users[$value['userid']]['comment'] = trim($value['comment']);
        $users[$value['userid']]['ifsecured'] = $value['securitysure'];
        $users[$value['userid']]['securedanswer'] = !empty($value['security']) ? trim($value['security']) : '';
        $users[$value['userid']]['userid'] = $value['userid'];
    }
    return $users;
}

function get_quiz_attempts($cm) {
    global $DB;
    $params = ['state' => 'finished', 'cmid' => $cm->id];

    $sql = "SELECT qa.*
            FROM {quiz_attempts} qa
            JOIN {quiz} q ON qa.quiz = q.id
            JOIN {course_modules} cm ON cm.instance = q.id
            WHERE cm.id = :cmid AND qa.state = :state
            ORDER BY qa.attempt DESC";

    $attempts = $DB->get_records_sql($sql, $params);
    $lastattempts = [];

    foreach ($attempts as $attempt) {
        if (empty($lastattempts[$attempt->userid])) {
            $lastattempts[$attempt->userid] = $attempt;
        }
    }

    return $lastattempts;
}

function get_user_in_teamwork($userid, $moduleid) {
    global $DB;
    $sql = "
            SELECT *
            FROM {local_teamwork_members} m1
            where m1.teamworkgroupid in 
                (SELECT g.id
                FROM {local_teamwork_members} m
                JOIN {local_teamwork_groups} g ON g.id = m.teamworkgroupid
                JOIN {local_teamwork} t ON t.id = g.teamworkid
                WHERE m.userid = ? AND t.moduleid = ?)";
    $users = $DB->get_records_sql($sql, [$userid, $moduleid]);
    $tmp = [];
    foreach ($users as $user) {
        $tmp[] = $user->userid;
    }
    return $tmp;
}

function get_chat_history($attemptid) {
        global $DB;

        if (!$attemptid || !is_numeric($attemptid)) {
            return '';
        }

        $records = $DB->get_records('qtype_diagadvai_prompts', ['qattemptid' => $attemptid], 'timecreated ASC', 'prompt, response');

        $history = [];
        foreach ($records as $record) {
            $history[] = "User: " . $record->prompt;
            $history[] = "AI: " . $record->response;
        }
        return implode("\n\n", $history);
    }

function save_message($attemptid, $message, $response, $userid, $prompt) {
    global $DB;
    $record = new \stdClass();
    $record->qattemptid = $attemptid;
    $record->userid = $userid;
    $record->prompt = $message;
    $record->fullprompt = $prompt;
    $record->response = $response;
    $record->usermodified = $userid;
    $record->timecreated = time();
    $record->timemodified = time();

    $DB->insert_record('qtype_diagadvai_prompts', $record);
}

function replaceusersnames($text) {
    global $DB;
    preg_match_all('/user\d+/', $text, $matches);
    $userids = [];
    if (!empty($matches[0])) {
        foreach ($matches[0] as $match) {
            $userids[] = str_replace('user', '', $match);
        }
    }

    if ($userids) {
        $placeholders = implode(',', array_fill(0, count($userids), '?'));
        $sql = "SELECT * FROM {user} WHERE id IN ($placeholders)";
        $users = $DB->get_records_sql($sql, $userids);

        // Print or process users
        foreach ($users as $user) {
            $text = str_replace('user' . $user->id, fullname($user), $text);
        }
    }

    preg_match_all('/User\d+/', $text, $matches);
    $userids = [];
    if (!empty($matches[0])) {
        foreach ($matches[0] as $match) {
            $userids[] = str_replace('User', '', $match);
        }
    }

    if ($userids) {
        $placeholders = implode(',', array_fill(0, count($userids), '?'));
        $sql = "SELECT * FROM {user} WHERE id IN ($placeholders)";
        $users = $DB->get_records_sql($sql, $userids);

        // Print or process users
        foreach ($users as $user) {
            $text = str_replace('User' . $user->id, fullname($user), $text);
        }
    }
    return $text;
}

function getlogcolumns(){
    $columns =  get_config('qtype_diagnosticadv', 'logcolumns');
    $lines = explode("\n", trim($columns));
    foreach ($lines as $line) {
        // Trim spaces and split by colon
        $parts = explode(":", $line, 2);
        if (count($parts) === 2) {
            $key = trim($parts[0]);
            $value = trim($parts[1]);
            $result[$key] = $value;
        }
    }
    return $result;
}
