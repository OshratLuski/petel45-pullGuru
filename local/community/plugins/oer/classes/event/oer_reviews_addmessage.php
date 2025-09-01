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
 * The community_oer chapter viewed event class.
 *
 * @package    community_oer
 * @copyright  2018 Devlion <info@devlion.co>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace community_oer\event;

/**
 * The community_oer chapter viewed event class.
 *
 * @package    community_oer
 * @copyright  2018 Devlion <info@devlion.co>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class oer_reviews_addmessage extends \core\event\base {
    /**
     * Create instance of event.
     *
     * @param \stdClass $book
     * @param \context_module $context
     * @param \stdClass $chapter
     * @return chapter_viewed
     * @since Moodle 2.7
     *
     */

    public static function create_event($eventdata) {
        switch ($eventdata['type']) {
            case 'activity':
                $context = \context_module::instance($eventdata['objid']);
                break;
            case 'course':
                $context = \context_course::instance($eventdata['objid']);
                break;
            default:
                $context = \context_system::instance();
        }

        $data = array(
                'context' => $context,
                'other' => $eventdata
        );
        /** @var chapter_viewed $event */

        $event = self::create($data);
        return $event;
    }

    /**
     * Returns description of what happened.
     *
     * @return string
     */
    public function get_description() {
        switch ($this->other['type']) {
            case 'activity':
                return "The user with id " . $this->other['userid'] . " added review message on activity tab with cmid " .
                        $this->other['objid'];
                break;
            case 'course':
                return "The user with id " . $this->other['userid'] . " added review message on course tab with courseid " .
                        $this->other['objid'];
                break;
            case 'question':
                return "The user with id " . $this->other['userid'] . " added review message on question tab with questionid " .
                        $this->other['objid'];
                break;
            case 'sequence':
                return "The user with id " . $this->other['userid'] . " added review message on sequence tab with sequenceid " .
                        $this->other['objid'];
                break;
            default:
                return "The user with id " . $this->other['userid'] . " added review message";
        }
    }

    /**
     * Return localised event name.
     *
     * @return string
     */
    public static function get_name() {
        return get_string('oer_reviews_addmessage', 'community_oer');
    }

    /**
     * Get URL related to the action.
     *
     * @return \moodle_url
     */
    public function get_url() {
        switch ($this->other['type']) {
            case 'activity':
                return new \moodle_url('/local/community/plugins/oer/activityshare.php', array('id' => $this->other['objid']));
                break;
            default:
                return new \moodle_url('/local/community/plugins/oer/', array());
        }
    }

    /**
     * Init method.
     *
     * @return void
     */
    protected function init() {
        $this->data['crud'] = 'r';
        $this->data['edulevel'] = self::LEVEL_PARTICIPATING;
    }

    public static function get_objectid_mapping() {
        return array();
    }
}
