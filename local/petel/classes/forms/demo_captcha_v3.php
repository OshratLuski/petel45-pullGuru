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
 * Class demo_captcha _v3
 *
 * @copyright  2022 Devlion Ltd <info@devlion.co>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class demo_captcha_v3 extends \moodleform {

    /**
     * Define the form.
     */
    public function definition() {
        global $PAGE;

        $mform = $this->_form;

        $recaptchaSiteKey = $this->_customdata['recaptchasitekey'];
        $recaptchaUrl = $this->_customdata['recaptchaurl'];

        $PAGE->requires->js(new \moodle_url($recaptchaUrl.'.js?render=' . $recaptchaSiteKey), true);

        $PAGE->requires->js_amd_inline(<<<EOD
                                    require(["jquery"], function($) {
                                        grecaptcha.ready(function() {
                                            document.getElementById('id_submitbutton').addEventListener('click', function(e) {
                                                e.preventDefault();
                                                grecaptcha.execute('$recaptchaSiteKey', {action: 'login'}).then(function(token) {                                            
                                                    $('input[name=token]').val(token);                                                
                                                    $('form').submit();
                                                });
                                            });
                                        });
                                    });
                                    EOD);

        $mform->addElement('header', 'democaptchaheader', get_string('democaptchaheader', 'local_petel'));

        $mform->addElement('hidden', 'token', '');
        $mform->setType('token', PARAM_ALPHANUMEXT);

        $mform->addElement('hidden', 'key', $this->_customdata['key']);
        $mform->setType('key', PARAM_ALPHANUMEXT);

        $mform->addElement('hidden', 'cmid', $this->_customdata['cmid']);
        $mform->setType('cmid', PARAM_INT);

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

        return $errors;
    }
}


