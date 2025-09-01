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
 * External message petel API
 *
 * @package     message_petel
 * @category    external
 * @copyright   2019 Devlion <info@devlion.co>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once("$CFG->libdir/externallib.php");
require_once($CFG->dirroot . "/message/lib.php");

/**
 * Message external functions
 *
 * @package    message_petel
 * @category   external
 * @copyright  2016 Ryan Wyllie <ryan@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class message_petel_external extends external_api {

    /**
     * Get petel notifications parameters description.
     *
     * @return external_function_parameters
     * @since 3.2
     */
    public static function get_petel_notifications_parameters() {
        return new external_function_parameters(
                array(
                        'useridto' => new external_value(PARAM_INT, 'the user id who received the message, 0 for current user'),
                        'newestfirst' => new external_value(
                                PARAM_BOOL, 'true for ordering by newest first, false for oldest first',
                                VALUE_DEFAULT, true),
                        'limit' => new external_value(PARAM_INT, 'the number of results to return', VALUE_DEFAULT, 0),
                        'offset' => new external_value(PARAM_INT, 'offset the result set by a given amount', VALUE_DEFAULT, 0)
                )
        );
    }

    /**
     * Get notifications function.
     *
     * @param int $useridto the user id who received the message
     * @param bool $newestfirst true for ordering by newest first, false for oldest first
     * @param int $limit the number of results to return
     * @param int $offset offset the result set by a given amount
     * @return array
     * @throws moodle_exception
     * @throws invalid_parameter_exception
     * @since  3.2
     */
    public static function get_petel_notifications($useridto, $newestfirst, $limit, $offset) {
        global $USER, $PAGE;

        $params = self::validate_parameters(
                self::get_petel_notifications_parameters(),
                array(
                        'useridto' => $useridto,
                        'newestfirst' => $newestfirst,
                        'limit' => $limit,
                        'offset' => $offset,
                )
        );

        $context = context_user::instance($USER->id);
        self::validate_context($context);

        $useridto = $params['useridto'];
        $newestfirst = $params['newestfirst'];
        $limit = $params['limit'];
        $offset = $params['offset'];
        $issuperuser = has_capability('moodle/site:readallmessages', $context);
        $renderer = $PAGE->get_renderer('core_message');

        if (empty($useridto)) {
            $useridto = $USER->id;
        }

        // Check if the current user is the sender/receiver or just a privileged user.
        if ($useridto != $USER->id && !$issuperuser) {
            throw new moodle_exception('accessdenied', 'admin');
        }

        if (!empty($useridto)) {
            if (!core_user::is_real_user($useridto)) {
                throw new moodle_exception('invaliduser');
            }
        }

        $sort = $newestfirst ? 'DESC' : 'ASC';
        $notifications = \message_petel\api::get_petel_notifications($useridto, $sort, $limit, $offset);
        $notificationcontexts = [];

        if ($notifications) {
            foreach ($notifications as $notification) {

                $notificationoutput = new \message_petel\output\petel_notification($notification);

                $notificationcontext = $notificationoutput->export_for_template($renderer);

                // Keep this for BC.
                $notificationcontext->deleted = false;
                $notificationcontexts[] = $notificationcontext;
            }
        }

        return array(
                'notifications' => $notificationcontexts,
                'unreadcount' => \message_petel\api::count_unread_petel_notifications($useridto),
        );
    }

    /**
     * Get notifications return description.
     *
     * @return external_single_structure
     * @since 3.2
     */
    public static function get_petel_notifications_returns() {
        return new external_single_structure(
                array(
                        'notifications' => new external_multiple_structure(
                                new external_single_structure(
                                        array(
                                                'id' => new external_value(PARAM_INT,
                                                        'Notification id (this is not guaranteed to be unique within this result set)'),
                                                'useridfrom' => new external_value(PARAM_INT, 'User from id'),
                                                'useridto' => new external_value(PARAM_INT, 'User to id'),
                                                'subject' => new external_value(PARAM_TEXT, 'The notification subject'),
                                                'shortenedsubject' => new external_value(PARAM_TEXT,
                                                        'The notification subject shortened with ellipsis'),
                                                'text' => new external_value(PARAM_RAW, 'The message text formated'),
                                                'fullmessage' => new external_value(PARAM_RAW, 'The message'),
                                                'fullmessageformat' => new external_format_value('fullmessage'),
                                                'fullmessagehtml' => new external_value(PARAM_RAW, 'The message in html'),
                                                'smallmessage' => new external_value(PARAM_RAW, 'The shorten message'),
                                                'contexturl' => new external_value(PARAM_RAW, 'Context URL'),
                                                'contexturlname' => new external_value(PARAM_TEXT, 'Context URL link name'),
                                                'timecreated' => new external_value(PARAM_INT, 'Time created'),
                                                'timecreatedpretty' => new external_value(PARAM_TEXT,
                                                        'Time created in a pretty format'),
                                                'timeread' => new external_value(PARAM_INT, 'Time read'),
                                                'read' => new external_value(PARAM_BOOL, 'notification read status'),
                                                'deleted' => new external_value(PARAM_BOOL, 'notification deletion status'),
                                                'iconurl' => new external_value(PARAM_URL, 'URL for notification icon'),
                                                'component' => new external_value(PARAM_TEXT,
                                                        'The component that generated the notification',
                                                        VALUE_OPTIONAL),
                                                'eventtype' => new external_value(PARAM_TEXT, 'The type of notification',
                                                        VALUE_OPTIONAL),
                                                'customdata' => new external_value(PARAM_RAW, 'Custom data to be passed to the message processor.
                                The data here is serialised using json_encode().', VALUE_OPTIONAL),
                                                'popupremoved' => new external_value(PARAM_INT, 'Time read'),
                                        ), 'message'
                                )
                        ),
                        'unreadcount' => new external_value(PARAM_INT, 'the number of unread message for the given user'),
                )
        );
    }

    /**
     * Get unread notification count parameters description.
     *
     * @return external_function_parameters
     * @since 3.2
     */
    public static function get_unread_petel_notification_count_parameters() {
        return new external_function_parameters(
                array(
                        'useridto' => new external_value(PARAM_INT, 'the user id who received the message, 0 for any user',
                                VALUE_REQUIRED),
                )
        );
    }

    /**
     * Get unread notification count function.
     *
     * @param int $useridto the user id who received the message
     * @return \message_petel\api::count_unread_petel_notifications
     * @throws invalid_parameter_exception
     * @throws moodle_exception
     * @since  3.2
     */
    public static function get_unread_petel_notification_count($useridto) {
        global $USER;

        $params = self::validate_parameters(
                self::get_unread_petel_notification_count_parameters(),
                array('useridto' => $useridto)
        );

        $context = context_user::instance($USER->id);
        self::validate_context($context);

        $useridto = $params['useridto'];

        if (!empty($useridto)) {
            if (core_user::is_real_user($useridto)) {
                $userto = core_user::get_user($useridto, '*', MUST_EXIST);
            } else {
                throw new moodle_exception('invaliduser');
            }
        }

        // Check if the current user is the sender/receiver or just a privileged user.
        if ($useridto != $USER->id && !has_capability('moodle/site:readallmessages', $context)) {
            throw new moodle_exception('accessdenied', 'admin');
        }

        return \message_petel\api::count_unread_petel_notifications($useridto);
    }

    /**
     * Get unread petel notification count return description.
     *
     * @return external_value
     * @since 3.2
     */
    public static function get_unread_petel_notification_count_returns() {
        return new external_value(PARAM_INT, 'The count of unread petel notifications');
    }

    /**
     * Close notification.
     *
     * @return external_function_parameters
     * @since 3.2
     */
    public static function close_notification_parameters() {
        return new external_function_parameters(
                array(
                        'messageid' => new external_value(PARAM_INT, 'message id', VALUE_REQUIRED),
                )
        );
    }

    /**
     * Get close notification function.
     *
     * @param int $useridto the user id who received the message
     * @return external_description
     * @throws invalid_parameter_exception
     * @throws moodle_exception
     * @since  3.2
     */
    public static function close_notification($messageid) {
        global $DB, $USER;

        $context = context_user::instance($USER->id);
        self::validate_context($context);

        $params = self::validate_parameters(
                self::close_notification_parameters(),
                array('messageid' => $messageid)
        );

        $messageid = $params['messageid'];

        $DB->delete_records('notifications', array('id' => $messageid));
        $DB->delete_records('message_petel_notifications', array('notificationid' => $messageid));

        return $messageid;
    }

    /**
     * Get close notification return description.
     *
     * @return external_value
     * @since 3.2
     */
    public static function close_notification_returns() {
        return new external_value(PARAM_INT, 'True/False');
    }

    /**
     * Close popup notification.
     *
     * @return external_function_parameters
     * @since 3.2
     */
    public static function close_popup_notification_parameters() {
        return new external_function_parameters(
                array(
                        'messageid' => new external_value(PARAM_INT, 'message id', VALUE_REQUIRED),
                )
        );
    }

    /**
     * Get close popup notification function.
     *
     * @param int $useridto the user id who received the message
     * @return external_description
     * @throws invalid_parameter_exception
     * @throws moodle_exception
     * @since  3.2
     */
    public static function close_popup_notification($messageid) {
        global $DB, $USER;

        $context = context_user::instance($USER->id);
        self::validate_context($context);

        $params = self::validate_parameters(
                self::close_notification_parameters(),
                array('messageid' => $messageid)
        );

        $messageid = $params['messageid'];

        $row = $DB->get_record('message_petel_notifications', array('notificationid' => $messageid));
        if (!empty($row)) {
            $row->popupremoved = 1;
            $DB->update_record('message_petel_notifications', $row);
        }

        $row2 = $DB->get_record('notifications', array('id' => $messageid));
        if (!empty($row2)) {
            $row2->timeread = time();
            $DB->update_record('notifications', $row2);
        }

        return $messageid;
    }

    /**
     * Get close popup notification return description.
     *
     * @return external_value
     * @since 3.2
     */
    public static function close_popup_notification_returns() {
        return new external_value(PARAM_INT, 'True/False');
    }
}
