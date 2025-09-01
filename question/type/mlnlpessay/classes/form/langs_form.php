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
 * Form to let users edit all the options for embedding a question.
 *
 * @package   qtype_mlnlpessay
 * @category  form
 * @copyright 2018 The Open University - based on question/preview.php
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace qtype_mlnlpessay\form;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->libdir . '/formslib.php');

/**
 * Form to let users edit all the options for embedding a question.
 *
 * @copyright 2018 The Open University - based on question/preview.php
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class langs_form extends \moodleform {
    public function definition() {
        global $PAGE, $CFG;

        $mform = $this->_form;

        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);

        $mform->addElement('select', 'code', get_string('langcode', 'qtype_mlnlpessay'), array_intersect_key(get_string_manager()->get_list_of_languages(), array_flip(['en', 'he','ar'])));
        $mform->addRule('code', null, 'required', null, 'client');
        $mform->setType('code', PARAM_LANG);

        $mform->addElement('text', 'name', get_string('langname', 'qtype_mlnlpessay'));
        $mform->addRule('name', null, 'required', null, 'client');
        $mform->setType('name', PARAM_TEXT);

        $mform->addElement('advcheckbox', 'active', get_string('active', 'qtype_mlnlpessay'));

        $this->add_action_buttons(false, get_string('submitbtn', 'qtype_mlnlpessay'));
    }
}
