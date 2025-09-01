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
 * @package     quiz_advancedoverview
 * @copyright   2023 Devlion <info@devlion.co>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

require_once($CFG->libdir . "/externallib.php");

class quiz_advancedoverview_external extends external_api {

    public static function render_dynamic_block_parameters() {
        return new external_function_parameters(
                array(
                        'cmid' => new external_value(PARAM_INT, 'course module id'),
                        'groupid' => new external_value(PARAM_INT, 'group id'),
                        'config' => new external_value(PARAM_RAW, 'config'),
                )
        );
    }

    public static function render_dynamic_block($cmid, $groupid, $config) {

        $context = \context_system::instance();
        self::validate_context($context);

        $params = self::validate_parameters(self::render_dynamic_block_parameters(),
                array(
                        'cmid' => $cmid,
                        'groupid' => $groupid,
                        'config' => $config,
                )
        );

        $config = json_decode($params['config']);

        $quizdata = new \quiz_advancedoverview\quizdata($params['cmid'], $params['groupid'], $config);

        $quizdata->prepare_questions();
        $quizdata->prepare_skills();
        $quizdata->prepare_charts();
        $quizdata->prepare_students();

        return json_encode($quizdata->get_render_data());
    }

    public static function render_dynamic_block_returns() {
        return new external_value(PARAM_RAW, 'The blocks settings');
    }

    public static function regrade_attempts_parameters() {
        return new external_function_parameters(
                array(
                        'cmid' => new external_value(PARAM_INT, 'course module id'),
                        'courseid' => new external_value(PARAM_INT, 'courseid'),
                        'quizid' => new external_value(PARAM_INT, 'quizid'),
                        'attemptids' => new external_value(PARAM_SEQUENCE, 'quizid'),
                )
        );
    }

    public static function regrade_attempts($cmid, $courseid, $quizid, $attemptids) {
        global $DB, $CFG;
        $context = \context_system::instance();
        self::validate_context($context);

        $params = self::validate_parameters(self::regrade_attempts_parameters(),
                array(
                        'cmid' => $cmid,
                        'courseid' => $courseid,
                        'quizid' => $quizid,
                        'attemptids' => $attemptids,
                )
        );

        $response = null;

        $course = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);
        $cm = $DB->get_record('course_modules', array('id' => $cmid), '*', MUST_EXIST);
        $quiz = $DB->get_record('quiz', array('id' => $quizid), '*', MUST_EXIST);

        $file = $CFG->dirroot . '/mod/quiz/locallib.php';
        if (is_readable($file)) {
            include_once($file);
        }
        $file = $CFG->dirroot . '/mod/quiz/report/advancedoverview/overview_form.php';
        if (is_readable($file)) {
            include_once($file);
        }
        $file = $CFG->dirroot . '/mod/quiz/report/default.php';
        if (is_readable($file)) {
            include_once($file);
        }
        $file = $CFG->dirroot . '/mod/quiz/report/reportlib.php';
        if (is_readable($file)) {
            include_once($file);
        }
        $file = $CFG->dirroot . '/mod/quiz/report/attemptsreport.php';
        if (is_readable($file)) {
            include_once($file);
        }
        $file = $CFG->dirroot . '/mod/quiz/report/advancedoverview/report.php';
        if (is_readable($file)) {
            include_once($file);
        }

        ob_start();

        $report = new quiz_advancedoverview_report();

        list($currentgroup, $studentsjoins, $groupstudentsjoins, $allowedjoins) =
                $report->init('advancedoverview', 'quiz_advancedoverview_settings_form', $quiz, $cm, $course);

        $attemptids = explode(',', $attemptids);

        $report->regrade_attempts($quiz, false, $groupstudentsjoins, $attemptids);

        ob_end_clean();

        return json_encode(true);
    }

    public static function regrade_attempts_returns() {
        return new external_value(PARAM_RAW, '');
    }

    public static function close_attempts_parameters() {
        return new external_function_parameters(
                array(
                        'cmid' => new external_value(PARAM_INT, 'course module id'),
                        'courseid' => new external_value(PARAM_INT, 'courseid'),
                        'quizid' => new external_value(PARAM_INT, 'quizid'),
                        'attemptids' => new external_value(PARAM_SEQUENCE, 'quizid'),
                )
        );
    }

    public static function close_attempts($cmid, $courseid, $quizid, $attemptids) {
        global $DB, $CFG;
        $context = \context_system::instance();
        self::validate_context($context);

        $params = self::validate_parameters(self::close_attempts_parameters(),
                array(
                        'cmid' => $cmid,
                        'courseid' => $courseid,
                        'quizid' => $quizid,
                        'attemptids' => $attemptids,
                )
        );

        $response = null;

        $course = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);
        $cm = $DB->get_record('course_modules', array('id' => $cmid), '*', MUST_EXIST);
        $quiz = $DB->get_record('quiz', array('id' => $quizid), '*', MUST_EXIST);

        $file = $CFG->dirroot . '/mod/quiz/locallib.php';
        if (is_readable($file)) {
            include_once($file);
        }
        $file = $CFG->dirroot . '/mod/quiz/report/advancedoverview/overview_form.php';
        if (is_readable($file)) {
            include_once($file);
        }
        $file = $CFG->dirroot . '/mod/quiz/report/default.php';
        if (is_readable($file)) {
            include_once($file);
        }
        $file = $CFG->dirroot . '/mod/quiz/report/reportlib.php';
        if (is_readable($file)) {
            include_once($file);
        }
        $file = $CFG->dirroot . '/mod/quiz/report/attemptsreport.php';
        if (is_readable($file)) {
            include_once($file);
        }
        $file = $CFG->dirroot . '/mod/quiz/report/advancedoverview/report.php';
        if (is_readable($file)) {
            include_once($file);
        }

        ob_start();

        $report = new quiz_advancedoverview_report();

        list($currentgroup, $studentsjoins, $groupstudentsjoins, $allowedjoins) =
                $report->init('advancedoverview', 'quiz_advancedoverview_settings_form', $quiz, $cm, $course);

        $attemptids = explode(',', $attemptids);

        $report->close_selected_attempts($quiz, $cm, $attemptids, $allowedjoins);

        ob_end_clean();

        return json_encode(true);
    }

    public static function close_attempts_returns() {
        return new external_value(PARAM_RAW, '');
    }

    public static function delete_attempts_parameters() {
        return new external_function_parameters(
                array(
                        'cmid' => new external_value(PARAM_INT, 'course module id'),
                        'courseid' => new external_value(PARAM_INT, 'courseid'),
                        'quizid' => new external_value(PARAM_INT, 'quizid'),
                        'attemptids' => new external_value(PARAM_SEQUENCE, 'quizid'),
                )
        );
    }

    public static function delete_attempts($cmid, $courseid, $quizid, $attemptids) {
        global $DB, $CFG;

        $context = \context_system::instance();
        self::validate_context($context);

        $params = self::validate_parameters(self::delete_attempts_parameters(),
                array(
                        'cmid' => $cmid,
                        'courseid' => $courseid,
                        'quizid' => $quizid,
                        'attemptids' => $attemptids,
                )
        );

        $response = null;

        $course = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);
        $cm = $DB->get_record('course_modules', array('id' => $cmid), '*', MUST_EXIST);
        $quiz = $DB->get_record('quiz', array('id' => $quizid), '*', MUST_EXIST);

        $file = $CFG->dirroot . '/mod/quiz/locallib.php';
        if (is_readable($file)) {
            include_once($file);
        }
        $file = $CFG->dirroot . '/mod/quiz/report/advancedoverview/overview_form.php';
        if (is_readable($file)) {
            include_once($file);
        }
        $file = $CFG->dirroot . '/mod/quiz/report/default.php';
        if (is_readable($file)) {
            include_once($file);
        }
        $file = $CFG->dirroot . '/mod/quiz/report/reportlib.php';
        if (is_readable($file)) {
            include_once($file);
        }
        $file = $CFG->dirroot . '/mod/quiz/report/attemptsreport.php';
        if (is_readable($file)) {
            include_once($file);
        }
        $file = $CFG->dirroot . '/mod/quiz/report/advancedoverview/report.php';
        if (is_readable($file)) {
            include_once($file);
        }

        ob_start();

        $report = new quiz_advancedoverview_report();

        list($currentgroup, $studentsjoins, $groupstudentsjoins, $allowedjoins) =
                $report->init('advancedoverview', 'quiz_advancedoverview_settings_form', $quiz, $cm, $course);

        $attemptids = explode(',', $attemptids);

        $report->delete_selected_attempts($quiz, $cm, $attemptids, $allowedjoins);

        ob_end_clean();

        return json_encode(true);
    }

    public static function delete_attempts_returns() {
        return new external_value(PARAM_RAW, '');
    }
}
