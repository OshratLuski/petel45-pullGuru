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

namespace mod_learningmap\external;

/**
 * Class set_externallinkcompleted
 *
 * @package    mod_learningmap
 * @copyright  2024 oshiOsh
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use external_api;
use external_function_parameters;
use external_value;
use external_single_structure;
use external_multiple_structure;

class set_externallinkcompleted extends external_api{
    /**
     * Returns description of method parameters.
     *
     * This method defines the structure of the input parameters for the API function.
     * It accepts an array of completed places, where each place is represented as a structure
     * containing the following keys:
     * - placeId: The ID of the completed place (string).
     * - linkText: The link text of the completed place (string).
     *
     * @return external_function_parameters The structure of the parameters.
     */
    public static function store_completed_places_parameters(): external_function_parameters {
        return new external_function_parameters([
            'completedPlaces' => new external_multiple_structure(
                new external_single_structure([
                    'placeId' => new external_value(PARAM_TEXT, 'ID of the completed place'),
                    'linkText' => new external_value(PARAM_TEXT, 'Link text of the completed place')
                ])
            ),
        ]);
    }

    /**
     * Process the completed places and return a response.
     *
     * @param array $completedplaces Array of completed places (each containing placeId and linkText)
     * @return array Status of the operation
     */
    public static function store_completed_places(array $completedplaces): array {
        global $SESSION;

        // Ensure $SESSION->completedplaces is defined as an object.
        if (!isset($SESSION->completedplaces) || !is_object($SESSION->completedplaces)) {
            $SESSION->completedplaces = new \stdClass();
        }
    
        // Add new data to $SESSION->completedplaces
        foreach ($completedplaces as $completedPlace) {
            if (isset($completedPlace['placeId'], $completedPlace['linkText'])) {
                // Add the data directly to the object
                $SESSION->completedplaces->{$completedPlace['placeId']} = $completedPlace['linkText'];
            }
        }
    
        // Save the session and release the lock
        \core\session\manager::write_close();
    
        return [
            'status' => 'success',
            'message' => count((array)$SESSION->completedplaces),
        ];
    }

    
    /**
     * Returns the description of the response.
     *
     * @return external_single_structure
     */
    public static function store_completed_places_returns(): external_single_structure {
        return new external_single_structure([
            'status' => new external_value(PARAM_TEXT, 'The status of the update operation'),
            'message' => new external_value(PARAM_TEXT, 'Message describing the result'),
        ]);
    }
}
