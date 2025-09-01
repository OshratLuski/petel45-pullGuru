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
 * Feedback event handler definition.
 *
 * @package     community_oer
 * @category    event
 * @copyright   2018 Devlion <info@devlion.co>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

// List of observers.
$observers = array(
        array(
                'eventname' => '\core\event\course_category_created',
                'callback' => 'community_oer_observer::course_category_created',
        ),

        array(
                'eventname' => '\core\event\course_category_updated',
                'callback' => 'community_oer_observer::course_category_updated',
        ),

        array(
                'eventname' => '\core\event\course_category_deleted',
                'callback' => 'community_oer_observer::course_category_deleted',
        ),

        array(
                'eventname' => '\core\event\course_created',
                'callback' => 'community_oer_observer::course_created',
        ),

        array(
                'eventname' => '\core\event\course_updated',
                'callback' => 'community_oer_observer::course_updated',
        ),

        array(
                'eventname' => '\core\event\course_deleted',
                'callback' => 'community_oer_observer::course_deleted',
        ),

        array(
                'eventname' => '\local_metadata\event\update_metadata',
                'callback' => 'community_oer_observer::update_metadata',
        ),

        array(
                'eventname' => '\community_sharequestion\event\question_to_catalog_copy',
                'callback' => 'community_oer_observer::question_to_catalog_copy',
        ),

        array(
                'eventname' => '\core\event\course_module_created',
                'callback' => 'community_oer_observer::course_module_created',
        ),

        array(
                'eventname' => '\core\event\course_module_updated',
                'callback' => 'community_oer_observer::course_module_updated',
        ),

        array(
                'eventname' => '\core\event\course_module_deleted',
                'callback' => 'community_oer_observer::course_module_deleted',
        ),

        array(
                'eventname' => '\core\event\question_created',
                'callback' => 'community_oer_observer::question_created',
        ),

        array(
                'eventname' => '\core\event\question_updated',
                'callback' => 'community_oer_observer::question_updated',
        ),

        array(
                'eventname' => '\core\event\question_deleted',
                'callback' => 'community_oer_observer::question_deleted',
        ),

        array(
            'eventname' => '\core\event\question_moved',
            'callback' => 'community_oer_observer::question_moved',
        ),

        array(
                'eventname' => '\community_sharewith\event\activity_from_bank_download',
                'callback' => 'community_oer_observer::activity_from_bank_download',
        ),

        array(
                'eventname' => '\community_sharequestion\event\question_to_quiz_copy',
                'callback' => 'community_oer_observer::question_to_quiz_copy',
        ),

        array(
                'eventname' => '\community_sharequestion\event\question_to_category_copy',
                'callback' => 'community_oer_observer::question_to_category_copy',
        ),

        array(
                'eventname' => '\core\event\course_section_created',
                'callback' => 'community_oer_observer::course_section_created',
        ),

        array(
                'eventname' => '\core\event\course_section_updated',
                'callback' => 'community_oer_observer::course_section_updated',
        ),

        array(
                'eventname' => '\core\event\course_section_deleted',
                'callback' => 'community_oer_observer::course_section_deleted',
        ),

        array(
                'eventname' => '\community_oer\event\module_move',
                'callback' => 'community_oer_observer::module_move',
        ),

        array(
                'eventname' => '\community_oer\event\resort_course',
                'callback' => 'community_oer_observer::resort_course',
        ),

        array(
                'eventname' => '\community_oer\event\resort_category',
                'callback' => 'community_oer_observer::resort_category',
        ),
);
