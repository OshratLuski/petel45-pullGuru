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
 * External services definition for the qtype_diagnosticadvai plugin.
 *
 * @package    qtype_diagnosticadvai
 * @copyright  2024 Devlion <info@devlion.co>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$functions = [
    'qtype_diagnosticadvai_send_message' => [
        'classname' => 'qtype_diagnosticadvai\external\send_message',
        'methodname' => 'execute',
        'description' => 'Send message to AI',
        'capabilities' => '',
        'type' => 'write',
        'ajax' => true,
    ],
    'qtype_diagnosticadvai_get_message' => [
        'classname' => 'qtype_diagnosticadvai\external\get_message',
        'methodname' => 'execute',
        'description' => 'Get message history',
        'capabilities' => '',
        'type' => 'write',
        'ajax' => true,
    ],
];
