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
 * Local plugin "petel" - Task definition
 *
 * @package    local_petel
 * @copyright  2020 Nadav Kavalerchik <nadav.kavalerchik@weizmann.ac.il>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_petel\task;

/**
 * The local_petel calculate social relationships task class.
 *
 * @package    local_petel
 * @copyright  2022 Weizmann institute of science, Israel.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class calculate_social_relationships extends \core\task\scheduled_task {

    const DEFAULT_BULK_USER_PREFIX = 'bulkuser';

    /**
     * Return localised task name.
     *
     * @return string
     */
    public function get_name() {
        return get_string('calculatesocialrelationships', 'local_petel');
    }

    /**
     * Execute scheduled task
     *
     * @return boolean
     */
    public function execute() {
        require_once(__DIR__ . '/../../locallib.php');

        \local_petel\funcs::calculate_social_relationships();

        return true;
    }
}
