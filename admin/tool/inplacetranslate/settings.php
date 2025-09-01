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
 * Plugin administration pages are defined here.
 *
 * @package     tool_inplacetranslate
 * @category    admin
 * @copyright   2024 Devlion <info@devlion.co>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {

    $ADMIN->add(
        'tools',
        new admin_category('tool_inplacetranslate', get_string('pluginname', 'tool_inplacetranslate'))
    );

    $settings = new admin_settingpage('tool_inplacetranslate_settings', get_string('mainsettings', 'tool_inplacetranslate'));

    $settings->add(new admin_setting_configcheckbox(
        'tool_inplacetranslate' . '/enabled',
        get_string('tool_inplacetranslate_enabled', 'tool_inplacetranslate'),
        get_string('tool_inplacetranslate_enabled_desc', 'tool_inplacetranslate'),
        1
    ));

    $ADMIN->add('tool_inplacetranslate', $settings);

}
