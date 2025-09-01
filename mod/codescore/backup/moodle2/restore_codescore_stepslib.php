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
 * codescore restore task that provides all the settings and steps to perform one
 * complete restore of the activity
 */

/**
 * Structure step to restore one codescore activity
 */

defined('MOODLE_INTERNAL') || die();
class restore_codescore_activity_structure_step extends restore_activity_structure_step {

    protected function define_structure() {

        $paths = array();
        $userinfo = $this->get_setting_value('userinfo');

        $paths[] = new restore_path_element('codescore', '/activity/codescore');
        if ($userinfo) {
            $paths[] = new restore_path_element('codescore_attempt', '/activity/codescore/attempts/attempt');
        }

        // Return the paths wrapped into standard activity structure
        return $this->prepare_activity_structure($paths);
    }

    protected function process_codescore($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;
        $data->course = $this->get_courseid();

        // insert the codescore record
        $newitemid = $DB->insert_record('codescore', $data);
        // immediately after inserting "activity" record, call this
        $this->apply_activity_instance($newitemid);
    }

    protected function process_codescore_attempt($data) {
        global $DB;

        $data = (object)$data;

        $data->codescoreid = $this->get_new_parentid('codescore');
        $data->userid = $this->get_mappingid('user', $data->userid);

        $newitemid = $DB->insert_record('codescore_attempts', $data);
        // No need to save this mapping as far as nothing depend on it
        // (child paths, file areas nor links decoder)
    }

    protected function after_execute() {
        // Add codescore related files, no need to match by itemname (just internally handled context)
        $this->add_related_files('mod_codescore', 'intro', null);
    }
}
