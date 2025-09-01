<?php
namespace local_petel\task;

defined('MOODLE_INTERNAL') || die();

require_once(__DIR__ . '/../../lib.php');


/**
 * Adhoc task to process IP addresses with AbuseIP.
 *
 * This task checks for flagged IP addresses in the database,
 * queries the AbuseIP API to retrieve data, and updates the database accordingly.
 */
class schedule_abuseip extends \core\task\scheduled_task {

    /**
     * Return localised task name.
     *
     * @return string
     */
    public function get_name() {
        return get_string('taskabuseip', 'local_petel');
    }

    /**
     * Execute the task.
     *
     * Fetches IP abuse data using the AbuseIP API and updates the database.
     *
     * @return void
     */
    public function execute() {
        $task = new \local_petel\task\adhoc_abuseip();
        $task->set_custom_data(
                array()
        );
        \core\task\manager::queue_adhoc_task($task);
    }
}
