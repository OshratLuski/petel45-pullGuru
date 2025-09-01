<?php
// This file is part of Moodle Course Rollover Plugin
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
 * @package     local_exportgrade
 * @copyright  Devlion
 * @author      Devlion
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define('NO_DEBUG_DISPLAY', true);
define('NO_MOODLE_COOKIES', true);
require_once(__DIR__ . '/../../../config.php');
require_once(__DIR__ . '/../../../lib/moodlelib.php');
require_once(__DIR__ . '/../lib.php');
require_once($CFG->libdir . '/filelib.php');
header('Content-Type: application/json; charset: utf-8');

define('TOKENEXPIRED', 60 * 60);

$debug = local_exportgrade_init_debug();

$responce = [
    'token_type' => 'Bearer',
    'expires_in' => '',
    'access_token' => '',
    'error' => 0,
];

$responceerror = [
    'error' => 1,
    'error_message' => ''
];

$granttypes = ['client_credentials'];
try {
    $data = required_param('form_params', PARAM_AREA);
    if (empty($data['grant_type']) || !in_array($data['grant_type'], $granttypes)) {
        if ($debug) {
            mtrace('Wrong credentials: grant_type empty');
        }
        throw new Exception('Wrong credentials');
    }
    if (empty($data['client_key']) || empty($data['client_secret'])) {
        if ($debug) {
            mtrace('Wrong credentials: client_secret empty');
        }
        throw new Exception('Wrong credentials');
    }

    if (!validateCLient($data['client_key'], $data['client_secret'])) {
        if ($debug) {
            mtrace('Wrong credentials: validate client_key and client_secret client_key: ' . $data['client_key'] . " client_secret:" . $data['client_secret']);
        }
        throw new Exception('Wrong credentials');
    }

    $webservice = local_exportgrade_get_config_webservices_by_instance($CFG->instancename);
    if (empty($webservice['webservicetoken'])) {
        if ($debug) {
            mtrace('Wrong credentials: token error');
        }
        throw new Exception('Wrong credentials');
    } else {
        $responce['access_token'] = $webservice['webservicetoken'];
    }

} catch (\Exception $e) {
    $responceerror['error_message'] = 'Wrong credentials';
    if ($debug) {
        mtrace($responceerror['error_message']);
    }
    echo json_encode($responceerror);
    die();
}

echo json_encode($responce);

function validateCLient($clientkey, $clientsecret)
{
    $clients = get_config('local_exportgrade', 'clientsnumber');
    if (!empty($clients)) {
        for ($i = 0; $i < $clients; $i++) {
            $temp = [];
            $temp['clientkey'] = get_config('local_exportgrade', 'clientkey_' . $i);
            $temp['clientsecret'] = get_config('local_exportgrade', 'clientsecret_' . $i);
            $temp['clientips'] = get_config('local_exportgrade', 'clientips_' . $i);
            $temp['clientexpired'] = get_config('local_exportgrade', 'clientexpired_' . $i);
            if ($clientkey == $temp['clientkey'] && $clientsecret == $temp['clientsecret']) {
                if (!empty($temp['clientips']) && !in_array(getremoteaddr(), explode(',', $temp['clientips']))) {
                    return false;
                }
                if (!empty($temp['clientexpired']) && $temp['clientexpired'] < time()) {
                    return false;
                }
                return true;
            }
        }
    }
    return false;
}
