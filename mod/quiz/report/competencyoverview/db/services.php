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
 * @package    quiz_competencyoverview
 * @category   webservice
 * @copyright  2018 Devlion <info@devlion.co>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

$functions = array(
    'quiz_competencyoverview_get_courses'                       => array(
        'classname'   => 'quiz_competencyoverview_external',
        'methodname'  => 'get_courses',
        'classpath'   => 'mod/quiz/report/competencyoverview/externallib.php',
        'description' => 'Get courses',
        'type'        => 'read',
        'ajax'        => true,
        'services'    => array(MOODLE_OFFICIAL_MOBILE_SERVICE),
    ),
    'quiz_competencyoverview_get_activities'                    => array(
        'classname'   => 'quiz_competencyoverview_external',
        'methodname'  => 'get_activities',
        'classpath'   => 'mod/quiz/report/competencyoverview/externallib.php',
        'description' => 'Get activities',
        'type'        => 'read',
        'ajax'        => true,
        'services'    => array(MOODLE_OFFICIAL_MOBILE_SERVICE),
    ),
    'quiz_competencyoverview_get_items'                         => array(
        'classname'   => 'quiz_competencyoverview_external',
        'methodname'  => 'get_items',
        'classpath'   => 'mod/quiz/report/competencyoverview/externallib.php',
        'description' => 'Get items',
        'type'        => 'read',
        'ajax'        => true,
        'services'    => array(MOODLE_OFFICIAL_MOBILE_SERVICE),
    ),
    'quiz_competencyoverview_get_item'                          => array(
        'classname'   => 'quiz_competencyoverview_external',
        'methodname'  => 'get_item',
        'classpath'   => 'mod/quiz/report/competencyoverview/externallib.php',
        'description' => 'Get item',
        'type'        => 'read',
        'ajax'        => true,
        'services'    => array(MOODLE_OFFICIAL_MOBILE_SERVICE),
    ),
    'quiz_competencyoverview_get_targetsections'                => array(
        'classname'   => 'quiz_competencyoverview_external',
        'methodname'  => 'get_targetsections',
        'classpath'   => 'mod/quiz/report/competencyoverview/externallib.php',
        'description' => 'Get targetsections',
        'type'        => 'read',
        'ajax'        => true,
        'services'    => array(MOODLE_OFFICIAL_MOBILE_SERVICE),
    ),
    'quiz_competencyoverview_submit_assignment'                 => array(
        'classname'   => 'quiz_competencyoverview_external',
        'methodname'  => 'submit_assignment',
        'classpath'   => 'mod/quiz/report/competencyoverview/externallib.php',
        'description' => 'Submit assignment',
        'type'        => 'read',
        'ajax'        => true,
        'services'    => array(MOODLE_OFFICIAL_MOBILE_SERVICE),
    ),
    'quiz_competencyoverview_get_questions_by_competency_table' => array(
        'classname'   => 'quiz_competencyoverview_external',
        'methodname'  => 'get_questions_by_competency_table',
        'classpath'   => 'mod/quiz/report/competencyoverview/externallib.php',
        'description' => 'get_questions_by_competency_table',
        'type'        => 'read',
        'ajax'        => true,
        'services'    => array(MOODLE_OFFICIAL_MOBILE_SERVICE),
    ),
    'quiz_competencyoverview_get_init_params'                   => array(
        'classname'   => 'quiz_competencyoverview_external',
        'methodname'  => 'get_init_params',
        'classpath'   => 'mod/quiz/report/competencyoverview/externallib.php',
        'description' => 'Get courses',
        'type'        => 'read',
        'ajax'        => true,
        'services'    => array(MOODLE_OFFICIAL_MOBILE_SERVICE),
    ),
);
