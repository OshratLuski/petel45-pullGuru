<?php
// This file is part of Moodle - https://moodle.org/
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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * External functions and services provided by the plugin are declared here.
 *
 * @package     quiz_assessmentdiscussion
 * @category    external
 * @copyright   2022 Devlion <info@devlion.co>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$functions = [
        'quiz_assessmentdiscussion_render_main_block' => array(
                'classname' => 'quiz_assessmentdiscussion_external',
                'methodname' => 'render_main_block',
                'classpath' => 'mod/quiz/report/assessmentdiscussion/external.php',
                'description' => 'Render main block',
                'type' => 'read',
                'ajax' => true,
                'capabilities' => '',
                'services' => array(MOODLE_OFFICIAL_MOBILE_SERVICE),
        ),

        'quiz_assessmentdiscussion_render_answer_area_block' => array(
                'classname' => 'quiz_assessmentdiscussion_external',
                'methodname' => 'render_answer_area_block',
                'classpath' => 'mod/quiz/report/assessmentdiscussion/external.php',
                'description' => 'Render answer area block',
                'type' => 'read',
                'ajax' => true,
                'capabilities' => '',
                'services' => array(MOODLE_OFFICIAL_MOBILE_SERVICE),
        ),

        'quiz_assessmentdiscussion_change_discussion' => array(
                'classname' => 'quiz_assessmentdiscussion_external',
                'methodname' => 'change_discussion',
                'classpath' => 'mod/quiz/report/assessmentdiscussion/external.php',
                'description' => 'Change discussion',
                'type' => 'write',
                'ajax' => true,
                'capabilities' => '',
                'services' => array(MOODLE_OFFICIAL_MOBILE_SERVICE),
        ),

        'quiz_assessmentdiscussion_render_overlay_block' => array(
                'classname' => 'quiz_assessmentdiscussion_external',
                'methodname' => 'render_overlay_block',
                'classpath' => 'mod/quiz/report/assessmentdiscussion/external.php',
                'description' => 'Render overlay block',
                'type' => 'read',
                'ajax' => true,
                'capabilities' => '',
                'services' => array(MOODLE_OFFICIAL_MOBILE_SERVICE),
        ),

        'quiz_assessmentdiscussion_save_grades' => array(
                'classname' => 'quiz_assessmentdiscussion_external',
                'methodname' => 'save_grades',
                'classpath' => 'mod/quiz/report/assessmentdiscussion/external.php',
                'description' => 'Save grades',
                'type' => 'write',
                'ajax' => true,
                'capabilities' => '',
                'services' => array(MOODLE_OFFICIAL_MOBILE_SERVICE),
        ),

        'quiz_assessmentdiscussion_preview' => array(
                'classname' => 'quiz_assessmentdiscussion_external',
                'methodname' => 'preview',
                'classpath' => 'mod/quiz/report/assessmentdiscussion/external.php',
                'description' => 'Preview',
                'type' => 'read',
                'ajax' => true,
                'capabilities' => '',
                'services' => array(MOODLE_OFFICIAL_MOBILE_SERVICE),
        ),

        'quiz_assessmentdiscussion_change_grades' => array(
                'classname' => 'quiz_assessmentdiscussion_external',
                'methodname' => 'change_grades',
                'classpath' => 'mod/quiz/report/assessmentdiscussion/external.php',
                'description' => 'Change grades',
                'type' => 'write',
                'ajax' => true,
                'capabilities' => '',
                'services' => array(MOODLE_OFFICIAL_MOBILE_SERVICE),
        ),
];

$services = array(
        'Quiz assessmentdiscussion' => array(
                'functions' => array(
                        'quiz_assessmentdiscussion_render_main_block',
                        'quiz_assessmentdiscussion_render_answer_area_block',
                        'quiz_assessmentdiscussion_change_discussion',
                        'quiz_assessmentdiscussion_render_overlay_block',
                        'quiz_assessmentdiscussion_save_grades',
                        'quiz_assessmentdiscussion_preview',
                        'quiz_assessmentdiscussion_change_grades',

                ),
                'enabled' => 1,
                'shortname' => 'assessmentdiscussion'
        )
);
