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
 * External functions and service definitions.
 *
 * @package     message_petel
 * @copyright   2019 Devlion <info@devlion.co>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$functions = array(
        'message_petel_get_petel_notifications' => array(
                'classname' => 'message_petel_external',
                'methodname' => 'get_petel_notifications',
                'classpath' => 'message/output/petel/externallib.php',
                'description' => 'Retrieve a list of petel notifications for a user',
                'type' => 'read',
                'ajax' => true,
                'services' => array(MOODLE_OFFICIAL_MOBILE_SERVICE),
        ),
        'message_petel_get_unread_petel_notification_count' => array(
                'classname' => 'message_petel_external',
                'methodname' => 'get_unread_petel_notification_count',
                'classpath' => 'message/output/petel/externallib.php',
                'description' => 'Retrieve the count of unread petel notifications for a given user',
                'type' => 'read',
                'ajax' => true,
                'services' => array(MOODLE_OFFICIAL_MOBILE_SERVICE),
        ),
        'message_petel_close_notification' => array(
                'classname' => 'message_petel_external',
                'methodname' => 'close_notification',
                'classpath' => 'message/output/petel/externallib.php',
                'description' => 'Close notification',
                'type' => 'read',
                'ajax' => true,
                'services' => array(MOODLE_OFFICIAL_MOBILE_SERVICE),
        ),
        'message_petel_close_popup_notification' => array(
                'classname' => 'message_petel_external',
                'methodname' => 'close_popup_notification',
                'classpath' => 'message/output/petel/externallib.php',
                'description' => 'Close popup notification',
                'type' => 'read',
                'ajax' => true,
                'services' => array(MOODLE_OFFICIAL_MOBILE_SERVICE),
        ),
);
