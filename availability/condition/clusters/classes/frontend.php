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
 * Front-end class.
 *
 * @package availability
 * @subpackage clusters
 * @copyright  2022 Devlion.co <info@devlion.co>
 * @author  Evgeniy Voevodin
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace availability_clusters;

use local_clusters\clusters;

defined('MOODLE_INTERNAL') || die();

/**
 * Front-end class.
 *
 * @package availability_clusters
 * @copyright 2014 The Open University
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class frontend extends \core_availability\frontend {

    protected function get_javascript_init_params($course, \cm_info $cm = null, \section_info $section = null) {
        // Get all clusters for course.
        $clusters = [];
        if (isset($cm->id)) {
            $clusters = clusters::get_records(['courseid' => $course->id, 'cmid' => $cm->id]);
        }

        // Change to JS array format and return.
        $jsarray = [];

        foreach ($clusters as $cluster) {
            $clusterrec = $cluster->to_record();
            $name = $clusterrec->name = get_string('clustername', 'availability_clusters', $clusterrec);
            $jsarray[] = (object) [
                'id' => $clusterrec->id, 'name' => $name
            ];
        }

        return [$jsarray];
    }

    protected function allow_add($course, \cm_info $cm = null, \section_info $section = null) {

        // Only show this option if there are some clusters.
        $clusters = [];
        if (isset($cm->id)) {
            $clusters = clusters::get_records(['courseid' => $course->id, 'cmid' => $cm->id]);
        }
        return count($clusters) > 0;
    }
}
