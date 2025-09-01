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
 * This is a one-line short description of the file
 *
 * You can have a rather longer description of the file as well,
 * if you like, and it can span multiple lines.
 *
 * @package    community_oer
 * @copyright  2018 Devlion <info@devlion.co>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define('CLI_SCRIPT', true);
require_once(__DIR__ . '/../../../../config.php');
require_once(__DIR__ . '/lib.php');

// Build images.
$PAGE->theme->force_svg_use(1);

\community_oer\main_oer::set_oercacheversion();

// Purge main structure.
\community_oer\main_oer::purge_structure();

// Cache data module activity.
$activity = new \community_oer\activity_oer;
$activity->recalculate_all_activities_in_db_cache();

// Cache data module question.
$question = new \community_oer\question_oer;
$question->recalculate_all_questions_in_db_cache();

// Cache data module sequence.
$sequence = new \community_oer\sequence_oer;
$sequence->recalculate_all_sequences_in_db_cache();

// Cache data module course.
$course = new \community_oer\course_oer;
$course->recalculate_all_courses_in_db_cache();
