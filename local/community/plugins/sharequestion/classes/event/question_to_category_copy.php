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
 * The community_sharequestion chapter viewed event.
 *
 * @package    community_sharequestion
 * @copyright  2018 Devlion <info@devlion.co>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace community_sharequestion\event;

/**
 * Question copy
 *
 * @package    community_sharequestion
 * @copyright  2018 Devlion <info@devlion.co>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class question_to_category_copy extends \core\event\base {

    /**
     * Create instance of event
     *
     * @param int $id
     * @param obj $eventdata
     * @return obj
     */
    public static function create_event($eventdata) {

        $contextid = \context_system::instance();

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
        $sourcequestionids = $this->other['sourcequestionids'];
        $catid = $this->other['catid'];

        return "The user id '$userid' copied questions " . $sourcequestionids . " to category id " . $catid;
    }

    /**
     * Return localised event name.
     *
     * @return string
     */
    public static function get_name() {
        return get_string('eventcopytocategory', 'community_sharequestion');
    }

    /**
     * Get URL related to the action.
     *
     * @return \moodle_url
     */
    public function get_url() {
        return new \moodle_url('/', array());
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

    /**
     * Get mapping
     *
     * @return array
     */
    public static function get_objectid_mapping() {
        return array();
    }
}
