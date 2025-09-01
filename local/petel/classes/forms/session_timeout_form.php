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
 * This file contains the profile field category form.
 *
 * @package local_petel
 * @copyright  2007 onwards Shane Elliot {@link http://pukunui.com}
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_petel\forms;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/lib/formslib.php');

/**
 * Class session_timeout_form
 *
 * @copyright  2007 onwards Shane Elliot {@link http://pukunui.com}
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class session_timeout_form extends \moodleform {

    /**
     * Define the form.
     */
    public function definition() {
        $mform = $this->_form;

        $mform->addElement('hidden', 'userid');
        $mform->setType('userid', PARAM_INT);

        $choices = [
                1 => get_string('twohours', 'local_petel'),
                2 => get_string('day'),
                3 => get_string('week'),
                4 => get_string('month')
        ];

        $mform->addElement('static', 'timeout_warning', '',
                \html_writer::div(get_string('sessiontimeoutwarning', 'local_petel'), 'alert alert-danger'));
        $mform->addElement('select', 'timeout', get_string('selectsessiontimeout', 'local_petel'), $choices);
        $mform->setType('timeout', PARAM_INT);

        $this->add_action_buttons(true);
    }

    /**
     * Perform some moodle validation.
     *
     * @param array $data
     * @param array $files
     * @return array
     */
    public function validation($data, $files) {
        global $DB;
        $errors = parent::validation($data, $files);

        $data = (object) $data;

        return $errors;
    }
}


