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
 * Local plugin "sandbox" - Task definition
 *
 * @package    community_sharesequence
 * @copyright  2018 Devlion <info@devlion.co>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace community_sharesequence\task;

use context_course;
use context_module;

defined('MOODLE_INTERNAL') || die();

require_once(__DIR__ . '/../../lib.php');

/**
 * The local_sandbox restore courses task class.
 *
 * @package    community_sharesequence
 * @copyright  2014 Alexander Bias, Ulm University <alexander.bias@uni-ulm.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class adhoc_sharesequence extends \core\task\adhoc_task {

    /**
     * Return localised task name.
     *
     * @return string
     */
    public function get_component() {
        return 'community_sharesequence';
    }

    /**
     * Execute adhoc task
     *
     * @return boolean
     */
    public function execute() {

        $lockkey = 'sharesequence_cron' . time();
        $lockfactory = \core\lock\lock_config::get_lock_factory('community_sharesequence_task');
        $lock = $lockfactory->get_lock($lockkey, 0);

        if ($lock !== false) {

            $this->run_cron_sharesequence();
            $lock->release();
        }
    }

    public function run_cron_sharesequence() {
        global $DB, $USER, $CFG;

        $obj = $DB->get_records('community_sharesequence_task', array('status' => 0));

        // End working.
        foreach ($obj as $item) {
            $item->status = 2;
            $DB->update_record('community_sharesequence_task', $item);
        }

        foreach ($obj as $item) {
            switch ($item->type) {
                case 'createsequence':

                    $USER = get_admin();
                    $section = $DB->get_record('course_sections', ['id' => $item->sectionid]);

                    try {

                        $arrcmids = [];
                        foreach (json_decode($item->activities) as $cm) {

                            $lib = new \duplicate();
                            $lib->enable_glossary_copy_users();
                            $lib->enable_database_copy_users();

                            $newactivities = [];
                            $newcmids = $lib->duplicate_activity($cm->cmid, $section->course, $item->sectionid, $newactivities, []);

                            $newcmid = $newcmids[$cm->cmid];

                            $arrcmids[$cm->cmid] = $newcmid;

                            // Update field added in course_modules.
                            $newrow = $DB->get_record('course_modules', array('id' => $newcmid));
                            $newrow->added = time();
                            $DB->update_record('course_modules', $newrow);

                            // Deactivate activities.
                            //set_coursemodule_visible($newcmid, 0);

                            // Set same visible, visibleoncoursepage.
                            if ($row = $DB->get_record('course_modules', array('id' => $cm->cmid))) {
                                set_coursemodule_visible($newcmid, $row->visible, $row->visibleoncoursepage, true);
                            }

                            // Change name.
                            $cmobj = $DB->get_record('course_modules', ['id' => $newcmid]);
                            $info = get_fast_modinfo($cmobj->course);
                            $act = $info->get_cm($cmobj->id);
                            $DB->update_record($act->modname, ['id' => $act->instance, 'name' => $cm->name]);

                            // Change ID activity.
                            \local_metadata\mcontext::module()->save($newcmid, 'ID', 'SEQ-' . $item->sectionid);
                        }

                        // Availability.
                        foreach ($arrcmids as $oldcmid => $newcmid) {
                            $rowold = $DB->get_record('course_modules', array('id' => $oldcmid));
                            $rownew = $DB->get_record('course_modules', array('id' => $newcmid));

                            if (!empty($rowold->availability) && $rownew) {
                                $res = \community_sharewith\funcs::change_availability(json_decode($rowold->availability), $newcmid,
                                        $arrcmids);

                                if ($res != null) {
                                    $rownew->availability = json_encode($res, JSON_NUMERIC_CHECK);
                                } else {
                                    $rownew->availability = null;
                                }

                                $DB->update_record('course_modules', $rownew);
                            }
                        }

                        rebuild_course_cache($section->course);

                        $item->status = 1;
                        $DB->update_record('community_sharesequence_task', $item);

                    } catch (\Exception $e) {
                        $item->error = $e->getMessage();
                        $DB->update_record('community_sharesequence_task', $item);
                    }
                    break;
            }
        }
    }
}
