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
 * @package    local_petel
 * @copyright  2017 Nadav Kavalerchik <nadav.kavalerchik@weizmann.ac.il>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

$functions = array(
        'local_petel_store_applet_data' => array(
                'classname' => 'local_petel_external',
                'methodname' => 'store_applet_data',
                'classpath' => 'local/petel/classes/external.php',
                'description' => 'Store student applet activity data.',
                'type' => 'write',
        ),

        'local_petel_get_categories_ac' => array(
                'classname' => 'local_petel_external',
                'methodname' => 'get_categories_ac',
                'classpath' => 'local/petel/classes/external.php',
                'description' => 'Get categories for autocomplete',
                'type' => 'write',
                'ajax' => true,
                'services' => array(MOODLE_OFFICIAL_MOBILE_SERVICE)
        ),

        'local_petel_get_courses_ac' => array(
                'classname' => 'local_petel_external',
                'methodname' => 'get_courses_ac',
                'classpath' => 'local/petel/classes/external.php',
                'description' => 'Get categories for autocomplete',
                'type' => 'write',
                'ajax' => true,
                'services' => array(MOODLE_OFFICIAL_MOBILE_SERVICE)
        ),

        'local_petel_get_roles_ac' => array(
                'classname' => 'local_petel_external',
                'methodname' => 'get_roles_ac',
                'classpath' => 'local/petel/classes/external.php',
                'description' => 'Get roles for autocomplete',
                'type' => 'write',
                'ajax' => true,
                'services' => array(MOODLE_OFFICIAL_MOBILE_SERVICE)
        ),

        'local_petel_get_system_groups_ac' => array(
            'classname' => 'local_petel_external',
            'methodname' => 'get_system_groups_ac',
            'classpath' => 'local/petel/classes/external.php',
            'description' => 'Get system groups for autocomplete',
            'type' => 'write',
            'ajax' => true,
            'services' => array(MOODLE_OFFICIAL_MOBILE_SERVICE)
        ),

        'local_petel_create_courses_for_teachers' => array(
                'classname' => 'local_petel_external',
                'methodname' => 'create_courses_for_teachers',
                'classpath' => 'local/petel/classes/external.php',
                'description' => 'Create courses for teachers',
                'type' => 'write',
                'ajax' => true,
                'services' => array(MOODLE_OFFICIAL_MOBILE_SERVICE)
        ),

        'local_petel_create_system_groups_for_teachers' => array(
            'classname' => 'local_petel_external',
            'methodname' => 'create_system_groups_for_teachers',
            'classpath' => 'local/petel/classes/external.php',
            'description' => 'Create system groups for teachers',
            'type' => 'write',
            'ajax' => true,
            'services' => array(MOODLE_OFFICIAL_MOBILE_SERVICE)
        ),

        'local_petel_check_user_idnumber' => array(
                'classname' => 'local_petel_external',
                'methodname' => 'check_user_idnumber',
                'classpath' => 'local/petel/classes/external.php',
                'description' => 'Check user idnumber',
                'type' => 'write',
                'ajax' => true,
                'services' => array(MOODLE_OFFICIAL_MOBILE_SERVICE)
        ),

        'local_petel_create_course_for_teacher' => array(
                'classname' => 'local_petel_external',
                'methodname' => 'create_course_for_teacher',
                'classpath' => 'local/petel/classes/external.php',
                'description' => 'Create course for teacher',
                'type' => 'write',
                'ajax' => true,
                'services' => array(MOODLE_OFFICIAL_MOBILE_SERVICE)
        ),

        'local_petel_send_event' => array(
            'classname' => 'local_petel_external',
            'methodname' => 'send_event',
            'classpath' => 'local/petel/classes/external.php',
            'description' => 'Send event',
            'type' => 'write',
            'ajax' => true,
            'services' => array(MOODLE_OFFICIAL_MOBILE_SERVICE)
        ),

        'local_petel_popup_update_course_metadata' => array(
                'classname' => 'local_petel_external',
                'methodname' => 'popup_update_course_metadata',
                'classpath' => 'local/petel/classes/external.php',
                'description' => 'Popup update course metadata',
                'type' => 'read',
                'ajax' => true,
                'services' => array(MOODLE_OFFICIAL_MOBILE_SERVICE)
        ),

        'local_petel_update_course_metadata' => array(
                'classname' => 'local_petel_external',
                'methodname' => 'update_course_metadata',
                'classpath' => 'local/petel/classes/external.php',
                'description' => 'Update course metadata',
                'type' => 'write',
                'ajax' => true,
                'services' => array(MOODLE_OFFICIAL_MOBILE_SERVICE)
        ),
);
