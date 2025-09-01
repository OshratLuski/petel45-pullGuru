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
 * External Web Service class
 *
 * @package    theme_petel
 * @copyright  2019 Devlion.co
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . "/externallib.php");

/**
 * External functions for theme petel.
 *
 * @package     theme_petel
 * @copyright   2019 Devlion <info@devlion.co>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class theme_petel_courseformat_external extends external_api {

    // Set timer preferences.
    public static function quiz_set_timer_preferences_parameters() {
        return new external_function_parameters(
            array(
            'remindertype' => new external_value(PARAM_TEXT, 'remindertype'),
            'isvisible' => new external_value(PARAM_BOOL, 'isVisible'),
            'cmid' => new external_value(PARAM_INT, 'cmid'),
            )
        );
    }

    public static function quiz_set_timer_preferences($remindertype, $isvisible, $cmid) {
        global $DB, $CFG, $PAGE;
        $response = ['status' => false];

        $PAGE->set_context(context_system::instance());

        $data = self::validate_parameters(self::quiz_set_timer_preferences_parameters(),
            array(
                'remindertype' => $remindertype,
                'isvisible' => $isvisible,
                'cmid' => $cmid,
            )
        );

        // Check teacher.
        // $isteacher = false;
        // if (is_siteadmin() || has_capability('moodle/course:update', context_module::instance($cmid))) {
        //     $isteacher = true;
        // }

        // if (!$isteacher) {
        //     return $response;
        // }

        $name = 'custom_quiz_timer_settings|'.$data['cmid'];
        $value = $data['remindertype'] . '|' . $data['isvisible'];
        $plugin = 'theme_petel';

        if (set_config($name, $value, $plugin)) {
            $config = [];
            if ($getconfig = get_config($plugin, $name)) {
                $config = explode('|', $getconfig);
                $response['remindertype'] = $config[0];
                $response['isvisible'] = ($config[1] == "" || $config[1] == 0 || $config[1] == false) ? false : true;
                $response['status'] = true;
            }
        }

        return $response;
    }

    public static function quiz_set_timer_preferences_returns() {
        return new external_single_structure(array(
            'status' => new external_value(PARAM_BOOL, 'Whether the parameters was set'),
            'remindertype' => new external_value(PARAM_TEXT, 'remindertype'),
            'isvisible' => new external_value(PARAM_BOOL, 'isVisible'),
        ));
    }

    // Get timer preferences.
    public static function quiz_get_timer_preferences_parameters() {
        return new external_function_parameters(
            array(
            'cmid' => new external_value(PARAM_INT, 'cmid'),
            )
        );
    }

    public static function quiz_get_timer_preferences($cmid) {
        global $DB, $CFG, $PAGE;
        $response['remindertype'] = 'withoutWarnings';
        $response['isvisible'] = false;

        $PAGE->set_context(context_system::instance());

        $data = self::validate_parameters(self::quiz_get_timer_preferences_parameters(),
            array(
                'cmid' => $cmid,
            )
        );

        $name = 'custom_quiz_timer_settings|'.$data['cmid'];
        $plugin = 'theme_petel';

        $config = '';
        if ($getconfig = get_config($plugin, $name)) {
            $config = explode('|', $getconfig);

            $response['remindertype'] = $config[0];
            $response['isvisible'] = ($config[1] == "" || $config[1] == 0 || $config[1] == false) ? false : true;
        }

        return $response;
    }

    public static function quiz_get_timer_preferences_returns() {
        return new external_single_structure(array(
            'remindertype' => new external_value(PARAM_TEXT, 'remindertype'),
            'isvisible' => new external_value(PARAM_BOOL, 'isVisible'),
        ));
    }

}
