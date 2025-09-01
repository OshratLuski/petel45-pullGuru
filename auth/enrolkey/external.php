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
 * External functions backported.
 *
 * @package    auth_enrolkey
 * @copyright  2018 Devlion <info@devlion.co>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

require_once($CFG->libdir . "/externallib.php");

class auth_enrolkey_external extends external_api {

    public static function validate_token_parameters() {
        return new external_function_parameters(
                array(
                        'token' => new external_value(PARAM_RAW, 'token'),
                )
        );
    }

    public static function validate_token($token) {
        global $DB;

        $instances = $DB->get_records('enrol' , ['password' => $token]);

        if (count($instances) > 0) {
            $res = ['error' => 0, 'message' => get_string('signup_missing', 'auth_enrolkey')];
        } else {
            $res = ['error' => 1, 'message' => get_string('signup_wrong', 'auth_enrolkey')];
        }

        return json_encode($res);
    }

    public static function validate_token_returns() {
        return new external_value(PARAM_RAW, 'Validate token result');
    }
}
