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
 * Settings file for tiny_styles plugin.
 *
 * @package     tiny_styles
 * @copyright   2025 Oshrat Luski <oshrat.luski@weizmann.ac.il>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

if ($ADMIN->fulltree) {
    $name = new lang_string('config', 'tiny_styles');
    $desc = new lang_string('config_desc', 'tiny_styles');
    $default = '';
    
    $settings = new admin_settingpage('tiny_styles_settings', new lang_string('settings', 'tiny_styles'));
    $settings->add(new admin_setting_configtextarea('tiny_styles/configuration', $name, $desc, $default));
}
