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
 * User login failed event.
 *
 * @package    core
 * @copyright  2014 Rajesh Taneja <rajesh@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace core\event;

defined('MOODLE_INTERNAL') || die();

/**
 * User login failed event class.
 *
 * @property-read array $other {
 *      Extra information about event.
 *
 *      - string username: name of user.
 *      - int reason: failure reason.
 *      - string password: (optional) plaintext password provided during login attempt
 * }
 *
 * @package    core
 * @since      Moodle 2.7
 * @copyright  2014 Rajesh Taneja <rajesh@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class user_login_failed extends base {
    /**
     * Init method.
     *
     * @return void
     */
    protected function init() {
        $this->context = \context_system::instance();
        $this->data['crud'] = 'r';
        $this->data['edulevel'] = self::LEVEL_OTHER;

        $this->update_abuse_ip_record();
    }

    /**
     * Return localised event name.
     *
     * @return string
     */
    public static function get_name() {
        return get_string('eventuserloginfailed', 'auth');
    }

    /**
     * Returns non-localised event description with id's for admin use only.
     *
     * @return string
     */
    public function get_description() {
        // Note that username could be any random user input.
        $username = s($this->other['username']);
        $reasonid = $this->other['reason'];
        $loginfailed = 'Login failed for user';

        // Add password to the description if provided (for development purposes only).
        $password = isset($this->other['password']) ? ' with password: ' . $this->other['password'] : '';

        switch ($reasonid){
            case 1:
                return $loginfailed." '{$password}'. User does not exist (error ID '{$reasonid}').";
            case 2:
                return $loginfailed." '{$password}'. User is suspended (error ID '{$reasonid}').";
            case 3:
                return $loginfailed." '{$password}'. Most likely the password did not match (error ID '{$reasonid}').";
            case 4:
                return $loginfailed." '{$password}'. User is locked out (error ID '{$reasonid}').";
            case 5:
                return $loginfailed." '{$password}'. User is not authorised (error ID '{$reasonid}').";
            default:
                return $loginfailed." '{$password}', error ID '{$reasonid}'.";

        }
    }

    /**
     * Get URL related to the action.
     *
     * @return \moodle_url
     */
    public function get_url() {
        if (isset($this->data['userid'])) {
            return new \moodle_url('/user/profile.php', array('id' => $this->data['userid']));
        } else {
            return null;
        }
    }

    /**
     * Custom validation.
     *
     * @throws \coding_exception when validation does not pass.
     * @return void
     */
    protected function validate_data() {
        parent::validate_data();

        if (!isset($this->other['reason'])) {
            throw new \coding_exception('The \'reason\' value must be set in other.');
        }

        if (!isset($this->other['username'])) {
            throw new \coding_exception('The \'username\' value must be set in other.');
        }
    }

    public static function get_other_mapping() {
        return false;
    }

    protected function update_abuse_ip_record() {
        global $DB;

        $ip = getremoteaddr(); // Get the IP address of the user.

        try {
            // Check if the IP address is already in the table
            $existingrecord = $DB->get_record('abuse_ip', ['ip' => $ip]);

            if ($existingrecord) {
                // If the record exists, check the 'error' field
                if (empty($existingrecord->error)) {
                    // If 'error' is empty or null, update 'timeupdated' to now
                    $existingrecord->timeupdated = time();
                    $DB->update_record('abuse_ip', $existingrecord);
                } else {
                    // If 'error' is not empty, update 'status' to 1 and 'timeupdated' to now
                    $existingrecord->status = 1;
                    $existingrecord->timeupdated = time();
                    $DB->update_record('abuse_ip', $existingrecord);
                }
            } else {
                // If the IP address is not in the table, insert a new record
                $record = new \stdClass();
                $record->ip = $ip;
                $record->status = 1;
                $record->timecreated = time();

                $DB->insert_record('abuse_ip', $record);
            }
        } catch (\exception $e) {

        }
    }
}
