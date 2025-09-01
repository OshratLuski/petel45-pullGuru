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
 * Event observers.
 *
 * @package availability_clusters
 * @copyright  2022 Devlion.co
 * @author Evgeniy Voevodin
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright 2021 Devlion.co
 */

namespace availability_clusters;

use local_clusters\clusters;

defined('MOODLE_INTERNAL') || die();

class observer {

    /**
     * @param \local_diagnostic\event\added_by_centroid $event
     * @return bool
     */
    public static function added_by_centroid(\local_diagnostic\event\added_by_centroid $event): bool {

        $data = $event->other;
        $userid = $event->relateduserid;

        $clusters = clusters::get_records($data);

        foreach ($clusters as $cluster) {
            $memberdata = [
                'clusterid' => $cluster->get('id'),
                'userid' => $userid,
            ];

            $memberexists = \local_clusters\clusters_members::get_record($memberdata);

            if (!$memberexists) {
                $newmember = new \local_clusters\clusters_members(0, (object) $memberdata);
                $newmember->create();
            }
        }

        return true;
    }

    /**
     * @param \local_clusters\event\clusters_created $event
     * @return bool
     */
    public static function clusters_created(\local_clusters\event\clusters_created $event): bool {
        global $DB, $CFG;
        require_once($CFG->libdir . '/outputlib.php');

        $data = $event->other;

        if ($clusters = clusters::get_records($data)) {
            //should always be true
            $c = [];

            foreach ($clusters as $cluster) {
                $c[] = (object) [
                    'type' => 'clusters',
                    'id' => intval($cluster->get('id'))
                ];
            }

            if ($condition = $DB->get_field('course_modules', 'availability', ['id' => $data['cmid']])) {
                $condition = json_decode($condition);
            } else {
                $condition = (object) [
                    'op' => '|',
                    'show' => false
                ];
            }

            $condition->c = $c;
            $DB->set_field('course_modules', 'availability', json_encode($condition), ['id' => $data['cmid']]);
            purge_caches();
        }

        return true;
    }
}
