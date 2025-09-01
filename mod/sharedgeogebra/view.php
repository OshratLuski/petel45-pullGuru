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
 * Prints an instance of mod_sharedgeogebra.
 *
 * @package     mod_sharedgeogebra
 * @copyright   2019 Devlion <info@devlion.co>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__ . '/../../config.php');
require_once(__DIR__ . '/lib.php');

// Course_module ID, or.
$id = optional_param('id', 0, PARAM_INT);

// Module instance id.
$m = optional_param('m', 0, PARAM_INT);

if ($id) {
    $cm = get_coursemodule_from_id('sharedgeogebra', $id, 0, false, MUST_EXIST);
    $course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
    $moduleinstance = $DB->get_record('sharedgeogebra', array('id' => $cm->instance), '*', MUST_EXIST);
} else if ($m) {
    $moduleinstance = $DB->get_record('sharedgeogebra', array('id' => $m), '*', MUST_EXIST);
    $course = $DB->get_record('course', array('id' => $moduleinstance->course), '*', MUST_EXIST);
    $cm = get_coursemodule_from_instance('sharedgeogebra', $moduleinstance->id, $course->id, false, MUST_EXIST);
} else {
    throw new \moodle_exception(get_string('missingidandcmid', 'mod_sharedgeogebra'));
}

require_login($course, true, $cm);

$modulecontext = context_module::instance($cm->id);

$event = \mod_sharedgeogebra\event\course_module_viewed::create(array(
        'objectid' => $moduleinstance->id,
        'context' => $modulecontext,
));
$event->add_record_snapshot('course', $course);
$event->add_record_snapshot('sharedgeogebra', $moduleinstance);
$event->trigger();

$PAGE->set_url('/mod/sharedgeogebra/view.php', array('id' => $cm->id));
$PAGE->set_title(format_string($moduleinstance->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($modulecontext);

$PAGE->requires->css('/mod/sharedgeogebra/css/styles.css');
echo $OUTPUT->header();

$output = $PAGE->get_renderer('mod_sharedgeogebra');
$page = new \mod_sharedgeogebra\output\view_page($moduleinstance, $cm);
echo $output->render($page);

echo $OUTPUT->footer();
