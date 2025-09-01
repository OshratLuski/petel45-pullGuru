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
 * @package    core_webservice
 * @category   webservice
 * @copyright  2009 Devlion
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$functions = array(
    'local_exportgrades_learninggroup_activities' => array(
        'classname' => 'local_exportgrade_external',
        'methodname' => 'learninggroup_activities',
        'description' => 'Return learninggroup activities by users',
        'type' => 'read',
        'capabilities' => 'moodle/course:view, moodle/user:viewdetails',
        'ajax' => false,
        'services' => array(MOODLE_OFFICIAL_MOBILE_SERVICE),
    ),
    'local_exportgrades_find_activities' => array(
        'classname' => 'local_exportgrade_external',
        'methodname' => 'find_activities',
        'description' => 'Find activities',
        'type' => 'read',
        'capabilities' => 'moodle/course:view, moodle/user:viewdetails',
        'ajax' => false,
        'services' => array(MOODLE_OFFICIAL_MOBILE_SERVICE),
    ),
    'local_exportgrades_learninggroup_grades' => array(
        'classname' => 'local_exportgrade_external',
        'methodname' => 'learninggroup_grades',
        'description' => 'Get Learning group grades',
        'type' => 'read',
        'capabilities' => 'moodle/course:view, moodle/user:viewdetails',
        'ajax' => false,
        'services' => array(MOODLE_OFFICIAL_MOBILE_SERVICE),
    ),
    'local_exportgrades_get_grades' => array(
        'classname' => 'local_exportgrade_external',
        'methodname' => 'get_grades',
        'description' => 'Get grade by cmid',
        'type' => 'read',
        'capabilities' => 'moodle/course:view, moodle/user:viewdetails',
        'ajax' => false,
        'services' => array(MOODLE_OFFICIAL_MOBILE_SERVICE),
    )
);

$services = array(
        'Web service for mashov' => array(
                'functions' => array(
                        'local_exportgrades_learninggroup_activities',
                        'local_exportgrades_find_activities',
                        'local_exportgrades_learninggroup_grades',
                        'local_exportgrades_get_grades',
                ),
                'enabled' => 1,
                'shortname' => 'webservicemashov'
        ),
        'Web service for smartschool' => array(
                'functions' => array(
                        'local_exportgrades_learninggroup_activities',
                        'local_exportgrades_find_activities',
                        'local_exportgrades_learninggroup_grades',
                        'local_exportgrades_get_grades',
                ),
                'enabled' => 1,
                'shortname' => 'webservicesmartschool'
        )
);
