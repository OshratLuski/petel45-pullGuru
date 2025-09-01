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
 *
 * @package     mod_codescore
 * @copyright   2024 Devlion <info@devlion.co>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_codescore\external;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/externallib.php');
require_once('../../config.php');

use external_api;
use external_description;
use external_function_parameters;
use external_single_structure;
use external_value;
use stdClass;

class delete_attempts extends external_api {
    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters(
            [
                'ids' => new external_value(PARAM_RAW, '', VALUE_DEFAULT),
                'cmid' => new external_value(PARAM_RAW, '', VALUE_DEFAULT, ''),
            ]
        );
    }

    /**
     * Set the questions slot parameters to display the question template.
     *
     * @param int $slotid Slot id to display.
     * @param int $newversion the version to set. 0 means 'always latest'.
     * @return array
     */
    public static function execute($ids, $cmid) {
        require_once(__DIR__ . '../../../config.php');
        require_once(__DIR__ . '../lib.php');
        global $USER, $DB, $PAGE, $COURSE;

        $ids = explode(",", $ids);

        foreach ($ids as &$id) {
            $DB->delete_records_select('codescore_attempts', "id=" . $id);
        }

        $modulecontext = context_module::instance($cmid);

        $params = [
            'relateduserid' => $USER->id,
            'objectid' => context_module::instance($cmid)->id,
            'context' => $modulecontext,
        ];

        $event = \mod_codescore\event\attempt_delete::create($params);
        $event->trigger();

        return ['result' => 'event dispatched'];
    }

    /**
     * Define the webservice response.
     *
     * @return external_description
     */
    public static function execute_returns() {
        return new external_single_structure(
            [
                'result' => new external_value(PARAM_RAW, ''),
            ]
        );
    }
}
