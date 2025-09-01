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
 * Site recommendations for the activity chooser.
 *
 * @package    local_petel
 * @copyright  2020 Adrian Greeve
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once("../../config.php");

$context = context_system::instance();
$url = new moodle_url('/local/petel/copy_metadata_activity.php');

$pageheading = format_string($SITE->fullname, true, ['context' => $context]);

$PAGE->set_context($context);
$PAGE->set_url($url);
$PAGE->set_pagelayout('admin');

$PAGE->set_title(get_string('copymetadataactivity', 'local_petel'));
$PAGE->set_heading($pageheading);

require_login();

$form = new \local_petel\forms\copy_metadata_activity_form();

if ($form->is_cancelled()) {
    $redirect = new moodle_url('/');
    redirect($redirect);

} else {
    if ($data = $form->get_data()) {

        $soursecmid = trim($data->source_cmid);
        if (!$DB->get_record('course_modules', ['id' => $soursecmid])) {
            $soursecmid = false;
        }

        $targetcmids = [];
        foreach (explode(',', $data->target_cmids) as $cmid) {
            if ($DB->get_record('course_modules', ['id' => trim($cmid)])) {
                $targetcmids[] = trim($cmid);
            }
        }

        $mdfields = array_keys($data->mdfields);

        if ($soursecmid && !empty($targetcmids) && !empty($mdfields)) {
            foreach ($mdfields as $shortname) {
                foreach ($targetcmids as $targetcmid) {
                    $value = \local_metadata\mcontext::module()->get($soursecmid, $shortname);

                    if ($value == null || empty($value) || $value == false) {
                        \local_metadata\mcontext::module()->saveEmpty($targetcmid, $shortname);
                    } else {
                        \local_metadata\mcontext::module()->save($targetcmid, $shortname, $value);
                    }
                }
            }

            $eventdata = [];
            $eventdata['context'] = $context;
            $eventdata['userid'] = $USER->id;
            $eventdata['other']['soursecmid'] = $soursecmid;
            $eventdata['other']['targetcmids'] = $targetcmids;
            $eventdata['other']['mdfields'] = $mdfields;
            $eventdata['other']['subject'] = '';

            $eventdata['objectid'] = $context->instanceid;

            \local_petel\event\bulk_metadata_update::create($eventdata)->trigger();
        }
    }

    echo $OUTPUT->header();
    echo $OUTPUT->heading(get_string('copymetadataactivity', 'local_petel'));
    $form->display();
    echo $OUTPUT->footer();
}
