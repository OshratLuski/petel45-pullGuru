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
 * Local plugin "sandbox" - Task definition
 *
 * @package    quiz_advancedoverview
 * @copyright  2023 Devlion <info@devlion.co>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace quiz_advancedoverview\task;

/**
 * The quiz_advancedoverview restore courses task class.
 *
 * @package    quiz_advancedoverview
 * @copyright  2023 Devlion <info@devlion.co>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class schedule_advancedoverview extends \core\task\scheduled_task {

    /**
     * Return localised task name.
     *
     * @return string
     */
    public function get_name() {
        return get_string('task', 'quiz_advancedoverview');
    }

    /**
     * Execute scheduled task
     *
     * @return boolean
     */
    public function execute() {
        global $DB;

        $cache = \cache::make_from_params(\cache_store::MODE_APPLICATION, 'advancedoverview', 'data');

        // Hints count.
        $sql = "
            SELECT id, objectid, COUNT(*) AS count
            FROM {logstore_standard_log}
            WHERE eventname LIKE '%question_hint_shown%' AND component = 'theme_petel'
            GROUP BY objectid        
        ";

        $data = [];
        foreach ($DB->get_records_sql($sql) as $item) {
            $data[$item->objectid] = $item->count;
        }

        $cache->set('hints_count', $data);

        // Hints users.
        $sql = "
            SELECT id, objectid, userid
            FROM {logstore_standard_log}
            WHERE eventname LIKE '%question_hint_shown%' AND component = 'theme_petel'                   
        ";

        $users = [];
        foreach ($DB->get_records_sql($sql) as $item) {
            $users[$item->objectid][] = $item->userid;
        }

        $cache->set('hints_users', $users);

        // Chats count and users.
        $sql = "
            SELECT *
            FROM {logstore_standard_log}
            WHERE eventname LIKE '%quiz_student_question%' AND component = 'theme_petel'                  
        ";

        $tmp = $data = $users = [];
        foreach ($DB->get_records_sql($sql) as $item) {
            $obj = json_decode($item->other);
            if (isset($obj->questionid) && !empty($obj->questionid)) {
                $tmp[$obj->questionid][] = $obj->fromuserid;
            }
        }

        foreach ($tmp as $questionid => $item) {
            $data[$questionid] = count(array_unique($item));
            $users[$questionid] = array_unique($item);
        }

        $cache->set('chats_count', $data);

        $cache->set('chats_users', $users);

        return true;
    }
}
