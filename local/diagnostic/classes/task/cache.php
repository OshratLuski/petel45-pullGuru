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
 * @copyright  2021 Devlion.co
 * @author  Evgeniy Voevodin
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_diagnostic\task;

defined('MOODLE_INTERNAL') || die();

/**
 * The local_diagnostic cache task class.
 *
 * @package    local_diagnostic
 * @copyright  2021 Devlion <info@devlion.co>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class cache extends \core\task\scheduled_task {

    /**
     * Return localised task name.
     *
     * @return string
     */
    public function get_name() {
        return get_string('cachetask', 'local_diagnostic');
    }

    /**
     * Execute scheduled task
     *
     * @return boolean
     */
    public function execute() {
        global $CFG, $DB;
        require_once $CFG->dirroot . '/local/diagnostic/classes/external.php';

        $customids = get_config('local_diagnostic ', 'croncustommids');
        if (empty($customids)) {
            mtrace('Parameter croncustommids is empty');
            return;
        }

        if (!$parentcategoryid = \community_oer\main_oer::get_oer_category()) {
            $parentcategoryid = 0;
        }

        $metadatafieldid = $DB->get_field('local_metadata_field', 'id', ['shortname' => 'ID']);

        if ($customids == "-1") {

            $rows = $DB->get_records_sql('SELECT lm.id, lm.data, lm.instanceid 
                                                FROM {local_metadata} lm 
                                                JOIN {course_modules} cm ON (cm.id = lm.instanceid) 
                                                JOIN {course} c ON (cm.course = c.id) 
                                                JOIN {course_categories} AS cc ON c.category = cc.id
                                                JOIN {modules} AS m ON m.id = cm.module
                                                WHERE cc.id IN (SELECT id FROM {course_categories} WHERE parent = ?) AND m.name = "quiz"
                                                AND lm.fieldid = ? AND lm.data IS NOT NULL AND cm.id IS NOT NULL',
                    [$parentcategoryid, $metadatafieldid]);
            mtrace('All mids: ' . count($rows));
            $cmids = [];
            foreach ($rows as $row) {
                $cmids[] = $row->data;
            }

        } else {
            $customids = trim($customids);
            $cmids = explode(',', $customids);
        }
        local_diagnotic_rebuild($cmids);

        mtrace("diagnostic cache done");
    }
}