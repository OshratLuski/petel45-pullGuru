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
 * External function to retrieve the correct answer in the diagnostic statistics report via AJAX.
 *
 * @package    quiz_diagnosticstats
 * @copyright  2024 Oshrat Luski <oshrat.luski@weizmann.ac.il>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace quiz_diagnosticstats\external;

defined('MOODLE_INTERNAL') || die();
require_once($CFG->dirroot . '/lib/externallib.php');
require_once($CFG->dirroot . '/question/engine/bank.php');

use external_api;
use external_function_parameters;
use external_single_structure;
use external_value;
use moodle_exception;

/**
 * Class get_correctanswer
 *
 * Provides the external function to retrieve the correct answer for a question in the diagnostic statistics report.
 *
 * @package quiz_diagnosticstats
 */
class get_correctanswer extends external_api {

    /**
     * Defines the parameters required for the get_correctanswer function.
     *
     * @return external_function_parameters
     */
    public static function get_correctanswer_parameters(): external_function_parameters {
        return new external_function_parameters([
            'questionid' => new external_value(PARAM_INT, 'The ID of the question'),
        ]);
    }

    /**
     * Retrieves the correct answer for a specific question.
     *
     * @param int $questionid The ID of the question.
     * @return array An array containing the correct answer ID.
     * @throws moodle_exception If no answers are found for the question.
     */
    public static function get_correctanswer(int $questionid): array {
        // Validate the provided parameters.
        $params = self::validate_parameters(
            self::get_correctanswer_parameters(),
            ['questionid' => $questionid]
        );

        $questionid = $params['questionid'];

        // Load the question from the question bank.
        $question = \question_bank::load_question($questionid);

        $correctresponse = [];

        if (isset($question->answers)) {
            // Iterate over the answers and find the correct one.
            foreach ($question->answers as $answerid => $answer) {
                if ($answer->fraction > 0) {
                    $correctresponse['answer'] = $answerid;
                    break;
                }
            }
        } else {
            throw new moodle_exception('No answers found for this question.');
        }

        return [
            'correctAnswerId' => $correctresponse['answer']
        ];
    }

    /**
     * Defines the structure of the data returned by the get_correctanswer function.
     *
     * @return external_single_structure
     */
    public static function get_correctanswer_returns(): external_single_structure {
        return new external_single_structure([
            'correctAnswerId' => new external_value(PARAM_INT, 'The ID of the correct answer'),
        ]);
    }
}
