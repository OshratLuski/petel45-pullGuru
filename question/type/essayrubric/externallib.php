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
 * @package    qtype_essayrubric
 * @copyright  2018 Devlion <info@devlion.co>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

require_once ($CFG->libdir . "/externallib.php");
require_once ($CFG->dirroot . "/question/type/essayrubric/locallib.php");

class qtype_essayrubric_external extends external_api {

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function store_grades_parameters() {
        return new external_function_parameters(
            array(
                'data' => new external_value(PARAM_RAW, 'data'),
                'qaid' => new external_value(PARAM_INT, 'qaid'),
            )
        );
    }

    /**
     * Returns result
     *
     * @return external_single_structure
     */
    public static function store_grades_returns() {
        return new external_single_structure(
            array(
                'result' => new external_value(PARAM_RAW, 'result json'),
            )
        );
    }

    /**
     * Obtain data for teacher colleagues
     *
     * @param int $cmid
     * @return array
     */
    public static function store_grades($data, $qaid) {
        global $DB, $USER;

        $context = context_user::instance($USER->id);
        self::validate_context($context);

        $params = self::validate_parameters(
            self::store_grades_parameters(),
            array(
                'data' => $data,
                'qaid' => $qaid,
            )
        );

        $data = json_decode($params['data']);
        $qaid = json_decode($params['qaid']);

        $result = qtype_essayrubric_store_grades($data, $qaid);

        $content = array(
            'result' => json_encode($result),
        );

        return $content;
    }

    // get_grades
    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function get_grades_parameters() {
        return new external_function_parameters(
            array(
                'qid' => new external_value(PARAM_RAW, 'qid'),
                'qaid' => new external_value(PARAM_INT, 'qaid'),
            )
        );
    }

    /**
     * Returns result
     *
     * @return external_single_structure
     */
    public static function get_grades_returns() {
        return new external_single_structure(
            array(
                'result' => new external_value(PARAM_RAW, 'result json'),
            )
        );
    }

    /**
     * Obtain data for teacher colleagues
     *
     * @param int $cmid
     * @return array
     */
    public static function get_grades($qid, $qaid) {
        global $DB, $USER;

        $context = context_user::instance($USER->id);
        self::validate_context($context);

        $params = self::validate_parameters(
            self::get_grades_parameters(),
            array(
                'qid' => $qid,
                'qaid' => $qaid,
            )
        );

        $qid = json_decode($params['qid']);
        $qaid = json_decode($params['qaid']);

        $result = qtype_essayrubric_get_grades($qid, $qaid);

        $content = array(
            'result' => json_encode($result[0]),
        );

        return $content;
    }

    // get_indicators
    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function get_indicators_parameters() {
        return new external_function_parameters(
            array(
            )
        );
    }

    /**
     * Returns result
     *
     * @return external_single_structure
     */
    public static function get_indicators_returns() {
        return new external_single_structure(
            array(
                'result' => new external_value(PARAM_RAW, 'result json'),
            )
        );
    }

    /**
     * Obtain data for teacher colleagues
     *
     * @param int $cmid
     * @return array
     */
    public static function get_indicators() {
        global $DB, $USER, $CFG;

        require_once ($CFG->dirroot . '/question/type/essayrubric/locallib.php');

        if (!is_siteadmin()) {
            array(
                'result' => json_encode([]),
            );
        }

        $context = context_user::instance($USER->id);
        self::validate_context($context);

        $params = self::validate_parameters(
            self::get_indicators_parameters(),
            array()
        );

        // $data = json_decode($params['data']);
        // $qaid = json_decode($params['qaid']);

        $result = qtype_essayrubric_get_indicators();

        $content = array(
            'result' => json_encode($result),
        );

        return $content;
    }

}
