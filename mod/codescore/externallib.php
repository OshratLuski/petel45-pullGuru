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

defined('MOODLE_INTERNAL') || die;

require_once("$CFG->libdir/externallib.php");
require_once("$CFG->dirroot/user/lib.php");
require_once("$CFG->dirroot/course/lib.php");
require_once("$CFG->dirroot/mod/codescore/locallib.php");


class mod_codescore_external extends external_api {
    public static function delete_attempts_parameters(): external_function_parameters {
        return new external_function_parameters(
            [
                'ids' => new external_value(PARAM_RAW, ''),
                'cmid' => new external_value(PARAM_RAW, ''),
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
    public static function delete_attempts($ids, $cmid) {
        require_once(__DIR__ . '/../../config.php');
        require_once(__DIR__ . '/lib.php');

        global $USER, $DB, $PAGE, $COURSE;

        $ids = explode(",", $ids);

        foreach ($ids as &$id) {
            $DB->delete_records_select('codescore_attempts', "id=" . $id);
        }

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
    public static function delete_attempts_returns() {
        return new external_single_structure(
            [
                'result' => new external_value(PARAM_RAW, ''),
            ]
        );
    }
    public static function regrade_attempts_parameters(): external_function_parameters {
        return new external_function_parameters(
            [
                'ids' => new external_value(PARAM_RAW, ''),
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
    public static function regrade_attempts($ids) {
        require_once(__DIR__ . '/../../config.php');
        require_once(__DIR__ . '/lib.php');

        global $USER, $DB, $PAGE, $COURSE;

        $ids = explode(",", $ids);

        foreach ($ids as $id) {
            $attempt = $DB->get_record('codescore_attempts', ['id' => $id]);
            $attempt->state = 'inprogress';
            $attempt->aigrade = null;
            $DB->update_record('codescore_attempts', $attempt);
            codescore_exec_adhoc($id);
        }

        return ['result' => 'event dispatched'];
    }

    /**
     * Define the webservice response.
     *
     * @return external_description
     */
    public static function regrade_attempts_returns() {
        return new external_single_structure(
            [
                'result' => new external_value(PARAM_RAW, ''),
            ]
        );
    }
}
