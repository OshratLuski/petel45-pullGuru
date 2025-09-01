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
class adhoc_createcourse extends \core\task\adhoc_task {

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
            $this->run_cron_createcourse();
            $lock->release();
        }
    }

    public function run_cron_createcourse() {

        $data = $this->get_custom_data();

        $result = \local_petel\funcs::copy_course_to_new_category($data->currentuserid, $data->categoryid, $data->courseid,
                $data->coursename);

        if ($result) {
            $smallmessage = $fullmessage = get_string('messagecoursectreate', 'local_petel', $result);

            // Send message to user.
            \local_petel\funcs::send_message_to_teacher(
                    $data->currentuserid, $data->currentuserid, 'local_petel', 'coursecreate',
                    $smallmessage, $fullmessage, array('contexturl' => $result->course_url)
            );

            // Send mail to user.
            $touser = \core_user::get_user($data->currentuserid);
            $subject = get_string('subjectmailcoursecreated', 'local_petel');

            if (!empty($touser) && !empty($touser->id) && !empty($touser->email)) {
                $fromuser = get_admin();
                email_to_user($touser, $fromuser, $subject, $fullmessage, $fullmessage);
            }

            // Send mail to admin.
            if (!empty(get_config('local_petel', 'admin_email'))) {

                $touser = new \stdClass();
                $touser->email = get_config('local_petel', 'admin_email');
                $touser->firstname = '';
                $touser->lastname = '';
                $touser->maildisplay = true;
                $touser->mailformat = 1;
                $touser->id = -99;
                $touser->firstnamephonetic = '';
                $touser->lastnamephonetic = '';
                $touser->middlename = '';
                $touser->alternatename = '';

                $subject = get_string('subjectmailcoursescreated', 'local_petel');
                $messagehtml = get_string('htmlmailcoursescreated', 'local_petel', $result);

                $fromuser = get_admin();
                email_to_user($touser, $fromuser, $subject, $messagehtml, $messagehtml);
            }
        }
    }

}
