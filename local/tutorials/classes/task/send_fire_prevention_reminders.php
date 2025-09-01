<?php

/**
 * Local plugin "tutorials" - Task definition
 *
 * @package    local_tutorials
 * @copyright  2022 Nadav Kavalerchik <nadav.kavalerchik@weizmann.ac.il>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_tutorials\task;

defined('MOODLE_INTERNAL') || die();

//require_once(__DIR__ . '/../../locallib.php');

/**
 * The local_tutorials
 * Send please complete your tutorial (SCORM activity) reminder, by intervals.
 *
 * @package    local_tutorials
 * @copyright  2023 Matan Berkovitch <matan.berkovitch@weizmann.ac.il>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class send_fire_prevention_reminders extends \core\task\scheduled_task {

    /**
     * Return localised task name.
     *
     * @return string
     */
    public function get_name() {
        return get_string('tasks:send_fire_prevention_reminders', 'local_tutorials');
    }

    /**
     * Execute scheduled task
     *
     * @return boolean
     */
    public function execute() {
        $task = new \local_tutorials\task\adhoc_send_fire_prevention_reminders();
        $task->set_custom_data(
            array()
        );
        \core\task\manager::queue_adhoc_task($task);
    }
}