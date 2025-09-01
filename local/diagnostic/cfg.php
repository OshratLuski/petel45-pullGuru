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
 * Web
 *
 * @package    local_diagnostic
 * @copyright  2023 Devlion.ltd <info@devlion.co>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(__DIR__ . '/../../config.php');
global $CFG, $OUTPUT, $PAGE;

require_once $CFG->dirroot . '/local/diagnostic/classes/external.php';
require_login();
if (!is_siteadmin()) {
    $systemcontext = context_system::instance();

    $userroles = array_map(function ($assignment) {
        return $assignment->shortname;
    }, get_user_roles($systemcontext));

    if (!in_array('manager', $userroles)) {
        throw new \moodle_exception('nopermissions');
    }
}

$mid = required_param('mid', PARAM_INT);
$clusters = optional_param('clusters', 5, PARAM_INT);
$action = required_param('action', PARAM_TEXT);

switch ($action) {
    case "add":
        $config = get_config('local_diagnostic');
        $croncustommids = explode(',', $config->croncustommids);
        if (!(in_array($mid, $croncustommids))) {
            $croncustommids[] = $mid;
        }
        set_config('croncustommids', implode(',', $croncustommids), 'local_diagnostic');
        $custommids = explode(',', $config->custommids);
        if (!(in_array($mid, $custommids))) {
            $custommids[] = $mid;
        }
        set_config('custommids', implode(',', $custommids), 'local_diagnostic');
        set_config('activityclusternum_' . $mid, $clusters, 'local_diagnostic');
        if ($cache = \local_diagnostic\cache::get_record(['mid' => $mid])) {
            $cache->delete();
        }
        $systemcontext = context_system::instance();
        $PAGE->set_context($systemcontext);
        $PAGE->set_url('/local/diagnostic/cfg.php');
        $PAGE->set_title('Mid ' . $mid . ' add & execute');
        echo $OUTPUT->header();
        ob_start();
        @local_diagnotic_rebuild([$mid]);
        $result = ob_get_clean();
        echo "<pre>";
        print_r($result);
        echo "</pre>";
        $urlparts = new moodle_url($_SERVER['HTTP_REFERER']);
        echo "<br>";
        echo html_writer::link($urlparts, "Back to the report");
        echo $OUTPUT->footer();
        break;
    case "remove":
        $config = get_config('local_diagnostic');
        $croncustommids = explode(',', $config->croncustommids);
        $croncustommids = array_diff($croncustommids, [$mid]);
        set_config('croncustommids', implode(',', $croncustommids), 'local_diagnostic');
        $custommids = explode(',', $config->custommids);
        $croncustommids = array_diff($custommids, [$mid]);
        set_config('custommids', implode(',', $croncustommids), 'local_diagnostic');
        $urlparts = new moodle_url($_SERVER['HTTP_REFERER']);
        redirect($urlparts);
        break;
    case "addandrun":
        $config = get_config('local_diagnostic');
        $croncustommids = explode(',', $config->croncustommids);
        if (!(in_array($mid, $croncustommids))) {
            $croncustommids[] = $mid;
        }
        set_config('croncustommids', implode(',', $croncustommids), 'local_diagnostic');
        $custommids = explode(',', $config->custommids);
        if (!(in_array($mid, $custommids))) {
            $custommids[] = $mid;
        }
        set_config('custommids', implode(',', $custommids), 'local_diagnostic');
        set_config('activityclusternum_' . $mid, $clusters, 'local_diagnostic');

        $data = new stdClass;
        $data->cmid = $mid;
        $data->mid = $mid;
        $data->rebuild = true;
        $task = new \local_diagnostic\task\rebuild();
        $task->set_custom_data(
            $data
        );
        \core\task\manager::queue_adhoc_task($task);
        echo $OUTPUT->header();
        echo $OUTPUT->box(get_string('rebuildqueued', 'local_diagnostic'));
        echo $OUTPUT->footer();
        exit;
    default:
}
