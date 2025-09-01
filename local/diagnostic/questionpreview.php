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
 * Redirect to question preview.
 *
 * @package    local_diagnostic
 * @copyright  2024 Devlion Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');
require_once($CFG->dirroot . '/question/editlib.php');

use mod_quiz\quiz_settings;

require_login();

$slot = optional_param('slot', 0, PARAM_INT);
$repocmid = required_param('repocmid', PARAM_INT);
$mid = required_param('mid', PARAM_INT);
$qid = optional_param('qid', 0, PARAM_INT);

list($modrec, $cmrec) = get_module_from_cmid($repocmid);

$excludedquestionids = [];

if (\local_diagnostic_external::has_custom_settings($mid)) {
    $settings = \local_diagnostic_external::get_custom_settings($mid);
    $excludedquestionids = $settings['customsettings']['excludedquestionids'];
}

if ($qid) {
    $url = new \moodle_url('/local/community/plugins/oer/previewquestion.php',
        ['id' => $qid, 'courseid' => $cmrec->course]);
    redirect($url);
    exit;
}

$quizobj = quiz_settings::create($cmrec->instance);
$quizobj->preload_questions();
$quizobj->load_questions();

$count = 1;

foreach ($quizobj->get_questions() as $question) {
    if (empty($question->stamp) || empty($question->length) || in_array($question->id, $excludedquestionids)) {
        continue;
    }
    if ($count == $slot) {
        $url = new \moodle_url('/local/community/plugins/oer/previewquestion.php',
                ['id' => $question->id, 'courseid' => $cmrec->course]);
        redirect($url);
    }
    $count++;
}

throw new moodle_exception('noquestionfound', 'local_diagnostic');