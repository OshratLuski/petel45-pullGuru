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
 * Redirect to question preview.
 *
 * @package    local_diagnostic
 * @copyright  2024 Devlion Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


require_once(__DIR__ . '/../../../../config.php');
require_once("$CFG->libdir/formslib.php");

class midform extends moodleform
{
    // Add elements to form.
    public function definition()
    {
        // A reference to the form is stored in $this->form.
        // A common convention is to store it in a variable, such as `$mform`.
        $mform = $this->_form; // Don't forget the underscore!

        $custommid = required_param('mid', PARAM_TEXT);
        $config = get_config('local_diagnostic');
        // Add elements to your form.
        $mform->addElement('html', '<h3>' . get_string('customactivitytext', 'local_diagnostic', $custommid) . '</h3>');
        $mform->addElement('advcheckbox', 'local_diagnostic/modelenabled' . $custommid, get_string('modelenabled', 'local_diagnostic', $custommid));

        $croncustommids = explode(',', $config->croncustommids);
        if (!(in_array($custommid, $croncustommids))) {
            $mform->setDefault('local_diagnostic/modelenabled' . $custommid, 0);
        } else {
            $mform->setDefault('local_diagnostic/modelenabled' . $custommid, 1);
        }

        $mform->setType('local_diagnostic/modelenabled' . $custommid, PARAM_INT);

        $mform->addElement('text', 'local_diagnostic/activityclusternum_' . $custommid, get_string('activityclusternum', 'local_diagnostic', $custommid));
        $mform->setDefault('local_diagnostic/activityclusternum_' . $custommid, $config->{'activityclusternum_' . $custommid});
        $mform->setType('local_diagnostic/activityclusternum_' . $custommid, PARAM_INT);

        $activityclusternum = get_config('local_diagnostic', 'activityclusternum_' . $custommid);
        if (!empty($activityclusternum)) {
            for ($j = 1; $j <= $activityclusternum; ++$j) {
                $mform->addElement('textarea', 'local_diagnostic/cluster' . $j . 'descriptionactivity' . $custommid, get_string('cluster_text_area', 'local_diagnostic', $j));
                $mform->setDefault('local_diagnostic/cluster' . $j . 'descriptionactivity' . $custommid, $config->{'cluster' . $j . 'descriptionactivity' . $custommid});
                $mform->setType('local_diagnostic/cluster' . $j . 'descriptionactivity' . $custommid, PARAM_TEXT);
                $mform->addElement('static', 'local_diagnostic/cluster_text_area_desc', '', get_string('cluster_text_area_desc', 'local_diagnostic', $j));
            }
        }

        $mform->addElement('text', 'local_diagnostic/activity' . $custommid . 'yellow', get_string('activity_yellow', 'local_diagnostic', $custommid));
        $mform->setDefault('local_diagnostic/activity' . $custommid . 'yellow', empty($config->{'activity' . $custommid . 'yellow'}) ? 50 : $config->{'activity' . $custommid . 'yellow'});
        $mform->setType('local_diagnostic/activity' . $custommid . 'yellow', PARAM_INT);
        $mform->addElement('static', 'local_diagnostic/yellowdesc', '', get_string('yellowdesc', 'local_diagnostic'));

        $mform->addElement('text', 'local_diagnostic/activity' . $custommid . 'green', get_string('activity_green', 'local_diagnostic', $custommid));
        $mform->setDefault('local_diagnostic/activity' . $custommid . 'green', empty($config->{'activity' . $custommid . 'green'}) ? 70 : $config->{'activity' . $custommid . 'green'});
        $mform->setType('local_diagnostic/activity' . $custommid . 'green', PARAM_INT);
        $mform->addElement('static', 'local_diagnostic/greendesc', '', get_string('greendesc', 'local_diagnostic'));

        $mform->addElement('text', 'local_diagnostic/activity' . $custommid . 'exludedcmids', get_string('activity_excluded_cmids', 'local_diagnostic', $custommid));
        $mform->setDefault('local_diagnostic/activity' . $custommid . 'exludedcmids', $config->{'activity' . $custommid . 'exludedcmids'});
        $mform->setType('local_diagnostic/activity' . $custommid . 'exludedcmids', PARAM_TEXT);
        $mform->addElement('static', 'local_diagnostic/activity_excluded_cmids_desc', '', get_string('activity_excluded_cmids_desc', 'local_diagnostic', $custommid));

        $mform->addElement('text', 'local_diagnostic/activity' . $custommid . 'exludedquestionids', get_string('activity_excluded_questionids', 'local_diagnostic', $custommid));
        $mform->setDefault('local_diagnostic/activity' . $custommid . 'exludedquestionids', $config->{'activity' . $custommid . 'exludedquestionids'});
        $mform->setType('local_diagnostic/activity' . $custommid . 'exludedquestionids', PARAM_TEXT);
        $mform->addElement('static', 'local_diagnostic/activity_excluded_questionids_desc', '', get_string('activity_excluded_questionids_desc', 'local_diagnostic', $custommid));

        $mform->addElement('text', 'local_diagnostic/activity' . $custommid . 'cutoff', get_string('activity_cutoff', 'local_diagnostic', $custommid));
        $mform->setDefault('local_diagnostic/activity' . $custommid . 'cutoff', $config->{'activity' . $custommid . 'cutoff'});
        $mform->setType('local_diagnostic/activity' . $custommid . 'cutoff', PARAM_TEXT);
        $mform->addElement('static', 'local_diagnostic/activity_cutoff_desc', '', get_string('activity_cutoff_desc', 'local_diagnostic', $custommid));

        $mform->addElement('text', 'local_diagnostic/activity' . $custommid . 'startdate', get_string('activity_startdate', 'local_diagnostic', $custommid));
        $mform->setDefault('local_diagnostic/activity' . $custommid . 'startdate', $config->{'activity' . $custommid . 'startdate'});
        $mform->setType('local_diagnostic/activity' . $custommid . 'startdate', PARAM_TEXT);
        $mform->addElement('static', 'local_diagnostic/activity_startdate_desc', '', get_string('activity_startdate_desc', 'local_diagnostic', $custommid));

        $mform->addElement('text', 'local_diagnostic/activity' . $custommid . 'enddate', get_string('activity_enddate', 'local_diagnostic', $custommid));
        $mform->setDefault('local_diagnostic/activity' . $custommid . 'enddate', $config->{'activity' . $custommid . 'enddate'});
        $mform->setType('local_diagnostic/activity' . $custommid . 'enddate', PARAM_TEXT);
        $mform->addElement('static', 'local_diagnostic/activity_enddate_desc', '', get_string('activity_enddate_desc', 'local_diagnostic', $custommid));

        $mform->addElement('advcheckbox', 'local_diagnostic/repoquestionsonly' . $custommid, get_string('repoquestionsonly', 'local_diagnostic', $custommid));
        $mform->setDefault('local_diagnostic/repoquestionsonly' . $custommid, $config->{'repoquestionsonly' . $custommid});
        $mform->setType('local_diagnostic/repoquestionsonly' . $custommid, PARAM_TEXT);
        $mform->addElement('static', 'local_diagnostic/repoquestionsonlydesc', '', get_string('repoquestionsonlydesc', 'local_diagnostic'));

        $mform->addElement('advcheckbox', 'local_diagnostic/excludeopenquestions' . $custommid, get_string('excludeopenquestions', 'local_diagnostic', $custommid));
        $mform->setType('local_diagnostic/excludeopenquestions' . $custommid, PARAM_INT);
        $mform->setDefault('local_diagnostic/excludeopenquestions' . $custommid, $config->{'excludeopenquestions' . $custommid});
        $mform->addElement('static', 'local_diagnostic/excludeopenquestionsdesc', '', get_string('excludeopenquestionsdesc', 'local_diagnostic'));

        $mform->addElement('text', 'local_diagnostic/midurl' . $custommid, get_string('midurl', 'local_diagnostic', $custommid));
        $mform->setDefault('local_diagnostic/midurl' . $custommid, $config->{'midurl' . $custommid});
        $mform->setType('local_diagnostic/midurl' . $custommid, PARAM_URL);
        $mform->addElement('static', 'local_diagnostic/midurldesc', '', get_string('midurldesc', 'local_diagnostic'));

        $options = array('subdirs' => 0, 'maxfiles' => 1, 'accepted_types' => '*');
        $mform->addElement('filemanager', 'local_diagnostic/midfile' . $custommid, get_string('midfile', 'local_diagnostic', $custommid), null, $options);
        $mform->addElement('static', 'local_diagnostic/midfiledesc', '', get_string('midfiledesc', 'local_diagnostic'));

        $draftitemid = file_get_submitted_draft_itemid('local_diagnostic/midfile' . $custommid);
        file_prepare_draft_area(
            $draftitemid,
            context_system::instance()->id,
            'local_diagnostic',
            'midfile',
            $custommid,
            ['maxfiles' => 1]
        );

        $entry = ["id" => $custommid, 'local_diagnostic/midfile' . $custommid => $draftitemid];

        $this->set_data($entry);

        $this->add_action_buttons(false);
    }

    // Custom validation should be added here.
    function validation($data, $files)
    {
        return [];
    }
}