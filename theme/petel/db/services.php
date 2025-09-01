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
 * Web service external functions and service definitions.
 *
 * @package    theme_petel
 * @copyright  2019 Devlion.co
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

// We defined the web service functions to install.
$functions = array(
    'theme_petel_quiz_student_question_message' => array(
            'classname' => 'theme_petel_external',
            'methodname' => 'student_question_message',
            'classpath' => 'theme/petel/externallib.php',
            'description' => 'Student question message',
            'type' => 'write',
            'ajax' => true,
    ),
    'theme_petel_quiz_set_timer_preferences' => array(
        'classname'   => 'theme_petel_courseformat_external',
        'methodname'  => 'quiz_set_timer_preferences',
        'classpath'   => 'theme/petel/courseformatexternallib.php',
        'description' => 'Get flexsections section content (intended to be used from AJAX)',
        'type'        => 'read',
        'ajax'        => true,
    ),
    'theme_petel_quiz_get_timer_preferences' => array(
        'classname'   => 'theme_petel_courseformat_external',
        'methodname'  => 'quiz_get_timer_preferences',
        'classpath'   => 'theme/petel/courseformatexternallib.php',
        'description' => 'Get flexsections section content (intended to be used from AJAX)',
        'type'        => 'read',
        'ajax'        => true,
    ),
    'theme_petel_course_search' => array(
            'classname' => 'theme_petel_external',
            'methodname' => 'course_search',
            'classpath' => 'theme/petel/externallib.php',
            'description' => 'Search activity in a course',
            'type' => 'read',
            'ajax' => true,
    ),
);

// We define the services to install as pre-build services. A pre-build service is not editable by administrator.
$services = array(
    'Petel AJAX' => array(
        'functions' => array(
            'theme_petel_quiz_student_question_message',
            'theme_petel_quiz_set_timer_preferences',
            'theme_petel_quiz_get_timer_preferences',
            'theme_petel_course_search',
            ),
        'restrictedusers' => 0,
        'enabled' => 1,
        'shortname' => 'petelajax'
    )
);
