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
 * This file defines the setting form for the quiz overview report.
 *
 * @package   quiz_advancedoverview
 * @copyright 2008 Jamie Pratt
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author    Devlion Moodle Development <service@devlion.co>
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/quiz/report/attemptsreport_form.php');

/**
 * Quiz overview report settings form.
 *
 * @copyright 2008 Jamie Pratt
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class quiz_advancedoverview_settings_form extends mod_quiz\local\reports\attempts_report_options_form {

    protected function definition() {
        $mform = $this->_form;

        $mform->addElement('header', 'preferencespage',
                get_string('reportwhattoinclude', 'quiz'));

        $this->standard_attempt_fields($mform);
        $this->other_attempt_fields($mform);

        $mform->addElement('submit', 'submitbutton',
                get_string('showreport', 'quiz'));
    }

    protected function other_attempt_fields(MoodleQuickForm $mform) {
        if (has_capability('mod/quiz:regrade', $this->_customdata['context'])) {
            $mform->addElement('advcheckbox', 'onlyregraded', get_string('reportshowonly', 'quiz'),
                    get_string('optonlyregradedattempts', 'quiz_advancedoverview'));
            $mform->disabledIf('onlyregraded', 'attempts', 'eq', mod_quiz\local\reports\attempts_report::ENROLLED_WITHOUT);
        }
    }

    protected function other_preference_fields(MoodleQuickForm $mform) {
        if (quiz_has_grades($this->_customdata['quiz'])) {
            $mform->addElement('selectyesno', 'slotmarks',
                    get_string('showdetailedmarks', 'quiz_advancedoverview'));
        } else {
            $mform->addElement('hidden', 'slotmarks', 0);
            $mform->setType('slotmarks', PARAM_INT);
        }

        $mform->setExpanded('preferencespage', false);
        $mform->setExpanded('preferencesuser', false);

    }

}
