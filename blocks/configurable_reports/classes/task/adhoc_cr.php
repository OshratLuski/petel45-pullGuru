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
 * @package    block_configurable_reports
 * @copyright  2018 Devlion <info@devlion.co>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_configurable_reports\task;

defined('MOODLE_INTERNAL') || die();

/**
 * The local_sandbox restore courses task class.
 *
 * @package    block_configurable_reports
 * @copyright  2014 Alexander Bias, Ulm University <alexander.bias@uni-ulm.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class adhoc_cr extends \core\task\adhoc_task {

    /**
     * Return localised task name.
     *
     * @return string
     */
    public function get_component() {
        return 'block_configurable_reports';
    }

    /**
     * Execute adhoc task
     *
     * @return boolean
     */
    public function execute() {

        $lockkey = 'cr_cron'.time();
        $lockfactory = \core\lock\lock_config::get_lock_factory('configurable_reports_task');
        $lock = $lockfactory->get_lock($lockkey, 0);

        if ($lock !== false) {
            $this->run_cron_cr();
            $lock->release();
        }
    }

    public function run_cron_cr() {
        global $CFG, $DB, $PAGE;

        $PAGE->theme->force_svg_use(1);

        $customdata = $this->get_custom_data();
        $customdata = (array) $customdata;

        if(!isset($customdata['id']) || empty($customdata['id'])) return false;

        $id = $customdata['id'];

        require_once($CFG->dirroot."/blocks/configurable_reports/locallib.php");
        require_once($CFG->dirroot.'/blocks/configurable_reports/report.class.php');
        require_once($CFG->dirroot.'/blocks/configurable_reports/reports/sql/report.class.php');

        if ($report = $DB->get_record('block_configurable_reports', ['id' => $id])) {
            // Running only SQL reports. $report->type == 'sql'.
            if ($report->type == 'sql') {
                $starttime = microtime(true);

                $reportclass = new \report_sql($report);

                $components = cr_unserialize($reportclass->config->components);
                $config = (isset($components['customsql']['config'])) ? $components['customsql']['config'] : new \stdclass;
                $filters = (isset($components['filters']['elements'])) ? $components['filters']['elements'] : array();

                // Filters.
                $sql = $config->querysql;
                if (!empty($filters)) {
                    foreach ($filters as $f) {
                        require_once($CFG->dirroot.'/blocks/configurable_reports/components/filters/'.$f['pluginname'].'/plugin.class.php');
                        $classname = 'plugin_'.$f['pluginname'];
                        $class = new $classname($config);
                        $sql = $class->execute($sql, $f['formdata']);
                    }
                }

                $sql = $reportclass->prepare_sql($sql);

                $tablehead = array();
                $finaltable = array();
                $totalrecords = 0;
                if ($rs = $reportclass->execute_query($sql)) {
                    foreach ($rs as $row) {
                        if (empty($finaltable)) {
                            foreach ($row as $colname => $value) {
                                $tablehead[] = $colname;
                            }
                        }
                        $arrayrow = array_values((array) $row);
                        foreach ($arrayrow as $ii => $cell) {
                            $cell = format_text($cell, FORMAT_HTML, array('trusted' => true, 'noclean' => true, 'para' => false));
                            $arrayrow[$ii] = str_replace('[[QUESTIONMARK]]', '?', $cell);
                        }
                        $totalrecords++;
                        $finaltable[] = $arrayrow;
                    }
                }

                $result = [];
                $result['tablehead'] = $tablehead;
                $result['totalrecords'] = $totalrecords;
                $result['finaltable'] = $finaltable;

                $report->lastexecutiontime = round((microtime(true) - $starttime) * 1000);
                $report->sqladhocstatus = CR_SQL_ADHOC_DONE;
                $report->sqldata = json_encode($result);
                $report->sqladhocdate = time();
                $DB->update_record('block_configurable_reports', $report);
            }
        }
    }
}
