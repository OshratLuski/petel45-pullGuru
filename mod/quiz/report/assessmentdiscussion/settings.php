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
 * @package     quiz_assessmentdiscussion
 * @category    admin
 * @copyright   2018 Devlion <info@devlion.co>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/cohort/lib.php');
require_once(__DIR__ . '/lib/settingslib.php');

if ($ADMIN->fulltree) {

    // Cache.
    $settings->add(new admin_setting_configselect('quiz_assessmentdiscussion/cacheenable', get_string('cacheenable',
            'quiz_assessmentdiscussion'), '', 1, [0 => get_string('no'), 1 => get_string('yes')]));;

    // Cohort.
    $cohorts = cohort_get_all_cohorts(0, 1000);
    $options = array();
    $options[-1] = get_string('none');
    foreach ($cohorts['cohorts'] as $cohort) {
        $options[$cohort->id] = $cohort->name;
    }
    $settings->add(new admin_setting_configselect('quiz_assessmentdiscussion/accesscohort', get_string('accesscohort',
            'quiz_assessmentdiscussion'), '', -1, $options));

    // Use capability.
    $settings->add(new admin_setting_configcheckbox(
            'quiz_assessmentdiscussion/accesscapability', get_string('accesscapability', 'quiz_assessmentdiscussion'),
            '', 0
    ));

    $settings->add(new assessmentdiscussion_admin_setting_question_types(
            'quiz_assessmentdiscussion/filter_qtypes',
            get_string('filter_qtypes', 'quiz_assessmentdiscussion'),
            get_string('filter_qtypes_desc', 'quiz_assessmentdiscussion'),
            \quiz_assessmentdiscussion\quizinfo::get_disabled_qtypes(),
            [
                'essay' => PROFILE_VISIBLE_PRIVATE,
            ]
    ));
}