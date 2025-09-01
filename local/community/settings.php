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
 * @package     local_community
 * @category    admin
 * @copyright   2019 Devlion <info@devlion.co>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

if ($ADMIN->fulltree) {
    foreach (core_plugin_manager::instance()->get_plugins_of_type('community') as $plugin) {
        /** @var \local_community\plugininfo\community $plugin */
        $plugin->load_settings($ADMIN, 'local_community', $hassiteconfig);
    }
}

$ADMIN->add('localplugins', new admin_externalpage('local_community_sharewith',
        get_string('pluginname', 'community_sharewith'),
        $CFG->wwwroot . '/admin/settings.php?section=community_sharewith'));

$ADMIN->add('localplugins', new admin_externalpage('local_community_oer',
        get_string('pluginname', 'community_oer'),
        $CFG->wwwroot . '/admin/settings.php?section=community_oer'));

$setting = new admin_settingpage('local_community', get_string('pluginname', 'local_community'));

// Catalog category ID.
$coursecategories = $DB->get_records('course_categories', []);
$categories = [];

usort($coursecategories, function($item1, $item2) {
    return $item1->path <=> $item2->path;
});

foreach ($coursecategories as $category) {
    $prefix = str_repeat('- ', $category->depth - 1);
    $categories[$category->id] = $prefix . $category->name;
}

$setting->add(new admin_setting_configselect('local_community/catalogcategoryid',
                get_string('settingscatalogcategoryid', 'local_community'),
                get_string('settingscatalogcategoryiddesc', 'local_community'),
                0, $categories)
);

$setting->add(new admin_setting_configtext('local_community/adminmails',
                get_string('settingsinsertmails', 'local_community'),
                get_string('settingsinsertmailsdesc', 'local_community'),
                '')
);

$setting->add(new  admin_setting_configcheckbox(
                'local_community/mailnewoeractivity',
                get_string('settingsmailnewoeractivity', 'community_oer'),
                get_string('settingsmailnewoeractivitydesc', 'community_oer'),
                '0')
);

$setting->add(new admin_setting_configtext('local_community/subjectmailnewoeractivity',
                get_string('settingssubjectmailnewoeractivity', 'community_oer'),
                get_string('settingssubjectmailnewoeractivitydesc', 'community_oer'),
                '')
);

$setting->add(new admin_setting_confightmleditor('local_community/messagemailnewoeractivity',
                get_string('settingsmessagemailnewoeractivity', 'community_oer'),
                get_string('settingsmessagemailnewoeractivitydesc', 'community_oer'),
                '')
);

$ADMIN->add('localplugins', $setting);
