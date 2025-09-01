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
 * User sign-up form.
 *
 * @package    core
 * @subpackage auth
 * @copyright  1999 onwards Martin Dougiamas  http://dougiamas.com
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/formslib.php');
require_once($CFG->dirroot.'/user/profile/lib.php');
require_once($CFG->dirroot . '/user/editlib.php');
require_once('lib.php');

class login_signup_form extends moodleform implements renderable, templatable {

    // For IDnumber validity.
    const R_ELEGAL_INPUT = -1;
    const R_NOT_VALID = -2;
    const R_VALID = 1;

    function definition() {
        global $USER, $CFG, $DB;

        $mform = $this->_form;

        //$mform->addElement('header', 'createuserandpass', get_string('createuserandpass'), '');

        $mform->addElement('static', 'usernamerestrictions', '', get_string('usernamerestrictions', 'theme_petel'));

        $mform->addElement('text', 'username', get_string('username'), 'maxlength="100" size="12" autocapitalize="none"');
        $mform->setType('username', PARAM_RAW);
        $mform->addRule('username', get_string('missingusername'), 'required', null, 'client');

        if (!empty($CFG->passwordpolicy)){
            $mform->addElement('static', 'passwordpolicyinfo', '', print_password_policy());
        }
        $mform->addElement('passwordunmask', 'password', get_string('password'), [
            'maxlength' => MAX_PASSWORD_CHARACTERS,
            'size' => 12,
            'autocomplete' => 'new-password'
        ]);
        $mform->setType('password', core_user::get_property_type('password'));
        $mform->addRule('password', get_string('missingpassword'), 'required', null, 'client');
        $mform->addRule('password', get_string('maximumchars', '', MAX_PASSWORD_CHARACTERS),
            'maxlength', MAX_PASSWORD_CHARACTERS, 'client');

        //$mform->addElement('header', 'supplyinfo', get_string('supplyinfo'),'');

        $mform->addElement('static', 'mustgiveemailorphone', '', get_string('mustgiveemailorphone', 'theme_petel'));

        $mform->addElement('text', 'email', get_string('email'), 'maxlength="100" size="25"');
        $mform->setType('email', core_user::get_property_type('email'));
        $mform->setForceLtr('email');

        $mform->addElement('text', 'phone1', get_string('phone1'), 'maxlength="15" size="15"');
        $mform->setType('phone1', core_user::get_property_type('phone1'));
        $mform->setForceLtr('phone1');

        $namefields = useredit_get_required_name_fields();
        foreach ($namefields as $field) {
            $mform->addElement('text', $field, get_string($field), 'maxlength="100" size="30"');
            $mform->setType($field, core_user::get_property_type('firstname'));
            $stringid = 'missing' . $field;
            if (!get_string_manager()->string_exists($stringid, 'moodle')) {
                $stringid = 'required';
            }
            $mform->addRule($field, get_string($stringid), 'required', null, 'client');
        }

        $mform->addElement('text', 'idnumber', get_string('idnumber', 'theme_petel'), 'maxlength="15" size="15"');
        $mform->setType('idnumber', PARAM_ALPHANUM);
        $mform->addRule('idnumber', get_string('missingidnumber', 'theme_petel'), 'required', null, 'client');
        $mform->setForceLtr('idnumber');

        if (!empty($CFG->defaultcity)) {
            $mform->addElement('hidden', 'city', $CFG->defaultcity);
            $mform->setType('city', PARAM_TEXT);
            $mform->setDefault('city', $CFG->defaultcity);
        } else {
            $mform->addElement('hidden', 'city');
            $mform->setType('city', PARAM_TEXT);
        }

        if (!empty($CFG->country)) {
            $mform->addElement('hidden', 'country', $CFG->country);
            $mform->setType('country', PARAM_TEXT);
            $mform->setDefault('country', $CFG->country);
        } else {
            $mform->addElement('hidden', 'country');
            $mform->setType('country', PARAM_TEXT);
        }

        if (signup_captcha_enabled()) {
            $mform->addElement('recaptcha', 'recaptcha_element', get_string('security_question', 'auth'));
            $mform->addHelpButton('recaptcha_element', 'recaptcha', 'auth');
            $mform->closeHeaderBefore('recaptcha_element');
        }

        profile_signup_fields($mform);

        // Hook for plugins to extend form definition.
        core_login_extend_signup_form($mform);
        //PTL-9338
        $source = optional_param('source', '', PARAM_RAW);
        if ($source == 'saml2') {
            $key = optional_param('key', '', PARAM_RAW);
            if (empty($key) || !$DB->record_exists('enrol', ['password' => $key])) {
                redirect($CFG->wwwroot . '/auth/saml2/login.php?error=invalidkey');
            }

            $firstname = optional_param('firstname', '', PARAM_RAW);
            $mform->setDefault('firstname', $firstname);
            $lastname = optional_param('lastname', '', PARAM_RAW);
            $mform->setDefault('lastname', $lastname);
            $idnumber = optional_param('idnumber', '', PARAM_RAW);
            $mform->setDefault('idnumber', $idnumber);
            $username = optional_param('username', '', PARAM_RAW);
            $mform->setDefault('username', $username);
            $chars = substr(str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZ'), 0, 3);
            $chars .= substr(str_shuffle('abcdefghijklmnopqrstuvwxyz'), 0, 3);
            $chars .= substr(str_shuffle('0123456789'), 0, 2);
            $mform->setDefault('password', str_shuffle($chars));
            $moe_exidentifier = optional_param('profile_field_moe_exidentifier', '', PARAM_RAW);
            $mform->setDefault('profile_field_moe_exidentifier', $moe_exidentifier);
            $mform->addElement('html',  '<style>#id_profile_field_moe_exidentifier {pointer-events: none;} #id_signup_token{ pointer-events: none;}</style>');
        }

        // Add "Agree to sitepolicy" controls. By default it is a link to the policy text and a checkbox but
        // it can be implemented differently in custom sitepolicy handlers.
        // PTL-3658 Disable site policy agree to register users (usually: students)
        //$manager = new \core_privacy\local\sitepolicy\manager();
        //$manager->signup_form($mform);

        // buttons
        // $this->set_display_vertical();
        $this->add_action_buttons(true, get_string('createaccount'));

    }

    function definition_after_data(){
        $mform = $this->_form;
        $mform->applyFilter('username', 'trim');

        // Trim required name fields.
        foreach (useredit_get_required_name_fields() as $field) {
            $mform->applyFilter($field, 'trim');
        }
    }

    /**
     * Validate user supplied data on the signup form.
     *
     * @param array $data array of ("fieldname"=>value) of submitted data
     * @param array $files array of uploaded files "element_name"=>tmp_file_path
     * @return array of "element_name"=>"error_description" if there are errors,
     *         or an empty array if everything is OK (true allowed for backwards compatibility too).
     */
    public function validation($data, $files) {
        global $DB;

        $errors = parent::validation($data, $files);

        // Extend validation for any form extensions from plugins.
        $errors = array_merge($errors, core_login_validate_extend_signup_form($data));

        if (signup_captcha_enabled()) {
            $recaptchaelement = $this->_form->getElement('recaptcha_element');
            if (!empty($this->_form->_submitValues['g-recaptcha-response'])) {
                $response = $this->_form->_submitValues['g-recaptcha-response'];
                if (!$recaptchaelement->verify($response)) {
                    $errors['recaptcha_element'] = get_string('incorrectpleasetryagain', 'auth');
                }
            } else {
                $errors['recaptcha_element'] = get_string('missingrecaptchachallengefield');
            }
        }

        // Add username validations.
        // longer then 6 chars.
        if (strlen($data['username']) < 7) {
            $errors['username'] = get_string('longerusername', 'theme_petel');
        }
        // Not an Israeli IDNUMBER.
        if( self::R_VALID == $this->validate_israeli_idnumber($data['username'])) {
            $errors['username'] = get_string('noidnumberinusername', 'theme_petel');
        }

        // Check for duplicate/existing ID number.
        if ($DB->record_exists('user', array('idnumber' => $data['idnumber']))) {
            $errors['idnumber'] = get_string('idnumbernotvalid', 'theme_petel');
        }

        // Get parent language.
        $parentlang = get_parent_language(current_language());
        if(empty($parentlang)){
            $parentlang = current_language();
        }

        if(in_array($parentlang, array('he', 'ar'))) {
            if (self::R_VALID != $this->validate_israeli_idnumber($data['idnumber'])) {
                $errors['idnumber'] = get_string('idnumbernotvalid', 'theme_petel');
            }
        }

        $cleanphone = str_replace('-', '', $data['phone1']);
        if (empty($cleanphone)) {
            if (empty($data['email'])) {
                $errors['phone1'] = get_string('mustgiveemailorphone', 'theme_petel');
            }
        } else {
            if ($DB->record_exists('user', array('phone1' => $cleanphone))) {
                $errors['phone1'] = get_string('phone1exists', 'theme_petel');
            }
            if (!is_numeric($cleanphone)) {
                $errors['phone1'] = get_string('phonenotnumerical', 'theme_petel');
            }
        }

        switch ($parentlang) {
            case 'en':
                if (!preg_match("/^[a-zA-Z0-9$@$!%*?&#^-_. +]+$/", $data['lastname'])) {
                    $errors['lastname'] = get_string('onlyenglishletters', 'theme_petel');
                }
                if (!preg_match("/^[a-zA-Z0-9$@$!%*?&#^-_. +]+$/", $data['firstname'])) {
                    $errors['firstname'] = get_string('onlyenglishletters', 'theme_petel');
                }
                break;
            case 'he':
                if ( preg_match("/^[\p{Hebrew}-]+$/u", $data['lastname']) === 0) {
                    $errors['lastname'] = get_string('onlyhebrewletters', 'theme_petel');
                }
                if (!preg_match("/^[\p{Hebrew}-]+$/u", $data['firstname'])) {
                    $errors['firstname'] = get_string('onlyhebrewletters', 'theme_petel');
                }
                break;
            case 'ar':
                if (!preg_match("/^[\p{Arabic}-]+$/u", $data['lastname'])) {
                    $errors['lastname'] = get_string('onlyarabicletters', 'theme_petel');
                }
                if (!preg_match("/^[\p{Arabic}-]+$/u", $data['firstname'])) {
                    $errors['firstname'] = get_string('onlyarabicletters', 'theme_petel');
                }
                break;
        }

        $errors += signup_validate_data($data, $files);

        // Remove email2 from validation.
        if(isset($errors['email2'])){
            unset($errors['email2']);
        }
        // Remove email from validation, if phone1 is set.
        if(isset($cleanphone)){
            unset($errors['email']);
        }

        return $errors;
    }

    /**
     * Export this data so it can be used as the context for a mustache template.
     *
     * @param renderer_base $output Used to do a final render of any components that need to be rendered for export.
     * @return array
     */
    public function export_for_template(renderer_base $output) {
        ob_start();
        $this->display();
        $formhtml = ob_get_contents();
        ob_end_clean();
        $context = [
            'formhtml' => $formhtml
        ];
        return $context;
    }

    /*
       * Validate Israeli Teudat Zeut ID Number correctness
       *
       * Creadit to CodeOassis
       * http://opencodeoasis.blogspot.com/2008/08/blog-post_10.html
       */
    private function validate_israeli_idnumber($str) {
        //Convert to string, in case numeric input
        $IDnum = (string)$str;

        //validate correct input
        if (!ctype_digit($IDnum)) // is it all digits
            return self::R_ELEGAL_INPUT;
        if ((strlen($IDnum) > 9) || (strlen($IDnum) < 5))
            return self::R_ELEGAL_INPUT;

        //If the input length less then 9 and bigger then 5 add leading 0
        while (strlen($IDnum < 9)) {
            $IDnum = '0' . $IDnum;
        }

        $mone = 0;
        //Validate the ID number
        for ($i = 0; $i < 9; $i++) {
            $char = mb_substr($IDnum, $i, 1);
            $incNum = (int)$char;
            $incNum *= ($i % 2) + 1;
            if ($incNum > 9)
                $incNum -= 9;
            $mone += $incNum;
        }

        if ($mone % 10 == 0)
            return self::R_VALID;
        else
            return self::R_NOT_VALID;
    }

}
