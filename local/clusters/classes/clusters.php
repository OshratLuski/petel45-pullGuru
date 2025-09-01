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
 * @package    local
 * @subpackage clusters
 * @copyright  2022 Devlion.co <info@devlion.co>
 * @author  Evgeniy Voevodin
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_clusters;

use core\persistent;
require_once($CFG->dirroot . '/question/editlib.php');

defined('MOODLE_INTERNAL') || die();

class clusters extends persistent
{
    const TABLE = 'local_clusters';

    const SOURCE_MYCOURSES = 'mycourses';
    const SOURCE_REPOSITORY = 'repository';
    const SOURCE_RECOMMENDED = 'recommended';

    /**
     * Return the list of properties.
     *
     * @return array
     */
    protected static function define_properties() {
        return array(
                'courseid' => array(
                        'type' => PARAM_INT,
                ),
                'source' => array(
                        'type' => PARAM_TEXT,
                ),
                'sourcecmid' => array(
                        'type' => PARAM_INT,
                ),
                'cmid' => array(
                        'type' => PARAM_INT,
                ),
                'mid' => array(
                        'type' => PARAM_INT,
                ),
                'type' => array(
                        'type' => PARAM_TEXT,
                ),
                'question' => array(
                        'type' => PARAM_INT,
                ),
                'attempt' => array(
                        'type' => PARAM_INT,
                ),
                'clusternum' => array(
                        'type' => PARAM_INT,
                ),
                'name' => array(
                        'type' => PARAM_TEXT,
                        'default' => null,
                        'null' => NULL_ALLOWED
                ),
                'description' => array(
                        'type' => PARAM_TEXT,
                        'default' => null,
                        'null' => NULL_ALLOWED
                ),
                'recommend' => array(
                        'type' => PARAM_INT,
                ),
        );
    }

    protected function after_create()
    {
        parent::after_create();

        $params = [
            'context' => \context_module::instance($this->get('cmid')),
            'courseid' => $this->get('courseid'),
            'objectid' => $this->get('id'),
            'relateduserid' => $this->get('usermodified'),
            'other' => [
                'cmid' => $this->get('cmid'),
                'source' => $this->get('source')
            ]
        ];

        $event = \local_clusters\event\clusters_created::create($params);
        $event->trigger();
    }

    public static function add_cluster(int $courseid, string $source, int $sourcecmid, int $mid, int $cmid, int $clusternum, int $attempt, string $description, int $recommend, array $userids, string $type, int $question) {
        global $DB;

        list($modrec, $cmrec) = get_module_from_cmid($cmid);
        $params = [
            'courseid' => $courseid,
            'source' => $source,
            'sourcecmid' => $sourcecmid,
            'clusternum' => $clusternum,
            'cmid' => $cmid,
            'attempt' => $attempt,
            'type' => $type,
            'question' => $question,
        ];

        if ($localcluster = \local_clusters\clusters::get_record($params)) {
            $localcluster->set('description', $description);
            $localcluster->set('recommend', $recommend);
            $localcluster->update();
        } else {
            $params['mid'] = $mid;
            $params['name'] = $modrec->name;
            $params['description'] = $description;
            $params['recommend'] = $recommend;

            $localcluster = new \local_clusters\clusters(0, (object) $params);
            $localcluster->create();
        }

        $DB->delete_records(\local_clusters\clusters_members::TABLE, [
            'clusterid' => $localcluster->get('id'),
        ]);

        foreach ($userids as $userid) {
            $localmemberdata = (object) [
                'clusterid' => $localcluster->get('id'),
                'userid' => $userid,
            ];

            $localclustermember = new \local_clusters\clusters_members(0, $localmemberdata);
            $localclustermember->create();
        }
    }
}


