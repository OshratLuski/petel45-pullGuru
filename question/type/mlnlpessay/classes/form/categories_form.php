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
class categories_form extends \moodleform {
    public function definition() {
        global $PAGE, $CFG;

        $mform = $this->_form;

        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);

        $mform->addElement('textarea', 'name', get_string('categoryname', 'qtype_mlnlpessay'));
        $mform->addRule('name', null, 'required', null, 'client');
        $mform->setType('name', PARAM_TEXT);

        $mform->addElement('textarea', 'description', get_string('descriptioncategory', 'qtype_mlnlpessay'));
        $mform->setType('description', PARAM_TEXT);

        $mform->addElement('text', 'modelid', get_string('modelid', 'qtype_mlnlpessay'), ['disabled' => true]);
        $mform->addRule('modelid', null, 'required', null, 'client');
        $mform->setType('modelid', PARAM_TEXT);

        $mform->addElement('text', 'model', get_string('modelname', 'qtype_mlnlpessay'), ['disabled' => true]);
        $mform->addRule('model', null, 'required', null, 'client');
        $mform->setType('model', PARAM_TEXT);

        $mform->addElement('text', 'tag', get_string('categorytag', 'qtype_mlnlpessay'), ['disabled' => true]);
        $mform->addRule('tag', null, 'required', null, 'client');
        $mform->setType('tag', PARAM_TEXT);

        $options = [0 => get_string('select', 'qtype_mlnlpessay')];
        foreach (\qtype_mlnlpessay\persistent\langs::get_records(['active' => 1]) as $persistent) {
            $options[$persistent->get('id')] = $persistent->get('name');
        }
        $mform->addElement('select', 'langid', get_string('langname', 'qtype_mlnlpessay'), $options);
        $mform->addRule('langid', null, 'required', null, 'client');
        $mform->setType('langid', PARAM_INT);

        foreach (['topic', 'subtopic'] as $field) {
            $classname = '\qtype_mlnlpessay\persistent\\' . $field . 's';
            $options = [0 => get_string('select', 'qtype_mlnlpessay')];
            foreach ($classname::get_records(['active' => 1]) as $persistent) {
                $options[$persistent->get('id')] = $persistent->get('name');
            }

            $mform->addElement('autocomplete', $field . 's', get_string($field . 'name', 'qtype_mlnlpessay'), $options, ['multiple' => true]);
            $mform->addRule($field . 's', null, 'required', null, 'client');
        }

        $mform->addElement('advcheckbox', 'active', get_string('active', 'qtype_mlnlpessay'));

        $this->add_action_buttons(false, get_string('submitbtn', 'qtype_mlnlpessay'));
    }
}