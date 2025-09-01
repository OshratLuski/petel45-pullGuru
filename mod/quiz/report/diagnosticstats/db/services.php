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
 * quiz_diagnosticstats service definition.
 *
 * @package    quiz_diagnosticstats
 * @copyright  2024 Oshrat Luski <oshrat.luski@weizmann.ac.il>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

$functions = [
    'quiz_diagnosticstats_get_correctanswer' => [
        'classname'   => 'quiz_diagnosticstats\external\get_correctanswer',
        'methodname'  => 'get_correctanswer',
        'classpath'   => 'mod/quiz/report/diagnosticstats/classes/external/get_correctanswer.php',
        'description' => 'Get the correct answer for a given question.',
        'type'        => 'read',
        'ajax'        => true,
    ],
    'quiz_diagnosticstats_set_anonymousstate' => [
        'classname'   => 'quiz_diagnosticstats\external\set_anonymousstate',
        'methodname'  => 'set_anonymousstate',
        'classpath'   => 'mod/quiz/report/diagnosticstats/classes/external/set_anonymousstate.php',
        'description' => 'Set the anonymous state for a given course module.',
        'type'        => 'write',
        'ajax'        => true,
    ],
];