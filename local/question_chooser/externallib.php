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


use core_external\external_description;
use core_external\external_value;
use core_external\external_format_value;
use core_external\external_single_structure;
use core_external\external_multiple_structure;
use core_external\external_function_parameters;
use core_external\external_warnings;

/**
 * External functions.
 *
 * @package local_question_chooser
 * @copyright 2022 Devlion.co
 * @author Devlion
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class local_question_chooser_external extends \core_external\external_api {

    /**
     * Store favorite data.
     *
     * @return array of settings
     */
    public static function save_qtypes_favorites($qtypes) {
        global $USER;

        $params = self::validate_parameters(self::save_qtypes_favorites_parameters(),
                array('qtypes' => $qtypes));

        $usercontext = context_user::instance($USER->id);
        $ufservice = \core_favourites\service_factory::get_service_for_user_context($usercontext);
        $favorite = $ufservice->get_favourite('core_question', $params['qtypes'], $USER->id, $usercontext);

        if (isset($favorite)) {
            $ufservice->delete_favourite('core_question', $params['qtypes'], $USER->id, $usercontext);
        } else {
            $ufservice->create_favourite('core_question', $params['qtypes'], $USER->id, $usercontext);
        }

        return ['status' => 1];
    }

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function save_qtypes_favorites_parameters() {
        return new external_function_parameters(
                array(
                        'qtypes' => new external_value(PARAM_RAW, 'qtypes')
                )
        );
    }

    /**
     * Returns description of method result value
     *
     * @return external_description
     */
    public static function save_qtypes_favorites_returns() {
        return new external_single_structure(
                array(
                        'status' => new external_value(PARAM_RAW, 'Status'),
                )
        );
    }

    /**
     * Returns description of method parameters.
     *
     * @return external_function_parameters
     */
    public static function toggle_qtypes_recommendation_parameters() {
        return new external_function_parameters([
                'name' => new external_value(PARAM_TEXT, 'name of the qtype or whatever', VALUE_REQUIRED),
        ]);
    }

    /**
     * Update the recommendation for an qtype item.
     *
     * @param string $name identifier for this qtype.
     * @return array some warnings or something.
     */
    public static function toggle_qtypes_recommendation($name) {

        $params = self::validate_parameters(self::toggle_qtypes_recommendation_parameters(),
                array('name' => $name));

        $admin = get_admin();
        $userid = $admin->id;

        $usercontext = context_user::instance($userid);
        $ufservice = \core_favourites\service_factory::get_service_for_user_context($usercontext);
        $recommend = $ufservice->count_favourites_by_type('core_question', 'recommend_' . $params['name']);

        if ($recommend > 0) {
            $ufservice->delete_favourite('core_question', 'recommend_' . $params['name'], $userid, $usercontext);
        } else {
            $ufservice->create_favourite('core_question', 'recommend_' . $params['name'], $userid, $usercontext);
        }

        return ['name' => $name];
    }

    /**
     * Returns warnings.
     *
     * @return external_description
     */
    public static function toggle_qtypes_recommendation_returns() {
        return new external_single_structure(
                [
                        'name' => new external_value(PARAM_TEXT, 'name of the qtype or whatever'),
                ]
        );
    }
}
