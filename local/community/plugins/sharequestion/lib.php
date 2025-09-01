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
 * @package    community_sharequestion
 * @copyright   2019 Devlion <info@devlion.co>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Allow plugins to provide some content to be rendered in the navbar.
 * The plugin must define a PLUGIN_render_navbar_output function that returns
 * the HTML they wish to add to the navbar.
 *
 * @return string HTML for the navbar
 */
function community_sharequestion_render_navbar_output() {
    global $PAGE, $COURSE, $USER, $CFG;

    $context = \context_course::instance($COURSE->id);
    // Check if user is member of cohort that allows to share questions to the OER question catalog.
    $availabletocohort = get_config('community_sharequestion', 'availabletocohort');
    require_once($CFG->dirroot . '/cohort/lib.php');
    if (cohort_is_member($availabletocohort, $USER->id)) {
        $visiblebuttons = 'all';
    } else {
        $visiblebuttons = 'all-nooer';
    }

    switch ($PAGE->pagetype) {
        case 'question-edit':
            if (has_capability('community/sharequestion:questioncopy', \context_course::instance($COURSE->id), $USER->id)) {
                $PAGE->requires->js_call_amd('community_sharequestion/main', 'question_edit_init',
                        [$COURSE->id, $context->id, $visiblebuttons]);
            }
            break;
        case 'mod-quiz-edit':
            $PAGE->requires->js_call_amd('community_sharequestion/main', 'mod_quiz_edit_init',
                    [$COURSE->id, $context->id, $visiblebuttons]);
            break;
    }

    if (has_capability('community/sharequestion:questioncopy', \context_course::instance($COURSE->id), $USER->id)) {
        $PAGE->requires->js_call_amd('community_sharequestion/main', 'message_edit_init', [$COURSE->id, $context->id]);
        $PAGE->requires->js_call_amd('community_sharequestion/copyFromMyCourses', 'init', [$COURSE->id, $context->id]);
    }

    return '';
}

function community_sharequestion_output_fragment_upload_questions_maagar($args) {
    global $CFG, $OUTPUT;

    require_once($CFG->dirroot . '/local/community/plugins/sharequestion/upload_to_catalog.php');

    $args = (object) $args;
    $context = $args->context;

    $formdata = [];
    if (!empty($args->jsonformdata)) {
        $serialiseddata = json_decode($args->jsonformdata);
        parse_str($serialiseddata, $formdata);
    }

    // Check if question present in oercatalog.
    $flagoercatalogpresent = false;
    $question = new \community_oer\question_oer;
    foreach (json_decode($args->selected_questions) as $selectedqid) {

        $parentqid = \local_metadata\mcontext::question()->get($selectedqid, 'qid');

        $data = $question->query()->compare('qid', $parentqid)->get();
        if (!empty($data)) {
            $flagoercatalogpresent = true;
        }
    }

    // Upload form.
    $uploadmform = new upload_to_catalog(null, ['courseid' => $args->courseid, 'selected_questions' => $args->selected_questions],
            'post', '', null, true, $formdata);

    $uploadhtml = '';
    ob_start();
    $uploadmform->display();
    $uploadhtml .= ob_get_contents();
    ob_end_clean();

    $uploadhtml = str_replace('col-md-3', '', $uploadhtml);
    $uploadhtml = str_replace('col-md-9', 'col-md-12', $uploadhtml);
    $uploadhtml = str_replace('</form>', '', $uploadhtml);

    // Number of sections.
    $numberofsections = get_config('community_sharequestion', 'numberofsections');
    $numberofsections = !empty($numberofsections) ? $numberofsections : 1;

    $data = array(
            'uploadhtml' => $uploadhtml,
            'uniqueid' => time(),
            'selected_questions' => $args->selected_questions,
            'courseid' => $args->courseid,
            'number_sections' => $numberofsections,
            'warning_qid_present' => $flagoercatalogpresent
    );

    return $OUTPUT->render_from_template('community_sharequestion/upload_to_catalog', $data);
}

function community_sharequestion_output_fragment_copy_questions_from_my_courses($args) {
    global $OUTPUT;

    $args = (object) $args;

    $data = array(
            'cmid' => $args->cmid,
            'uniqueid' => $args->uniqueid,
    );

    return $OUTPUT->render_from_template('community_sharequestion/copy_from_my_courses/main', $data);
}
