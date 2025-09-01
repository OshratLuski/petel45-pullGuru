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

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');

/**
 * Defines the form for the date extension report.
 *
 * This form allows users to configure time extensions for activities such as quizzes and assignments.
 * It includes options for behavior settings, penalty configuration, and activity selection.
 *
 * @package   report_dateextend
 * @copyright 2024 Oshrat Luski <oshrat.luski@weizmann.ac.il>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class report_dateextend_form extends moodleform {
    /**
     * Builds the structure of the form.
     *
     * This function defines the elements included in the form:
     * - Configuration options for behavior settings (e.g., time duration, penalties).
     * - Selection of specific activities (quizzes and assignments).
     * - A "Select All / None" checkbox for easier activity management.
     * - Action buttons for saving or canceling.
     *
     * @return void
     */
    public function definition() {
        $mform = $this->_form;

        $mform->addElement('html', '<div class="form-wrapper">');

        // Add a wrapper div with id="changebehaviour".
        $mform->addElement('html', '<div id="changebehaviour">');

        // Add hidden field for behaviourtime (default: 0).
        $mform->addElement('hidden', 'behaviourtime', 0);
        $mform->setType('behaviourtime', PARAM_INT);
        $mform->addElement('html', '<div class="behaviourtime_relative">');
        
        // Add behaviour duration field.
        $mform->addElement('duration', 'behaviourduration', get_string('behaviourtime_relative', 'quizaccess_changebehaviour'), [
            'optional' => true,
            'defaultunit' => DAYSECS
        ]);
        $mform->addHelpButton('behaviourduration', 'behaviourtime_relative', 'quizaccess_changebehaviour');
        $mform->addElement('html', '</div>');

        // Add behaviour dropdown.
        $mform->addElement('html', '<div class="behaviourtime_relative">');
        $behaviours = question_engine::get_behaviour_options(null);
        $mform->addElement('select', 'newbehaviour', get_string('newbehaviour', 'quizaccess_changebehaviour'), $behaviours);
        $mform->addElement('html', '</div>');

        // Add penalty field.
        $mform->addElement('html', '<div class="behaviourtime_relative">');
        $mform->addElement('text', 'penalty', get_string('penalty', 'quizaccess_changebehaviour'));
        $mform->setType('penalty', PARAM_INT);
        $mform->addHelpButton('penalty', 'penalty', 'quizaccess_changebehaviour');
        $mform->addElement('html', '</div>');

        // Close wrapper changebehaviour div.
        $mform->addElement('html', '</div>');

        $mform->addElement('html', '<div class="line-separation"></div>');

        
        // Fetch and display activities with checkboxes.
        global $course;
        $modinfo = get_fast_modinfo($course);
        $activities = $modinfo->get_cms();

        $mform->addElement('html', '<div class="chooseactivities-container">');

        // Instructions section
        $mform->addElement('html', '<div class="flex-item instructions">');
        $mform->addElement('html', '<label class="choose-activities-label">' . get_string('chooseactivities', 'report_dateextend') . '</label>');
        $mform->addElement('html', '<p>' . get_string('notequizclose', 'report_dateextend') . '</p>');
        $mform->addElement('html', '<p>' . get_string('notequizlink', 'report_dateextend') . '</p>');
        $mform->addElement('html', '</div>');

        // Quiz list section
        $mform->addElement('html', '<div class="flex-item quiz-list">');
        $mform->addElement('checkbox', 'selectall', '', '<span class="selectall-label">' . get_string('selectallnone', 'report_dateextend') . '</span>', ['class' => 'activity-checkbox']);

        global $DB;
        foreach ($activities as $cm) {
            if (!$cm->uservisible) {
                continue;
            }
            if ($cm->modname === 'quiz') {
                $quizdata = $DB->get_record('quiz', ['id' => $cm->instance], 'timeclose');
                if (!empty($quizdata->timeclose)) {
                    $timeclose = userdate($quizdata->timeclose);
                    $dateinfo = " (" . get_string('quizclose', 'report_dateextend') . ": $timeclose)";
                } else {
                    $dateinfo = " (" . get_string('noclose', 'report_dateextend') . ")";
                }
                $quizsettingsurl = new moodle_url('/course/modedit.php', ['update' => $cm->id]);
                $quizlabel = html_writer::link($quizsettingsurl, format_string($cm->name) . $dateinfo, ['target' => '_blank', 'class' => 'text-link']);
                $mform->addElement('checkbox', 'activities[' . $cm->id . ']', '', $quizlabel, ['class' => 'activity-checkbox']);
            }
        }
        $mform->addElement('html', '</div>'); // Closing the quiz list section

        $mform->addElement('html', '</div>'); // Closing the chooseactivities container

        // Add action buttons (Save/Cancel).
        $this->add_action_buttons(true, get_string('savechanges'));
        $mform->addElement('html', '</div>');

   }
}