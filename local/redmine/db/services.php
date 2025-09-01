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
 * Plugin administration pages are defined here.
 *
 * @package     local_redmine
 * @category    support
 * @copyright   2021 Devlion <info@devlion.co>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$functions = array(
        'local_redmine_support_request' => array(
                'classname' => 'local_redmine_external',
                'methodname' => 'support_request',
                'classpath'   => 'local/redmine/externallib.php',
                'description' => 'Process support request',
                'type' => 'read',
                'ajax' => true,
                'capabilities'  => '',
                'services'      => array(MOODLE_OFFICIAL_MOBILE_SERVICE)
        ),

        'local_redmine_get_support_activities' => array(
                'classname' => 'local_redmine_external',
                'methodname' => 'get_support_activities',
                'classpath'   => 'local/redmine/externallib.php',
                'description' => 'Get support activities',
                'type' => 'read',
                'ajax' => true,
                'capabilities'  => '',
                'services'      => array(MOODLE_OFFICIAL_MOBILE_SERVICE)
        ),

        'local_redmine_support_student_request' => array(
                'classname' => 'local_redmine_external',
                'methodname' => 'support_student_request',
                'classpath'   => 'local/redmine/externallib.php',
                'description' => 'Process support student request',
                'type' => 'read',
                'ajax' => true,
                'capabilities'  => '',
                'services'      => array(MOODLE_OFFICIAL_MOBILE_SERVICE)
        ),

        'local_redmine_get_active_issues' => array(
                'classname'   => 'local_redmine_external',
                'methodname'  => 'get_active_issues',
                'classpath'   => 'local/redmine/externallib.php',
                'description' => 'Get active issues from redmine',
                'type'          => 'read',
                'ajax'          => true,
                'capabilities'  => '',
                'services'      => array(MOODLE_OFFICIAL_MOBILE_SERVICE)
        ),

        'local_redmine_get_history_issues' => array(
                'classname'   => 'local_redmine_external',
                'methodname'  => 'get_history_issues',
                'classpath'   => 'local/redmine/externallib.php',
                'description' => 'Get history issues from redmine',
                'type'          => 'read',
                'ajax'          => true,
                'capabilities'  => '',
                'services'      => array(MOODLE_OFFICIAL_MOBILE_SERVICE)
        ),

        'local_redmine_get_chat_page' => array(
                'classname'   => 'local_redmine_external',
                'methodname'  => 'get_chat_page',
                'classpath'   => 'local/redmine/externallib.php',
                'description' => 'Get chat page from redmine',
                'type'          => 'read',
                'ajax'          => true,
                'capabilities'  => '',
                'services'      => array(MOODLE_OFFICIAL_MOBILE_SERVICE)
        ),

        'local_redmine_get_chat_messages' => array(
                'classname'   => 'local_redmine_external',
                'methodname'  => 'get_chat_messages',
                'classpath'   => 'local/redmine/externallib.php',
                'description' => 'Get chat message from DB',
                'type'          => 'read',
                'ajax'          => true,
                'capabilities'  => '',
                'services'      => array(MOODLE_OFFICIAL_MOBILE_SERVICE)
        ),

        'local_redmine_send_chat_message' => array(
                'classname'   => 'local_redmine_external',
                'methodname'  => 'send_chat_message',
                'classpath'   => 'local/redmine/externallib.php',
                'description' => 'Send chat message',
                'type'          => 'write',
                'ajax'          => true,
                'capabilities'  => '',
                'services'      => array(MOODLE_OFFICIAL_MOBILE_SERVICE)
        ),

        'local_redmine_issues_counter_user' => array(
                'classname'   => 'local_redmine_external',
                'methodname'  => 'issues_counter_user',
                'classpath'   => 'local/redmine/externallib.php',
                'description' => 'Issues counter user',
                'type'          => 'read',
                'ajax'          => true,
                'capabilities'  => '',
                'services'      => array(MOODLE_OFFICIAL_MOBILE_SERVICE)
        ),
);

// We define the services to install as pre-build services. A pre-build service is not editable by administrator.
$services = array(
        'Redmine plugin' => array(
                'functions' => array (
                    'local_redmine_support_request',
                    'local_redmine_get_support_activities',
                    'local_redmine_support_student_request',
                    'local_redmine_get_active_issues',
                    'local_redmine_get_history_issues',
                    'local_redmine_get_chat_page',
                    'local_redmine_get_chat_messages',
                    'local_redmine_send_chat_message',
                    'local_redmine_issues_counter_user',
                ),
                'enabled'=>1,
                'shortname'=>'local_redmine'
        )
);
