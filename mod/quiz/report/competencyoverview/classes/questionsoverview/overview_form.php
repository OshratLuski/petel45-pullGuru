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
 * This file defines the setting form for the quiz questionsoverview report.
 *
 * @package   quiz_questionsoverview
 * @copyright   2020 Devlion <info@devlion.co>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Quiz questionsoverview report settings form.
 *
 * @copyright   2020 Devlion <info@devlion.co>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class quiz_questionsoverview_settings_form extends mod_quiz\local\reports\attempts_report_options_form {

    protected function other_attempt_fields(MoodleQuickForm $mform) {
        if (has_capability('mod/quiz:regrade', $this->_customdata['context'])) {
            $mform->addElement('advcheckbox', 'onlyregraded', get_string('reportshowonly', 'quiz'),
                get_string('optonlyregradedattempts', 'quiz_questionsoverview'));
            $mform->disabledIf('onlyregraded', 'attempts', 'eq', mod_quiz\local\reports\attempts_report::ENROLLED_WITHOUT);
        }
    }

    protected function other_preference_fields(MoodleQuickForm $mform) {
        if (quiz_has_grades($this->_customdata['quiz'])) {
            $mform->addElement('selectyesno', 'slotmarks',
                get_string('showdetailedmarks', 'quiz_questionsoverview'));
        } else {
            $mform->addElement('hidden', 'slotmarks', 0);
            $mform->setType('slotmarks', PARAM_INT);
        }
    }
}
