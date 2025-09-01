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
 * hvp question definition class.
 *
 * @package   qtype_hvp
 * @copyright 2022 onwards SysBind  {@link http://sysbind.co.il}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/question/type/questionbase.php');

/**
 * Represents a hvp question.
 */
class qtype_hvp_question extends question_graded_automatically {

    /**
     * @var question_attempt attemptobj
     */
    protected $attemptobj;
    protected $qaid;


    public function get_expected_data() {
        return array('answer' => PARAM_INT);
    }

    public function get_correct_response() {
        return null;
    }

    public function is_complete_response(array $response) :  bool {
        global $DB;
        $questionusage = $DB->get_record('question_attempts', ['id' => $response['answer']]);
        $this->attemptobj = new question_attempt($this, $questionusage->questionusageid);
        $this->qaid = $response['answer'];
        return true;
    }

    public function is_same_response(array $prevresponse, array $newresponse) {
        return false;
    }

    public function summarise_response(array $response) {
        return "RESPONSE SUMMARY";
    }

    public function get_validation_error(array $response) {
        if ($this->is_complete_response($response)) {
            return '';
        }
        return get_string('pleaseananswerallparts', 'qtype_hvp');
    }

    public function grade_response(array $response) {
        $records = $this->get_records_from_xapi_results();
        $record = array_pop($records);
        $firstrecord = $record ? clone($record) : null;

        while (($record->interaction_type <> 'compound' || !empty($record->parent_id)) && !empty($record) ) {
            $record = array_pop($records);
        }

        $record = $record ?: $firstrecord;

        if (empty($record)) {
            return [0, question_state::$gradedwrong];
        }

        if ($record->max_score == $record->raw_score) {
            return [1, question_state::$gradedright];
        }

        if ($record->max_score > $record->raw_score && $record->raw_score != 0) {
            return [($record->raw_score / $record->max_score), question_state::$gradedpartial];
        }

        if ($record->raw_score == 0) {
            return [0, question_state::$gradedwrong];
        }

        return false;
    }

    /**
     * Return all current question attempts from xAPI table.
     */
    public function get_records_from_xapi_results() {
        global $DB, $USER;

        // Validate context.
        if (!$this->is_valid_context($this->contextid)) {
            return [];
        }

        // Determine the user ID.
        $userid = $this->get_userid_from_payload() ?? $USER->id;

        // Determine the question attempt ID.
        $qaid = $this->qaid ?? $this->get_latest_question_attempt_id($this->id, $userid);

        // Fetch and return records based on the question attempt ID.
        return $qaid
            ? $DB->get_records('qtype_hvp_xapi_results', ['question_attempt_id' => $qaid])
            : [];
    }

    /**
     * Check if the context is valid and of the correct level.
     *
     * @param int $contextid The context ID.
     * @return bool True if the context is valid, false otherwise.
     */
    private function is_valid_context($contextid) {
        global $DB;

        $context = $DB->get_record('context', [
            'id' => $contextid,
            'contextlevel' => CONTEXT_MODULE,
        ]);

        return !empty($context);
    }

    /**
     * Extract and return the user ID from the payload.
     *
     * @return int|null The user ID or null if not found.
     */
    private function get_userid_from_payload() {
        global $DB;

        // Decode the JSON payload.
        $payload = json_decode(file_get_contents('php://input'), true);

        // Extract attempt ID.
        $attemptid = $payload[0]['args']['attemptids'] ?? null;

        if ($attemptid) {
            // Fetch the user ID associated with the attempt ID.
            $quiz_attempt = $DB->get_record('quiz_attempts', ['id' => $attemptid], 'userid', MUST_EXIST);
            return $quiz_attempt->userid;
        }

        return null;
    }


    /**
     * Retrieves the latest question attempt ID for a specific question and user.
     *
     * @param int $questionid The ID of the question.
     * @param int $userid The ID of the user.
     * @return int|null The latest question attempt ID or null if none found.
     */
    private function get_latest_question_attempt_id($questionid, $userid) {
        global $DB;

        $sql = "
        SELECT qa.id AS question_attempt_id
        FROM {question_attempts} qa
        JOIN {question_usages} qu ON qu.id = qa.questionusageid
        JOIN {quiz_attempts} qt ON qt.uniqueid = qu.id
        JOIN {question_attempt_steps} qas ON qas.questionattemptid = qa.id
        WHERE qa.questionid = :questionid
          AND qas.userid = :userid
        ORDER BY qas.timecreated DESC
        LIMIT 1
    ";

        $params = [
            'questionid' => $questionid,
            'userid' => $userid,
        ];

        $record = $DB->get_record_sql($sql, $params);
        return $record ? $record->question_attempt_id : null;
    }
}
