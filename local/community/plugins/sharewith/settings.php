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
 * @package   community_sharewith
 * @copyright 2018 Devlion <info@devlion.co>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

global $CFG, $DB;

require_once($CFG->dirroot . '/cohort/lib.php');

if ($hassiteconfig) {
    $settingpage = new admin_settingpage('community_sharewith', get_string('pluginname', 'community_sharewith'));

    $ADMIN->add('localplugins', $settingpage);

    $options = [
            1 => get_string('showforstudent', 'community_sharewith'),
            2 => get_string('hideforstudent', 'community_sharewith'),
            3 => get_string('hideforstudentavailable', 'community_sharewith'),
    ];

    $settingpage->add(new admin_setting_configselect('community_sharewith/visibilitytype', get_string('settingsvisibilitytype',
            'community_sharewith'), get_string('settingsvisibilitytypedesc', 'community_sharewith'), 2, $options));

    $settingpage->add(new  admin_setting_configcheckbox(
                    'community_sharewith/coursecopy',
                    get_string('settingscoursecopy', 'community_sharewith'),
                    get_string('settingscoursecopydesc', 'community_sharewith'),
                    '1')
    );

    $settingpage->add(new  admin_setting_configcheckbox(
                    'community_sharewith/sectioncopy',
                    get_string('settingssectioncopy', 'community_sharewith'),
                    get_string('settingssectioncopydesc', 'community_sharewith'),
                    '1')
    );

    $settingpage->add(new  admin_setting_configcheckbox(
                    'community_sharewith/activitycopy',
                    get_string('settingsactivitycopy', 'community_sharewith'),
                    get_string('settingsactivitycopydesc', 'community_sharewith'),
                    '1')
    );

    $settingpage->add(new  admin_setting_configcheckbox(
                    'community_sharewith/activitysending',
                    get_string('settingsactivitysending', 'community_sharewith'),
                    get_string('settingsactivitysendingdesc', 'community_sharewith'),
                    '1')
    );

    $settingpage->add(new  admin_setting_configcheckbox(
                    'community_sharewith/shownonrequieredfields',
                    get_string('settingsshownonrequieredfields', 'community_sharewith'),
                    get_string('settingsshownonrequieredfieldsdesc', 'community_sharewith'),
                    '1')
    );

    $settingpage->add(new admin_setting_configselect(
                    'community_sharewith/numberofsections',
                    get_string('settingsnumberofsection', 'community_sharewith'),
                    get_string('settingsnumberofsectiondesc', 'community_sharewith'),
                    1, array('1' => 1, '2' => 2, '3' => 3, '4' => 4, '5' => 5))
    );

    $settingpage->add(new admin_setting_configtext(
                    'community_sharewith/course_tag',
                    get_string('settingscoursetag', 'community_sharewith'),
                    get_string('settingscoursetagdesc', 'community_sharewith'),
                    'קהילה', PARAM_TEXT)
    );

    $options = [];
    $defaults = [];
    foreach ($DB->get_records('role') as $role) {
        $options[$role->shortname] = !empty(trim($role->name)) ? $role->name : $role->shortname;

        if (in_array($role->shortname, ['teacher', 'editingteacher'])) {
            $defaults[] = $role->shortname;
        }
    }

    $settingpage->add(new admin_setting_configmultiselect(
                    'community_sharewith/roles_share_teacher',
                    get_string('settingsrolesshareteacher', 'community_sharewith'),
                    get_string('settingsrolesshareteacherdesc', 'community_sharewith'),
                    $defaults, $options)
    );

    $cohorts = cohort_get_all_cohorts(0, 1000);
    $options = array();
    $options[-1] = get_string('none');
    foreach ($cohorts['cohorts'] as $cohort) {
        $options[$cohort->id] = $cohort->name;
    }
    $settingpage->add(new admin_setting_configselect(
            'community_oer/addcompetenciescohort',
            get_string('settingsaddcompetenciescohort', 'community_sharewith'),
            get_string('settingsaddcompetenciescohortdesc', 'community_sharewith'),
            -1, $options));

    $settingpage->add(new  admin_setting_configcheckbox(
                    'community_sharewith/showncompetencysection',
                    get_string('settingsshowncompetencysection', 'community_sharewith'),
                    get_string('settingssshowncompetencysectiondesc', 'community_sharewith'),
                    '0')
    );
}
