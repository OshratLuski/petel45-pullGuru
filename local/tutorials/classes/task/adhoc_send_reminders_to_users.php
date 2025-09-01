<?php

/**
 * Local plugin "tutorials" - Task definition
 *
 * @package    local_tutorials
 * @copyright  2022 Matan Berkovitch <matan.berkovitch@weizmann.ac.il>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_tutorials\task;

defined('MOODLE_INTERNAL') || die();

require_once(__DIR__ . '/../../locallib.php');

/**
 * The local_tutorials
 * Send please complete your tutorial (SCORM activity) reminder, by intervals.
 *
 * @package    local_tutorials
 * @copyright  2022 Matan Berkovitch <matan.berkovitch@weizmann.ac.il>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class adhoc_send_reminders_to_users extends \core\task\adhoc_task {

    /**
     * Return localised task name.
     *
     * @return string
     */
    public function get_component() {
        return 'local_tutorials_reminders';
    }

    /**
     * Execute adhoc task
     *
     * @return boolean
     */
    public function execute() {

        $lockkey = 'tutorials_cron_send_reminders';
        $lockfactory = \core\lock\lock_config::get_lock_factory('local_tutorials_sendreminders_task');
        $lock = $lockfactory->get_lock($lockkey, 0);

        if ($lock !== false) {
            $this->run_adhoc_send_reminders();
            $lock->release();
        }
    }

    public function run_adhoc_send_reminders() {

        //raise_memory_limit(MEMORY_UNLIMITED);
        // TODO: could be a long list of emails (maybe 4K)
        // TODO: and we should probably split it into chunks
        send_reminders_to_uncompleted_activities();

    }
}