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
 * @package     quiz_advancedoverview
 * @category    external
 * @copyright   2022 Devlion <info@devlion.co>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$functions = [
        'quiz_advancedoverview_render_dynamic_block' => array(
                'classname' => 'quiz_advancedoverview_external',
                'methodname' => 'render_dynamic_block',
                'classpath' => 'mod/quiz/report/advancedoverview/external.php',
                'description' => 'Render dynamic block',
                'type' => 'read',
                'ajax' => true,
                'capabilities' => '',
                'services' => array(MOODLE_OFFICIAL_MOBILE_SERVICE),
        ),
        'quiz_advancedoverview_regrade_attempts' => array(
                'classname' => 'quiz_advancedoverview_external',
                'methodname' => 'regrade_attempts',
                'classpath' => 'mod/quiz/report/advancedoverview/external.php',
                'description' => 'regrade_attempts',
                'ajax' => true,
                'capabilities' => '',
                'services' => array(MOODLE_OFFICIAL_MOBILE_SERVICE),
        ),
        'quiz_advancedoverview_close_attempts' => array(
                'classname' => 'quiz_advancedoverview_external',
                'methodname' => 'close_attempts',
                'classpath' => 'mod/quiz/report/advancedoverview/external.php',
                'description' => 'close_attempts',
                'ajax' => true,
                'capabilities' => '',
                'services' => array(MOODLE_OFFICIAL_MOBILE_SERVICE),
        ),
        'quiz_advancedoverview_delete_attempts' => array(
                'classname' => 'quiz_advancedoverview_external',
                'methodname' => 'delete_attempts',
                'classpath' => 'mod/quiz/report/advancedoverview/external.php',
                'description' => 'delete_attempts',
                'ajax' => true,
                'capabilities' => '',
                'services' => array(MOODLE_OFFICIAL_MOBILE_SERVICE),
        ),
];

$services = [
        'Advanced overview' => [
                'functions' => [
                        'quiz_advancedoverview_render_dynamic_block',
                        'quiz_advancedoverview_regrade_attempt',
                        'quiz_advancedoverview_close_attempts',
                        'quiz_advancedoverview_delete_attempts',
                ],
                'enabled' => 1,
                'shortname' => 'advoverview',
        ],
];
