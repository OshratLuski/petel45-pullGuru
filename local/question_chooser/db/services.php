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
 * @package local_question_chooser
 * @copyright 2022 Devlion.co
 * @author Devlion
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

$functions = array(
        'local_question_chooser_save_qtypes_favorites' => array(
                'classname' => 'local_question_chooser_external',
                'methodname' => 'save_qtypes_favorites',
                'description' => 'save qtypes favorites',
                'classpath' => 'local/question_chooser/externallib.php',
                'type' => 'write',
                'ajax' => true,
        ),
        'local_question_chooser_toggle_qtypes_recommendation' => array(
                'classname' => 'local_question_chooser_external',
                'methodname' => 'toggle_qtypes_recommendation',
                'description' => 'toggle qtypes recommendation',
                'classpath' => 'local/question_chooser/externallib.php',
                'type' => 'write',
                'ajax' => true,
        ),
);
