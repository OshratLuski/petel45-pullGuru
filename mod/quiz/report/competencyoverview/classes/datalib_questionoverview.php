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
 * Code for loading and saving question attempts to and from the database.
 *
 * Note that many of the methods of this class should be considered private to
 * the question engine. They should be accessed through the
 * {@link question_engine} class. For example, you should call
 * {@link question_engine::save_questions_usage_by_activity()} rather than
 * {@link question_engine_data_mapper::insert_questions_usage_by_activity()}.
 * The exception to this is some of the reporting methods, like
 * {@link question_engine_data_mapper::load_attempts_at_question()}.
 *
 * (TODO, probably we should split this class up, so that it has no public
 * methods. They should all be moved to a new public class.)
 *
 * A note for future reference. This code is pretty efficient but there are some
 * potential optimisations that could be contemplated, at the cost of making the
 * code more complex:
 *
 * 1. (This is probably not worth doing.) In the unit-of-work save method, we
 *    could get all the ids for steps due to be deleted or modified,
 *    and delete all the question_attempt_step_data for all of those steps in one
 *    query. That would save one DB query for each ->stepsupdated. However that number
 *    is 0 except when re-grading, and when regrading, there are many more inserts
 *    into question_attempt_step_data than deletes, so it is really hardly worth it.
 *
 * @package   quiz_questionsoverview
 * @copyright   2020 Devlion <info@devlion.co>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * This class controls the loading and saving of question engine data to and from
 * the database.
 *
 * @package   quiz_questionsoverview
 * @copyright   2020 Devlion <info@devlion.co>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class question_engine_data_mapper_qiestionoverview extends question_engine_data_mapper {

    /**
     * Load information about the latest state of each question from the database.
     *
     * This method may be called publicly.
     *
     * @param qubaid_condition $qubaids used to restrict which usages are included
     *                                  in the query. See {@link qubaid_condition}.
     * @param array            $slots   A list of slots for the questions you want to know about.
     * @param string|null      $fields
     * @return array of records. See the SQL in this function to see the fields available.
     */
    public function load_questions_usages_firstest_steps(qubaid_condition $qubaids, $slots, $fields = null) {
        if ($slots === []) {
            return [];
        } else if ($slots !== null) {
            [$slottest, $params] = $this->db->get_in_or_equal($slots, SQL_PARAMS_NAMED, 'slot');
            $slotwhere = " AND qa.slot {$slottest}";
        } else {
            $slotwhere = '';
            $params = [];
        }

        if ($fields === null) {
            $fields = "qas.id,
    qa.id AS questionattemptid,
    qa.questionusageid,
    qa.slot,
    qa.behaviour,
    qa.questionid,
    qa.variant,
    qa.maxmark,
    qa.minfraction,
    qa.maxfraction,
    qa.flagged,
    qa.questionsummary,
    qa.rightanswer,
    qa.responsesummary,
    qa.timemodified,
    qas.id AS attemptstepid,
    qas.sequencenumber,
    qas.state,
    qas.fraction,
    qas.timecreated,
    qas.userid";

        }

        $records = $this->db->get_records_sql("
            SELECT
                {$fields}
            
            FROM {$qubaids->from_question_attempts('qa')}
            JOIN {question_attempt_steps} qas ON qas.questionattemptid = qa.id
                    AND qas.sequencenumber = {$this->firstest_step_for_qa_subquery()}
            
            WHERE
                {$qubaids->where()}
                $slotwhere
        ", $params + $qubaids->from_where_params());

        return $records;
    }

    protected function firstest_step_for_qa_subquery($questionattemptid = 'qa.id') {
        return "(
                SELECT MIN(sequencenumber)
                FROM {question_attempt_steps}
                WHERE questionattemptid = $questionattemptid
                AND fraction IS NOT NULL
            )";
    }

    /**
     * Load the average mark, and number of attempts, for each slot in a set of
     * question usages..
     *
     * This method may be called publicly.
     *
     * @param qubaid_condition $qubaids used to restrict which usages are included
     * in the query. See {@link qubaid_condition}.
     * @param array $slots if null, load info for all quesitions, otherwise only
     * load the averages for the specified questions.
     * @return array of objects with fields ->slot, ->averagefraction and ->numaveraged.
     */
    public function load_firstest_average_marks(qubaid_condition $qubaids, $slots = null) {
        if (!empty($slots)) {
            list($slottest, $slotsparams) = $this->db->get_in_or_equal(
                $slots, SQL_PARAMS_NAMED, 'slot');
            $slotwhere = " AND qa.slot {$slottest}";
        } else {
            $slotwhere = '';
            $slotsparams = array();
        }

        list($statetest, $stateparams) = $this->db->get_in_or_equal(array(
            (string) question_state::$gaveup,
            (string) question_state::$gradedwrong,
            (string) question_state::$gradedpartial,
            (string) question_state::$gradedright,
            (string) question_state::$mangaveup,
            (string) question_state::$mangrwrong,
            (string) question_state::$mangrpartial,
            (string) question_state::$mangrright), SQL_PARAMS_NAMED, 'st');

        return $this->db->get_records_sql("
            SELECT
                qa.slot,
                AVG(COALESCE(qas.fraction, 0)) AS averagefraction,
                COUNT(1) AS numaveraged
            
            FROM {$qubaids->from_question_attempts('qa')}
            JOIN {question_attempt_steps} qas ON qas.questionattemptid = qa.id
                    AND qas.sequencenumber = {$this->firstest_step_for_qa_subquery()}
            
            WHERE
                {$qubaids->where()}
                $slotwhere
                AND qas.state $statetest
            
            GROUP BY qa.slot
            
            ORDER BY qa.slot
        ", $slotsparams + $stateparams + $qubaids->from_where_params());
    }

    public function load_questions_usages_latest_steps(qubaid_condition $qubaids, $slots = null, $fields = null) {
        if ($slots === []) {
            return [];
        } else if ($slots !== null) {
            [$slottest, $params] = $this->db->get_in_or_equal($slots, SQL_PARAMS_NAMED, 'slot');
            $slotwhere = " AND qa.slot {$slottest}";
        } else {
            $slotwhere = '';
            $params = [];
        }

        if ($fields === null) {
            $fields = "qas.id,
    qa.id AS questionattemptid,
    qa.questionusageid,
    qa.slot,
    qa.behaviour,
    qa.questionid,
    qa.variant,
    qa.maxmark,
    qa.minfraction,
    qa.maxfraction,
    qa.flagged,
    qa.questionsummary,
    qa.rightanswer,
    qa.responsesummary,
    qa.timemodified,
    qas.id AS attemptstepid,
    qas.sequencenumber,
    qas.state,
    qas.fraction,
    qas.timecreated,
    qas.userid";

        }

        $records = $this->db->get_records_sql("
            SELECT
                {$fields}
            
            FROM {$qubaids->from_question_attempts('qa')}
            JOIN {question_attempt_steps} qas ON qas.questionattemptid = qa.id
                    AND qas.sequencenumber = {$this->latest_step_for_qa_subquery()}
            
            WHERE
                {$qubaids->where()}
                $slotwhere
        ", $params + $qubaids->from_where_params());

        return $records;
    }

}
