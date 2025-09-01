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
 * External API for retrieving diagnostic AI messages.
 *
 * @package    qtype_diagnosticadvai
 * @category   external
 * @copyright  2025 Your Name
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace qtype_diagnosticadvai\external;

defined('MOODLE_INTERNAL') || die();

use core_external\external_api;
use core_external\external_function_parameters;
use core_external\external_single_structure;
use core_external\external_multiple_structure;
use core_external\external_value;
use filter_mathjaxloader;

require_once($CFG->dirroot . '/question/type/diagnosticadv/lib.php');
/**
 * Class get_message
 *
 * Provides external API for retrieving AI message history.
 */
class get_message extends external_api {
    /**
     * Returns the parameters definition.
     *
     * @return external_function_parameters
     */
    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([
            'attemptid' => new external_value(PARAM_INT, 'Attempt ID', VALUE_REQUIRED),
        ]);
    }

    /**
     * Retrieves messages for a given attempt.
     *
     * @param int $attemptid Attempt ID.
     * @return array The message history.
     */
    public static function execute(int $attemptid): array {
        global $DB;

        $params = self::validate_parameters(self::execute_parameters(), ['attemptid' => $attemptid]);

        $messages = $DB->get_records('qtype_diagadvai_prompts', ['qattemptid' => $params['attemptid']], 'timecreated ASC');
        $filter = new \filter_mathjaxloader\text_filter(\context_system::instance(), []);

        $formattedmessages = [];
        foreach ($messages as $msg) {
            if (!empty(trim($msg->prompt))) {
                $msg->prompt = replaceusersnames($msg->prompt);
                $msg->prompt = $filter->filter($msg->prompt);
                $formattedmessages[] = [
                    'text' => $msg->prompt,
                    'sender' => 'user',
                    'timestamp' => date('H:i:s', $msg->timecreated),
                ];
            }

            if (!empty(trim($msg->response))) {
                $msg->response = replaceusersnames($msg->response);
                $msg->response = $filter->filter($msg->response);
                $formattedmessages[] = [
                    'text' => $msg->response,
                    'sender' => 'ai',
                    'timestamp' => date('H:i:s', $msg->timemodified),
                ];
            }
        }

        return [
            'messages' => $formattedmessages,
        ];
    }

    /**
     * Returns the response structure.
     *
     * @return external_single_structure
     */
    public static function execute_returns(): external_single_structure {
        return new external_single_structure([
            'messages' => new external_multiple_structure(
                new external_single_structure([
                    'text' => new external_value(PARAM_RAW, 'Message text'),
                    'sender' => new external_value(PARAM_RAW, 'Sender (user/ai)'),
                    'timestamp' => new external_value(PARAM_RAW, 'Timestamp'),
                ])
            ),
        ]);
    }
}