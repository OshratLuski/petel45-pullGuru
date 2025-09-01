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
 * Plugin administration pages are defined here.
 *
 * @package     theme_petel
 * @copyright   2023 Devlion <info@devlion.co>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

if ($ADMIN->fulltree) {

    $settings = new theme_boost_admin_settingspage_tabs('themesettingpetel', get_string('configtitle', 'theme_petel'));

    // Each page is a tab - the first is the "General" tab.
    $page = new admin_settingpage('theme_petel_general', get_string('generalsettings', 'theme_petel'));

    // About URL.
    $name = 'theme_petel/abouturl';
    $title = get_string('abouturl', 'theme_petel');
    $description = get_string('abouturldesc', 'theme_petel');
    $default = new moodle_url('https://petel.weizmann.ac.il/');
    $setting = new admin_setting_configtext($name, $title, $description, $default, PARAM_URL);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);

    $settings->add($page);

    $page = new admin_settingpage('theme_petel_instance', get_string('instancesettings', 'theme_petel'));

    // Image.
    $name = 'theme_petel/backgroundimage';
    $title = get_string('backgroundimage', 'theme_petel');
    $description = get_string('backgroundimage_desc', 'theme_petel');
    $setting = new admin_setting_configstoredfile($name, $title, $description, 'backgroundimage');
    $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);

    // Instance name.
    $name = 'theme_petel/instancename';
    $title = get_string('instancename', 'theme_petel');
    $description = get_string('instancenamedesc', 'theme_petel');
    $default = '';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);

    // Privacy url.
    $name = 'theme_petel/privacyurl';
    $title = get_string('privacyurl', 'theme_petel');
    $description = get_string('privacyurldesc', 'theme_petel');
    $setting = new admin_setting_configcheckbox($name, $title, $description, true);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);

    // Terms url.
    $name = 'theme_petel/termsurl';
    $title = get_string('termsurl', 'theme_petel');
    $description = get_string('termsurldesc', 'theme_petel');
    $setting = new admin_setting_configcheckbox($name, $title, $description, true);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);

    //Custom Accessibility Policy link
    $name = 'theme_petel/accessibility_policy_link';
    $title = get_string('accessibility_policy', 'theme_petel');
    $description = get_string('accessibility_policy_link_descr', 'theme_petel');
    $setting = new admin_setting_configcheckbox($name, $title, $description, true);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);

    // Display blocks sidebar open/close.
    $name = 'theme_petel/blockexpanded';
    $title = get_string('blockexpanded', 'theme_petel');
    $description = get_string('blockexpanded_desc', 'theme_petel');
    $default = '';
    $setting = new admin_setting_configcheckbox($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);

    $settings->add($page);
}
