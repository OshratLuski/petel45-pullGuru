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
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Defined caches used internally by the plugin.
 *
 * @package     quiz_assessmentdiscussion
 * @category    cache
 * @copyright   2018 Devlion <info@devlion.co>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$definitions = [
        'assessmentdiscussion_all_attempts' => [
                'mode' => cache_store::MODE_APPLICATION,
                'simplekeys' => true,
                'simpledata' => true,
                'staticacceleration' => false,
        ],

        'assessmentdiscussion_user_attempts_grade' => [
                'mode' => cache_store::MODE_APPLICATION,
                'simplekeys' => true,
                'simpledata' => true,
                'staticacceleration' => false,
        ],

        'assessmentdiscussion_questions' => [
                'mode' => cache_store::MODE_APPLICATION,
                'simplekeys' => true,
                'simpledata' => true,
                'staticacceleration' => false,
        ],
];
