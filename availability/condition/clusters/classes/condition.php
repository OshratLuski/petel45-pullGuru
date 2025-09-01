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
 * Condition main class.
 *
 * @package availability
 * @subpackage clusters
 * @copyright  2022 Devlion.co <info@devlion.co>
 * @author  Evgeniy Voevodin
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace availability_clusters;

use local_clusters\clusters;
use local_clusters\clusters_members;

defined('MOODLE_INTERNAL') || die();

/**
 * Condition main class.
 *
 * @package availability_clusters
 * @copyright  2022 Devlion.co <info@devlion.co>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class condition extends \core_availability\condition {
    /** @var array Array from clusters id => name */
    protected static $clusternames = array();

    /** @var int ID of clusters that this condition requires, or 0 = any clusters */
    protected $clusterid;

    /**
     * Constructor.
     *
     * @param \stdClass $structure Data structure from JSON decode
     * @throws \coding_exception If invalid data structure.
     */
    public function __construct($structure) {
        // Get clusters id.

        if (!property_exists($structure, 'id')) {
            $this->clusterid = 0;
        } else if (is_int($structure->id)) {
            $this->clusterid = $structure->id;
        } else {
            throw new \coding_exception('Invalid ->id for clusters condition');
        }
    }

    public function save() {
        $result = (object)array('type' => 'clusters');
        if ($this->clusterid) {
            $result->id = $this->clusterid;
        }
        return $result;
    }

    public function is_available($not, \core_availability\info $info, $grabthelot, $userid) {
        $course = $info->get_course();
        $context = \context_course::instance($course->id);
        $allow = true;

        // Get all clusters the user belongs to.
        $clusters = clusters_members::get_user_clusterids($userid);
        if ($this->clusterid) {
            $allow = in_array($this->clusterid, $clusters);
        }


        if ($not) {
            $allow = !$allow;
        }

        return $allow;
    }

    public function get_description($full, $not, \core_availability\info $info) {
        global $DB;

        $name = get_string('missing', 'availability_clusters');
        if ($this->clusterid) {
            // Need to get the name for the clusters. Unfortunately this requires
            // a database query. To save queries, get all clusters for course at
            // once in a static cache.

            if (!array_key_exists($this->clusterid, self::$clusternames)) {
                $course = $info->get_course();
                $context = \context_course::instance($course->id);
                $courseclusters = clusters::get_records(['courseid' => $course->id]);

                foreach ($courseclusters as $coursecluster) {
                    $clusterrec = $coursecluster->to_record();
                    if (has_capability('moodle/grade:viewall', $context)) {
                        $langkey = 'clustername';
                    } else {
                        $langkey = 'studentclustername';
                    }
                    self::$clusternames[$clusterrec->id] = get_string($langkey, 'availability_clusters', $clusterrec);
                }
            }

            if (array_key_exists($this->clusterid, self::$clusternames)) {
                $name = self::$clusternames[$this->clusterid];
            }
        }

        return get_string($not ? 'requires_notclusters' : 'requires_clusters',
                'availability_clusters', $name);
    }

    protected function get_debug_string() {
        return $this->clusterid ? '#' . $this->clusterid : 'any';
    }

    /**
     * Include this condition only if we are including clusters in restore, or
     * if it's a generic 'same activity' one.
     *
     * @param int $restoreid The restore Id.
     * @param int $courseid The ID of the course.
     * @param \base_logger $logger The logger being used.
     * @param string $name Name of item being restored.
     * @param \base_task $task The task being performed.
     *
     * @return Integer clusterid
     */
    public function include_after_restore($restoreid, $courseid, \base_logger $logger,
            $name, \base_task $task) {
        try {
            $task->get_setting_value('clusters');
        } catch (\Exception $e) {
            return !$this->clusterid;
        }

        return !$this->clusterid || $task->get_setting_value('clusters');
    }

    public function update_after_restore($restoreid, $courseid, \base_logger $logger, $name) {
        global $DB;
        if (!$this->clusterid) {
            return false;
        }
        $rec = \restore_dbops::get_backup_ids_record($restoreid, 'clusters', $this->clusterid);
        if (!$rec || !$rec->newitemid) {
            // If we are on the same course (e.g. duplicate) then we can just
            // use the existing one.
            if (clusters::get_record(['id' => $this->clusterid, 'courseid' => $courseid])) {
                return false;
            }
            // Otherwise it's a warning.
            $this->clusterid = -1;
            $logger->process('Restored item (' . $name .
                    ') has availability condition on clusters that was not restored',
                    \backup::LOG_WARNING);
        } else {
            $this->clusterid = (int)$rec->newitemid;
        }
        return true;
    }

    public function update_dependency_id($table, $oldid, $newid) {
        if ($table === 'clusters' && (int)$this->clusterid === (int)$oldid) {
            $this->clusterid = $newid;
            return true;
        } else {
            return false;
        }
    }

    /**
     * Wipes the static cache used to store clustersing names.
     */
    public static function wipe_static_cache() {
        self::$clusternames = array();
    }

    public function is_applied_to_user_lists() {
        // Group conditions are assumed to be 'permanent', so they affect the
        // display of user lists for activities.
        return true;
    }

    public function filter_user_list(array $users, $not, \core_availability\info $info,
            \core_availability\capability_checker $checker) {

        $result = [];

        // If the array is empty already, just return it.
        if (!$users) {
            return $users;
        }

        $clustersusersids = [];

        if ($this->clusterid) {
            $clustersusersids = array_map(function($item) {
                return $item->get('userid');
            }, clusters_members::get_records(['clusterid' => $this->clusterid]));
        }

        foreach ($users as $id => $user) {
            // Other users are included or not based on group membership.
            $allow = in_array($id, $clustersusersids);
            if ($not) {
                $allow = !$allow;
            }

            if ($allow) {
                $result[$id] = $user;
            }
        }

        return $result;
    }

    /**
     * Returns a JSON object which corresponds to a condition of this type.
     *
     * Intended for unit testing, as normally the JSON values are constructed
     * by JavaScript code.
     *
     * @param int $clusterid Required clusters id (0 = any clusters)
     * @return \stdClass Object representing condition
     */
    public static function get_json($clusterid = 0) {
        $result = (object) ['type' => 'clusters'];
        // Id is only included if set.
        if ($clusterid) {
            $result->id = (int)$clusterid;
        }
        return $result;
    }

    public function get_user_list_sql($not, \core_availability\info $info, $onlyactive) {
        global $DB;

        // Get all enrolled users.
        list ($enrolsql, $enrolparams) =
                get_enrolled_sql($info->get_context(), '', 0, $onlyactive);

        // Condition for specified or any clusters.
        $matchparams = [];
        $where = '1';

        if ($this->clusterid) {
            $condition = $not ? 'NOT' : '';
            $where = "$condition EXISTS (SELECT 1
                           FROM {" . clusters_members::TABLE . "} clm
                          WHERE clm.userid = userids.id
                                AND clm.clusterid = " .
                    self::unique_sql_parameter($matchparams, $this->clusterid) . ")";
        }

        $sql = "SELECT userids.id
                  FROM ($enrolsql) userids
                 WHERE $where";

        return [$sql, array_merge($enrolparams, $matchparams)];
    }
}
