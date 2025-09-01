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
 * @package     local_redmine
 * @category    support
 * @copyright   2021 <nadav.kavalerchik@weizmann.ac.il>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {

    // New settings page.
    $page = new admin_settingpage('local_redmine', get_string('pluginname', 'local_redmine', null, false));

    if ($ADMIN->fulltree) {

        // Redmine status.
        $name = 'local_redmine/redminestatus';
        $title = get_string('redminestatus', 'local_redmine');
        $description = get_string('redminestatusdesc', 'local_redmine');
        $default = 1;
        $choices = array(
                1 => get_string('enabled', 'local_redmine'),
                0 => get_string('disabled', 'local_redmine'),
        );
        $setting = new admin_setting_configselect($name, $title, $description, $default, $choices);
        $page->add($setting);

        // Redmine url.
        $name = 'local_redmine/redmineurl';
        $title = get_string('redmineurl', 'local_redmine');
        $description = get_string('redmineurldesc', 'local_redmine');
        $default = '';
        $setting = new admin_setting_configtext($name, $title, $description, $default);
        $page->add($setting);

        // Redmine username.
        $name = 'local_redmine/redmineusername';
        $title = get_string('redmineusername', 'local_redmine');
        $description = get_string('redmineusernamedesc', 'local_redmine');
        $default = '';
        $setting = new admin_setting_configtext($name, $title, $description, $default);
        $page->add($setting);

        // Redmine password.
        $name = 'local_redmine/redminepassword';
        $title = get_string('redminepassword', 'local_redmine');
        $description = get_string('redminepassworddesc', 'local_redmine');
        $default = '';
        $setting = new admin_setting_configpasswordunmask($name, $title, $description, $default);
        $page->add($setting);

        // Redmine admin username.
        $name = 'local_redmine/redmineadminusername';
        $title = get_string('redmineadminusername', 'local_redmine');
        $description = get_string('redmineadminusernamedesc', 'local_redmine');
        $default = '';
        $setting = new admin_setting_configtext($name, $title, $description, $default);
        $page->add($setting);

        // Redmine admin password.
        $name = 'local_redmine/redmineadminpassword';
        $title = get_string('redmineadminpassword', 'local_redmine');
        $description = get_string('redmineadminpassworddesc', 'local_redmine');
        $default = '';
        $setting = new admin_setting_configpasswordunmask($name, $title, $description, $default);
        $page->add($setting);

        // Redmine helpdesk user ID.
        $name = 'local_redmine/redminereporterid';
        $title = get_string('redminereporterid', 'local_redmine');
        $description = get_string('redminereporteriddesc', 'local_redmine');
        $default = '';
        $setting = new admin_setting_configtext($name, $title, $description, $default);
        $page->add($setting);

        // Redmine leadoercatalog user ID.
        $name = 'local_redmine/redmine_leadoercatalog';
        $title = get_string('redmine_leadoercatalog', 'local_redmine');
        $description = get_string('redmine_leadoercatalogdesc', 'local_redmine');
        $default = '';
        $setting = new admin_setting_configtext($name, $title, $description, $default);
        $page->add($setting);

        // Redmine technopedagogical user ID.
        $name = 'local_redmine/redmine_technopedagogical';
        $title = get_string('redmine_technopedagogical', 'local_redmine');
        $description = get_string('redmine_technopedagogicaldesc', 'local_redmine');
        $default = '';
        $setting = new admin_setting_configtext($name, $title, $description, $default);
        $page->add($setting);

        // Redmine allmighty mentor.
        $name = 'local_redmine/allmightymentor';
        $title = get_string('allmightymentor', 'local_redmine');
        $description = get_string('allmightymentordesc', 'local_redmine');
        $default = '';
        $setting = new admin_setting_configtext($name, $title, $description, $default);
        $page->add($setting);

        // Redmine watcher bug user ID.
        $name = 'local_redmine/redminewatcherbugid';
        $title = get_string('redminewatcherbugid', 'local_redmine');
        $description = get_string('redminewatcherbugiddesc', 'local_redmine');
        $default = '';
        $setting = new admin_setting_configtext($name, $title, $description, $default);
        $page->add($setting);

        // Redmine search words count.
        $name = 'local_redmine/redminesearchwords';
        $title = get_string('redminesearchwords', 'local_redmine');
        $description = get_string('redminesearchwordsdesc', 'local_redmine');
        $default = '5';
        $setting = new admin_setting_configtext($name, $title, $description, $default);
        $page->add($setting);

        // Redmine show results count.
        $name = 'local_redmine/redmineshowresults';
        $title = get_string('redmineshowresults', 'local_redmine');
        $description = get_string('redmineshowresultsdesc', 'local_redmine');
        $default = '5';
        $setting = new admin_setting_configtext($name, $title, $description, $default);
        $page->add($setting);

        // Support course.
        $oercourses = [];
        $catid = \community_oer\main_oer::get_oer_category();
        $categories = $DB->get_records_sql("
                        SELECT * FROM {course_categories} 
                        WHERE path LIKE('%/" . $catid . "/%')
                        ORDER BY sortorder ASC 
                    ");
        foreach ($categories as $cat) {
            $courses = $DB->get_records('course', ['category' => $cat->id]);
            foreach ($courses as $course) {
                $oercourses[] = $course->id;
            }
        }

        $coursesselect = [];
        foreach($oercourses as $cid){
            $course = $DB->get_record('course', ['id' => $cid]);
            $coursesselect[$cid] = $course->shortname;
        }

        $default = !empty($coursesselect) ? key($coursesselect) : 0;
        $page->add(new admin_setting_configselect('local_redmine/supportcourse',
                        get_string('settingssupportcourse', 'local_redmine'),
                        get_string('settingssupportcoursedesc', 'local_redmine'),
                        $default, $coursesselect)
        );
    }

    // Add settings page to the local settings category.
    $ADMIN->add('localplugins', $page);
}