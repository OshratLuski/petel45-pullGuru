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
 * Site recommendations for the question chooser.
 *
 * @package local_question_chooser
 * @copyright 2022 Devlion.co
 * @author Devlion
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once("../../config.php");
require_once($CFG->dirroot . '/question/engine/bank.php');

$search = optional_param('search', '', PARAM_TEXT);

$context = context_system::instance();
$url = new moodle_url('/local/question_chooser/recommendations.php');

$pageheading = format_string($SITE->fullname, true, ['context' => $context]);

$PAGE->set_context($context);
$PAGE->set_url($url);
$PAGE->set_pagelayout('admin');

$PAGE->set_title(get_string('questionchooserrecommendations', 'local_question_chooser'));
$PAGE->set_heading($pageheading);

require_login();

$renderer = $PAGE->get_renderer('local_question_chooser', 'recommendations');

echo $renderer->header();
echo $renderer->heading(get_string('questionchooserrecommendations', 'local_question_chooser'));

$admin = get_admin();
$usercontext = context_user::instance($admin->id);
$ufservice = \core_favourites\service_factory::get_service_for_user_context($usercontext);

$result = [];
$questionbank = question_bank::get_all_qtypes();
foreach ($questionbank as $name => $qtype) {
    $data = new stdClass();
    $data->id = $data->componentname = 'qtype_' . $name;
    $data->title = $data->label = $qtype->local_name();
    $data->name = $name;
    $data->icon = $PAGE->get_renderer('question', 'bank')->qtype_icon($name);

    $recommended = $ufservice->count_favourites_by_type('core_question', 'recommend_qtype_' . $name);

    if ($recommended > 0) {
        $data->recommended = true;
    } else {
        $data->recommended = '';
    }

    $ts = trim(strtolower($search));
    if (!empty($ts)) {
        if (strpos(strtolower($data->title), $ts) !== false) {
            $result[] = $data;
        }
    } else {
        $result[] = $data;
    }
}
$questionlist = new \local_question_chooser\output\recommendations\question_list($result, $search);

echo $renderer->render_question_list($questionlist);

echo $renderer->footer();
