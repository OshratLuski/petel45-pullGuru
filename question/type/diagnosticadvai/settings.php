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
 * This file contains settings for the diagnosticadvai question type in Moodle.
 *
 * @package   qtype_diagnosticadvai
 * @copyright (c) [devlion]
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

if ($ADMIN->fulltree) {
    // Introductory explanation that all the settings are defaults for the edit_diagnosticadv_form.
    $settings->add(
        new admin_setting_heading('settingstitle', '', get_string('settingstitle', 'qtype_diagnosticadv'))
    );

    $settings->add(
        new admin_setting_configtextarea('qtype_diagnosticadvai/systemprompt',
            get_string('systemprompt', 'qtype_diagnosticadvai'),
            get_string('systemprompt_help', 'qtype_diagnosticadvai'),
            get_string('systemprompt_default', 'qtype_diagnosticadvai'),
            PARAM_TEXT)
    );

    $settings->add(
        new admin_setting_configtextarea('qtype_diagnosticadvai/prompttemaplate',
            get_string('prompttemaplate', 'qtype_diagnosticadvai'),
            get_string('prompttemaplate_help', 'qtype_diagnosticadvai'),
            get_string('prompttemaplate_dafault', 'qtype_diagnosticadvai'),
            PARAM_TEXT)
    );

    $settings->add(
        new admin_setting_configtextarea('qtype_diagnosticadvai/disclaimer',
            get_string('disclaimer', 'qtype_diagnosticadvai'),
            get_string('disclaimer_help', 'qtype_diagnosticadvai'),
            get_string('disclaimer_default', 'qtype_diagnosticadvai'),
            PARAM_TEXT)
    );

    $settings->add(
        new admin_setting_configtext('qtype_diagnosticadvai/temperature',
            get_string('temperature', 'qtype_diagnosticadvai'),
            get_string('temperature_help', 'qtype_diagnosticadvai'),
            get_config('tool_aiconnect', 'temperature'),
            PARAM_FLOAT)
    );
}
