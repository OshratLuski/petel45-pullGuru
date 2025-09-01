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
 * Forgot password page.
 *
 * @package    core
 * @subpackage auth
 * @copyright  2006 Petr Skoda {@link http://skodak.org}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/formslib.php');
require_once($CFG->dirroot.'/user/lib.php');
require_once('lib.php');

/**
 * Reset forgotten password form definition.
 *
 * @package    core
 * @subpackage auth
 * @copyright  2006 Petr Skoda {@link http://skodak.org}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class login_forgot_password_form extends moodleform {

    /**
     * Define the forgot password form.
     */
    function definition() {
        global $USER, $CFG;

        $mform    = $this->_form;
        $mform->setDisableShortforms(true);

        // Hook for plugins to extend form definition.
        core_login_extend_forgot_password_form($mform);

        $mform->addElement('header', 'searchbyusername', get_string('searchbyusername'), '');

        $purpose = user_edit_map_field_purpose($USER->id, 'username');
        $mform->addElement('text', 'username', get_string('username'), 'size="20"' . $purpose);
        $mform->setType('username', PARAM_RAW);

        $submitlabel = get_string('search');
        $mform->addElement('submit', 'submitbuttonusername', $submitlabel);

        $mform->addElement('header', 'searchbyemail', get_string('searchbyemail'), '');

        $purpose = user_edit_map_field_purpose($USER->id, 'email');
        $mform->addElement('text', 'email', get_string('email'), 'maxlength="100" size="30"' . $purpose);
        $mform->setType('email', PARAM_RAW_TRIMMED);

        $submitlabel = get_string('search');
        $mform->addElement('submit', 'submitbuttonemail', $submitlabel);

        if(isset($CFG->smsapicode ) && !empty($CFG->smsapicode )) {
            $mform->addElement('header', 'searchbyphone', get_string('searchbyphone', 'theme_petel'), '');

            $mform->addElement('text', 'phone', get_string('phone'));
            $mform->setType('phone', PARAM_RAW_TRIMMED);

            $submitlabel = get_string('search');
            $mform->addElement('submit', 'submitbuttonemail', $submitlabel);
        }

        $mform->addElement('hidden', 'recaptcha_response', '');
        $mform->setType('recaptcha_response', PARAM_RAW);

    }

    /**
     * Validate user input from the forgot password form.
     * @param array $data array of submitted form fields.
     * @param array $files submitted with the form.
     * @return array errors occuring during validation.
     */
    function validation($data, $files) {
        global $CFG;

        $errors = parent::validation($data, $files);
        if (isset($CFG->recaptchav3enable) && $CFG->recaptchav3enable && isset($CFG->recaptchav3url) && isset($CFG->recaptchav3privatekey)) {
            if (!empty($data['recaptcha_response'])) {
                $recaptcha_url = $CFG->recaptchav3url . '/siteverify';
                $recaptcha_secret = $CFG->recaptchav3privatekey;
                $recaptcha_response = $data['recaptcha_response'];
                $response = file_get_contents($recaptcha_url . '?secret=' . $recaptcha_secret . '&response=' . $recaptcha_response);
                $responseKeys = json_decode($response, true);

                $recaptchascore = isset($CFG->recaptchav3score) && !empty($CFG->recaptchav3score) ? $CFG->recaptchav3score : 0.5;

                if (!$responseKeys["success"] || $responseKeys["score"] < $recaptchascore) {
                    $errors['recaptcha_response'] = get_string('configrecaptchav3failed', 'local_petel');
                }
            } else {
                $errors['recaptcha_response'] = get_string('configrecaptchav3failed', 'local_petel');
            }
        }

        // Extend validation for any form extensions from plugins.
        $errors = array_merge($errors, core_login_validate_extend_forgot_password_form($data));

        $errors += core_login_validate_forgot_password_data($data);

        return $errors;
    }

}
