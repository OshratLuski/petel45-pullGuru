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
 * Code to be executed after the plugin's database scheme has been installed is defined here.
 *
 * @package     mod_codescore
 * @category    upgrade
 * @copyright   2024 Devlion <info@devlion.co>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Custom code to be run on installing the plugin.
 */

defined('MOODLE_INTERNAL') || die;

$capabilities = array(
        'mod/codescore:view' => array(
                'captype' => 'read',
                'contextlevel' => CONTEXT_MODULE,
                'archetypes' => array(
                        'guest' => CAP_ALLOW,
                        'user' => CAP_ALLOW,
                ),
        ),

        'mod/codescore:addinstance' => array(
                'riskbitmask' => RISK_XSS,

                'captype' => 'write',
                'contextlevel' => CONTEXT_COURSE,
                'archetypes' => array(
                        'editingteacher' => CAP_ALLOW,
                        'manager' => CAP_ALLOW,
                ),
                'clonepermissionsfrom' => 'moodle/course:manageactivities',
        ),

        'mod/codescore:addmanualinstance' => array(
                'captype' => 'read',
                'contextlevel' => CONTEXT_MODULE,
                'archetypes' => array(
                        'guest' => CAP_PROHIBIT,
                        'student' => CAP_PROHIBIT,
                        'teacher' => CAP_PROHIBIT,
                        'editingteacher' => CAP_PROHIBIT,
                ),
                'clonepermissionsfrom' => 'mod/codescore:addinstance',
        ),
        'mod/codescore:attempt' => [
                'riskbitmask' => RISK_SPAM,
                'captype' => 'write',
                'contextlevel' => CONTEXT_MODULE,
                'archetypes' => [
                        'student' => CAP_ALLOW,
                ],
        ],
);
