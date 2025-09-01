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
 * @package    qtype_essayrubric
 * @category   webservice
 * @copyright  2018 Devlion <info@devlion.co>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$functions = array(

    'qtype_essayrubric_store_grades' => array(
        'classname' => 'qtype_essayrubric_external',
        'methodname' => 'store_grades',
        'classpath' => 'question/type/essayrubric/externallib.php',
        'description' => 'store_grades',
        'type' => 'write',
        'ajax' => true,
        'services' => array(MOODLE_OFFICIAL_MOBILE_SERVICE),
    ),

    'qtype_essayrubric_get_indicators' => array(
        'classname' => 'qtype_essayrubric_external',
        'methodname' => 'get_indicators',
        'classpath' => 'question/type/essayrubric/externallib.php',
        'description' => 'get_indicators',
        'type' => 'read',
        'ajax' => true,
        'services' => array(MOODLE_OFFICIAL_MOBILE_SERVICE),
    ),

    'qtype_essayrubric_get_grades' => array(
        'classname' => 'qtype_essayrubric_external',
        'methodname' => 'get_grades',
        'classpath' => 'question/type/essayrubric/externallib.php',
        'description' => 'get_grades',
        'type' => 'read',
        'ajax' => true,
        'services' => array(MOODLE_OFFICIAL_MOBILE_SERVICE),
    ),

);

// We define the services to install as pre-build services. A pre-build service is not editable by administrator.
// $services = array(
//     'qtype_essayrubric' => array(
//         'functions' => array(
//             'qtype_essayrubric_store_grades',
//         ),
//         'enabled' => 1,
//         'shortname' => 'qtype_essayrubric',
//     ),
// );
