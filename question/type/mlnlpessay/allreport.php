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
 * Report for all quiz attempts in the system.
 *
 * @package    qtype_mlnlpessay
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

 require_once(__DIR__ . '/../../../config.php');
 
 defined('MOODLE_INTERNAL') || die();
 
 require_once($CFG->dirroot . '/question/type/mlnlpessay/locallib.php');
 
 $limit = optional_param('limit', 20, PARAM_INT);
 $offset = optional_param('offset', 0, PARAM_INT);
 $sort = optional_param('sort', 'ASC', PARAM_TEXT);
 $col = optional_param('col', '', PARAM_TEXT);
 $search = optional_param('search', '', PARAM_TEXT);
 
 require_login();
 
 $title = get_string('allquestionreport', 'qtype_mlnlpessay');
 
 $PAGE->set_url(new moodle_url('/question/type/mlnlpessay/allreport.php'));
 $PAGE->set_title($title);
 $PAGE->set_heading($title);
 
 $context = context_system::instance();
 $PAGE->set_context($context);
 
 echo $OUTPUT->header();
 
 if (!has_capability('moodle/site:config', $context, $USER)) {
     echo $OUTPUT->notification(get_string('nopermissiontoaccesspage', 'qtype_mlnlpessay'));
     echo $OUTPUT->footer();
     return;
 }
 
 $data = qtype_mlnlpessay_get_all_question_attempts($limit, $offset, $sort, $col, $search);

 $js_data = (object) [
    'last_page' => $data->last_page,
    'coltitles' => $data->coltitles
    ];

 echo $OUTPUT->render_from_template('qtype_mlnlpessay/all_attempts_report', $data);
 $PAGE->requires->js_call_amd('qtype_mlnlpessay/allqreport', 'init', [$js_data]);
 
 echo $OUTPUT->footer();