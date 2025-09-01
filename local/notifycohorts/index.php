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
 * Local plugin "Notify cohorts" - Page for editing the list of cohorts available to the plugin
 *
 * @package   local_notifycohorts
 * @copyright 2021 Daniel Neis Araujo <daniel@adapta.online>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__ . '/../../config.php');
require_once($CFG->libdir.'/adminlib.php');

$url = new moodle_url('/local/notifycohorts/index.php');
admin_externalpage_setup('local_notifycohorts', '', null, $url);

$title = get_string('pluginname', 'local_notifycohorts');
$PAGE->set_title($title);
$PAGE->set_heading($title);

$cohorts = $DB->get_records('cohort');
$form = new \local_notifycohorts\notification_form($url, ['availablecohorts' => $cohorts]);
$form->process();

echo $OUTPUT->header();
echo $OUTPUT->heading($title);
echo $form->render();
echo $OUTPUT->footer();
