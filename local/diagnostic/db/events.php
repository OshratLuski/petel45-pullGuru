<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option] any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Plugin event observers are registered here.
 *
 * @package    local
 * @subpackage diagnostic
 * @copyright  2021 Devlion.co
 * @author  Evgeniy Voevodin
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

// For more information about the Events API, please visit:
// https://docs.moodle.org/dev/Event_2.

$observers = [
    [
            'eventname' => '\mod_quiz\event\attempt_submitted',
            'callback' => '\local_diagnostic\observer::attempt_submitted',
    ],
    [
        'eventname' => '\core\event\course_module_created',
        'callback'  => '\local_diagnostic\observer::clear_quizzes_cache',
        'internal'  => false, // This means that we get events only after transaction commit.
        'priority'  => 1000,
    ],
    [
        'eventname' => '\core\event\course_module_updated',
        'callback'  => '\local_diagnostic\observer::clear_quizzes_cache',
        'internal'  => false, // This means that we get events only after transaction commit.
        'priority'  => 1000,
    ],
    [
        'eventname' => '\core\event\course_module_deleted',
        'callback'  => 'block_recent_activity_observer::clear_quizzes_cache',
        'internal'  => false, // This means that we get events only after transaction commit.
        'priority'  => 1000,
    ]
];
