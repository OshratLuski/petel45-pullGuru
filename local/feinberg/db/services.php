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
 * @package    local_feinberg
 * @copyright  2024 Oshrat Luski <oshrat.luski@weizmann.ac.il>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$functions = array(
    'enrol_manual_enrol_users_feinberg' => array(
        'classname' => 'local_feinberg_external',
        'methodname' => 'enrol_users_feinberg',
        'classpath' => 'local/feinberg/classes/external.php',
        'description' => 'Enrol users (register them if they do not exist) and update user info',
        'type' => 'write',
        'ajax' => true,
        'services' => array(MOODLE_OFFICIAL_MOBILE_SERVICE)
    ),

    'enrol_manual_unenrol_users_feinberg' => array(
        'classname' => 'local_feinberg_external',
        'methodname' => 'unenrol_users_feinberg',
        'classpath' => 'local/feinberg/classes/external.php',
        'description' => 'unroll users from a course by username',
        'type' => 'write',
        'ajax' => true,
        'services' => array(MOODLE_OFFICIAL_MOBILE_SERVICE)
    ),
);