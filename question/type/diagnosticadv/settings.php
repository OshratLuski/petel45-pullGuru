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
 * This file contains settings for the drawing question type in Moodle.
 *
 * @package   qtype_drawing
 * @copyright (c) [devlion]
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/cohort/lib.php');

if ($ADMIN->fulltree) {

    // Introductory explanation that all the settings are defaults for the edit_diagnosticadv_form.
    $settings->add(
            new admin_setting_heading('settingstitle', '', get_string('settingstitle', 'qtype_diagnosticadv')));

    $settings->add(
            new admin_setting_configtextarea('qtype_diagnosticadv/systempromt',
                    get_string('systempromt', 'qtype_diagnosticadv'),
                    get_string('systempromt_help', 'qtype_diagnosticadv'), get_string('systempromt_default', 'qtype_diagnosticadv'),
                    PARAM_TEXT));

    $settings->add(
            new admin_setting_configtextarea('qtype_diagnosticadv/promttemaplate',
                    get_string('promttemaplate', 'qtype_diagnosticadv'),
                    get_string('promttemaplate_help', 'qtype_diagnosticadv'),
                    get_string('promttemaplate_dafault', 'qtype_diagnosticadv'), PARAM_TEXT));

    $settings->add(
            new admin_setting_configtextarea('qtype_diagnosticadv/disclaimer',
                    get_string('disclaimer', 'qtype_diagnosticadv'),
                    get_string('disclaimer_help', 'qtype_diagnosticadv'),
                    get_string('disclaimer_default', 'qtype_diagnosticadv'), PARAM_TEXT));

    $settings->add(
            new admin_setting_configtext('qtype_diagnosticadv/temperature',
                    get_string('temperature', 'qtype_diagnosticadv'),
                    get_string('temperature_help', 'qtype_diagnosticadv'),
                    get_config('tool_aiconnect', 'temperature'), PARAM_FLOAT));

    $settings->add(
            new admin_setting_configtextarea('qtype_diagnosticadv/logcolumns',
                    get_string('logcolumns', 'qtype_diagnosticadv'),
                    get_string('logcolumns_help', 'qtype_diagnosticadv'),
                    get_string('logcolumns_default', 'qtype_diagnosticadv'), PARAM_TEXT));

    $cohorts = cohort_get_all_cohorts(0, 1000);
    $options = array();
    $options[-1] = "None";
    foreach ($cohorts['cohorts'] as $cohort) {
        $options[$cohort->id] = $cohort->name;
    }
    $settings->add(new admin_setting_configselect('qtype_diagnosticadv/availabletocohort',
            get_string('availabletocohort', 'qtype_diagnosticadv'),
            get_string('availabletocohort_help', 'qtype_diagnosticadv'), 1, $options));
}
