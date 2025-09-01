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
 * Scheduled task definitions for Mlnlpessay
 *
 * Documentation: {@link https://moodledev.io/docs/apis/subsystems/task}
 *
 * @package    qtype_mlnlpessay
 * @category   task
 * @copyright  2025 Devlion.co
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$tasks = [
    [
        'classname' => 'qtype_mlnlpessay\task\categories',
        'blocking' => 0,
        'minute' => '0',
        'hour' => '0',
        'day' => '0',
        'dayofweek' => '0',
        'month' => '12',
    ],
];
