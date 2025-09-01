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
 * External function to set anonymous state for quiz diagnostic stats.
 *
 * @package   quiz_diagnosticstats
 * @copyright 2024 Oshrat Luski
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace quiz_diagnosticstats\external;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/lib/externallib.php');

use external_api;
use external_function_parameters;
use external_value;
use context_module;
use required_capability_exception;

class set_anonymousstate extends external_api {

    /**
     * Defines the parameters for the external function.
     *
     * @return external_function_parameters
     */
    public static function set_anonymousstate_parameters(): external_function_parameters {
        return new external_function_parameters([
            'cmid' => new external_value(PARAM_INT, 'The course module ID'),
            'state' => new external_value(PARAM_INT, 'The anonymous state (1 for enabled, 0 for disabled)')
        ]);
    }

    /**
     * Sets the anonymous state for a given course module.
     *
     * @param int $cmid The course module ID.
     * @param int $state The anonymous state.
     * @return array Confirmation message.
     * @throws required_capability_exception If the user lacks permissions.
     */
    public static function set_anonymousstate(int $cmid, int $state): array {
        global $USER;

        // Validate parameters.
        $params = self::validate_parameters(self::set_anonymousstate_parameters(), [
            'cmid' => $cmid,
            'state' => $state
        ]);
        debugging('DEBUG: Received cmid: ' . $params['cmid'], DEBUG_DEVELOPER);
        debugging('DEBUG: Received state: ' . $params['state'], DEBUG_DEVELOPER);
        // Get context and validate capability.
        $context = context_module::instance($params['cmid']);
        self::validate_context($context);

        if (!has_capability('mod/quiz:manage', $context)) {
            throw new required_capability_exception($context, 'mod/quiz:manage', 'nopermissions', '');
        }

        // Set the user preference for the anonymous state.
        $name = 'quiz_advancedoverview_anon_' . $params['cmid'];
        set_user_preference($name, $params['state'], $USER->id);

        return ['status' => 'success', 'message' => get_string('anonymousstatesaved', 'quiz_diagnosticstats')];
    }

    /**
     * Defines the return values of the external function.
     *
     * @return \external_single_structure
     */
    public static function set_anonymousstate_returns() {
        return new \external_single_structure([
            'status' => new external_value(PARAM_TEXT, 'Status of the operation'),
            'message' => new external_value(PARAM_TEXT, 'Confirmation message')
        ]);
    }
}
