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
class adhoc_oer_course_enrol extends \core\task\adhoc_task {

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

        $lockkey = 'oer_course_enrol_cron' . time();
        $lockfactory = \core\lock\lock_config::get_lock_factory('community_oer_task');
        $lock = $lockfactory->get_lock($lockkey, 0);

        if ($lock !== false) {
            $this->run_cron();
            $lock->release();
        }
    }

    public function run_cron() {

        $customdata = $this->get_custom_data();
        $customdata = (array) $customdata;

        if (isset($customdata['cid']) && !empty($customdata['cid'])) {

            $chidden = \local_metadata\mcontext::course()->get($customdata['cid'], 'chidden');

            $sharecourse = new \community_sharecourse\sharecourse();
            if ($chidden == 1) {
                $sharecourse->unenrol_course($customdata['cid']);
            } else {
                $sharecourse->enrol_course($customdata['cid']);
            }
        }
    }
}
