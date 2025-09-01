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
require_once($CFG->dirroot . '/local/petel/locallib.php');

/**
 * The local_petel BBB BigBlueButton WS task class.
 *
 * @package    local_petel
 * @copyright  2020 Nadav Kavalerchik <nadav.kavalerchik@weizmann.ac.il>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class adhoc_participiant extends \core\task\adhoc_task {

    /**
     * Return localised task name.
     *
     * @return string
     */
    public function get_component() {
        return 'local_petel';
    }

    /**
     * Execute adhoc task
     *
     * @return boolean
     */
    public function execute() {

        $micro = str_replace('.', '', microtime(true));
        $lockkey = rand(10, 1000) . $micro;
        $lockfactory = \core\lock\lock_config::get_lock_factory('local_petel_task');
        $lock = $lockfactory->get_lock($lockkey, 0);

        if ($lock !== false) {
            $this->run_cron_participiant();
            $lock->release();
        }
    }

    public function run_cron_participiant() {
        global $DB;

        $data = $this->get_custom_data();

        $result = [];
        foreach (json_decode($data->userids) as $userid) {
            $res = \local_petel\funcs::copy_course_to_new_category($userid, $data->categoryid, $data->courseid, null, $data->roleid);
            if ($res) {
                $result[] = $res;
            }

            // Remove enrol self method if not set in original course.
            if ($data->nullcheck) {
                if (!$DB->get_record('enrol', ['enrol' => 'self', 'courseid' => $data->courseid])) {
                    $DB->delete_records('enrol', ['enrol' => 'self', 'courseid' => $res->course_id]);
                }
            }
        }

        // Set system groups for user.
        foreach (json_decode($data->groups) as $cohortid) {
            foreach (json_decode($data->userids) as $userid) {
                if (!$DB->get_record('cohort_members', ['cohortid' => $cohortid, 'userid' => $userid])) {
                    $DB->insert_record('cohort_members', [
                            'cohortid' => $cohortid,
                            'userid' => $userid,
                            'timeadded' => time()
                    ]);
                }
            }
        }

        if (!empty($result)) {

            // Render html.
            $messagehtml = '';
            $catcreated = $catnotcreated = [];
            foreach ($result as $item) {
                if ($item->flag_create_category) {
                    $catcreated[] = $item;
                } else {
                    $catnotcreated[] = $item;
                }
            }

            if (!empty($catcreated)) {
                $messagehtml .= get_string('htmlcategorycreated', 'local_petel');

                foreach ($catcreated as $item) {
                    $messagehtml .= get_string('htmlmailcoursescreated', 'local_petel', $item);
                }
            }

            if (!empty($catnotcreated)) {
                $messagehtml .= '<br>' . get_string('htmlcategorynotcreated', 'local_petel');

                foreach ($catnotcreated as $item) {
                    $messagehtml .= get_string('htmlmailcoursescreated', 'local_petel', $item);
                }
            }

            // Send mail to current teacher.
            $touser = \core_user::get_user($data->currentuserid);
            $subject = get_string('subjectmailcoursescreated', 'local_petel');

            if (!empty($touser) && !empty($touser->id) && !empty($touser->email)) {
                $fromuser = get_admin();
                email_to_user($touser, $fromuser, $subject, $messagehtml, $messagehtml);
            }
        }
    }

}
