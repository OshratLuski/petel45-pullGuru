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
 * system the code checker from the web.
 *
 * @package    qtype_mlnlpessay
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once __DIR__ . '/../../../config.php';

defined('MOODLE_INTERNAL') || die();

require_once ($CFG->dirroot . '/question/type/mlnlpessay/locallib.php');

$questionid = required_param('id', PARAM_INT);
$limit = optional_param('limit', 20, PARAM_INT);
$offset = optional_param('offset', 0, PARAM_INT);
$sort = optional_param('sort', 'ASC', PARAM_TEXT);
$col = optional_param('col', '', PARAM_TEXT);
$search = optional_param('search', '', PARAM_TEXT);

require_login();

$title = get_string('questionreport', 'qtype_mlnlpessay');

$courseid = qtype_mlnlpessay_get_courseid($questionid);

$context = context_course::instance($courseid);
$PAGE->set_context($context);

$urlparams = [];
$urlparams['id'] = $questionid;

$PAGE->set_url(new moodle_url('/question/type/mlnlpessay/questionreport.php'), $urlparams);
$PAGE->set_title($title);
$PAGE->set_heading($title);

echo $OUTPUT->header();

if (!has_capability('moodle/course:update', $context, $USER)) {
    echo $OUTPUT->notification(get_string('nopermissiontoaccesspage', 'qtype_mlnlpessay'));
    echo $OUTPUT->footer();
    return;
}

$data = qtype_mlnlpessay_get_questionattempts_w_categories($questionid, $limit, $offset, $sort, $col, $search);

echo $OUTPUT->render_from_template('qtype_mlnlpessay/attempts_report', $data);

$PAGE->requires->js_call_amd('qtype_mlnlpessay/qreport', 'init', [$questionid, (object) ['last_page' => $data->last_page]]);

echo $OUTPUT->footer();
