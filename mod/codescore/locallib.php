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

defined('MOODLE_INTERNAL') || die;

require_once("$CFG->libdir/filelib.php");
require_once("$CFG->libdir/resourcelib.php");
require_once("$CFG->dirroot/mod/page/lib.php");

function codescore_save_attempt($data) {
    global $DB, $USER;

    $lastattempt = $DB->get_record_sql(
        "SELECT *
           FROM {codescore_attempts}
       ORDER BY attempt desc",
        [
            'userid' => $USER->id,
            'codescore' => $data->cmid,
        ]
    );

    $data->codescore = $data->cmid;
    $data->userid = $USER->id;
    $data->studentnotes = $data->notes;
    $data->attempt = $lastattempt ? $lastattempt->attempt + 1 : 1;
    $data->state = 'inprogress';
    $data->timefinish = time();
    $data->grade = 0;
    $data->correctedcode = '';
    $data->output = '';
    $data->gradednotificationsenttime = 0;

    $result = $DB->insert_record('codescore_attempts', $data, true);
    $params = [
        'relateduserid' => $USER->id,
        'context' => context_module::instance($data->cmid),
        'objectid' => context_module::instance($data->cmid)->id,
    ];

    $event = \mod_codescore\event\attempt_submit::create($params);
    $event->trigger();

    return $result;
}

function codescore_set_grade_attempt($data) {
    global $DB, $USER;

    $attempts = $DB->update_record('codescore_attempts', array(
        'id' => $data->id,
        'timegraded' => time(),
        'grade' => $data->grade,
    ));
    return $attempts;
}

function codescore_exec_adhoc($id) {
    global $DB;

    $attempt = $DB->get_record('codescore_attempts', ['id' => $id]);

    $task = new \mod_codescore\task\check_attempt;
    $task->set_custom_data($attempt);
    \core\task\manager::queue_adhoc_task($task, true);
    $task = new \tool_monitor\task\check_subscriptions();
    $task->execute();
}

function codescore_get_user_attempts($codescore, $userid = null) {
    global $DB, $USER;

    $userid = $userid ?? $USER->id;

    $attempts = $DB->get_records('codescore_attempts',
        ['userid' => $userid, 'codescore' => $codescore]
    );

    return $attempts;
}

function codescore_get_attempts($codescore) {
    global $DB, $USER;

    $context = context_module::instance($codescore);
    $isteacher = has_capability('mod/quiz:addinstance', $context);
    if ($isteacher) {
        $attempts = $DB->get_records('codescore_attempts',
            ['codescore' => $codescore]
        );
        return $attempts;
    }
    return [];
}
