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


/**
 * Reset forgotten password form definition.
 *
 * @package    core
 * @subpackage auth
 * @copyright  2006 Petr Skoda {@link http://skodak.org}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class sms_validation_form extends moodleform {

    /**
     * Define the forgot password form.
     */

    function definition() {
        $mform    = $this->_form;
        $mform->setDisableShortforms(true);

        // Execute counter for captcha.
        $this->sms_validation_execute_counter();

        $mform->addElement('header', 'smsvalidation', get_string('smsvalidation', 'theme_petel'), '');

        $mform->addElement('text', 'code', get_string('varificationcode', 'theme_petel'));
        $mform->setType('code', PARAM_RAW);

        // Recaptcha.
        if ($this->sms_validation_captcha_enabled()) {
            $mform->addElement('recaptcha', 'recaptcha_sms', '');
        }

        $submitlabel = get_string('sendcode', 'theme_petel');
        $mform->addElement('submit', 'submitbuttonsms', $submitlabel);
    }

    /**
     * Validate user input from the forgot password form.
     * @param array $data array of submitted form fields.
     * @param array $files submitted with the form.
     * @return array errors occuring during validation.
     */
    function validation($data, $files) {
        global $CFG, $DB;

        $errors = parent::validation($data, $files);

        if ($this->sms_validation_captcha_enabled()) {
            $recaptchaelement = $this->_form->getElement('recaptcha_sms');
            if (!empty($this->_form->_submitValues['g-recaptcha-response'])) {
                $response = $this->_form->_submitValues['g-recaptcha-response'];
                if (!$recaptchaelement->verify($response)) {
                    $errors['recaptcha_sms'] = get_string('incorrectpleasetryagain', 'auth');
                }
            } else {
                $errors['recaptcha_sms'] = get_string('missingrecaptchachallengefield');
            }
        }

        if(empty($data['code'])){
            $errors['code'] = get_string('emptycodesms', 'theme_petel');
        }else{
            $result = $DB->get_record('user_password_resets', array('token' => md5($data['code'])));
            if(!$result){
                $errors['code'] = get_string('wrongcodesms', 'theme_petel');
            }
        }

        return $errors;
    }

    function sms_validation_captcha_enabled() {
        global $CFG;
        return !empty($CFG->recaptchapublickey) && !empty($CFG->recaptchaprivatekey) && get_config('auth_enrolkey', 'recaptcha');
    }

    function sms_validation_execute_counter() {
        global $CFG;

        $ifpresent = false;
        foreach(\core_component::get_plugin_list('local') as $name=>$path){
            if($name == 'petel') $ifpresent = true;
        }

        if($ifpresent && \local_petel\funcs::execute_counter()){
            return true;
        }

        return false;
    }

    function sms_validation_view_captcha_for_user() {
        global $CFG;

        $ifpresent = false;
        foreach(\core_component::get_plugin_list('local') as $name=>$path){
            if($name == 'petel') $ifpresent = true;
        }

        if($ifpresent && \local_petel\funcs::enable_captcha()){
            return true;
        }

        return false;
    }

}
