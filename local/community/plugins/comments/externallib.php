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
 * External interface library for customfields component
 *
 * @package   community_comments
 * @copyright   2019 Devlion <info@devlion.co>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

require_once($CFG->libdir . "/externallib.php");
require_once($CFG->libdir . '/datalib.php');
require_once($CFG->dirroot . '/local/community/plugins/comments/classes/comments.php');

require_login();

/**
 * Class community_comments_external
 *
 * @copyright 2018 David Matamoros <davidmc@moodle.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class community_comments_external extends external_api {

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function render_block_parameters() {
        return new external_function_parameters(
                array(
                        'activityid' => new external_value(PARAM_INT, 'activity id', VALUE_DEFAULT, null),
                        'sort' => new external_value(PARAM_INT, 'sort number', VALUE_DEFAULT, null),
                )
        );
    }

    /**
     * Returns result
     *
     * @return external_single_structure
     */
    public static function render_block_returns() {
        return new external_single_structure(
                array(
                        'content' => new external_value(PARAM_RAW, 'result html'),
                        'header' => new external_value(PARAM_RAW, 'result html'),
                )
        );
    }

    /**
     * Render block
     *
     * @param int $activityid
     * @param int $sort
     * @return array
     */
    public static function render_block($activityid, $sort) {
        global $USER;

        $context = context_user::instance($USER->id);
        self::validate_context($context);

        $class = new comments();
        $class->set_sort($sort);
        $class->set_config_data_ajax($activityid);
        $class->set_template_context();
        $html = $class->render_mustache();

        return array(
                'content' => $html,
                'header' => ''
        );
    }

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function save_comment_parameters() {
        return new external_function_parameters(
                array(
                        'activityid' => new external_value(PARAM_INT, 'activity id', VALUE_DEFAULT, null),
                        'comment' => new external_value(PARAM_RAW, 'comment', VALUE_DEFAULT, null),
                )
        );
    }

    /**
     * Returns result
     *
     * @return external_single_structure
     */
    public static function save_comment_returns() {
        return new external_single_structure(
                array()
        );
    }

    /**
     * Save comment
     *
     * @param int $activityid
     * @param string $comment
     * @return array
     */
    public static function save_comment($activityid, $comment) {
        global $USER, $DB;

        $context = context_user::instance($USER->id);
        self::validate_context($context);

        $insert = new stdClass();
        $insert->contextid = $activityid;
        $insert->component = 'my_dashboard';
        $insert->commentarea = 'page_comments';
        $insert->itemid = 0;
        $insert->content = $comment;
        $insert->format = 0;
        $insert->userid = $USER->id;
        $insert->timecreated = time();

        $commentid = $DB->insert_record('comments', $insert);

        $params = array(
                'context' => context_module::instance($activityid),
                'objectid' => $commentid,
                'other' => array(
                        'cmid' => $activityid,
                        'itemid' => $activityid,
                ),
        );
        \community_comments\event\oer_comment_created::create($params)->trigger();

        return [];
    }
}
