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
 * Display date setting report for a course
 *
 * @package   report_dateextend
 * @copyright 2024 Oshrat Luski <oshrat.luski@weizmann.ac.il>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(__FILE__) . '/../../config.php');
require_once(dirname(__FILE__) . '/lib.php');
require_once(dirname(__FILE__) . '/form.php');
require_once($CFG->dirroot . '/mod/quiz/accessrule/changebehaviour/rule.php');

// Receive and validate the course ID parameter
$id = required_param('id', PARAM_INT);
$course = $DB->get_record('course', ['id' => $id], '*', MUST_EXIST);

require_login($course);
require_capability('report/dateextend:view', context_course::instance($id));

// Prepare the page settings
$PAGE->set_url('/report/dateextend/index.php', ['id' => $id]);
$PAGE->set_context(context_course::instance($id));
$PAGE->set_title(get_string('pluginname', 'report_dateextend'));
$PAGE->set_heading($course->fullname);

// Create the form for the report
$mform = new report_dateextend_form($PAGE->url);

// Handle form submission or cancellation
if ($mform->is_cancelled()) {
    // Redirect to the course page if the form is cancelled
    redirect(new moodle_url('/course/view.php', ['id' => $id]));
} elseif ($data = $mform->get_data()) {
    // Process and save form data if submitted
    if (!empty($data->activities)) {
        report_dateextend_save_settings(array_keys($data->activities), [
            'behaviourtime' => $data->behaviourtime,
            'behaviourduration' => $data->behaviourduration,
            'newbehaviour' => $data->newbehaviour,
            'penalty' => $data->penalty,
        ], $course);
    }
    // Redirect with success message
    redirect($PAGE->url, get_string('changessaved', 'core'), null, \core\output\notification::NOTIFY_SUCCESS);
}

// JavaScript to adjust a text label and manage the "Select All" checkbox functionality
$removePrefix = get_string('remove_prefix', 'report_dateextend');
$PAGE->requires->js_init_code("
    document.addEventListener('DOMContentLoaded', function() {
        // Remove 'או' or 'or' from the behaviour label.
        const behaviourLabel = document.getElementById('id_behaviourduration_label');
        if (behaviourLabel) {
            const prefixToRemove = " . json_encode($removePrefix) . ";
            behaviourLabel.textContent = behaviourLabel.textContent.trim().replace(new RegExp('^' + prefixToRemove + '[:]?'), '');
        }

        // Handle 'Select All / None' checkbox functionality.
        const selectAllCheckbox = document.getElementById('id_selectall');
        const activityCheckboxes = document.querySelectorAll('.activity-checkbox');

        if (selectAllCheckbox && activityCheckboxes.length > 0) {
            selectAllCheckbox.addEventListener('change', function() {
                const isChecked = selectAllCheckbox.checked;
                activityCheckboxes.forEach(checkbox => {
                    checkbox.checked = isChecked;
                });
            });
        }
    });
");

// Display the report
echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('dateextend', 'report_dateextend'));
$mform->display();
echo $OUTPUT->footer();