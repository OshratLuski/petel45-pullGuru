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
 *
 * @package    local_quizpreset
 * @copyright  devlion.co
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

function local_quizpreset_render_navbar_output() {
    global $PAGE, $COURSE, $USER, $DB;

    $id = optional_param('id', 0, PARAM_INT);
    $updateid = optional_param('update', 0, PARAM_INT);

    $cmid = 0;
    if ($id > 0) {
        $cmid = $id;
    } else {
        if ($updateid > 0) {
            $cmid = $updateid;
        }
    }
    // EC-596.
    if (is_siteadmin() || (class_exists('\community_oer\main_oer') && !\community_oer\main_oer::is_activity_in_research($cmid))) {
        $PAGE->requires->js_call_amd('local_quizpreset/hiddenvalues', 'init', array(
            'cmid' => $cmid
        ));

        $PAGE->requires->js_call_amd('local_quizpreset/settings', 'init');

        $PAGE->requires->js_call_amd('local_quizpreset/main', 'init');
    } elseif ($PAGE->url->compare(new moodle_url('/course/modedit.php'), URL_MATCH_BASE)) {
        $sql = 'SELECT cm.id FROM {course_modules} cm JOIN {modules} m ON (cm.module = m.id AND m.name = "quiz") WHERE cm.id = ?';
        if ($DB->get_record_sql($sql, [$cmid])) {
            $PAGE->requires->js_call_amd('local_quizpreset/quizedit', 'init');
        }
    }
}

function local_quizpreset_output_fragment_popup_preset($args) {
    global $DB, $OUTPUT, $USER;

    $args = (object) $args;

    $presetname = '';
    $teacherdescription = '';
    $studentdescription = '';

    if ($row = $DB->get_record('local_quizpreset_mystates', ['userid' => $USER->id])) {
        $presetname = $row->typename;

        $settings = json_decode($row->settings);
        $teacherdescription = isset($settings->teacher_description) ? $settings->teacher_description : '';
        $studentdescription = isset($settings->student_description) ? $settings->student_description : '';
    }

    $data = [];
    $data['presetid'] = $args->presetid;
    $data['presetname'] = $presetname;
    $data['teacher_description'] = $teacherdescription;
    $data['student_description'] = $studentdescription;

    return $OUTPUT->render_from_template('local_quizpreset/popup_preset', $data);
}
