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
 * Moodle custom "REST" client for Moodle 3.x - local_petel
 *
 * This script does not depend of any Moodle code,
 * and it can be called from a browser.
 *
 * @authorr Nadav Kavalerchik
 */

define('CLI_SCRIPT', true);

defined('MOODLE_INTERNAL') || die;

require_once(__DIR__ . '/../../../config.php');
require_once($CFG->libdir . '/filelib.php');

$CFG->debug = (E_ALL | E_STRICT);
$CFG->debugdisplay = true;
$CFG->debugdeveloper = true;
echo 'Get BBB stats...' . PHP_EOL;

$url = 'https://bbb.moodlemagic.info/bigbluebutton/api/getMeetings?checksum=eedf7ac55b7b27745b26c83f4255780f366ef692';

$curl = new \curl();
$options['CURLOPT_PROXY'] = $CFG->proxyhost . ':' . $CFG->proxyport;
$curl->setopt($options);
$bbbapixml = $curl->get($url);

$xml = simplexml_load_string($bbbapixml, "SimpleXMLElement", LIBXML_NOCDATA);
$json = json_encode($xml);
$meetingarray = json_decode($json, true);

echo 'meetings=' . count($meetingarray['meetings']);

var_dump($meetingarray);
echo PHP_EOL;
