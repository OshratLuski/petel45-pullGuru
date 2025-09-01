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

global $CFG;

require_once($CFG->dirroot . '/mod/quiz/locallib.php');
require_once($CFG->dirroot . '/question/lib.php');
require_once($CFG->dirroot . '/question/type/diagnosticadv/lib.php');

use core_external\external_api;
use core_external\external_function_parameters;
use core_external\external_single_structure;
use core_external\external_value;
use moodle_exception;

/**
 * Class to handle sending messages to the AI for diagnostics.
 *
 * @package    qtype_diagnosticadvai
 * @category   external
 * @copyright  Your Name or Organization
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class send_message extends external_api {

    /**
     * Define the parameters for the execute function.
     *
     * @return external_function_parameters
     */
    public static function execute_parameters() {
        return new external_function_parameters([
                'attemptid' => new external_value(PARAM_INT, 'Attempt ID', VALUE_REQUIRED),
                'message' => new external_value(PARAM_TEXT, 'Message text', VALUE_REQUIRED),
                'cmid' => new external_value(PARAM_INT, 'Course module ID', VALUE_REQUIRED),
                'slot' => new external_value(PARAM_INT, 'Slot ID', VALUE_REQUIRED),
        ]);
    }

    /**
     * Executes the API call to send the message and get a response from AI.
     *
     * @param int $attemptid The attempt ID
     * @param string $message The message to send
     * @param int $cmid The course module ID
     * @param int $slot The slot ID for the question
     * @return array The response from AI
     * @throws moodle_exception If API response is invalid
     */
    public static function execute($attemptid, $message, $cmid, $slot) {
        global $USER, $DB, $SESSION;

        $params = self::validate_parameters(self::execute_parameters(), [
            'attemptid' => $attemptid,
            'message' => $message,
            'cmid' => $cmid,
            'slot' => $slot,
        ]);

        $courseid = $DB->get_field('course_modules', 'course', ['id' => $cmid]);
        $attemptobj = \mod_quiz\quiz_attempt::create($attemptid);
        $qa = $attemptobj->get_question_attempt($slot);
        $question = \question_bank::load_question_data($qa->get_question_id());

        $temperature = $question->options->temperature ?? 0;
        $teacherprompt = $question->options->teacherprompt ?? get_config('qtype_diagnosticadvai', 'prompttemaplate');

        $systemprompt = get_config('qtype_diagnosticadvai', 'systemprompt');
        $conversationhistory = get_chat_history($attemptid) ?? '';
        $newconversation = empty($conversationhistory);

        $extendetoption = $DB->get_record('qtype_diagadvai_options', ['questionid' => $qa->get_question()->id]);
        if (!$extendetoption) {
            throw new moodle_exception('', '', '', null, 'Diagnostic options not found');
        }
        $columns = getlogcolumns();
        $data = get_diagnosticadv_attempts_data($courseid, $extendetoption->relatedqid, $extendetoption->quizid);
        $memberinteam = get_user_in_teamwork($qa->get_last_step()->get_user_id(), $cmid);
        $users = [];
        foreach ($data as $key => $row) {
            if (in_array($row['userid'], $memberinteam)) {
                $users[] = $row;
            }
        }

        $log[] = implode(',', $columns);
        foreach ($users as $user => $value) {
            $tmp = [];
            foreach (array_keys($columns) as $columindex) {
                $tmp[] = $value[$columindex];
            }
            $log[] = implode(',', $tmp);
        }
        $result = implode("\n", $log);

        $message = trim($message);
        $conversationhistory = trim($conversationhistory);
        $teacherprompt = trim($teacherprompt);
        $teacherprompt = strtr($teacherprompt, [
                '{LOG}' => $result,
                '{CONVERSATION}' => $conversationhistory,
                '{STUDENTTEXT}' => $message
        ]);
        $prompt = $systemprompt . "\n" . $teacherprompt;

        if (class_exists('\aiplacement_petel\external\qtype_diagnosticadvai')) {
            $SESSION->openai_adv_temperature = $temperature;
            $params['instanceid'] = $attemptid;
            $response = \aiplacement_petel\external\qtype_diagnosticadvai::execute(
                \context_module::instance($cmid)->id,
                $prompt,
                'diagnosticadvai',
                json_encode($params)
            );

            if ($response['errorcode'] > 0) {
                throw new moodle_exception('', '', '', null, 'Invalid API response: No content received');
            }

            $reply = $response['generatedcontent'];
            $reply = str_replace(['```html', '```'], '', $reply);

            save_message($attemptid, $message, $reply, $USER->id, $prompt);
            $reply = replaceusersnames($reply);
            $filter = new \filter_mathjaxloader\text_filter(\context_system::instance(), []);
            $reply = $filter->filter($reply);
        }
        return [
                'reply' => $reply,
                'newconversation' => $newconversation
        ];
    }

    /**
     * Define the return values for the execute function.
     *
     * @return external_single_structure
     */
    public static function execute_returns() {
        return new external_single_structure([
                'reply' => new external_value(PARAM_RAW, 'Response from AI'),
                'newconversation' => new external_value(PARAM_BOOL, 'Is this a new conversation')
        ]);
    }
}
