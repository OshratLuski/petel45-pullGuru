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
class module_move extends \core\event\base {
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
        $context = \context_module::instance($eventdata['cmid']);

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
        return "The user with id " . $this->other['userid'] . " move course module " . $this->other['cmid'];
    }

    /**
     * Return localised event name.
     *
     * @return string
     */
    public static function get_name() {
        return get_string('oer_move_module', 'community_oer');
    }

    /**
     * Get URL related to the action.
     *
     * @return \moodle_url
     */
    public function get_url() {
        global $DB;

        if ($record = $DB->get_record('course_modules', ['id' => $this->other['cmid']])) {
            $modinfo = get_fast_modinfo($record->course);
            $cm = $modinfo->get_cm($this->other['cmid']);
            return new \moodle_url('/mod/' . $cm->modname . '/view.php', array('id' => $this->other['cmid']));
        }

        return new \moodle_url('/');
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
