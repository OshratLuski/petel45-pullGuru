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
 * @package local_diagnostic
 * @author Evgeniy Voevodin
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright 2021 Devlion.co
 */

namespace local_diagnostic;

use stdClass;

defined('MOODLE_INTERNAL') || die();

class observer {

    /**
     * @param \mod_quiz\event\attempt_submitted $event
     * @return bool
     */
    public static function attempt_submitted(\mod_quiz\event\attempt_submitted $event): bool {
        global $DB, $USER, $CFG;

        $context = $event->get_context();
        $cmid = $context->instanceid;
        $mid = \local_metadata\mcontext::module()->get($cmid, 'ID');
        if (\local_diagnostic_external::is_enabled($mid) && in_array($mid, \community_oer\main_oer::get_repository_mids())) {
            $data = new stdClass;
            $data->cmid = $cmid;
            $data->mid = $mid;
            $task = new \local_diagnostic\task\rebuild();
            $task->set_custom_data(
                $data
            );
            \core\task\manager::queue_adhoc_task($task);
        }

        return true;
    }

    public static function clear_quizzes_cache($event): bool {
        (\cache::make('local_diagnostic', 'quizzes'))->set($event->courseid, []);

        return true;
    }
}
