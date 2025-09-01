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

namespace smsgateway_smscenter;
use core_sms\manager;
use core_sms\message;
use core_sms\message_status;


/**
 * SMS Center gateway.
 *
 * @package    smsgateway_smscenter
 * @copyright  2024 Safat Shahin <safat.shahin@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class gateway extends \core_sms\gateway {

    /**
     * @throws \coding_exception
     * @throws \dml_exception
     * @throws \JsonException
     */
    public function send(message $message): message {
        global $DB;

        $config = $DB->get_field(
            table: 'sms_gateways',
            return: 'config',
            conditions: ['id' => $message->gatewayid, 'enabled' => 1, 'gateway' => 'smsgateway_smscenter\gateway'],
        );

        $status = message_status::GATEWAY_NOT_AVAILABLE;

        if ($config) {
            $config = (object)json_decode($config, true, 512, JSON_THROW_ON_ERROR);

            $recipientnumber = manager::format_number(
                phonenumber: $message->recipientnumber,
                countrycode: $config->countrycode ?? null,
            );

            $params = [
                'UserName' => $config->username,
                'Password' => $config->password,
                'SenderName' => $config->sendername,
                'SendToPhoneNumber' => '0'.$recipientnumber,
                'Message' => urlencode($message->content),
                'CCToEmail' => $config->cctomail ?? 0,
                'SMSOperation' => $config->smsoperation ?? 0,
                'DeliveryReportURL' => $config->deliveryreporturl ?? 0,
                'DeliveryReportMask' => $config->deliveryreportmask ?? 0,
                'DeliveryDelayInMinutes' => $config->deliverydelayinminutes ?? 0,
                'ExpirationDelayInMinutes' => $config->expirationdelayinminutes ?? 0,
            ];

            $arr = [];
            foreach ($params as $param => $value) {
                $arr[] = $param.'='.$value;
            }

            $query = implode('&', $arr);

            $url = $config->url . '?' . $query;

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            $response = curl_exec($ch);
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($http_code == 200 && $response !== false) {
                if (str_contains($response, 'OK')) {
                    $status = message_status::GATEWAY_SENT;
                } else {
                    $status = message_status::GATEWAY_FAILED;
                }
            } else {
                $status = message_status::GATEWAY_FAILED;
            }
        }

        return $message->with(
            status: $status,
        );
    }

    public function get_send_priority(message $message): int {
        return 50;
    }
}
