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

namespace theme_petel;

/**
 * Custom navigation.
 *
 * This class is copied and modified from /theme/boost/classes/boostnavbar.php
 *
 * @package    theme_petel
 * @copyright  2023 Luca Bösch <luca.boesch@bfh.ch>
 * @copyright  based on code from theme_boost by Adrian Greeve
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class custom_navigation {

    /**
     * Custom secondary navigation.
     */
    public static function secondary_navigation(): bool {
        global $PAGE, $COURSE, $USER, $CFG, $DB;

        $coursecontext = \context_course::instance($COURSE->id);
        $roles = get_user_roles($coursecontext, $USER->id);

        $cmid = 0;
        if ($PAGE->cm->id && $DB->get_record('course_modules', ['id' => $PAGE->cm->id])) {
            $cmid = $PAGE->cm->id;
        }

        // Add items.

        // Add buttons for assign.
        if ($cmid) {
            $context = \context_module::instance($cmid);

            list($course, $cm) = get_course_and_cm_from_cmid($context->instanceid);

            // Capability.
            if ($cm->modname == 'assign' && has_any_capability(array('mod/assign:viewgrades', 'mod/assign:grade'), $context)) {

                // Submission link.
                $submissionlink = new \moodle_url('/mod/assign/view.php', ['id' => $cmid, 'action' => 'grading']);
                $PAGE->secondarynav->add(get_string('viewgrading', 'mod_assign'),
                        $submissionlink, $PAGE->secondarynav::TYPE_CUSTOM, 'assignsubmissionlink', 'assignsubmissionlink');


                // Remove 'הגשות'.
                foreach ($PAGE->secondarynav->get_children_key_list() as $key) {
                    if (in_array($key, ['mod_assign_submissions'])) {
                        $PAGE->secondarynav->children->remove($key);
                    }
                }

                // Grade link.
                $gradelink = new \moodle_url('/mod/assign/view.php', ['id' => $cmid, 'action' => 'grader']);
                $PAGE->secondarynav->add(get_string('gradeverb', 'core'),
                        $gradelink, $PAGE->secondarynav::TYPE_CUSTOM, 'assigngradelink', 'assigngradelink');

                if (in_array('advgrading', $PAGE->secondarynav->get_children_key_list())) {
                    $PAGE->secondarynav->get('advgrading')->set_force_into_more_menu(true);
                }

                // Set active.
                if ($PAGE->pagetype == 'mod-assign-grading') {
                    $PAGE->secondarynav->get('modulepage')->make_inactive();
                    $PAGE->secondarynav->get('assignsubmissionlink')->make_active();
                }
            }
        }

        // Add advanced overview and grading students. PTL-9414.
        if (in_array($PAGE->pagetype, ['mod-quiz-view', 'mod-quiz-edit', 'mod-quiz-mod', 'mod-quiz-report', 'mod-quiz-attempt',
                                        'question-edit', 'mod-quiz-override', ''])) {

            if ($PAGE->pagetype == 'mod-quiz-report') {
                $mode = required_param('mode', PARAM_RAW);
                switch ($mode) {
                    case 'advancedoverview':
                        $PAGE->set_secondary_active_tab('reportadvancedoverview');
                    case 'gradingstudents':
                        $PAGE->set_secondary_active_tab('reportgradingstudents');
                    case 'assessmentdiscussion':
                        $PAGE->set_secondary_active_tab('reportassessmentdiscussion');
                }
            }

            if ($cmid) {
                $context = \context_module::instance($cmid);

                $flagteacher = false;
                foreach ($roles as $role) {
                    if (in_array($role->shortname, ['teacher'])) {
                        $flagteacher = true;
                    }
                }

                if (has_capability('mod/quiz:manage', $context) || $flagteacher) {

                    $label = get_string('advancedoverviewlink', 'theme_petel');

                    $numattempts = 0;
                    if (class_exists('\quiz_advancedoverview\quizdata')) {
                        $quizdata = new \quiz_advancedoverview\quizdata($cmid);
                        $quizdata->prepare_questions();
                        $quizdata->prepare_charts();
                        $quizdata->prepare_students();

                        $advdata = $quizdata->get_render_data();
                        foreach ($advdata['data_table_according_students_options']['participants']['states'] as $state) {
                            if ($state['name'] == 'finished') {
                                $numattempts = $state['value'];
                            }
                        }
                    }

                    if ($numattempts > 0) {
                        $label .= ' ('.$numattempts.')';
                    }

                    $advancedoverviewurl = new \moodle_url('/mod/quiz/report.php', array('id' => $cmid, 'mode' => 'advancedoverview'));
                    $PAGE->secondarynav->add($label, $advancedoverviewurl,
                            $PAGE->secondarynav::TYPE_CUSTOM, 'reportadvancedoverview', 'reportadvancedoverview');

                    // Report assessmentdiscussion.
                    if (class_exists('\quiz_assessmentdiscussion\assessmentdiscussion') && \quiz_assessmentdiscussion\assessmentdiscussion::check_access()) {
                        $label = get_string('assessmentdiscussionlink', 'theme_petel');
                        $assessmentdiscussionurl = new \moodle_url('/mod/quiz/report.php', array('id' => $cmid, 'mode' => 'assessmentdiscussion'));
                        $PAGE->secondarynav->add($label, $assessmentdiscussionurl,
                                $PAGE->secondarynav::TYPE_CUSTOM, 'reportassessmentdiscussion', 'reportassessmentdiscussion');
                    }

                    $gradingstudentsurl = new \moodle_url('/mod/quiz/report.php', array('id' => $cmid, 'mode' => 'gradingstudents'));
                    $PAGE->secondarynav->add(get_string('gradingstudentslink', 'theme_petel'), $gradingstudentsurl,
                            $PAGE->secondarynav::TYPE_CUSTOM, 'reportgradingstudents', 'reportgradingstudents');
                }
            }
        }

        // Activity remind.
        list($categories, $courses, $activities) = \community_oer\main_oer::get_main_structure_elements();

        $catid = \community_oer\main_oer::get_oer_category();
        if ($cmid && $catid != null) {
            $context = \context_coursecat::instance($catid);

            if (in_array($COURSE->id, $courses) && has_capability('moodle/category:manage', $context)) {
                $activityremind = new \moodle_url('javascript:void(0)');
                $PAGE->secondarynav->add(get_string('activity_update_notification', 'community_oer'),
                        $activityremind, $PAGE->secondarynav::TYPE_CUSTOM, 'activityRemind_'.$cmid, 'activityRemind_'.$cmid);
            }
        }

        // Move items.

        // Move some menu items to "others" menu listbox for all users.
        $movetootherlist = [
                'quiz_report',
                'questionbank',
                'metadata'
        ];

        $lists = $PAGE->secondarynav->get_children_key_list();
        foreach ($movetootherlist as $key) {
            if (in_array($key, $lists)) {
                $PAGE->secondarynav->get($key)->set_force_into_more_menu(true);
            }
        }

        // If admin see all.
        if (is_siteadmin()) {
            return true;
        }

        foreach ($roles as $role) {
            if (in_array($role->shortname, ['manager'])) {
                return true;
            }
        }

        // Remove items.

        // EC-622, EC-596, EC-704.
        if (in_array($PAGE->pagetype, ['mod-quiz-view', 'mod-quiz-edit', 'mod-quiz-mod', 'mod-quiz-report', 'mod-quiz-attempt',
            'question-edit', 'mod-quiz-override', ''])) {

            if ($cmid) {
                $flagteacher = false;
                foreach ($roles as $role) {
                    if (in_array($role->shortname, ['teacher'])) {
                        $flagteacher = true;
                    }
                }

                if (!$flagteacher && \community_oer\main_oer::is_activity_in_research($cmid)) {
                    $PAGE->secondarynav->children->remove('questionbank');
                    $PAGE->secondarynav->children->remove('mod_quiz_edit');
                }
            }
        }

        // PTL-10144. PTL-9713. PTL-9383. PTL-9730. Remove all not relevant items for students.
        // Exclude links from menu (מורה צופה או כמורה עמית).
        $flagpermission = false;
        $rolespermitted = ['browsingteacher', 'teachercolleague'];
        foreach ($roles as $role) {
            if (in_array($role->shortname, $rolespermitted)) {
                $flagpermission = true;
            }
        }

        $notteacher    = !has_capability('moodle/course:update', $coursecontext);

        if ($notteacher || $flagpermission) {
            $present = [];
            if (isset($lists[0])) {
                $present[] = $lists[0];
            }

            foreach ($roles as $role) {
                if (in_array($role->shortname, ['teacher']) && in_array('reportadvancedoverview', $lists)) {
                    $present[] = 'reportadvancedoverview';
                    $present[] = 'reportassessmentdiscussion';
                }
                if (in_array($role->shortname, ['teacher']) && in_array('reportgradingstudents', $lists)) {
                    $present[] = 'reportgradingstudents';
                }

                if (in_array($role->shortname, ['teacher']) && in_array('assignsubmissionlink', $lists)) {
                    $present[] = 'assignsubmissionlink';
                }

                if (in_array($role->shortname, ['teacher']) && in_array('assigngradelink', $lists)) {
                    $present[] = 'assigngradelink';
                }
            }

            // EC-330.
            require_once($CFG->dirroot.'/cohort/lib.php');
            $availabletocohort = get_config('community_sharecourse', 'availabletocohort');
            $flagcourse = cohort_is_member($availabletocohort, $USER->id) ? true : false;
            if(\community_oer\course_oer::funcs()::if_course_shared($COURSE->id) && $flagcourse && in_array('participants', $lists)) {
                $present[] = 'editsettings';
                $present[] = 'participants';
            }

            foreach ($PAGE->secondarynav->get_children_key_list() as $key) {
                if (!in_array($key, $present)) {
                    $PAGE->secondarynav->children->remove($key);
                }
            }
        }

        // PTL-9968.
        $flagpermission = false;
        foreach ($roles as $role) {
            if (in_array($role->shortname, ['teacher', 'student'])) {
                $flagpermission = true;
            }
        }

        if ($flagpermission) {
            foreach ($PAGE->secondarynav->get_children_key_list() as $key) {
                if ($key == 'competencies') {
                    $PAGE->secondarynav->children->remove($key);
                }
            }
        }

        // PTL-9609.
        // Exclude links from menu.
        if (!has_capability('moodle/site:config', \context_system::instance())) {
            $exclude = [
                    //'filtermanagement',
                    //'filtermanage',
                    'roleoverride',
                    'backup',
                    'restore',
                    'metadata',
                    //'questionbank',
                    'quiz_report',
            ];

            foreach ($exclude as $key) {
                if (in_array($key, $lists)) {
                    $PAGE->secondarynav->children->remove($key);
                }
            }
        }

        return true;
    }
}
