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
 * Local plugin "community_oer" - Upgrade plugin tasks
 *
 * @package     community_oer
 * @copyright   2018 Devlion <info@devlion.co>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace community_oer\task;

/**
 * The local_tutorials
 * Send please complete your tutorial (SCORM activity) reminder, by intervals.
 *
 * @package     community_oer
 * @copyright   2018 Devlion <info@devlion.co>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class adhoc_email_toteachers_new_oer_activity extends \core\task\adhoc_task {

    /**
     * Return localised task name.
     *
     * @return string
     */
    public function get_component() {
        return 'community_oer';
    }

    /**
     * Execute adhoc task
     *
     * @return boolean
     */
    public function execute() {

        $lockkey = 'email_toteachers_new_oer_activity' . time();
        $lockfactory = \core\lock\lock_config::get_lock_factory('community_oer_task');
        $lock = $lockfactory->get_lock($lockkey, 0);

        if ($lock !== false) {
            $this->run_adhoc_send_mail_about_new_oer_activity();
            $lock->release();
        }
    }

    public function run_adhoc_send_mail_about_new_oer_activity() {
        global $DB, $CFG;

        $customdata = $this->get_custom_data();
        $customdata = (array) $customdata;

        $from = get_admin();
        $subject = get_config('local_community', 'subjectmailnewoeractivity');
        $content = get_config('local_community', 'messagemailnewoeractivity');
        $content = str_replace(["{activity}", "{course}", "{cmid}"], [$customdata['name'],
                $customdata['course'], $customdata['cmid']], $content);

        $url = new \moodle_url('/local/community/plugins/oer/removefrom_magarmaillist.php');
        $content .= get_string('removefrommagarmaillist', 'community_oer', array('url' => $url->out()));

        $sql = "
        SELECT u.* 
        FROM {cohort} cohort
        JOIN {cohort_members} members ON members.cohortid = cohort.id 
            AND cohort.idnumber = '" . $CFG->defaultcohortscourserequest . "'
        JOIN {user} u ON u.id = members.userid  
    ";

        $teachers = $DB->get_records_sql($sql);
        foreach ($teachers as $teacher) {
            if (!get_user_preferences('remove_from_magar_mailing_list', '', $teacher)) {
                email_to_user($teacher, $from, $subject, $content, $content);
            }
        }
    }
}
