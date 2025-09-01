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
 * @package     community_oer
 * @category    admin
 * @copyright   2018 Devlion <info@devlion.co>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

global $CFG, $DB;

require_once($CFG->dirroot . '/cohort/lib.php');
require_once(__DIR__ . '/lib/settingslib.php');

if ($hassiteconfig) {

    $page = new admin_settingpage('community_oer',
            get_string('pluginname', 'community_oer', null, true));

    if ($ADMIN->fulltree) {

        // Review.
        $page->add(new admin_setting_heading('community_oer_main',
                get_string('community_oer_main_section', 'community_oer'), ''));

        $options = array(
                1 => get_string('enable'),
                2 => get_string('disable'),
        );
        $page->add(new admin_setting_configselect('community_oer/enablereviews', get_string('enablereviews',
                'community_oer'), '', 1, $options));

        $page->add(new admin_setting_configtext(
                        'community_oer/activityviewed',
                        get_string('activityviewed', 'community_oer'),
                        get_string('activityvieweddesc', 'community_oer'),
                        5, PARAM_INT)
        );

        $page->add(new admin_setting_configtext(
                        'community_oer/archiveoldrequest',
                        get_string('archiveoldrequest', 'community_oer'),
                        get_string('archiveoldrequestdesc', 'community_oer'),
                        4, PARAM_INT)
        );

        $page->add(new admin_setting_configtext(
                        'community_oer/rejectreviewbutton',
                        get_string('rejectreviewbutton', 'community_oer'),
                        get_string('rejectreviewbuttondesc', 'community_oer'),
                        0, PARAM_INT)
        );

        $cohorts = cohort_get_all_cohorts(0, 1000);
        $options = array();
        $options[-1] = get_string('none');
        foreach ($cohorts['cohorts'] as $cohort) {
            $options[$cohort->id] = $cohort->name;
        }
        $page->add(new admin_setting_configselect('community_oer/reviewcohort', get_string('reviewcohort',
                'community_oer'), '', 1, $options));

        $page->add(new admin_setting_configtext(
                        'community_oer/reviewscountstartdate',
                        get_string('reviewscountstartdate', 'community_oer'),
                        get_string('reviewscountstartdatedesc', 'community_oer'),
                        '', PARAM_TEXT, 30)
        );
        $page->add(new admin_setting_configtextarea(
                        'community_oer/reviewsquestiontext',
                        get_string('reviewsquestiontext', 'community_oer'),
                        get_string('reviewsquestiontextdesc', 'community_oer'),
                        'האם פעילות זו מומלצת למורים אחרים לדעתך?', PARAM_TEXT)
        );
        $page->add(new admin_setting_configtextarea(
                        'community_oer/reviewstextarea',
                        get_string('reviewstextarea', 'community_oer'),
                        get_string('reviewstextareadesc', 'community_oer'),
                        'נשמח לקבל חוות הדעת שלך על השימוש בפעילות:', PARAM_TEXT)
        );
        $page->add(new admin_setting_configtext(
                        'community_oer/reviewrating',
                        get_string('reviewrating', 'community_oer'),
                        get_string('reviewratingdesc', 'community_oer'),
                        '75', PARAM_TEXT, 30)
        );

        $page->add(new admin_setting_configtext(
                        'community_oer/minreviewcount',
                        get_string('minreviewcount', 'community_oer'),
                        get_string('minreviewcountdesc', 'community_oer'),
                        '0', PARAM_TEXT, 10)
        );

        // Activity.
        $page->add(new admin_setting_heading('community_oer_activity',
                get_string('community_oer_activity_section', 'community_oer'), ''));

        $cohorts = cohort_get_all_cohorts(0, 1000);
        $options = array();
        $options[-1] = get_string('none');
        foreach ($cohorts['cohorts'] as $cohort) {
            $options[$cohort->id] = $cohort->name;
        }
        $page->add(new admin_setting_configselect('community_oer/oer_tab_activities', get_string('oertabactivity',
                'community_oer'), '', 1, $options));

        $page->add(new admin_setting_configtext(
                        'community_oer/activity_items_on_page',
                        get_string('itemsonpage', 'community_oer'),
                        get_string('itemsonpagedesc', 'community_oer'),
                        10, PARAM_INT)
        );

        $options = array();
        $options['empty'] = get_string('none');

        $language = $DB->get_record('local_metadata_field', ['contextlevel' => CONTEXT_MODULE, 'shortname' => 'language']);

        $res = preg_split('/\R/', $language->param1);
        foreach (array_unique($res) as $name) {
            $options[$name] = $name;
        }

        $page->add(new admin_setting_configselect('community_oer/default_lang_activity', get_string('defaultlangactivity',
                'community_oer'), '', 'empty', $options));

        require_once($CFG->dirroot . '/local/community/plugins/oer/classes/activity_oer.php');

        $options = [];
        $options[0] = get_string('notselected', 'community_oer');
        foreach (\community_oer\activity_help::sorting_elements() as $item) {
            $options[$item['value']] = $item['name'];
        }

        $page->add(new admin_setting_configselect('community_oer/default_sort_activity', get_string('defaultsortactivity',
                'community_oer'), '', 3, $options));

        $page->add(new oer_admin_setting_mod_types(
                        'community_oer/filter_modtypes',
                        get_string('filter_modtypes', 'community_oer'),
                        get_string('filter_modtypes_desc', 'community_oer'),
                        [
                                'quiz' => PROFILE_VISIBLE_PRIVATE,
                                'assign' => PROFILE_VISIBLE_PRIVATE,
                                'questionnaire' => PROFILE_VISIBLE_PRIVATE,
                                'data' => PROFILE_VISIBLE_PRIVATE,
                                'glossary' => PROFILE_VISIBLE_PRIVATE,
                                'lesson' => PROFILE_VISIBLE_PRIVATE,
                                'hvp' => PROFILE_VISIBLE_PRIVATE,
                                'game' => PROFILE_VISIBLE_PRIVATE,
                                'workshop' => PROFILE_VISIBLE_PRIVATE,
                                'resource' => PROFILE_VISIBLE_PRIVATE,
                                'url' => PROFILE_VISIBLE_PRIVATE,
                                'page' => PROFILE_VISIBLE_PRIVATE,
                        ]
                )
        );

        // Question.
        $page->add(new admin_setting_heading('community_oer_question',
                get_string('community_oer_question_section', 'community_oer'), ''));

        $cohorts = cohort_get_all_cohorts(0, 1000);
        $options = array();
        $options[-1] = get_string('none');
        foreach ($cohorts['cohorts'] as $cohort) {
            $options[$cohort->id] = $cohort->name;
        }
        $page->add(new admin_setting_configselect('community_oer/oer_tab_questions', get_string('oertabquestion',
                'community_oer'), '', 1, $options));

        $page->add(new admin_setting_configtext(
                        'community_oer/question_items_on_page',
                        get_string('itemsonpage', 'community_oer'),
                        get_string('itemsonpagedesc', 'community_oer'),
                        20, PARAM_INT)
        );

        require_once($CFG->dirroot . '/local/community/plugins/oer/classes/question_oer.php');

        $options = [];
        $options[0] = get_string('notselected', 'community_oer');
        foreach (\community_oer\question_help::sorting_elements() as $item) {
            $options[$item['value']] = $item['name'];
        }

        $page->add(new admin_setting_configselect('community_oer/default_sort_question', get_string('defaultsortquestion',
                'community_oer'), '', 0, $options));

        $page->add(new oer_admin_setting_question_types(
                        'community_oer/filter_qtypes',
                        get_string('filter_qtypes', 'community_oer'),
                        get_string('filter_qtypes_desc', 'community_oer'),
                        [
                                'algebra' => PROFILE_VISIBLE_PRIVATE,
                                'essay' => PROFILE_VISIBLE_PRIVATE,
                                'formulas' => PROFILE_VISIBLE_PRIVATE,
                                'geogebra' => PROFILE_VISIBLE_PRIVATE,
                                'multichoice' => PROFILE_VISIBLE_PRIVATE,
                                'numerical' => PROFILE_VISIBLE_PRIVATE,
                                'truefalse' => PROFILE_VISIBLE_PRIVATE,
                                'multianswer' => PROFILE_VISIBLE_PRIVATE,
                        ]
                )
        );

        // Sequence.
        $page->add(new admin_setting_heading('community_oer_sequence',
                get_string('community_oer_sequence_section', 'community_oer'), ''));

        $cohorts = cohort_get_all_cohorts(0, 1000);
        $options = array();
        $options[-1] = get_string('none');
        foreach ($cohorts['cohorts'] as $cohort) {
            $options[$cohort->id] = $cohort->name;
        }
        $page->add(new admin_setting_configselect('community_oer/oer_tab_sequences', get_string('oertabsequence',
                'community_oer'), '', 1, $options));

        $page->add(new admin_setting_configtext(
                        'community_oer/sequence_items_on_page',
                        get_string('itemsonpage', 'community_oer'),
                        get_string('itemsonpagedesc', 'community_oer'),
                        20, PARAM_INT)
        );

        require_once($CFG->dirroot . '/local/community/plugins/oer/classes/sequence_oer.php');

        $options = [];
        $options[0] = get_string('notselected', 'community_oer');
        foreach (\community_oer\sequence_help::sorting_elements() as $item) {
            $options[$item['value']] = $item['name'];
        }

        $page->add(new admin_setting_configselect('community_oer/default_sort_sequence', get_string('defaultsortsequence',
                'community_oer'), '', 0, $options));

        // Course.
        $page->add(new admin_setting_heading('community_oer_course',
                get_string('community_oer_course_section', 'community_oer'), ''));

        $cohorts = cohort_get_all_cohorts(0, 1000);
        $options = array();
        $options[-1] = get_string('none');
        foreach ($cohorts['cohorts'] as $cohort) {
            $options[$cohort->id] = $cohort->name;
        }
        $page->add(new admin_setting_configselect('community_oer/oer_tab_courses', get_string('oertabcourse',
                'community_oer'), '', 1, $options));

        $page->add(new admin_setting_configtext(
                        'community_oer/course_items_on_page',
                        get_string('itemsonpage', 'community_oer'),
                        get_string('itemsonpagedesc', 'community_oer'),
                        20, PARAM_INT)
        );

        require_once($CFG->dirroot . '/local/community/plugins/oer/classes/course_oer.php');

        $options = [];
        $options[0] = get_string('notselected', 'community_oer');
        foreach (\community_oer\course_help::sorting_elements() as $item) {
            $options[$item['value']] = $item['name'];
        }

        $page->add(new admin_setting_configselect('community_oer/default_sort_course', get_string('defaultsortcourse',
                'community_oer'), '', 0, $options));

        $page->add(new admin_setting_configtext(
                        'community_oer/min_student_response',
                        get_string('minresponses', 'community_oer'),
                        get_string('minresponsesdesc', 'community_oer'),
                        5, PARAM_INT)
        );
    }

    // Add settings page to the appearance settings category.
    $ADMIN->add('localplugins', $page);
}
