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
 * Event observers supported by this module
 *
 * @package    community_comments
 * @copyright   2019 Devlion <info@devlion.co>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/message/lib.php');

/**
 * Event observers supported by this module
 *
 * @package    community_comments
 * @copyright  2019 Nadav Kavalerchk <nadav.kavalerchik@weizmann.ac.il>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class community_comments_observer {

    /**
     * Observer for the event oer_comment_created - send notification to all related users.
     *
     * @param \community_comments\event\oer_comment_created $event
     */
    public static function oer_comment_created(\community_comments\event\oer_comment_created $event) {
        global $DB, $CFG;

        $comment = $DB->get_record('comments', array('id' => $event->objectid));

        // Get userid of source OER module.
        $oeruserid = \local_metadata\mcontext::module()->get($event->other['cmid'], 'userid');
        if (!empty($oeruserid)) {
            $touser = $DB->get_record('user', array('id' => $oeruserid));

            $admin = get_admin();
            $a = new \stdClass;
            $a->comment = $comment->content;
            $a->activityid = $CFG->wwwroot . '/local/community/plugins/oer/activityshare.php?id=' . $event->contextinstanceid;
            $msg = get_string('newoercomment', 'community_comments', $a, $touser->lang);
            $msgid = message_post_message($admin, $touser, $msg, FORMAT_HTML);
        }
    }
}
