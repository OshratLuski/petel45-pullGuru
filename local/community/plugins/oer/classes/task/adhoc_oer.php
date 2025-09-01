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
 * Local plugin "oer" - Task definition
 *
 * @package    community_oer
 * @copyright  2018 Devlion <info@devlion.co>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace community_oer\task;

use context_course;
use context_module;

defined('MOODLE_INTERNAL') || die();

require_once(__DIR__ . '/../../lib.php');

/**
 * The local_sandbox restore courses task class.
 *
 * @package    community_oer
 * @copyright  2018 Devlion <info@devlion.co>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class adhoc_oer extends \core\task\adhoc_task {

    /**
     * Return localised task name.
     *
     * @return string
     */
    public function get_component() {
        return 'community_oer';
    }

    /**
     * Execute adhoc task
     *
     * @return boolean
     */
    public function execute() {

        $lockkey = 'oer_cron' . time();
        $lockfactory = \core\lock\lock_config::get_lock_factory('community_oer_task');
        $lock = $lockfactory->get_lock($lockkey, 0);

        if ($lock !== false) {
            $this->run_cron_oer();
            $lock->release();
        }
    }

    public function run_cron_oer() {
        global $PAGE;

        $PAGE->theme->force_svg_use(1);

        \community_oer\main_oer::set_oercacheversion();

        $customdata = $this->get_custom_data();
        $customdata = (array) $customdata;

        if (empty($customdata)) {
            $customdata = ['menu', 'activity', 'question', 'sequence', 'course', 'review'];
        }

        foreach ($customdata as $type) {
            switch ($type) {
                case 'menu':
                    // Purge main structure.
                    \community_oer\main_oer::purge_structure();
                    break;
                case 'activity':
                    // Cache data module activity.
                    $activity = new \community_oer\activity_oer;
                    $activity->recalculate_all_activities_in_db_cache();
                    break;
                case 'question':
                    // Cache data module question.
                    $question = new \community_oer\question_oer;
                    $question->recalculate_all_questions_in_db_cache();
                    break;
                case 'sequence':
                    // Cache data module sequence.
                    $sequence = new \community_oer\sequence_oer;
                    $sequence->recalculate_all_sequences_in_db_cache();
                    break;
                case 'course':
                    // Cache data module course.
                    $course = new \community_oer\course_oer;
                    $course->recalculate_all_courses_in_db_cache();
                case 'review':
                    // Reviews.
                    $this->cron_reviews();
            }
        }
    }

    private function cron_reviews() {
        global $DB, $CFG;

        $enablereviews = get_config('community_oer', 'enablereviews');
        if ($enablereviews == 1) {

            try {
                $sql = "                    
                    SELECT u.id AS id
                    FROM {user} u
                    INNER JOIN {role_assignments} ra ON ra.userid = u.id
                    INNER JOIN {context} ct ON ct.id = ra.contextid
                    INNER JOIN {course} c ON c.id = ct.instanceid
                    INNER JOIN {role} r ON r.id = ra.roleid
                    WHERE r.shortname IN ('teacher', 'editingteacher') 
                    GROUP BY u.id
                ";
                $users = $DB->get_records_sql($sql);

                foreach ($users as $user) {
                    \community_oer\reviews_oer::update_stack($user->id);
                }

                // Keep only activities that were requested in the last 4 weeks.
                $archiveoldrequest = get_config('community_oer', 'archiveoldrequest');
                if (empty($archiveoldrequest)) {
                    $archiveoldrequest = 4;
                }

                $sql = "
                    SELECT rr.id
                    FROM {community_oerctlg_rvw_rqsts} rr
                    JOIN {community_oer_log} ol ON ol.id = rr.logid
                    JOIN {user} u ON rr.userid = u.id
                    JOIN {course_modules} cm ON cm.id = ol.newactivityid
                    WHERE rr.state = 0
                    AND FROM_UNIXTIME(rr.timecreated, '%Y-%m-%d') < DATE_SUB(curdate(), INTERVAL $archiveoldrequest WEEK )
                ";

                if($res = $DB->get_records_sql($sql)){
                    $arr = [];
                    foreach($res as $item){
                        $arr[] = $item->id;
                    }

                    $sqlupdate = "UPDATE {community_oerctlg_rvw_rqsts} SET state = 99 WHERE id IN (".implode(',', $arr).")";
                    $DB->execute($sqlupdate);
                }

                // Add oer_reviews block to teacher my page, if they are in research cohort.
                if(isset($CFG->eladresearch_cohort_a)){
                    $sqlupdateblock = "
                    INSERT INTO {block_instances} (blockname,parentcontextid,showinsubcontexts,requiredbytheme,pagetypepattern,
                                    subpagepattern,defaultregion,defaultweight,configdata,timecreated,timemodified)
                    SELECT 'oer_reviews', c.id 'parentcontextid', 0,0,'my-index', mp.id 'subpagepattern','side-pre',1,'',NOW(),NOW()
                    FROM {user} u
                    JOIN {context} c ON c.instanceid = u.id AND c.contextlevel = 30
                    JOIN {my_pages} mp ON mp.userid = u.id AND mp.private = 1
                    JOIN {cohort_members} cm ON cm.userid = u.id
                    JOIN {cohort} cohort ON cohort.id = cm.cohortid
                    WHERE cohort.idnumber = ?
                        AND c.id NOT IN (SELECT parentcontextid
                                     FROM {block_instances}
                                     WHERE blockname = 'oer_reviews' AND pagetypepattern ='my-index')
                ";
                    $DB->execute($sqlupdateblock, [$CFG->eladresearch_cohort_a]);
                }
            } catch (\Exception $e) {
                //throw new \moodle_exception('error');
                mtrace_exception($e);
            }
        }
    }
}
