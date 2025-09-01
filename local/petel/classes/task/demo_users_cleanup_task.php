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
 * Local plugin "petel" - Task definition
 *
 * @package    local_petel
 * @copyright  2020 Nadav Kavalerchik <nadav.kavalerchik@weizmann.ac.il>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_petel\task;

/**
 * The local_petel cache task class.
 *
 * @package    local_petel
 * @copyright  2022 Weizmann institute of science, Israel.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class demo_users_cleanup_task extends \core\task\scheduled_task {

    const DEFAULT_BULK_USER_PREFIX = 'bulkuser';

    /**
     * Return localised task name.
     *
     * @return string
     */
    public function get_name() {
        return get_string('democleanuptask', 'local_petel');
    }

    /**
     * Execute scheduled task
     *
     * @return boolean
     */
    public function execute() {
        global $CFG, $DB;
        require_once(__DIR__ . '/../../locallib.php');
        require_once("$CFG->dirroot/user/lib.php");

        $bulkuserprefix = $CFG->local_petel_prefix_bulk_user ?? static::DEFAULT_BULK_USER_PREFIX;
        $params = ['username' => $DB->sql_like_escape($bulkuserprefix) . '%'];
        $pluginman = \core_plugin_manager::instance();

        $users = $DB->get_records_select('user', 'deleted = 0 AND ' .
                $DB->sql_like('username', ':username', false, false), $params);

        foreach ($users as $user) {
            $isexpired = \local_petel\funcs::logout_by_session_timeout_per_user($user->id, false, false);
            $userenrolments = $DB->get_records('user_enrolments', ['userid' => $user->id]);
            foreach ($userenrolments as $userenrolment) {
                $instance = $DB->get_record('enrol', ['id' => $userenrolment->enrolid]);
                $plugin = enrol_get_plugin($instance->enrol);

                // If session exist or if seession have been killed.
                if ($isexpired || (!empty($CFG->sessiontimeout) && $userenrolment->timemodified + $CFG->sessiontimeout < time())) {
                    $plugin->unenrol_user($instance, $userenrolment->userid);
                    mtrace("Unenrol user id: " . $user->username);
                    user_delete_user($user);
                    mtrace('User deleted: '.$user->username);
                    user_create_user($user);
                    mtrace('User created: '.$user->username);
                }
            }
        }
    }
}
