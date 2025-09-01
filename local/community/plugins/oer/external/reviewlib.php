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
 * @package    community_oer
 * @copyright  2018 Devlion <info@devlion.co>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

require_once($CFG->libdir . "/externallib.php");

class community_oer_review_external extends external_api {

    public static function show_review_popup_parameters() {
        return new external_function_parameters(
                array(
                        'courseid' => new external_value(PARAM_INT, 'Course ID')
                )
        );
    }

    public static function show_review_popup($courseid) {

        $params = self::validate_parameters(self::show_review_popup_parameters(),
                array(
                        'courseid' => (int) $courseid
                )
        );

        $popupdata = \community_oer\reviews_oer::popup_engine($params['courseid']);
        if ($courseid && $popupdata) {
            \community_oer\reviews_oer::add_view_to_request($popupdata->requestid);
        }

        $popupdata->reviewsquestiontext = get_config('community_oer', 'reviewsquestiontext');
        $popupdata->reviewstextarea = get_config('community_oer', 'reviewstextarea');

        return json_encode($popupdata);
    }

    public static function show_review_popup_returns() {
        return new external_value(PARAM_RAW, 'Review information for user');
    }

    public static function send_review_later_parameters() {
        return new external_function_parameters(
                array(
                        'activityid' => new external_value(PARAM_INT, 'Activity ID'),
                        'requestid' => new external_value(PARAM_INT, 'Request ID')
                )
        );
    }

    public static function send_review_later($activityid, $requestid) {
        $params = self::validate_parameters(self::send_review_later_parameters(),
                array(
                        'activityid' => (int) $activityid,
                        'requestid' => (int) $requestid,
                )
        );
        return \community_oer\reviews_oer::remind_me_later($params['activityid']);
    }

    public static function send_review_later_returns() {
        return new external_value(PARAM_TEXT, 'The result of remind me later action');
    }

    public static function reject_review_parameters() {
        return new external_function_parameters(
                array(
                        'requestid' => new external_value(PARAM_INT, 'Request ID'),
                        'courseid' => new external_value(PARAM_INT, 'Course ID')
                )
        );
    }

    public static function reject_review($requestid, $courseid) {
        $params = self::validate_parameters(self::reject_review_parameters(),
                array(
                        'requestid' => $requestid,
                        'courseid' => $courseid
                )
        );

        $rejectreview = \community_oer\reviews_oer::reject_review($params['requestid']);
        $countreview = \community_oer\reviews_oer::count_review_oncourse($params['courseid']);

        $result = array(
                'rejectreview' => $rejectreview,
                'countreview' => $countreview
        );

        return json_encode($result);
    }

    public static function reject_review_returns() {
        return new external_value(PARAM_RAW, 'Number of requests in the queue');
    }

    public static function show_review_parameters() {
        return new external_function_parameters(
                array(
                        'objid' => new external_value(PARAM_INT, 'Obj ID'),
                        'type' => new external_value(PARAM_RAW, 'Type of obj')
                )
        );
    }

    public static function show_review($objid, $type) {
        $params = self::validate_parameters(self::show_review_parameters(),
                array(
                        'objid' => $objid,
                        'type' => $type,
                )
        );

        return \community_oer\reviews_oer::show_review($params['objid'], $params['type']);
    }

    public static function show_review_returns() {
        return new external_value(PARAM_TEXT, 'Show review action');
    }

    public static function send_comment_parameters() {
        return new external_function_parameters(
                array(
                        'reviewid' => new external_value(PARAM_INT, 'Review ID'),
                        'comment' => new external_value(PARAM_TEXT, 'Comment')
                )
        );
    }

    public static function send_comment($reviewid, $comment) {
        $params = self::validate_parameters(self::send_comment_parameters(),
                array(
                        'reviewid' => (int) $reviewid,
                        'comment' => $comment,
                )
        );

        return \community_oer\reviews_oer::send_comment($params);
    }

    public static function send_comment_returns() {
        return new external_value(PARAM_TEXT, 'The result of save comment');
    }

    public static function edit_comment_parameters() {
        return new external_function_parameters(
                array(
                        'commentid' => new external_value(PARAM_INT, 'Comment ID'),
                        'comment' => new external_value(PARAM_TEXT, 'Comment'),
                )
        );
    }

    public static function edit_comment($commentid, $comment) {
        $params = self::validate_parameters(self::edit_comment_parameters(),
                array(
                        'commentid' => (int) $commentid,
                        'comment' => $comment,
                )
        );

        return \community_oer\reviews_oer::edit_comment($params);
    }

    public static function edit_comment_returns() {
        return new external_value(PARAM_TEXT, 'The result of edit comment');
    }

    public static function delete_comment_parameters() {
        return new external_function_parameters(
                array(
                        'commentid' => new external_value(PARAM_INT, 'Comment ID'),
                )
        );
    }

    public static function delete_comment($commentid) {
        $params = self::validate_parameters(self::delete_comment_parameters(),
                array(
                        'commentid' => (int) $commentid,
                )
        );

        return \community_oer\reviews_oer::delete_comment($params['commentid']);
    }

    public static function delete_comment_returns() {
        return new external_value(PARAM_TEXT, 'The result of delete comment');
    }

    public static function delete_review_parameters() {
        return new external_function_parameters(
                array(
                        'reviewid' => new external_value(PARAM_INT, 'Review ID'),
                )
        );
    }

    public static function delete_review($reviewid) {
        $params = self::validate_parameters(self::delete_review_parameters(),
                array(
                        'reviewid' => (int) $reviewid,
                )
        );

        return \community_oer\reviews_oer::delete_review($params['reviewid']);
    }

    public static function delete_review_returns() {
        return new external_value(PARAM_TEXT, 'The result of delete review');
    }

    public static function open_popup_remind_parameters() {
        return new external_function_parameters(
                array(
                        'activityid' => new external_value(PARAM_INT, 'Activity ID'),
                )
        );
    }

    public static function open_popup_remind($activityid) {
        global $DB, $CFG;

        $params = self::validate_parameters(self::open_popup_remind_parameters(),
                array(
                        'activityid' => (int) $activityid,
                )
        );

        $act = null;

        $cm = $DB->get_record('course_modules', ['id' => $activityid]);
        if ($cm) {
            $module = $DB->get_record('modules', ['id' => $cm->module]);
            $act = $DB->get_record($module->name, ['id' => $cm->instance]);
            $act->cmid = $cm->id;
        }

        if ($act == null || empty($act)) {
            return json_encode(['result' => false]);
        }

        // Mid activity url.
        $mid = \local_metadata\mcontext::module()->get($act->cmid, 'ID');
        if (!empty($mid) && is_number($mid)) {
            $url = $CFG->wwwroot . '/local/community/plugins/oer/activityshare.php?id=' . $mid;
        } else {
            $url = $CFG->wwwroot . '/local/community/plugins/oer/activityshare.php?id=' . $act->cmid;
        }

        return json_encode([
                'result' => true,
                'activityId' => $params['activityid'],
                'activityName' => $act->name,
                'date' => date('d-m-Y H:i'),
                'url' => $url
        ]);
    }

    public static function open_popup_remind_returns() {
        return new external_value(PARAM_TEXT, 'The result of open popup remind');
    }

    public static function send_remind_parameters() {
        return new external_function_parameters(
                array(
                        'activityid' => new external_value(PARAM_INT, 'Activity ID'),
                        'remindtext' => new external_value(PARAM_TEXT, 'Remind text'),
                        'ifsend' => new external_value(PARAM_BOOL, 'If send'),
                )
        );
    }

    public static function send_remind($activityid, $remindtext, $ifsend) {
        $params = self::validate_parameters(self::send_remind_parameters(),
                array(
                        'activityid' => (int) $activityid,
                        'remindtext' => $remindtext,
                        'ifsend' => $ifsend,
                )
        );

        return \community_oer\reviews_oer::send_remind($params['activityid'], $params['remindtext'], $params['ifsend']);
    }

    public static function send_remind_returns() {
        return new external_value(PARAM_TEXT, 'The result of send remind');
    }
}
