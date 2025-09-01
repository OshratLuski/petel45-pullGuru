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
 * @package    community_sharecourse
 * @category   admin
 * @copyright  2018 Devlion <info@devlion.co>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

global $CFG, $DB;

require_once($CFG->dirroot . '/cohort/lib.php');

if ($hassiteconfig) {
    $settingpage = new admin_settingpage('community_sharecourse', get_string('pluginname', 'community_sharecourse'));

    $ADMIN->add('localplugins', $settingpage);

    $settingpage->add(new  admin_setting_configcheckbox(
                    'community_sharecourse/shownonrequieredfields',
                    get_string('settingsshownonrequieredfields', 'community_sharecourse'),
                    get_string('settingsshownonrequieredfieldsdesc', 'community_sharecourse'),
                    '1')
    );

    // Cohort for view share.
    $cohorts = cohort_get_all_cohorts(0, 1000);
    $options = [];
    $options[-1] = get_string('none');
    foreach ($cohorts['cohorts'] as $cohort) {
        $options[$cohort->id] = $cohort->name;
    }
    $settingpage->add(new admin_setting_configselect('community_sharecourse/availabletocohort',
            get_string('availabletocohort', 'community_sharecourse'),
            get_string('availabletocohortdesc', 'community_sharecourse'), 1, $options));

    // Role for enrol course.
    $options = [];
    $options[-1] = get_string('none');
    $default = -1;
    foreach ($DB->get_records('role') as $role) {
        $name = !empty($role->name) ? $role->name : $role->shortname;
        $options[$role->id] = $name;

        if ($role->shortname == 'teachercolleague') {
            $default = $role->id;
        }
    }
    $settingpage->add(new admin_setting_configselect('community_sharecourse/oercoursecohortrole',
            get_string('oercoursecohortrole', 'community_sharecourse'),
            get_string('oercoursecohortroledesc', 'community_sharecourse'), $default, $options));

    // Cohort for enrol course.
    $cohorts = cohort_get_all_cohorts(0, 1000);
    $options = [];
    $options[-1] = get_string('none');
    $default = -1;
    foreach ($cohorts['cohorts'] as $cohort) {
        $options[$cohort->id] = $cohort->name;

        if ($cohort->name == 'teachers') {
            $default = $cohort->id;
        }
    }
    $settingpage->add(new admin_setting_configselect('community_sharecourse/oercoursecohort',
            get_string('oercoursecohort', 'community_sharecourse'),
            get_string('oercoursecohortdesc', 'community_sharecourse'), $default, $options));

    $settingpage->add(new admin_setting_configcheckbox('community_sharecourse/oercoursesharevisible',
            get_string('oercoursesharevisible', 'community_sharecourse'),
            get_string('oercoursesharevisibledesc', 'community_sharecourse'), 0));
}
