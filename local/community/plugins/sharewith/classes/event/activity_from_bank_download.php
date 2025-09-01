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
 * The community_sharewith chapter viewed event.
 *
 * @package    community_sharewith
 * @copyright  2013 Frédéric Massart
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace community_sharewith\event;

class activity_from_bank_download extends \core\event\base {
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

    public static function create_event($id, $eventdata) {

        $contextid = \context_course::instance($id);

        $data = array(
                'context' => $contextid,
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
        $userid = $this->other['userid'];
        $instanceid = $this->other['instanceid'];
        $targetcourseid = $this->other['targetcourseid'];

        return "The user with id '$userid' downloaded activity id '$instanceid' to course id " . $targetcourseid;
    }

    /**
     * Return localised event name.
     *
     * @return string
     */
    public static function get_name() {
        return get_string('eventactivityfrombankdownload', 'community_sharewith');
    }

    /**
     * Get URL related to the action.
     *
     * @return \moodle_url
     */
    public function get_url() {
        return new \moodle_url('/course/view.php', array('id' => $this->contextinstanceid));
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
