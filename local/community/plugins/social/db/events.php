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
 * Add event handlers for the quiz
 *
 * @package    community_social
 * @category   event
 * @copyright  2019 Devlion <info@devlion.co>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$observers = array(
        array(
                'eventname' => '\community_social\event\course_module_visibility_changed',
                'callback' => '\community_social\observer::course_module_visibility_changed',
        ),

        array(
                'eventname' => '\core\event\course_deleted',
                'callback' => '\community_social\observer::course_deleted',
        ),

        array(
                'eventname' => '\core\event\course_updated',
                'callback' => '\community_social\observer::course_updated',
        ),

        array(
                'eventname' => '\core\event\user_deleted',
                'callback' => '\community_social\observer::user_deleted',
        ),

        array(
                'eventname' => '\core\event\user_updated',
                'callback' => '\community_social\observer::user_updated',
        ),

        array(
            'eventname' => '\core\event\course_module_updated',
            'callback' => '\community_social\observer::course_module_updated',
        ),

        array(
            'eventname' => '\core\event\course_module_deleted',
            'callback' => '\community_social\observer::course_module_deleted',
        ),

        array(
            'eventname' => '\local_metadata\event\update_metadata',
            'callback' => '\community_social\observer::update_metadata',
        ),
);
