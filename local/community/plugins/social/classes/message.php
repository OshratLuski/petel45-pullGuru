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
 * The community_social.
 *
 * @package     community_social
 * @copyright   2019 Devlion <info@devlion.co>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace community_social;

defined('MOODLE_INTERNAL') || die();

class message {

    private $followers;
    private $courseid;
    private $activityid;
    private $sourceuserid;

    public function __construct($sourceuserid, $activityid, $courseid) {
        global $DB;

        $this->sourceuserid = $sourceuserid;
        $this->activityid = $activityid;
        $this->courseid = $courseid;
        $this->followers = $DB->get_records('local_social_followers', ['followuserid' => $sourceuserid, 'isactive' => 1]);
    }

    public function sendmessagetofollowers() {

        if (!empty($this->followers)) {
            foreach ($this->followers as $row) {
                $this->messageToUser($row->userid);
            }
        }
    }

    public function messagetouser($teacherid) {
        global $DB, $USER;

        $a = new \stdClass;
        $a->activity_name = $this->activityid;
        $a->teacher_name = $USER->firstname . ' ' . $USER->lastname;
        $subject = get_string('subject_message_for_teacher', 'community_sharewith', $a);

        $message = '';

        $objinsert = new \stdClass();
        $objinsert->useridfrom = $this->sourceuserid;
        $objinsert->useridto = $teacherid;
        $objinsert->subject = $subject;
        $objinsert->fullmessage = $message;
        $objinsert->fullmessageformat = 2;
        $objinsert->fullmessagehtml = '';
        $objinsert->smallmessage = get_string('info_message_for_teacher', 'community_sharewith');
        $objinsert->notification = 1;
        $objinsert->timecreated = time();
        $objinsert->component = 'local_social';
        $objinsert->eventtype = 'social_activity_shared';
        $messageid = $DB->insert_record('message', $objinsert);

        $objinsert = new \stdClass();
        $objinsert->messageid = $messageid;
        $objinsert->isread = 0;
        $DB->insert_record('message_popup', $objinsert);

        // Save in activities_sharing_shared.
        $objinsert = new \stdClass();
        $objinsert->useridto = $teacherid;
        $objinsert->useridfrom = isset($this->sourceuserid) ? $this->sourceuserid : "";
        $objinsert->courseid = $this->courseid;
        $objinsert->activityid = $this->activityid;
        $objinsert->messageid = $messageid;
        $objinsert->restoreid = null;
        $objinsert->source = 'social_notification';
        $objinsert->complete = 0;
        $objinsert->timecreated = time();

        $rowid = $DB->insert_record('activities_sharing_shared', $objinsert);

        // Update full message and fullmessagehtml.
        $a = new \stdClass;
        $a->restore_id = $rowid;
        $fullmessage = get_string('fullmessagehtml_for_teacher', 'community_sharewith', $a);

        $obj = new \stdClass();
        $obj->id = $messageid;
        $obj->fullmessage = $message;
        $obj->fullmessagehtml = $fullmessage;
        $DB->update_record('message', $obj);
    }

    public static function send_to_teacher($useridfrom, $useridto, $sharedcourseid, $component, $eventtype, $customdata = []) {
        global $DB, $CFG;

        $smallmessage = get_string($component . '_' . $eventtype, 'message_petel');

        $time = time();
        $userfrom = $DB->get_record("user", array('id' => $useridfrom));

        $customdata['custom'] = true;
        $customdata[$eventtype] = true;
        $customdata['firstname'] = $userfrom->firstname;
        $customdata['lastname'] = $userfrom->lastname;
        $customdata['teacher_image'] = $CFG->wwwroot . '/user/pix.php/' . $useridfrom . '/f1.jpg';
        $customdata['dateformat'] = date("d.m.Y", $time);
        $customdata['timeformat'] = date("H:i", $time);

        // Prepare course.
        if (!empty($sharedcourseid)) {
            $row = $DB->get_record('community_social_shrd_crss', array('id' => $sharedcourseid));
            try {
                $course = get_course($row->courseid);
            } catch (\Exception $e) {
                $course = new \stdClass();
                $course->fullname = '';
                $course->id = '';
            }

            $customdata['coursename'] = $course->fullname;
            $customdata['courseurl'] = $CFG->wwwroot . '/course/view.php?id=' . $course->id;
        }

        $objinsert = new \stdClass();
        $objinsert->useridfrom = $useridfrom;
        $objinsert->useridto = $useridto;

        $objinsert->subject = $smallmessage;
        $objinsert->fullmessage = $smallmessage;
        $objinsert->fullmessageformat = 2;
        $objinsert->fullmessagehtml = '';
        $objinsert->smallmessage = $smallmessage;
        $objinsert->component = $component;
        $objinsert->eventtype = $eventtype;
        $objinsert->timecreated = $time;
        $objinsert->customdata = json_encode($customdata);

        $notificationid = $DB->insert_record('notifications', $objinsert);

        $objinsert = new \stdClass();
        $objinsert->notificationid = $notificationid;
        $DB->insert_record('message_petel_notifications', $objinsert);

        return $notificationid;
    }
}
