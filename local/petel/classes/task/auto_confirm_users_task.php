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

defined('MOODLE_INTERNAL') || die();

require_once(__DIR__ . '/../../lib.php');

/**
 * Simple task to delete user accounts for users who have not confirmed in time.
 */
class auto_confirm_users_task extends \core\task\scheduled_task {

    /**
     * Get a descriptive name for this task (shown to admins).
     *
     * @return string
     */
    public function get_name() {
        return get_string('autoconfirmuserstask', 'local_petel');
    }

    /**
     * Do the job.
     * Throw exceptions on errors (the job will be retried).
     */
    public function execute() {
        global $CFG, $DB;

        // Confirm users who haven't confirmed within required period.
        if (!empty($CFG->autoconfirmusers)) {
            $rs = $DB->get_recordset_sql("SELECT *
                                             FROM {user}
                                            WHERE confirmed = 0 AND deleted = 0");
            foreach ($rs as $user) {
                $DB->set_field('user', 'confirmed', '1', ['id' => $user->id]);
                mtrace("auto confirm user  " . fullname($user, true) . " ($user->id)");
            }
            $rs->close();
        }
    }
}

