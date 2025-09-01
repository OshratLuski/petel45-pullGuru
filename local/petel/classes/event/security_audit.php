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
 * Security audit event.
 *
 * @package    local_petel
 * @since      Moodle 3.3
 * @copyright  2013 Nadav Kavalerchik <nadav.kavalerchik@weizmann.ac.il>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_petel\event;

/**
 * Security audit event class.
 *
 * @property-read array $other {
 *      Extra information about event.
 *
 *      - string subject: the security subject.
 *      - string level: the severity level.
 * }
 *
 * @package    core
 * @since      Moodle 3.3
 * @copyright  2013 Nadav Kavalerchik <nadav.kavalerchik@weizmann.ac.il>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class security_audit extends \core\event\base {

    /**
     * Initialise the event data.
     */
    protected function init() {
        $this->data['crud'] = 'c';
        $this->data['edulevel'] = self::LEVEL_OTHER;
    }

    /**
     * Returns localised general event name.
     *
     * @return string
     */
    public static function get_name() {
        return get_string('eventsecurityaudit', 'local_petel');
    }

    /**
     * Returns non-localised description of what happened.
     *
     * @return string
     */
    public function get_description() {
        return "Security audit '\"{$this->other['subject']}\"' with severity level '\"{$this->other['level']}\"'
        by user with id '$this->userid'.";
    }

    /**
     * Custom validation.
     *
     * @throws \coding_exception
     */
    protected function validate_data() {
        parent::validate_data();

        if (!isset($this->relateduserid)) {
            throw new \coding_exception('The \'relateduserid\' must be set.');
        }
        if (!isset($this->other['subject'])) {
            throw new \coding_exception('The \'subject\' value must be set in other.');
        }
        if (!isset($this->other['level'])) {
            throw new \coding_exception('The \'level\' value must be set in other.');
        }
    }

    public static function get_other_mapping() {
        return false;
    }
}
