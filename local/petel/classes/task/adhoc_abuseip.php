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
 * @package    local_petel
 * @copyright  2018 Devlion <info@devlion.co>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_petel\task;

defined('MOODLE_INTERNAL') || die();

require_once(__DIR__ . '/../../lib.php');

/**
 * The local_sandbox restore courses task class.
 *
 * @package    community_oer
 * @copyright  2018 Devlion <info@devlion.co>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class adhoc_abuseip extends \core\task\adhoc_task {

    /**
     * Return localised task name.
     *
     * @return string
     */
    public function get_component() {
        return 'local_petel';
    }

    /**
     * Execute adhoc task
     *
     * @return boolean
     */
    public function execute() {

        $lockkey = 'abuseip_cron' . time();
        $lockfactory = \core\lock\lock_config::get_lock_factory('local_petel_task');
        $lock = $lockfactory->get_lock($lockkey, 0);

        if ($lock !== false) {
            $this->run_cron_abuseip();
            $lock->release();
        }
    }

    public function run_cron_abuseip() {
        global $DB, $CFG;

        // Ensure the API key is defined.
        if (!isset($CFG->abuseipdb_api) || empty($CFG->abuseipdb_api)) {
            return;
        }

        // Get all records from the abuse_ip table where status is set to 1.
        foreach ($DB->get_records('abuse_ip', ['status' => 1]) as $record) {
            // Update the status to 0 regardless of the API call result.
            $record->status = 0;
            $record->error = '';
            $ip = $record->ip;

            //// Validate if the IP address is in a valid format.
            //if (!filter_var($ip, FILTER_VALIDATE_IP)) {
            //    mtrace("Invalid IP address format: {$ip}");
            //    continue; // Skip to the next record if the IP address is not valid.
            //}

            // Prepare API call to AbuseIPDB.
            $apiKey = isset($CFG->abuseipdb_api) ? $CFG->abuseipdb_api : '';
            $url = "https://api.abuseipdb.com/api/v2/check?ipAddress={$ip}&maxAgeInDays=90";

            // Initialize CURL for the API call.
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_HTTPHEADER, ["Key: {$apiKey}", "Accept: application/json"]);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError = curl_error($ch);
            curl_close($ch);

            // Check for CURL errors or unsuccessful HTTP response.
            if ($response === false || $httpCode !== 200) {
                $record->error = "CURL error: {$curlError} or HTTP error code: {$httpCode}";
                $DB->update_record('abuse_ip', $record);
                continue;
            }

            // Parse the API response.
            $responseData = json_decode($response, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                $record->error = "Error decoding JSON response: " . json_last_error_msg();
                $DB->update_record('abuse_ip', $record);
                continue;
            }

            if (isset($responseData['data'])) {
                // Update the relevant IP data in the database.
                $record->country = $responseData['data']['countryCode'] ?? null;
                $record->confidence = $responseData['data']['abuseConfidenceScore'] ?? 0;
                $record->timeupdated = time();
                $record->error = null; // Clear any previous errors.

                try {
                    $DB->update_record('abuse_ip', $record);
                } catch (\exception $e) {
                }
            } else {
                $record->error = "Unexpected API response structure";
                $DB->update_record('abuse_ip', $record);
            }
        }

    }
}
