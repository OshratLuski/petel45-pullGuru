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
 * Define all the restore steps that will be used by the restore_mindmaap_activity_task
 *
 * @package     mod_mindmaap
 * @copyright   2019 Devlion <info@devlion.co>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

/**
 * Structure step to restore one mindmaap activity
 */
class restore_mindmaap_activity_structure_step extends restore_activity_structure_step {

    protected function define_structure() {
        $paths = array();

        $paths[] = new restore_path_element('mindmaap', '/activity/mindmaap');

        // Return the paths wrapped into standard activity structure.
        return $this->prepare_activity_structure($paths);
    }

    /**
     * Process mindmaap information
     *
     * @param array $data information
     */
    protected function process_mindmaap($data) {
        global $CFG;

        require_once($CFG->dirroot . '/mod/mindmaap/lib.php');

        $record = (object) [
                'course' => $this->get_courseid(),
                'name' => $data['name'],
                'intro' => $data['intro'],
                'type' => $data['type'],
                'introformat' => $data['introformat'],
                'coursemodule' => $this->task->get_moduleid(),
                'oldmoduleid' => $this->task->get_old_moduleid()
        ];

        $id = mindmaap_add_instance($record);

        $this->apply_activity_instance($id);
    }

    protected function after_execute() {

        // Add mindmaap intro files.
        $this->add_related_files('mod_mindmaap', 'intro', null);
    }
}
