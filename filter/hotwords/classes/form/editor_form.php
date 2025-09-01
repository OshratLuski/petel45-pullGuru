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
 * @package   filter_hotwords
 * @category  form
 * @copyright 2018 The Open University - based on question/preview.php
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace filter_hotwords\form;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->libdir . '/formslib.php');

/**
 * Form to let users edit all the options for embedding a question.
 *
 * @copyright 2018 The Open University - based on question/preview.php
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class editor_form extends \moodleform {
    public function definition() {
        global $PAGE, $CFG;
        require_once($CFG->dirroot . '/repository/lib.php');

        $mform = $this->_form;
        // The default form id ('mform1') is also highly likely to be the same as the
        // id of the form in the background when we are shown in an atto editor pop-up.
        // Therefore, set something different.
        $mform->updateAttributes(['id' => 'embedhotwordsform']);

        /** @var \context $context */
        $context = $this->_customdata['context'];

        $mform->addElement('hidden', 'courseid', $context->instanceid);
        $mform->setType('courseid', PARAM_INT);

        $mform->addElement('text', 'urltext', get_string('urltext', 'filter_hotwords'), 'class="atto_hotwords_urltext"');
        $mform->addRule('urltext', null, 'required', null, 'client');
        $editoroptions = array(
            'autosave' => false,
            'maxfiles' => EDITOR_UNLIMITED_FILES,
            'maxbytes' => 0,
            'context' => $context,
            'noclean' => false,
            'return_types' => (FILE_INTERNAL | FILE_EXTERNAL | FILE_CONTROLLED_LINK),
        );

        $mform->addElement('editor', 'content', get_string('content', 'filter_hotwords'), [], $editoroptions);
        $mform->addRule('content', null, 'required', null, 'client');

        $this->add_action_buttons(false, get_string('submitbtn', 'filter_hotwords'));
        $mform->disabledIf('submitbutton', 'courseid', 'eq', '');
    }
}
