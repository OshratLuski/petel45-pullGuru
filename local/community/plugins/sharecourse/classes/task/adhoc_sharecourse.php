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
 * @package    community_sharecourse
 * @copyright  2018 Devlion <info@devlion.co>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace community_sharecourse\task;

use context_course;
use context_module;

defined('MOODLE_INTERNAL') || die();

require_once(__DIR__ . '/../../lib.php');

/**
 * The local_sandbox restore courses task class.
 *
 * @package    community_sharecourse
 * @copyright  2014 Alexander Bias, Ulm University <alexander.bias@uni-ulm.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class adhoc_sharecourse extends \core\task\adhoc_task {

    /**
     * Return localised task name.
     *
     * @return string
     */
    public function get_component() {
        return 'community_sharecourse';
    }

    /**
     * Execute adhoc task
     *
     * @return boolean
     */
    public function execute() {

        $lockkey = 'sharecourse_cron' . time();
        $lockfactory = \core\lock\lock_config::get_lock_factory('community_sharecourse_task');
        $lock = $lockfactory->get_lock($lockkey, 0);

        if ($lock !== false) {
            $this->run_cron_sharecourse();
            $lock->release();
        }
    }

    public function run_cron_sharecourse() {
        global $DB, $USER, $CFG;

        $obj = $DB->get_records('community_sharecourse_task', array('status' => 0));

        // End working.
        foreach ($obj as $item) {
            $item->status = 2;
            $DB->update_record('community_sharecourse_task', $item);
        }

        require_once($CFG->dirroot . '/local/community/plugins/sharecourse/classes/duplicate_course.php');

        foreach ($obj as $item) {
            try {

                switch ($item->type) {
                    case 'coursecopy':
                        $newcourse = $this->duplicate_course($item);
                        \local_metadata\mcontext::course()->saveEmpty($newcourse['id'], 'csubject');
                        \local_metadata\mcontext::course()->save($newcourse['id'], 'chidden', 1);
                        break;

                    case 'coursecopy_share':
                        require_once($CFG->dirroot . '/local/community/plugins/sharecourse/externallib.php');

                        $newcourse = $this->duplicate_course($item);
                        $cmid = \local_metadata\mcontext::course()->get($item->courseid, 'cID');
                        \local_metadata\mcontext::course()->save($newcourse['id'], 'cID', $cmid);

                        $metadata = json_decode($item->metadata);
                        $metadata->courseid = $newcourse['id'];

                        // Unshare new course.
                        \local_metadata\mcontext::course()->saveEmpty($newcourse['id'], 'csubject');

                        \community_sharecourse_external::submit_upload_course(json_encode($metadata));

                        \community_sharecourse_external::unshare_course($item->courseid);

                        // Cache data module course.
                        $course = new \community_oer\course_oer;
                        $course->recalculate_all_courses_in_db_cache();

                        break;
                }

                // Fill share table.
                $DB->insert_record('community_sharecourse_shr', [
                        'type' => $item->type,
                        'courseid' => $item->courseid,
                        'catid' => $item->categoryid,
                        'useridfrom' => $USER->id,
                        'timecreated' => time(),
                ]);

                $item->status = 1;
                $DB->update_record('community_sharecourse_task', $item);

            } catch (\Exception $e) {
                $item->error = $e->getMessage();
                $DB->update_record('community_sharecourse_task', $item);
            }
        }
    }

    private function duplicate_course($item) {
        global $DB;

        $tc = get_course($item->courseid);

        $category = $DB->get_record('course_categories', array('id' => $tc->category));

        $fullname = $tc->fullname . ' ' . $category->name . ' ' . get_string('wordcopy', 'community_sharecourse');

        $shortnamedefault = $tc->shortname . '-' . $tc->category;
        $shortname = $this->create_relevant_shortname($shortnamedefault);

        $adminid = isset($CFG->adminid) ? $CFG->adminid : 2;

        // Copy course.
        $newcourse = \duplicate_course::duplicate_course($adminid, $tc->id, $fullname, $shortname, $item->categoryid);

        // Copy metadata and update cID.
        \local_metadata\mcontext::course()->copy_all_metadata($tc->id, $newcourse['id']);
        \local_metadata\mcontext::course()->save($newcourse['id'], 'cID', $tc->id);

        // Change startdate and enddate in new course.
        $startdate = time();
        $diff = $tc->enddate - $tc->startdate;
        $enddate = ($diff > 0) ? $startdate + $diff : 0;

        $obj = $DB->get_record('course', ['id' => $newcourse['id']]);
        $obj->startdate = $startdate;
        $obj->enddate = $enddate;

        $DB->update_record('course', $obj);

        // Set user to course.
        if ($item->userid && !is_siteadmin($item->userid)) {
            $enrol = enrol_get_plugin('manual');

            $enrolinstances = enrol_get_instances($newcourse['id'], true);
            foreach ($enrolinstances as $courseenrolinstance) {
                if ($courseenrolinstance->enrol == 'manual') {
                    $instance = $courseenrolinstance;
                    break;
                }
            }

            $role = $DB->get_record('role', ['shortname' => 'editingteacher']);

            if (!empty($enrol) && !empty($instance) && !empty($role)) {
                $enrol->enrol_user($instance, $item->userid, $role->id);
            }
        }

        $roles = array();
        $context = \context_course::instance($tc->id);
        if ($userroles = get_user_roles($context, $item->userid)) {
            foreach ($userroles as $role) {
                $roles[] = $role->shortname;
            }
        }

        $usertype = 'teacher';
        if (in_array('teachercolleague', $roles)) {
            $usertype = 'teachercolleague';
        }

        $eventdata = array(
                'userid' => $item->userid,
                'courseid' => $tc->id,
                'categoryid' => $tc->category,
                'targetcourseid' => $newcourse['id'],
                'usertype' => $usertype,
        );

        \community_sharecourse\event\course_copy::create_event($newcourse['id'], $eventdata)->trigger();

        // Send mail.
        $this->send_mail_to_teacher($item, $newcourse);

        // Send notification.
        $this->send_notification_to_teacher($item, $newcourse);

        return $newcourse;
    }

    private function create_relevant_shortname($shortname) {
        global $DB;

        $i = 1;
        do {
            $arr = $DB->get_records('course', array('shortname' => $shortname));
            if (!empty($arr)) {
                $shortname .= $i;
                $i++;
            } else {
                break;
            }
        } while (1);

        return $shortname;
    }

    private function send_mail_to_teacher($duplicatejob, $target) {
        global $DB, $OUTPUT, $CFG;

        $metadataobj = !empty($duplicatejob) ? json_decode($duplicatejob->metadata) : null;
        $messagetype = isset($metadataobj->notification) ? $metadataobj->notification : "";

        $supportuser = \core_user::get_support_user();

        $userto = $DB->get_record('user', array('id' => $duplicatejob->userid));
        $newcourse = get_course($target['id']);

        $message = '';
        $subject = get_string('mail_subject_to_teacher_course', 'community_sharewith');

        // Render html.
        $templatecontext = array();
        $a = new \stdClass;
        $a->user_fname = $userto->firstname;
        $a->user_lname = $userto->lastname;
        $a->coursename = $newcourse->fullname;
        $a->url = $CFG->wwwroot . "/course/view.php?id=" . $target['id'];
        $templatecontext['url'] = $a->url;
        $templatecontext['notification_course_to_teacher'] =
                get_string('notification_course_to_teacher', 'community_sharewith', $a);
        $messagehtml = $OUTPUT->render_from_template('community_sharewith/mails/mail_course_to_teacher', $templatecontext);

        email_to_user($userto, $supportuser, $subject, $message, $messagehtml);
    }

    private function send_notification_to_teacher($duplicatejob, $target) {
        global $DB, $OUTPUT;

        $metadataobj = !empty($duplicatejob) ? json_decode($duplicatejob->metadata) : null;
        $messagetype = isset($metadataobj->notification) ? $metadataobj->notification : "";

        $supportuser = \core_user::get_support_user();

        $userto = $DB->get_record('user', array('id' => $duplicatejob->userid));
        $newcourse = get_course($target['id']);

        $subject = get_string('mail_subject_to_teacher_course', 'community_sharewith');

        // Render html.
        $templatecontext = array();
        $a = new \stdClass;
        $a->user_fname = $userto->firstname;
        $a->user_lname = $userto->lastname;
        $a->coursename = $newcourse->fullname;
        $url = new \moodle_url('/course/view.php', array("id" => $target['id']));
        $a->url = $url->out();
        $templatecontext['notification_course_to_teacher'] =
                get_string('notification_course_to_teacher', 'community_sharewith', $a);
        $messagehtml =
                $OUTPUT->render_from_template('community_sharewith/notifications/notification_course_to_teacher', $templatecontext);
        $objinsert = new \stdClass();
        $objinsert->useridfrom = $supportuser->id;
        $objinsert->useridto = $userto->id;

        $objinsert->subject = $subject;
        $objinsert->fullmessage = $subject;
        $objinsert->fullmessageformat = 2;
        $objinsert->fullmessagehtml = $messagehtml;
        $objinsert->smallmessage = $subject;
        $objinsert->component = 'community_sharewith';
        $objinsert->eventtype = 'copy_course';
        $objinsert->timecreated = time();
        $objinsert->customdata = json_encode(array());

        $notificationid = $DB->insert_record('notifications', $objinsert);

        $objinsert = new \stdClass();
        $objinsert->notificationid = $notificationid;
        $DB->insert_record('message_petel_notifications', $objinsert);
    }
}
