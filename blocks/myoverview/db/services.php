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
 * Core external functions and service definitions.
 *
 * The functions and services defined on this file are
 * processed and registered into the Moodle DB after any
 * install or upgrade operation. All plugins support this.
 *
 * For more information, take a look to the documentation available:
 *     - Webservices API: {@link http://docs.moodle.org/dev/Web_services_API}
 *     - External API: {@link http://docs.moodle.org/dev/External_functions_API}
 *     - Upgrade API: {@link http://docs.moodle.org/dev/Upgrade_API}
 *
 * @package    block_myoverview
 * @category   webservice
 * @copyright  2009 Petr Skodak
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$functions = array(
        'block_myoverview_get_enrolled_courses_by_timeline_classification' => array(
                'classname' => 'block_myoverview_external',
                'methodname' => 'get_enrolled_courses_by_timeline_classification',
                'classpath' => 'block/myoverview/classes/external.php',
                'description' => 'Get enrolled courses.',
                'type' => 'read',
                'ajax' => true,
                'loginrequired' => false,
                'services' => array(MOODLE_OFFICIAL_MOBILE_SERVICE),
        ),
        'block_myoverview_set_favourite_courses' => array(
                'classname' => 'block_myoverview_external',
                'methodname' => 'set_favourite_courses',
                'classpath' => 'block/myoverview/classes/external.php',
                'description' => 'Add a list of courses to the list of favourite courses.',
                'type' => 'write',
                'ajax' => true,
                'loginrequired' => false,
                'services' => array(MOODLE_OFFICIAL_MOBILE_SERVICE),
        ),
        'block_myoverview_update_user_preferences' => array(
                'classname' => 'block_myoverview_external',
                'methodname' => 'update_user_preferences',
                'classpath' => 'block/myoverview/classes/external.php',
                'description' => 'Update a user\'s preferences',
                'type' => 'write',
                'capabilities' => 'moodle/user:editownmessageprofile, moodle/user:editmessageprofile',
                'ajax' => true,
                'loginrequired' => false,
                'services' => array(MOODLE_OFFICIAL_MOBILE_SERVICE),
        ),
        'block_myoverview_get_custom_info_by_course' => array(
                'classname' => 'block_myoverview_external',
                'methodname' => 'get_custom_info_by_course',
                'classpath' => 'block/myoverview/classes/external.php',
                'description' => 'Get custom info by course.',
                'type' => 'read',
                'ajax' => true,
                'readonlysession' => true,
                'loginrequired' => false,
                'services' => array(MOODLE_OFFICIAL_MOBILE_SERVICE),
        ),
        'block_myoverview_send_course_messages' => array(
                'classname' => 'block_myoverview_external',
                'methodname' => 'send_course_messages',
                'classpath' => 'block/myoverview/classes/external.php',
                'description' => 'Send course messages',
                'type' => 'write',
                'capabilities' => 'moodle/site:sendmessage',
                'ajax' => true,
                'loginrequired' => false,
                'services' => array(MOODLE_OFFICIAL_MOBILE_SERVICE),
        ),
);

$services = array(
        'Block myoverview web service' => array(
                'functions' => array(
                        'block_myoverview_get_enrolled_courses_by_timeline_classification',
                        'block_myoverview_set_favourite_courses',
                        'block_myoverview_update_user_preferences',
                        'block_myoverview_get_custom_info_by_course',
                        'block_myoverview_send_course_messages',
                ),
                'enabled' => 1,
                'restrictedusers' => 0,
                'shortname' => 'myoverview',
        ),
);


