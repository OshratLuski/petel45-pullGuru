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
 * Local plugin "notifycohorts" form to send notification to members of cohorts
 *
 * @package   local_notifycohorts
 * @copyright 2020 Daniel Neis Araujo <daniel@adapta.online>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_notifycohorts;

defined('MOODLE_INTERNAL') || die();
require_once($CFG->libdir.'/formslib.php');

/**
 * Class notification_form
 * @package   local_notifycohorts
 * @copyright 2020 Daniel Neis Araujo <daniel@adapta.online>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class notification_form extends \moodleform {

    /**
     * Form definition.
     */
    protected function definition() {
        $mform = $this->_form;
        $availablecohorts = $this->_customdata['availablecohorts'];

        if (!$availablecohorts) {
            $mform->addElement('html', \html_writer::tag('div', get_string('nocohorts', 'local_notifycohorts'),
                                                         array('class' => 'alert alert-warning')));
        } else {

            $mform->addElement('text', 'subject', get_string('subject', 'local_notifycohorts'));
            $mform->setType('subject', PARAM_TEXT);
            $mform->addRule('subject', get_string('subjectrequired', 'local_notifycohorts'), 'required', null, 'client');

            $mform->addElement('editor', 'body', get_string('body', 'local_notifycohorts'));
            $mform->addRule('body', get_string('bodyrequired', 'local_notifycohorts'), 'required', null, 'client');

            $options = [];
            foreach ($availablecohorts as $cohort) {
                $options[] = $mform->createElement('advcheckbox', $cohort->id, null, format_string($cohort->name));
            }
            $mform->addGroup($options, 'cohort', get_string('cohorts', 'local_notifycohorts'));
        }

        $this->add_action_buttons();
    }

    /**
     * Process the form for sending notifications.
     */
    public function process() {
        global $DB;

        $url = new \moodle_url('/local/notifycohorts/index.php');
        if ($this->is_cancelled()) {
            redirect($url);
        }
        if ($data = $this->get_data()) {
            $admin = get_admin();
            foreach ($data->cohort as $cohortid => $selected) {
                if ($selected) {
                    $members = $DB->get_records('cohort_members', ['cohortid' => $cohortid]);
                    foreach ($members as $member) {
                        $eventdata = new \core\message\message();
                        $eventdata->component         = 'local_notifycohorts';
                        $eventdata->name              = 'sendnotifications';
                        $eventdata->userfrom          = $admin;
                        $eventdata->userto            = $member->userid;
                        $eventdata->subject           = $data->subject;
                        $eventdata->fullmessage       = $data->body['text'];
                        $eventdata->fullmessageformat = FORMAT_MOODLE;
                        $eventdata->fullmessagehtml   = $data->body['text'];
                        $eventdata->smallmessage      = '';
                        $eventdata->notification      = true;
                        message_send($eventdata);
                    }
                }
            }
            redirect($url, get_string('notificationssent', 'local_notifycohorts'));
        }
    }
}
