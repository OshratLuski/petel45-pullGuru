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
 * @package    local
 * @subpackage h5ptracker
 * @copyright  2022 Weizmann institute of science, Israel.
 * @author 2021 Devlion.co
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_h5ptracker;

defined('MOODLE_INTERNAL') || die();

class external extends \external_api
{

    public static function track_actions_parameters() {
        return new \external_function_parameters(
            [
                'cmid' => new \external_value(PARAM_INT, 'cm id'),
                'seconds' => new \external_value(PARAM_FLOAT, 'timestamp'),
                'action' => new \external_value(PARAM_ALPHA, 'action name'),
            ]
        );
    }

    public static function track_actions($cmid, $seconds, $action) {
        global $USER;

        $return = [
            'result' => true,
            'message' => ''
        ];

        $params = self::validate_parameters(self::track_actions_parameters(),
            [
                'cmid' => $cmid,
                'seconds' => $seconds,
                'action' => $action,
            ]
        );

        try {
            $context = \context_module::instance($params['cmid']);
            $classname = '\local_h5ptracker\event\h5p_' . $params['action'];
            if (class_exists($classname)) {
                $eventparams = [
                    'objectid' => $params['cmid'],
                    'relateduserid' => $USER->id,
                    'context' => $context,
                    'other' => [
                        'seconds' => $params['seconds'],
                    ]
                ];

                $event = $classname::create($eventparams);
                $event->trigger();
            } else {
                $return = [
                    'result' => false,
                    'message' => get_string('undefinedevent', 'local_h5ptracker', $params['action'])
                ];
            }

        } catch (\Exception $e) {
            $return = [
                'result' => false,
                'message' => $e->getMessage() . $e->getTraceAsString()
            ];
        }

        return $return;
    }

    public static function track_actions_returns() {
        return new \external_single_structure(
            [
                'result'    => new \external_value(PARAM_BOOL, 'Result'),
                'message'    => new \external_value(PARAM_RAW, 'message', VALUE_OPTIONAL),
            ]
        );
    }
}


