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
 * Settings.
 *
 * @package local_h5ptracker
 * @copyright  2022 Weizmann institute of science, Israel.
 * @author 2021 Devlion.co
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

if ($hassiteconfig) {
    $settings = new admin_settingpage('local_h5ptracker', get_string('pluginname', 'local_h5ptracker'));
    $ADMIN->add('localplugins', $settings);

    // Enable.
    $setting = new admin_setting_configcheckbox('local_h5ptracker/enabled',
            get_string('enabled', 'local_h5ptracker'), get_string('enabled_desc', 'local_h5ptracker'), 0);
    $settings->add($setting);
}
