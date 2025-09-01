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
 * Class demo_captcha_v2
 *
 * @copyright  2022 Devlion Ltd <info@devlion.co>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class demo_captcha_v2 extends \moodleform {

    /**
     * Define the form.
     */
    public function definition() {
        global $CFG;

        $mform = $this->_form;

        $mform->addElement('header', 'democaptchaheader', get_string('democaptchaheader', 'local_petel'));

        $mform->addElement('hidden', 'key', $this->_customdata['key']);
        $mform->setType('key', PARAM_ALPHANUMEXT);

        $mform->addElement('hidden', 'cmid', $this->_customdata['cmid']);
        $mform->setType('cmid', PARAM_INT);

        $demosessiontimeout = isset($CFG->demosessiontimeout) ? $CFG->demosessiontimeout : 2;
        $mform->addElement('html', '
                <div class="alert">
                ' . get_string('democaptchadesc', 'local_petel', $demosessiontimeout) . '
                </div>
            ');

        $mform->addElement('recaptcha', 'democaptcha');
        $mform->addHelpButton('democaptcha', 'recaptcha', 'auth');
        $mform->closeHeaderBefore('democaptcha');

        $this->add_action_buttons(false, get_string('demosubmitlabel', 'local_petel'));
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

        $recaptchaelement = $this->_form->getElement('democaptcha');

        if (!empty($this->_form->_submitValues['g-recaptcha-response'])) {
            $response = $this->_form->_submitValues['g-recaptcha-response'];
            if (!$recaptchaelement->verify($response)) {
                $errors['democaptcha'] = get_string('incorrectpleasetryagain', 'auth');
            }
        } else {
            $errors['democaptcha'] = get_string('missingrecaptchachallengefield');
        }

        return $errors;
    }
}


