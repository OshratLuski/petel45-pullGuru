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
 * External tool external functions and service definitions.
 *
 * @package    mod_codescore
 * @category   external
 * @copyright  2024 Devlion <info@devlion.co>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @since      Moodle 3.9
 */

defined('MOODLE_INTERNAL') || die;

$functions = [
    'mod_codescore_delete_attempts' => [
        'classname' => 'mod_codescore_external',
        'methodname' => 'delete_attempts',
        'description' => 'Delete user attempts',
        'capabilities' => 'mod/quiz:addinstance',
        'type' => 'write',
        'ajax' => true,
    ],
    'mod_codescore_regrade_attempts' => [
        'classname' => 'mod_codescore_external',
        'methodname' => 'regrade_attempts',
        'description' => 'Regrade user attempts',
        'capabilities' => 'mod/quiz:addinstance',
        'type' => 'write',
        'ajax' => true,
    ],
];
$services = array(
    'Codescore web services' => [
        'functions' => [
            'mod_codescore_delete_attempts',
            'mod_codescore_regrade_attempts',
        ],
        'restrictedusers' => 1,
        'enabled' => 1,
    ],
);
