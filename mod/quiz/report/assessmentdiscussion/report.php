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
 * Plugin capabilities are defined here.
 *
 * @package     quiz_assessmentdiscussion
 * @category    access
 * @copyright   2022 Devlion <info@devlion.co>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Quiz report subclass for the overview (grades) report.
 *
 * @copyright 1999 onwards Martin Dougiamas and others {@link http://moodle.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class quiz_assessmentdiscussion_report extends mod_quiz\local\reports\attempts_report {

    public $hasgroupstudents;

    public function display($quiz, $cm, $course) {
        global $OUTPUT, $PAGE;

        if (!\quiz_assessmentdiscussion\assessmentdiscussion::check_access()) {
            throw new moodle_exception('nopermissions', 'error', '', get_string('pluginname', 'quiz_assessmentdiscussion'));
        }

        // TODO Iframe or html.
        $iframeenable = optional_param('iframe', 0, PARAM_BOOL);
        set_user_preference('quiz_assessmentdiscussion_iframe', $iframeenable);

        // Print the page header.
        $PAGE->set_title($quiz->name);
        $PAGE->set_heading($course->fullname);
        echo $OUTPUT->header();

        // Purge all caches.
        $assessmentcache = new \quiz_assessmentdiscussion\assessmentcache($cm->id);
        $assessmentcache->all_attempts()->purge();
        $assessmentcache->user_attempts_grade()->purge();
        $assessmentcache->questions()->purge();

        // Default groupid.
        $groupid = 0;
        $assessmentdiscussion = new \quiz_assessmentdiscussion\assessmentdiscussion($cm->id, $groupid);

        // Default.
        $splitterwidth = 75;
        $anonymousmode = $assessmentdiscussion->get_anon_state_for_user();
        $dashboardtabid = $assessmentdiscussion->get_default_dasboard_tab();
        $sortdashboard = $assessmentdiscussion->get_default_dasboard_sort();

        // Single question.
        $singleqid = optional_param('qid', 0, PARAM_INT);

        if ($singleqid) {
            $dashboardtabid = $assessmentdiscussion::tab_all;
            $data = $assessmentdiscussion->prepare_data_for_dashboard_template($dashboardtabid, $anonymousmode, $sortdashboard, $singleqid);
        } else {
            $data = $assessmentdiscussion->prepare_data_for_dashboard_template($dashboardtabid, $anonymousmode, $sortdashboard);
        }

        $preset = [
                'cmid' => $cm->id,
                'groupid' => $assessmentdiscussion->get_groupid(),
                'dashboard_tab' => $dashboardtabid,
                'anonymous_mode' => $anonymousmode,
                'sort_question' => $sortdashboard,
                'splitter_width' => $splitterwidth,
                'dashboard_tab_discussion_value' => $assessmentdiscussion::tab_discussion,
                'qid' => $data['activeqid'],
                'answers_tab_default' => $assessmentdiscussion->get_default_answers_tab(),
                'sort_answers_default' => $assessmentdiscussion->get_default_answers_sort(),
                'answer_tab_discussion_value' => $assessmentdiscussion::tab_discussion,
                'overlay_qid' => 0,
                'overlay_tab' => $assessmentdiscussion::tab_discussion,
                'overlay_view_list' => 1,
                'overlay_show_answers' => 0,
        ];

        $data = array_merge($data, $preset);

        echo $OUTPUT->render_from_template('quiz_assessmentdiscussion/main', $data);

        $PAGE->requires->js_call_amd('quiz_assessmentdiscussion/main', 'init', [json_encode($preset)]);
        $PAGE->requires->js_call_amd('quiz_assessmentdiscussion/overlay', 'init', [json_encode($preset)]);

        return true;
    }

}
