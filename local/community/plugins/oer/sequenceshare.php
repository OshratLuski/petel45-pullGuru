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
 * View the poster instance
 *
 * @package    community_oer
 * @copyright  2018 Devlion <info@devlion.co>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require(__DIR__ . '/../../../../config.php');

require_login();

// We get MID of a module that is in the OER catalog, and find its CMID.
$seqid = required_param('id', PARAM_INT);

$context = context_system::instance();
$PAGE->set_context($context);

$PAGE->set_url('/local/community/plugins/oer/sequenceshare.php', ['id' => $seqid]);

$strname = get_string('sequenceshare', 'community_oer');
$PAGE->navbar->add($strname);
$PAGE->set_title($strname);

echo $OUTPUT->header();

$sequence = new \community_oer\sequence_oer;
$obj = $sequence->query()->compare('seqid', $seqid)->compare('visible', '1');
$obj = $sequence->calculate_data_online($obj);

$data = $obj->get();

if (empty($data) || count($data) > 1) {
    throw new \moodle_exception('invalidarguments');
}

$result = [
    'blocks' => array_values($data)
];

echo $OUTPUT->render_from_template('community_oer/sequence/sequenceshare', $result);

// Event.
$eventdata = array(
        'userid' => $USER->id,
        'sequenceid' => $seqid,
        'other' => []
);
\community_oer\event\oer_sequence_share_link::create_event($eventdata)->trigger();

echo $OUTPUT->footer();
