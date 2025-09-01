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
 * @package     community_sharequestion
 * @category    admin
 * @copyright  2018 Devlion <info@devlion.co>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

global $CFG, $DB;

require_once($CFG->dirroot . '/cohort/lib.php');

if ($hassiteconfig) {
    $settingpage = new admin_settingpage('community_sharequestion', get_string('pluginname', 'community_sharequestion'));

    $ADMIN->add('localplugins', $settingpage);

    $settingpage->add(new  admin_setting_configcheckbox(
                    'community_sharequestion/shownonrequieredfields',
                    get_string('settingsshownonrequieredfields', 'community_sharequestion'),
                    get_string('settingsshownonrequieredfieldsdesc', 'community_sharequestion'),
                    '1')
    );

    $settingpage->add(new admin_setting_configselect(
                    'community_sharequestion/numberofsections',
                    get_string('settingsnumberofsection', 'community_sharequestion'),
                    get_string('settingsnumberofsectiondesc', 'community_sharequestion'),
                    1, array('1' => 1, '2' => 2, '3' => 3, '4' => 4, '5' => 5))
    );

    $cohorts = cohort_get_all_cohorts(0, 1000);
    $options = array();
    $options[-1] = "None";
    foreach ($cohorts['cohorts'] as $cohort) {
        $options[$cohort->id] = $cohort->name;
    }
    $settingpage->add(new admin_setting_configselect('community_sharequestion/availabletocohort',
            get_string('availabletocohort', 'community_sharequestion'),
            get_string('availabletocohortdesc', 'community_sharequestion'), 1, $options));

    $settingpage->add(new  admin_setting_configcheckbox(
                    'community_sharequestion/showncompetencysection',
                    get_string('settingsshowncompetencysection', 'community_sharequestion'),
                    get_string('settingssshowncompetencysectiondesc', 'community_sharequestion'),
                    '0')
    );
}
