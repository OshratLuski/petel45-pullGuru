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
 * Local plugin "OER catalog" - Library
 *
 * @package    community_oer
 * @copyright  2018 Devlion <info@devlion.co>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Allow plugins to provide some content to be rendered in the navbar.
 * The plugin must define a PLUGIN_render_navbar_output function that returns
 * the HTML they wish to add to the navbar.
 *
 * @return string HTML for the navbar
 */
function community_oer_render_navbar_output() {
    global $PAGE, $OUTPUT, $USER, $COURSE, $CFG;

    $output = '';

    // Not for forgot password.
    if ($_SERVER['REQUEST_URI'] !== '/login/forgot_password.php') {
        $menu = \community_oer\main_oer::structure_main_catalog();

        $data['user_access'] = \community_oer\main_oer::check_if_user_admin_or_teacher() && count($menu);
        $data['user_editing'] = $PAGE->user_is_editing() && is_siteadmin();
        $data['menu'] = $menu;

        $output .= $OUTPUT->render_from_template('community_oer/header', $data);
    }

    // Reviews.
    $popupdata = false;
    if (\community_oer\main_oer::get_oer_category() !== null) {
        $enablereviews = get_config('community_oer', 'enablereviews');
        if ($enablereviews == 1) {
            // Lookup for all activities for review in a course.
            if ($PAGE->pagetype === 'grade-report-grader-index' ||
                    substr($PAGE->pagetype, 0, strlen("course-view")) === "course-view") {
                $popupdata = \community_oer\reviews_oer::popup_engine($COURSE->id);
            } else if (in_array($PAGE->pagetype, ['mod-h5pactivity-view', 'mod-questionnaire-view', 'mod-assign-view',
                    'mod-quiz-view'])) { // Check if this activity is subject for review.
                $popupdata = \community_oer\reviews_oer::popup_engine($COURSE->id, 'activity');
            }

            if ($popupdata && $popupdata->allowed) {
                $popupdata->username = fullname($USER);
                $popupdata->reviewsquestiontext = get_config('community_oer', 'reviewsquestiontext');
                $popupdata->reviewstextarea = get_config('community_oer', 'reviewstextarea');
                // Show review request popup only one time a day after user logged in.
                $reviewspopupold = isset($_COOKIE['reviewspopup']) ? $_COOKIE['reviewspopup'] : 0;
                $reviewspopup = $reviewspopupold ? ((date("d") == date("d", $reviewspopupold)) ? false : true) : true;
                $popupdata->showrequestv2 = ($reviewspopup) ? true : false;
                if ($popupdata->showrequestv2) {
                    if (isset($popupdata->requestid)) {
                        \community_oer\reviews_oer::add_view_to_request($popupdata->requestid);
                        setcookie("reviewspopup", time(), time() + 86400, "/"); // Set marker for 1 day.
                    }
                }
                $stringman = get_string_manager();
                $strings = $stringman->load_component_strings('community_oer', 'he');
                $PAGE->requires->strings_for_js(array_keys($strings), 'community_oer');
            }
        }
    }

    // PTL-6366.
    if ($PAGE->pagetype == 'mod-quiz-attempt' && \community_oer\main_oer::if_activity_in_research_mode($PAGE->cm->id)) {
        $PAGE->requires->js_amd_inline('require(["jquery"], function($) { $(".editquestion").attr("style","display: none !important"); });');
    }

    // Simple bar TODO update
    $output .= '<script src="' . $CFG->wwwroot . '/local/community/plugins/oer/javascript/simplebar.js"></script>';
    $PAGE->requires->js_call_amd('community_oer/review', 'init', array($popupdata));

    // View single page.
    community_oer_render_info_single_page();

    // Add comment icon in view page of activity.
    community_oer_add_comment_icon_in_view_page();

    // Copy questions form oer catalog.
    if (has_capability('community/oer:questioncopy', \context_course::instance($COURSE->id), $USER->id)) {
        $context = \context_course::instance($COURSE->id);
        $PAGE->requires->js_call_amd('community_oer/popup-copy-questions', 'init', [$COURSE->id, $context->id]);
    }

    return $output;
}

/**
 * Allow plugins to provide some content to be rendered in the primarynav.
 * The plugin must define a PLUGIN_get_primarynav_output function that returns
 * the array with params for rendering output.
 *
 * @return array for primarynav navbar.
 */

function community_oer_get_primarynav_output() {
    global $PAGE, $USER;

    // Should only be available to teachers
    if (!\community_oer\main_oer::check_if_user_admin_or_teacher()) {
        return [];
    }

    return [
        'title' => get_string('oerrepository', 'community_oer'),
        'url' => 'javascript:void(0)',
        'text' => get_string('oerrepository', 'community_oer'),
        'icon' => '',
        'isactive' => $PAGE->pagetype === 'local-community-plugins-oer-index',
        'key' => 'oer',
        'classes' => ['oer-popup-btn'],
    ];
}

function community_oer_render_info_single_page() {
    global $DB, $PAGE;

    // Check permission.
    if (!\community_oer\main_oer::check_if_user_admin_or_teacher() || $PAGE->cm === null) {
        return false;
    }

    $pagedata = $PAGE->cm->get_course_module_record();
    $module = $DB->get_record('modules', array('id' => $pagedata->module));

    // Check mods.
    $disabledmods = array();
    if (!isset($module->name) || in_array($module->name, $disabledmods)) {
        return false;
    }

    if (!empty($pagedata->id) && !empty($pagedata->course)) {
        $cm = $DB->get_record('course_modules',
                array('instance' => $pagedata->instance, 'course' => $pagedata->course, 'module' => $pagedata->module));

        if (!empty($cm->id)) {

            // Oer activity.
            $activity = new \community_oer\activity_oer;
            if ($activity->single_cmid_render_data($cm->id, 'view')) {

                if (strpos($PAGE->url->get_path(), 'quiz/attempt.php') === false &&
                        strpos($PAGE->url->get_path(), 'questionnaire/preview.php') === false &&
                        strpos($PAGE->url->get_path(), 'mod/hvp/view.php') === false
                ) {
                    return false;
                }

                $PAGE->requires->js_call_amd('community_oer/activity', 'singlePage', [$cm->id]);
            } else {

                // Oer sequence.
                $sequence = new \community_oer\sequence_oer();
                if ($sequence->single_cmid_render_data($cm->id, 'view')) {
                    $PAGE->requires->js_call_amd('community_oer/sequence', 'singlePage', [$cm->id]);
                }
            }

        }
    }

    return true;
}

function community_oer_add_comment_icon_in_view_page() {
    global $DB, $PAGE;

    $pagemods = [];
    foreach (array_keys(get_module_types_names()) as $mod) {
        $pagemods[] = 'mod-' . $mod . '-view';
        $pagemods[] = 'mod-' . $mod . '-edit';
    }

    if (!in_array($PAGE->pagetype, $pagemods)) {
        return false;
    }

    // Structure oercatalog data.
    $cmid = $PAGE->context->instanceid;
    $mid = \local_metadata\mcontext::module()->get($cmid, 'ID');
    $relevantcmid = '';

    if (\community_oer\main_oer::check_if_user_admin_or_teacher()) {
        if (in_array($mid, \community_oer\main_oer::get_repository_mids())) {
            $relevantcmid = $mid;
        }
    }

    if (empty($relevantcmid)) {
        return false;
    }

    $reviews = $DB->get_records('community_oer_reviews', [
            'objid' => $relevantcmid,
            'reviewtype' => 'activity',
    ]);
    $countcomments = count($reviews);

    $result = '
        <a href="javascript:void(0)" data-objid="' . $relevantcmid . '" data-type="activity" data-handler="showReview" class="comment-icon"
            title="' . get_string('teachersdiscourse', 'local_quizpreset', $countcomments) . '">
            <span>(' . $countcomments . ')</span> <i class="fal fa-comment" aria-hidden="true"></i>
        </a>
    ';

    if (isset($_COOKIE['openpopupreview']) && $_COOKIE['openpopupreview'] === 1) {
        $PAGE->requires->js_call_amd('community_oer/review', 'add_and_open_icon_on_page', array($result));
    } else {
        $PAGE->requires->js_call_amd('community_oer/review', 'add_icon_to_page', array($result));
    }

    if (isset($_COOKIE['openpopupreview'])) {
        unset($_COOKIE['openpopupreview']);
        setcookie('openpopupreview', '', time() - 3600, '/');
    }

    return true;
}

function community_oer_output_fragment_copy_questions_from_catalog($args) {
    global $OUTPUT, $PAGE;

    $args = (object) $args;

    $tabs = \community_oer\main_oer::get_tabs_by_user();
    $default = $tabs[1];

    $type = 'category';

    $activity = new \community_oer\activity_oer;
    $structure = $activity->structure_activity_catalog();

    $elementid = $structure[0]['cat_id'];

    $PAGE->requires->js_call_amd('community_oer/popup-main', 'init', [$default, $type, intval($elementid)]);

    $data = \community_oer\main_oer::get_main_menu($default);
    $data['cmid'] = $args->cmid;
    $data['uniqueid'] = $args->uniqueid;

    return $OUTPUT->render_from_template('community_oer/main-popup', $data);
}
