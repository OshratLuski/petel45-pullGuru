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
 * External functions backported.
 *
 * @package     quiz_assessmentdiscussion
 * @copyright   2023 Devlion <info@devlion.co>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

require_once($CFG->libdir . "/externallib.php");
require_once($CFG->dirroot . '/mod/quiz/locallib.php');

class quiz_assessmentdiscussion_external extends external_api {

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function render_main_block_parameters() {
        return new external_function_parameters(
                array(
                        'cmid' => new external_value(PARAM_INT, 'cmid', VALUE_DEFAULT, null),
                        'groupid' => new external_value(PARAM_INT, 'groupid', VALUE_DEFAULT, null),
                        'tabid' => new external_value(PARAM_INT, 'tabid', VALUE_DEFAULT, null),
                        'anonymousmode' => new external_value(PARAM_INT, 'anonymousmode', VALUE_DEFAULT, null),
                        'sort' => new external_value(PARAM_INT, 'anonymousmode', VALUE_DEFAULT, null),
                )
        );
    }

    /**
     * Returns result
     *
     * @return external_single_structure
     */
    public static function render_main_block_returns() {
        return new external_single_structure(
                array(
                        'data' => new external_value(PARAM_RAW, 'result data'),
                )
        );
    }

    /**
     * Render aside user data
     *
     * @return array
     */
    public static function render_main_block($cmid, $groupid, $tabid, $anonymousmode, $sort) {
        global $USER;

        $context = context_user::instance($USER->id);
        self::validate_context($context);

        // Validate parameters using the defined parameter schema.
        $params = self::validate_parameters(self::render_main_block_parameters(), [
                'cmid' => $cmid,
                'groupid' => $groupid,
                'tabid' => $tabid,
                'anonymousmode' => $anonymousmode,
                'sort' => $sort,
        ]);

        $cmid = $params['cmid'];
        $groupid = $params['groupid'];
        $tabid = $params['tabid'];
        $anonymousmode = $params['anonymousmode'];
        $sort = $params['sort'];

        $assessmentdiscussion = new \quiz_assessmentdiscussion\assessmentdiscussion($cmid, $groupid, $anonymousmode);
        $data = $assessmentdiscussion->prepare_data_for_dashboard_template($tabid, $anonymousmode, $sort);

        $eventdata = array(
                'userid' => $USER->id,
                'cmid' => $cmid,
                'qid' => 0,
                'groupid' => $groupid,
                'tabid' => $tabid,
                'anonymousmode' => $anonymousmode,
                'sort' => $sort,
                'type' => 'dashboard',
        );
        \quiz_assessmentdiscussion\event\quiz_assessmentdiscussion_render::create_event($eventdata)->trigger();

        return ['data' => json_encode($data)];
    }

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function render_answer_area_block_parameters() {
        return new external_function_parameters(
                array(
                        'cmid' => new external_value(PARAM_INT, 'cmid', VALUE_DEFAULT, null),
                        'groupid' => new external_value(PARAM_INT, 'groupid', VALUE_DEFAULT, null),
                        'qid' => new external_value(PARAM_INT, 'qid', VALUE_DEFAULT, null),
                        'anonymousmode' => new external_value(PARAM_INT, 'anonymousmode', VALUE_DEFAULT, null),
                        'sort' => new external_value(PARAM_INT, 'sorting', VALUE_DEFAULT, null),
                        'tabid' => new external_value(PARAM_INT, 'tab id', VALUE_DEFAULT, null),
                )
        );
    }

    /**
     * Returns result
     *
     * @return external_single_structure
     */
    public static function render_answer_area_block_returns() {
        return new external_single_structure(
                array(
                        'data' => new external_value(PARAM_RAW, 'result data'),
                )
        );
    }

    /**
     * Render aside user data
     *
     * @return array
     */
    public static function render_answer_area_block($cmid, $groupid, $qid, $anonymousmode, $sort, $tabid) {
        global $USER;

        $context = context_user::instance($USER->id);
        self::validate_context($context);

        // Validate parameters using the defined parameter schema.
        $params = self::validate_parameters(self::render_answer_area_block_parameters(), [
                'cmid' => $cmid,
                'groupid' => $groupid,
                'qid' => $qid,
                'tabid' => $tabid,
                'anonymousmode' => $anonymousmode,
                'sort' => $sort,
        ]);

        $cmid = $params['cmid'];
        $groupid = $params['groupid'];
        $qid = $params['qid'];
        $tabid = $params['tabid'];
        $anonymousmode = $params['anonymousmode'];
        $sort = $params['sort'];

        $assessmentdiscussion = new \quiz_assessmentdiscussion\assessmentdiscussion($cmid, $groupid, $anonymousmode);
        $data = $assessmentdiscussion->prepare_data_for_answer_area($qid, $tabid, $anonymousmode, $sort);

        $eventdata = array(
                'userid' => $USER->id,
                'cmid' => $cmid,
                'qid' => $qid,
                'groupid' => $groupid,
                'tabid' => $tabid,
                'anonymousmode' => $anonymousmode,
                'sort' => $sort,
                'type' => 'answer_area',
        );
        \quiz_assessmentdiscussion\event\quiz_assessmentdiscussion_render::create_event($eventdata)->trigger();

        return ['data' => json_encode($data)];
    }

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function change_discussion_parameters() {
        return new external_function_parameters(
                array(
                        'cmid' => new external_value(PARAM_INT, 'cmid', VALUE_DEFAULT, null),
                        'groupid' => new external_value(PARAM_INT, 'groupid', VALUE_DEFAULT, null),
                        'qid' => new external_value(PARAM_INT, 'qid', VALUE_DEFAULT, null),
                        'userid' => new external_value(PARAM_INT, 'userid', VALUE_DEFAULT, null),
                        'attemptid' => new external_value(PARAM_INT, 'attemptid', VALUE_DEFAULT, null),
                )
        );
    }

    /**
     * Returns result
     *
     * @return external_single_structure
     */
    public static function change_discussion_returns() {
        return new external_single_structure(
                array(
                        'data' => new external_value(PARAM_RAW, 'result data'),
                )
        );
    }

    /**
     * Render aside user data
     *
     * @return array
     */
    public static function change_discussion($cmid, $groupid, $qid, $selecteduserid, $attemptid) {
        global $USER, $DB;

        $context = context_user::instance($USER->id);
        self::validate_context($context);

        // Validate parameters using the defined parameter schema.
        $params = self::validate_parameters(self::change_discussion_parameters(), [
                'cmid' => $cmid,
                'groupid' => $groupid,
                'qid' => $qid,
                'userid' => $selecteduserid,
                'attemptid' => $attemptid,
        ]);

        $cmid = $params['cmid'];
        $groupid = $params['groupid'];
        $qid = $params['qid'];
        $selecteduserid = $params['userid'];
        $attemptid = $params['attemptid'];

        // Question level.
        if ($selecteduserid == null && $attemptid == null) {
            if ($row = $DB->get_records('assessmentdiscussion_discus',
                    ['userid' => $USER->id, 'cmid' => $cmid, 'groupid' => $groupid, 'qid' => $qid])) {

                $DB->delete_records('assessmentdiscussion_discus',
                        ['userid' => $USER->id, 'cmid' => $cmid, 'groupid' => $groupid, 'qid' => $qid]);
            } else {
                $dataobject = new stdClass();
                $dataobject->userid = $USER->id;
                $dataobject->cmid = $cmid;
                $dataobject->groupid = $groupid;
                $dataobject->qid = $qid;
                $dataobject->timecreated = time();
                $dataobject->timemodified = time();
                $DB->insert_record('assessmentdiscussion_discus', $dataobject);
            }
        }

        // User level.
        if ($selecteduserid != null && $attemptid == null) {
            if ($row = $DB->get_record('assessmentdiscussion_discus',
                    ['userid' => $USER->id, 'cmid' => $cmid, 'groupid' => $groupid, 'qid' => $qid,
                            'selecteduserid' => $selecteduserid])) {

                $DB->delete_records('assessmentdiscussion_discus',
                        ['userid' => $USER->id, 'cmid' => $cmid, 'groupid' => $groupid, 'qid' => $qid,
                                'selecteduserid' => $selecteduserid]);
            } else {
                $dataobject = new stdClass();
                $dataobject->userid = $USER->id;
                $dataobject->cmid = $cmid;
                $dataobject->groupid = $groupid;
                $dataobject->qid = $qid;
                $dataobject->selecteduserid = $selecteduserid;
                $dataobject->timecreated = time();
                $dataobject->timemodified = time();
                $DB->insert_record('assessmentdiscussion_discus', $dataobject);
            }
        }

        // Attempt level.
        if ($selecteduserid != null && $attemptid != null) {
            if ($row = $DB->get_record('assessmentdiscussion_discus',
                    ['userid' => $USER->id, 'cmid' => $cmid, 'groupid' => $groupid, 'qid' => $qid,
                            'selecteduserid' => $selecteduserid, 'attemptid' => $attemptid])) {

                $DB->delete_records('assessmentdiscussion_discus',
                        ['userid' => $USER->id, 'cmid' => $cmid, 'groupid' => $groupid, 'qid' => $qid,
                                'selecteduserid' => $selecteduserid, 'attemptid' => $attemptid]);
            } else {
                $dataobject = new stdClass();
                $dataobject->userid = $USER->id;
                $dataobject->cmid = $cmid;
                $dataobject->groupid = $groupid;
                $dataobject->qid = $qid;
                $dataobject->selecteduserid = $selecteduserid;
                $dataobject->attemptid = $attemptid;
                $dataobject->timecreated = time();
                $dataobject->timemodified = time();
                $DB->insert_record('assessmentdiscussion_discus', $dataobject);
            }
        }


        if ($row = $DB->get_records('assessmentdiscussion_discus',
                ['userid' => $USER->id, 'cmid' => $cmid, 'groupid' => $groupid, 'qid' => $qid])) {
            $questionenable = true;
        } else {
            $questionenable = false;
        }

        if ($row = $DB->get_records('assessmentdiscussion_discus',
                ['userid' => $USER->id, 'cmid' => $cmid, 'groupid' => $groupid, 'qid' => $qid, 'selecteduserid' => $selecteduserid])) {
            $answerenable = true;
        } else {
            $answerenable = false;
        }



        $eventdata = array(
                'userid' => $USER->id,
                'cmid' => $cmid,
                'qid' => $qid,
                'groupid' => $groupid,
                'selecteduserid' => $selecteduserid,
                'attemptid' => $attemptid,
        );
        \quiz_assessmentdiscussion\event\quiz_assessmentdiscussion_change_discussion::create_event($eventdata)->trigger();

        return ['data' => json_encode(['question_enable' => $questionenable, 'answer_enable' => $answerenable])];
    }

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function render_overlay_block_parameters() {
        return new external_function_parameters(
                array(
                        'cmid' => new external_value(PARAM_INT, 'cmid', VALUE_DEFAULT, null),
                        'qid' => new external_value(PARAM_INT, 'qid', VALUE_DEFAULT, null),
                        'groupid' => new external_value(PARAM_INT, 'groupid', VALUE_DEFAULT, null),
                        'tabid' => new external_value(PARAM_INT, 'tabid', VALUE_DEFAULT, null),
                        'anonymousmode' => new external_value(PARAM_INT, 'anonymousmode', VALUE_DEFAULT, null),
                        'viewlist' => new external_value(PARAM_INT, 'viewlist', VALUE_DEFAULT, null),
                        'showanswers' => new external_value(PARAM_INT, 'showanswers', VALUE_DEFAULT, null),
                )
        );
    }

    /**
     * Returns result
     *
     * @return external_single_structure
     */
    public static function render_overlay_block_returns() {
        return new external_single_structure(
                array(
                        'data' => new external_value(PARAM_RAW, 'result data'),
                )
        );
    }

    /**
     * Render aside user data
     *
     * @return array
     */
    public static function render_overlay_block($cmid, $qid, $groupid, $tabid, $anonymousmode, $viewlist, $showanswers) {
        global $USER;

        $context = context_user::instance($USER->id);
        self::validate_context($context);

        // Validate parameters using the defined parameter schema.
        $params = self::validate_parameters(self::render_overlay_block_parameters(), [
                'cmid' => $cmid,
                'groupid' => $groupid,
                'qid' => $qid,
                'tabid' => $tabid,
                'anonymousmode' => $anonymousmode,
                'viewlist' => $viewlist,
                'showanswers' => $showanswers,
        ]);

        $cmid = $params['cmid'];
        $groupid = $params['groupid'];
        $qid = $params['qid'];
        $tabid = $params['tabid'];
        $anonymousmode = $params['anonymousmode'];
        $viewlist = $params['viewlist'];
        $showanswers = $params['showanswers'];

        $assessmentdiscussion = new \quiz_assessmentdiscussion\assessmentdiscussion($cmid, $groupid, $anonymousmode);
        $data = $assessmentdiscussion->prepare_data_for_overlay_area($qid, $tabid, $anonymousmode, $viewlist, $showanswers);

        $eventdata = array(
                'userid' => $USER->id,
                'cmid' => $cmid,
                'qid' => $qid,
                'groupid' => $groupid,
                'tabid' => $tabid,
                'anonymousmode' => $anonymousmode,
                'viewlist' => $viewlist,
                'showanswers' => $showanswers,
        );
        \quiz_assessmentdiscussion\event\quiz_assessmentdiscussion_render_overlay::create_event($eventdata)->trigger();

        return ['data' => json_encode($data)];
    }

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function save_grades_parameters() {
        return new external_function_parameters(
                array(
                        'cmid' => new external_value(PARAM_INT, 'cmid', VALUE_DEFAULT, null),
                        'qid' => new external_value(PARAM_INT, 'qid', VALUE_DEFAULT, null),
                        'grades' => new external_multiple_structure(self::grading_area(), 'list of grades'),
                )
        );
    }

    /**
     * Save grades
     * @return external_single_structure
     * @since  Moodle 2.5
     */
    private static function grading_area() {
        return new external_single_structure(
                array (
                        'attemptid'    => new external_value(PARAM_INT, 'attempt id'),
                        'slot'  => new external_value(PARAM_INT, 'slot'),
                        'sesskey' => new external_value(PARAM_TEXT, 'sesskey'),
                        'maxmark'  => new external_value(PARAM_TEXT, 'maxmark'),
                        'minfraction'  => new external_value(PARAM_INT, 'minfraction'),
                        'maxfraction'  => new external_value(PARAM_INT, 'maxfraction'),
                        'comment'  => new external_value(PARAM_RAW, 'comment'),
                        'grade'  => new external_value(PARAM_TEXT, 'grade'),
                )
        );
    }

    /**
     * Returns result
     *
     * @return external_single_structure
     */
    public static function save_grades_returns() {
        return new external_single_structure(
                array(
                        'data' => new external_value(PARAM_RAW, 'result data'),
                )
        );
    }

    /**
     * Save grades
     *
     * @return array
     */
    public static function save_grades($cmid, $qid, $grades) {
        global $USER, $DB;

        $context = context_user::instance($USER->id);
        self::validate_context($context);

        foreach ($grades as $grade) {

            if (empty($grade['grade']) || !is_numeric($grade['grade'])) {
                continue;
            }

            $transaction = $DB->start_delegated_transaction();

            $fattempt = "quiz_create_attempt_handling_errors";
            $attemptobj = $fattempt($grade['attemptid']);

            $attempt = $attemptobj->get_attempt();
            $quba = question_engine::load_questions_usage_by_activity($attempt->uniqueid);

            $quba->manual_grade($grade['slot'], $grade['comment'], $grade['grade']);
            question_engine::save_questions_usage_by_activity($quba);
            $attempt = $attemptobj->get_attempt();
            $attempt->timemodified = time();
            $attempt->sumgrades = $quba->get_total_mark();
            $DB->update_record('quiz_attempts', $attempt);
            $fngrade = "quiz_save_best_grade";
            $fngrade($attemptobj->{"get_quiz"}(), $attemptobj->get_userid());

            // Log this action.
            $params = array(
                    'objectid' => $attemptobj->get_question_attempt($grade['slot'])->get_question()->id,
                    'courseid' => $attemptobj->get_courseid(),
                    'context' => context_module::instance($attemptobj->get_cmid()),
                    'other' => array(
                            'quizid' => $attemptobj->{"get_quizid"}(),
                            'attemptid' => $attemptobj->get_attemptid(),
                            'slot' => $grade['slot']
                    )
            );
            $event = \mod_quiz\event\question_manually_graded::create($params);
            $event->trigger();

            $transaction->allow_commit();

            // Recache grade.
            $quizinfo = new \quiz_assessmentdiscussion\quizinfo($attemptobj->get_cmid());
            $quizinfo->recache_grade($attemptobj->get_question_attempt(
                    $grade['slot'])->get_question()->id, $attemptobj->get_userid(), $grade['grade'], $grade['comment']
            );
        }

        $eventdata = array(
                'userid' => $USER->id,
                'cmid' => $cmid,
                'qid' => $qid,
                'grades' => $grades,
        );
        \quiz_assessmentdiscussion\event\quiz_assessmentdiscussion_save_grades::create_event($eventdata)->trigger();

        return ['data' => ''];
    }

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function preview_parameters() {
        return new external_function_parameters(
                array(
                        'attempts' => new external_value(PARAM_TEXT, 'attempts', VALUE_DEFAULT, null),
                )
        );
    }

    /**
     * Returns result
     *
     * @return external_single_structure
     */
    public static function preview_returns() {
        return new external_single_structure(
                array(
                        'data' => new external_value(PARAM_RAW, 'result data'),
                )
        );
    }

    /**
     * Render aside user data
     *
     * @return array
     */
    public static function preview($attempts) {
        global $USER, $OUTPUT;

        $context = context_user::instance($USER->id);
        self::validate_context($context);

        // Validate parameters using the defined parameter schema.
        $params = self::validate_parameters(self::preview_parameters(), [
                'attempts' => $attempts,
        ]);

        $result = [];

        if ($attempts = json_decode($params['attempts'])) {
            foreach ($attempts as $attempt) {
                $preview = new \quiz_assessmentdiscussion\preview($attempt->cmid, $attempt->qid);
                $pdata = $preview->preview_answer_data($attempt->attemptid, $attempt->slot);

                $data = new \StdClass();
                $data->iframeenable = $pdata->iframeenable;
                $data->previewanswer = $pdata->previewanswer;
                $data->previewanswer_link = $pdata->previewanswer_link;

                $attempt->html = $OUTPUT->render_from_template('quiz_assessmentdiscussion/preview', $data);

                $result[] = $attempt;
            }
        }

        return ['data' => json_encode($result)];
    }

    public static function change_grades_parameters() {
        return new external_function_parameters(
                array(
                        'cmid' => new external_value(PARAM_INT, 'cmid', VALUE_REQUIRED, null),
                        'state' => new external_value(PARAM_RAW, 'state', VALUE_REQUIRED, null),
                )
        );
    }

    /**
     * Returns result
     *
     * @return external_single_structure
     */
    public static function change_grades_returns() {
        return new external_single_structure(
                array(
                        'data' => new external_value(PARAM_RAW, 'result data'),
                )
        );
    }

    /**
     * Render aside user data
     *
     * @return array
     */
    public static function change_grades($cmid, $state) {
        global $DB;

        $context = context_module::instance($cmid);
        self::validate_context($context);

        // Validate parameters using the defined parameter schema.
        $params = self::validate_parameters(self::change_grades_parameters(), [
                'cmid' => $cmid,
                'state' => $state,
        ]);

        $result = false;
        if (in_array($params['state'], ['enable', 'disable']) && $params['cmid'] > 0) {

            if ($cm = $DB->get_record('course_modules', array('id' => $params['cmid']))) {

                $quiz = $DB->get_record('quiz', array('id' => $cm->instance));

                switch ($params['state']) {
                    case 'enable':
                        $quiz->timeclose = time();
                        $quiz->reviewmarks = 69904;
                        $DB->update_record('quiz', $quiz);
                        break;
                    case 'disable':
                        $quiz->timeclose = 0;
                        $quiz->reviewmarks = 0;
                        $DB->update_record('quiz', $quiz);
                        break;
                }

                $result = true;
            }
        }

        $data = ['result' => $result];

        return ['data' => json_encode($data)];
    }
}
