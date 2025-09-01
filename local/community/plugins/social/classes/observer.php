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
 * Event observers for community_social
 *
 * @package    community_social
 * @copyright  2019 Devlion <info@devlion.co>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace community_social;

defined('MOODLE_INTERNAL') || die();

/**
 * community_social observers class
 */
class observer {

    /**
     * course_module_visibility_changed
     *
     * @param \core\event\base $event The event.
     * @return void
     */
    public static function course_module_visibility_changed(\community_social\event\course_module_visibility_changed $event) {
        global $CFG, $COURSE, $USER;

        require_once($CFG->libdir . "/coursecatlib.php");

        $eventdata = $event->get_data();

        // Trigger jbserver for social.
        $userid = \local_metadata\mcontext::module()->get($eventdata['objectid'], 'userid');

        // Recache user.
        $social = new \community_social\social();
        $social->refreshUser($userid);

        // Trigger observer only if cm is shown (opened).
        if ($eventdata['other']['action'] == 'show') {
            $coursecat = \core_course_category::get($COURSE->category);
            $coursecatstree = $coursecat->get_parents();
            array_push($coursecatstree, $COURSE->category);

            if (in_array(\community_oer\main_oer::get_oer_category(), $coursecatstree)) {

                // Find userid of the user, who has shared cm (activity) to the oer catalog.
                $sharinguserid = \local_metadata\mcontext::module()->get($eventdata['objectid'], 'userid');
                $sharinguserid = !empty($sharinguserid) ? $sharinguserid : $USER->id; // Fallback to current user id.

                // Sending notification to following teachers.
                $classfollowers = new \community_social\message($sharinguserid, $eventdata['objectid'], $eventdata['courseid']);
                $classfollowers->sendMessageToFollowers();
            }
        }
    }

    public static function course_deleted(\core\event\course_deleted $event) {
        global $DB;

        $courseid = $event->objectid;
        $users = [];

        $data = $DB->get_records('community_social_shrd_crss', array('courseid' => $courseid));
        foreach ($data as $item) {
            $users[] = $item->userid;
            $DB->delete_records('community_social_shrd_crss', array('id' => $item->id));
        }

        $data = $DB->get_records('community_social_collegues', array('social_shared_courses_id' => $courseid));
        foreach ($data as $item) {
            $users[] = $item->userid;
            $DB->delete_records('community_social_collegues', array('id' => $item->id));
        }

        $data = $DB->get_records('community_social_requests', array('social_shared_courses_ids' => $courseid));
        foreach ($data as $item) {
            $users[] = $item->userid;
            $users[] = $item->usersendid;
            $DB->delete_records('community_social_requests', array('id' => $item->id));
        }

        // Oercatalog course.
        $course = new \community_oer\course_oer;
        if ($oercourse = $course->query()->compare('cid', $courseid)->get()) {
            $oercourse = reset($oercourse);
            foreach ($oercourse->users as $user) {
                $users[] = $user->userid;
            }
        }

        // Oercatalog activities.
        $activity = new \community_oer\activity_oer;
        if ($oeractivities = $activity->query()->compare('courseid', $courseid)->get()) {
            foreach ($oeractivities as $activity) {
                foreach ($activity->users as $user) {
                    $users[] = $user->userid;
                }
            }
        }

        // Refresh users.
        $social = new \community_social\social();
        foreach (array_unique($users) as $userid) {
            $social->refreshUser($userid);
        }
    }

    public static function course_updated(\core\event\course_updated $event) {
        global $DB;

        $courseid = $event->objectid;
        $users = [];

        $data = $DB->get_records('community_social_shrd_crss', array('courseid' => $courseid));
        foreach ($data as $item) {
            $users[] = $item->userid;
        }

        $data = $DB->get_records('community_social_collegues', array('social_shared_courses_id' => $courseid));
        foreach ($data as $item) {
            $users[] = $item->userid;
        }

        $data = $DB->get_records('community_social_requests', array('social_shared_courses_ids' => $courseid));
        foreach ($data as $item) {
            $users[] = $item->userid;
            $users[] = $item->usersendid;
        }

        // Oercatalog course.
        $course = new \community_oer\course_oer;
        if ($oercourse = $course->query()->compare('cid', $courseid)->get()) {
            $oercourse = reset($oercourse);
            foreach ($oercourse->users as $user) {
                $users[] = $user->userid;
            }
        }

        // Oercatalog activities.
        $activity = new \community_oer\activity_oer;
        if ($oeractivities = $activity->query()->compare('courseid', $courseid)->get()) {
            foreach ($oeractivities as $activity) {
                foreach ($activity->users as $user) {
                    $users[] = $user->userid;
                }
            }
        }

        // Refresh users.
        $social = new \community_social\social();
        foreach (array_unique($users) as $userid) {
            $social->refreshUser($userid);
        }
    }

    public static function user_deleted(\core\event\user_deleted $event) {
        global $DB;

        $userid = $event->objectid;

        $DB->delete_records('community_social_collegues', array('userid' => $userid));

        $DB->delete_records('community_social_followers', array('userid' => $userid));
        $DB->delete_records('community_social_followers', array('followuserid' => $userid));

        $DB->delete_records('community_social_requests', array('userid' => $userid));
        $DB->delete_records('community_social_requests', array('usersendid' => $userid));

        $DB->delete_records('community_social_shrd_crss', array('userid' => $userid));

        $DB->delete_records('community_social_usr_dtls', array('userid' => $userid));

        // Refresh all users.
        $social = new \community_social\social();
        foreach ($DB->get_records('community_social_usr_dtls') as $item) {
            $social->refreshUser($item->userid);
        }
    }

    public static function user_updated(\core\event\user_updated $event) {

        $userid = $event->objectid;

        // Refresh user.
        $social = new \community_social\social();
        $social->refreshUser($userid);
    }

    public static function course_module_updated(\core\event\course_module_updated $event) {

        $cmid = $event->objectid;
        $users = [];

        // Oercatalog activities.
        $activity = new \community_oer\activity_oer;
        if ($oeractivity = $activity->query()->compare('cmid', $cmid)->get()) {
            $oeractivity = reset($oeractivity);
            foreach ($oeractivity->users as $user) {
                $users[] = $user->userid;
            }
        }

        // Refresh users.
        $social = new \community_social\social();
        foreach (array_unique($users) as $userid) {
            $social->refreshUser($userid);
        }
    }

    public static function course_module_deleted(\core\event\course_module_deleted $event) {

        $cmid = $event->objectid;
        $users = [];

        // Oercatalog activities.
        $activity = new \community_oer\activity_oer;
        if ($oeractivity = $activity->query()->compare('cmid', $cmid)->get()) {
            $oeractivity = reset($oeractivity);
            foreach ($oeractivity->users as $user) {
                $users[] = $user->userid;
            }
        }

        // Refresh users.
        $social = new \community_social\social();
        foreach (array_unique($users) as $userid) {
            $social->refreshUser($userid);
        }
    }

    public static function update_metadata(\local_metadata\event\update_metadata $event) {

        $users = [];

        if ($event->contextlevel == CONTEXT_COURSE) {

            $courseid = $event->objectid;

            // Oercatalog course.
            $course = new \community_oer\course_oer;
            if ($oercourse = $course->query()->compare('cid', $courseid)->get()) {
                $oercourse = reset($oercourse);
                foreach ($oercourse->users as $user) {
                    $users[] = $user->userid;
                }
            }

            // Oercatalog activities.
            $activity = new \community_oer\activity_oer;
            if ($oeractivities = $activity->query()->compare('courseid', $courseid)->get()) {
                foreach ($oeractivities as $activity) {
                    foreach ($activity->users as $user) {
                        $users[] = $user->userid;
                    }
                }
            }
        }

        if ($event->contextlevel == CONTEXT_MODULE) {

            $cmid = $event->objectid;

            // Oercatalog activities.
            $activity = new \community_oer\activity_oer;
            if ($oeractivity = $activity->query()->compare('cmid', $cmid)->get()) {
                $oeractivity = reset($oeractivity);
                foreach ($oeractivity->users as $user) {
                    $users[] = $user->userid;
                }
            }
        }

        // Refresh users.
        $social = new \community_social\social();
        foreach (array_unique($users) as $userid) {
            $social->refreshUser($userid);
        }
    }
}
