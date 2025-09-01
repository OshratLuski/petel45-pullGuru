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
 * @subpackage diagnostic
 * @copyright  2024 Devlion.co
 * @author     Anton Putin
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_diagnostic\task;

defined('MOODLE_INTERNAL') || die();

/**
 * The local_diagnostic rebuild task class.
 *
 * @package    local_diagnostic
 * @copyright  2024 Devlion <info@devlion.co>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class rebuild extends \core\task\adhoc_task {

    /**
     * Return localised task name.
     *
     * @return string
     */
    public function get_name() {
        return get_string('rebuildtask', 'local_diagnostic');
    }

    /**
     * Execute scheduled task
     *
     * @return boolean
     */
    public function execute() {
        global $CFG, $DB;
        require_once $CFG->dirroot . '/local/diagnostic/classes/external.php';

        $customdata = self::get_custom_data();
        $cmid = $customdata->cmid;
        $mid = $customdata->mid;
        $rebuild = $customdata->rebuild ?? false;
        mtrace("diagnostic rebuild started for mid & cmid: ". $mid. ", ". $cmid);

        $sql = "SELECT COUNT(q.id)
                FROM {question} q
                JOIN {question_versions} qv ON q.id=qv.questionid
                JOIN {question_bank_entries} qbe ON qbe.id=qv.questionbankentryid
                JOIN {question_references} qr ON qr.questionbankentryid=qbe.id
                JOIN {quiz_slots} quizslots ON quizslots.id=qr.itemid
                JOIN {course_modules} cm ON cm.instance = quizslots.quizid
                WHERE q.qtype='mlnlpessay' and cm.id = ?";

        if ($DB->count_records_sql($sql, [$mid])){
            $rebuild = true;
        }
        local_diagnotic_rebuild([$cmid], $rebuild);
        mtrace("diagnostic rebuild done");
        return true;
    }
}
