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
 * This is a one-line short description of the file
 *
 * You can have a rather longer description of the file as well,
 * if you like, and it can span multiple lines.
 *
 * @package    community_oer
 * @copyright  2018 Devlion <info@devlion.co>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../../../config.php');
require_once(__DIR__ . '/lib.php');

require_login();

$access = false;
foreach (get_admins() as $admin) {
    if ($USER->id == $admin->id) {
        $access = true;
        break;
    }
}

if (!$access) {
    throw new \moodle_exception('accessdenied', 'admin');
}

$menu = optional_param('menu', 0, PARAM_INT);
$activity = optional_param('activity', 0, PARAM_INT);
$question = optional_param('question', 0, PARAM_INT);
$sequence = optional_param('sequence', 0, PARAM_INT);
$course = optional_param('course', 0, PARAM_INT);
$ispost = optional_param('ispost', 0, PARAM_INT);

$ifsuccess = false;
if ($menu || $activity || $question || $sequence || $course) {
    $items = [];
    if ($menu) {
        $items[] = 'menu';
    }
    if ($activity) {
        $items[] = 'activity';
    }
    if ($question) {
        $items[] = 'question';
    }
    if ($sequence) {
        $items[] = 'sequence';
    }
    if ($course) {
        $items[] = 'course';
    }

    // Create task (run immediately) for recache.
    $task = new \community_oer\task\adhoc_oer();
    $task->set_custom_data(
            $items
    );
    \core\task\manager::queue_adhoc_task($task);

    $ifsuccess = true;
}

$context = context_system::instance();
$url = new moodle_url('/local/community/plugins/oer/page_recache.php');

$PAGE->set_context($context);
$PAGE->set_url($url);
$PAGE->set_pagelayout('admin');

$PAGE->set_title(get_string('cacheoercatalog', 'community_oer'));
$PAGE->set_heading(get_string('cacheoercatalog', 'community_oer'));

echo $OUTPUT->header();

if ($ispost && !$ifsuccess) {
    echo '
        <div class="alert alert-danger" role="alert">
          ' . get_string('recacheerror', 'community_oer') . '
        </div>
        ';
}

if ($ifsuccess) {
    echo '
        <div class="alert alert-success" role="alert">
          ' . get_string('recachebegin', 'community_oer') . '
        </div>
        ';
}

echo $OUTPUT->box_start();
echo $OUTPUT->container_start();

echo html_writer::start_tag('form', array('action' => $url, 'method' => 'post'));
echo html_writer::empty_tag('input', array('name' => 'ispost', 'type' => 'hidden', 'value' => 1));

echo html_writer::start_div('form-group');
$title = get_string('recachemenu', 'community_oer');
echo html_writer::checkbox('menu', 1, false, $title,
        array('title' => $title, 'class' => 'checkbox-inline mr-2'));
echo html_writer::end_div();

echo html_writer::start_div('form-group');
$title = get_string('recacheactivity', 'community_oer');
echo html_writer::checkbox('activity', 1, false, $title,
        array('title' => $title, 'class' => 'checkbox-inline mr-2'));
echo html_writer::end_div();

echo html_writer::start_div('form-group');
$title = get_string('recachequestion', 'community_oer');
echo html_writer::checkbox('question', 1, false, $title,
        array('title' => $title, 'class' => 'checkbox-inline mr-2'));
echo html_writer::end_div();

echo html_writer::start_div('form-group');
$title = get_string('recachesequence', 'community_oer');
echo html_writer::checkbox('sequence', 1, false, $title,
        array('title' => $title, 'class' => 'checkbox-inline mr-2'));
echo html_writer::end_div();

echo html_writer::start_div('form-group');
$title = get_string('recachecourse', 'community_oer');
echo html_writer::checkbox('course', 1, false, $title,
        array('title' => $title, 'class' => 'checkbox-inline mr-2'));
echo html_writer::end_div();

$label = get_string('recachebutton', 'community_oer');
echo $OUTPUT->single_button($url, $label, 'post', array('disabled' => false, 'id' => 'dfdf'));

echo html_writer::end_tag('form');

echo $OUTPUT->container_end();
echo $OUTPUT->box_end();
echo $OUTPUT->footer();
