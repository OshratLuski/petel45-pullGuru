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
 * Local plugin "petel" - Task definition
 *
 * @package    local_petel
 * @copyright  2020 Nadav Kavalerchik <nadav.kavalerchik@weizmann.ac.il>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_petel\task;

defined('MOODLE_INTERNAL') || die();

require_once(__DIR__ . '/../../lib.php');

/**
 * The local_petel BBB BigBlueButton WS task class.
 *
 * @package    local_social
 * @copyright  2020 Nadav Kavalerchik <nadav.kavalerchik@weizmann.ac.il>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class adhoc_bbb extends \core\task\adhoc_task {

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
        $lockkey = 'petel_cron';
        $lockfactory = \core\lock\lock_config::get_lock_factory('local_petel_task');
        $lock = $lockfactory->get_lock($lockkey, 0);

        if ($lock !== false) {
            $this->run_cron_bbb();
            $lock->release();
        }
    }

    public function run_cron_bbb() {
        global $CFG;

        require_once($CFG->libdir . '/filelib.php');

        if (isset($CFG->bbb_server)) {
            $url = $CFG->bbb_server;
        } else {
            $url = 'https://bbb.stweizmann.org.il/bigbluebutton/api/getMeetings?checksum=eedf7ac55b7b27745b26c83f4255780f366ef692';
        }

        $curl = new \curl();
        $options['CURLOPT_PROXY'] = $CFG->proxyhost . ':' . $CFG->proxyport;
        $curl->setopt($options);
        $bbbapigetmeetingsxml = $curl->get($url);

        $xml = simplexml_load_string($bbbapigetmeetingsxml, "SimpleXMLElement", LIBXML_NOCDATA);
        $json = json_encode($xml);
        $meetingarray = json_decode($json, true);

        if($meetingarray == false){
            return false;
        }

        $bbbusersessions = 0;
        $bbbusersessionsvideo = 0;
        $bbbusersessionsaudio = 0;

        $meetingid = 0;
        $displaymeeting = [];

        if (count($meetingarray['meetings']) > 1) {
            $meetings = $meetingarray['meetings']['meeting'];
        } else {
            $meetings = $meetingarray['meetings'];
        }

        foreach ($meetings as $key => $meeting) {
            $displaymeeting[$meetingid]['meetingName'] = $meeting['meetingName'];
            $displaymeeting[$meetingid]['createDate'] = $meeting['createDate'];
            $displaymeeting[$meetingid]['running'] = $meeting['running'];
            $displaymeeting[$meetingid]['moderatorCount'] = $meeting['moderatorCount'];
            $displaymeeting[$meetingid]['participantCount'] = $meeting['participantCount'];
            $displaymeeting[$meetingid]['videoCount'] = $meeting['videoCount'];
            $displaymeeting[$meetingid]['voiceParticipantCount'] = $meeting['voiceParticipantCount'];

            $meetingid++;

            $bbbusersessions += (int) $meeting['participantCount'];
            $bbbusersessionsvideo += (int) $meeting['videoCount'];
            $bbbusersessionsaudio += (int) $meeting['voiceParticipantCount'];
        }

        $bbbrooms = count($meetingarray['meetings']);

        set_config('bbb_rooms', $bbbrooms, 'local_petel');
        set_config('bbb_usersessions', $bbbusersessions, 'local_petel');
        set_config('bbb_usersessions_video', $bbbusersessionsvideo, 'local_petel');
        set_config('bbb_usersessions_audio', $bbbusersessionsaudio, 'local_petel');
    }
}
