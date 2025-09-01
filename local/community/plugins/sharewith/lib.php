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
 * Local plugin "OER catalog" - Library
 *
 * @package    community_sharewith
 * @copyright  2018 Devlion <info@devlion.co>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use core_competency\course_module_competency;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/local/community/plugins/sharewith/classes/sharewith.php');

/**
 * Allow plugins to provide some content to be rendered in the navbar.
 * The plugin must define a PLUGIN_render_navbar_output function that returns
 * the HTML they wish to add to the navbar.
 *
 * @return string HTML for the navbar
 */
function community_sharewith_render_navbar_output() {
    global $PAGE, $USER, $COURSE;

    $output = '';

    $activitycopyenable = get_config('community_sharewith', 'activitycopy');

    $context = \context_course::instance($COURSE->id);
    $roles = get_user_roles($context, $USER->id, false);

    // Check permission copy section.
    $sectioncopyenable = false;
    if (get_config('community_sharewith', 'sectioncopy') == 1) {
        if (has_capability('community/sharewith:copysection', $context, $USER->id)) {
            $sectioncopyenable = true;
        }
        foreach ($roles as $role) {
            if ($role->shortname == 'teachercolleague') {
                $sectioncopyenable = true;
            }
        }
    }

    // Check permission.
    if (!has_capability('community/sharewith:copyactivity', $context, $USER->id)) {
        $activitycopyenable = false;
    }

    // If page editing.
    if(!$PAGE->user_is_editing()){
        //$activitycopyenable = false;
        $sectioncopyenable = false;
    }

    $teachercolleague = false;
    if (!has_capability('moodle/course:manageactivities', $context, $USER->id)) {
        $teachercolleague = false;
        foreach ($roles as $role) {
            if ($role->shortname == 'teachercolleague' || $role->shortname == 'teachertraining') {
                $teachercolleague = true;
            }
        }
    }

    if ($teachercolleague) {
        $activitycopyenable = false;
    }

    $stringman = get_string_manager();
    $strings = $stringman->load_component_strings('community_sharewith', 'en');
    $PAGE->requires->strings_for_js(array_keys($strings), 'community_sharewith');

    $params = array(
            'sectioncopyenable' => $sectioncopyenable,
            'activitycopyenable' => $activitycopyenable,
            'teachercolleague' => $teachercolleague
    );

    $PAGE->requires->js_call_amd('community_sharewith/init', 'init', array($params, $context->id));

    return $output;
}

function community_sharewith_output_fragment_upload_activity_maagar($args) {
    global $CFG, $OUTPUT, $DB, $USER;

    require_once($CFG->dirroot . '/local/community/plugins/sharewith/sharewith_form.php');

    $args = (object) $args;
    $context = $args->context;

    $formdata = [];
    if (!empty($args->jsonformdata)) {
        $serialiseddata = json_decode($args->jsonformdata);
        parse_str($serialiseddata, $formdata);
    }

    // If error.
    $sharewith = new sharewith();
    $sharewith->setactivityid($args->cmid, $args->courseid);
    $error = $sharewith->check_sharewith_error();
    if (!empty($error)) {
        $error = '<h4 class="text-danger text-center ">' . $error . '</h4>';
        return $error;
    }

    // Prepare default data.
    $data = (array) \local_metadata\mcontext::module()->get($args->cmid);

    // Default section.
    $defaultsection = [];
    $defaultcompetencies = [];
    list($oercategories, $oercourses, $oeractivities) = \community_oer\main_oer::get_main_structure_elements();

    if (isset($data['ID']) && in_array(trim($data['ID']), $oeractivities)) {
        if ($cm = $DB->get_record('course_modules', ['id' => trim($data['ID']), 'deletioninprogress' => 0])) {

            $section = $DB->get_record('course_sections', ['id' => $cm->section]);
            if ($section->section != 0) {
                try {
                    $course = get_course($cm->course);
                    $category = $DB->get_record('course_categories', ['id' => $course->category]);

                    $defaultsection = [
                            'cat_id' => $category->id,
                            'cat_name' => htmlspecialchars($category->name, ENT_QUOTES),
                            'course_id' => $course->id,
                            'course_name' => htmlspecialchars($course->fullname, ENT_QUOTES),
                            'section_id' => $section->id,
                            'section_name' => !empty($section->name) ? htmlspecialchars($section->name, ENT_QUOTES) :
                                    (get_string('sectionname', 'format_' . $course->format) . " " . $section->section)
                    ];

                    $coursemodulecompetency = new course_module_competency();
                    foreach ($coursemodulecompetency->get_records(array('cmid' => $cm->id)) as $obj) {
                        $defaultcompetencies[] = $obj->get('competencyid');
                    }
                } catch (\Exception $e) {
                    throw new \moodle_exception('error');
                }
            }
        }
    }

    $default = [];
    $sharewith->prepare_active_fields();

    foreach ($sharewith->get_active_fields() as $item) {
        switch ($item->datatype) {
            case 'text':
                $default[$item->shortname] = isset($data[$item->shortname]) ? $data[$item->shortname] : '';
                break;
            case 'menu':
                $default[$item->shortname] = isset($data[$item->shortname]) ? $data[$item->shortname] : '';
                break;
            case 'levelactivity':
                $default[$item->shortname] = isset($data[$item->shortname]) ? $data[$item->shortname] : '';
                break;
            case 'fileupload':
                $default[$item->shortname] = isset($data[$item->shortname]) ? $data[$item->shortname] : '';

                if (isset($data[$item->shortname]) && !empty($data[$item->shortname])) {
                    $cmcontext = \context_module::instance($args->cmid);
                    $usercontext = \context_user::instance($USER->id);
                    $fs = get_file_storage();

                    $files = $fs->get_area_files($cmcontext->id, 'local_metadata', 'image', $data[$item->shortname]);

                    foreach ($files as $file) {
                        if ($file->is_valid_image()) {

                            $draftitemid = file_get_unused_draft_itemid();
                            $filerecord = array(
                                    'contextid' => $usercontext->id,
                                    'component' => 'user',
                                    'filearea' => 'draft',
                                    'itemid' => $draftitemid,
                                    'filepath' => $file->get_filepath(),
                                    'filename' => $file->get_filename(),
                            );

                            $content = $file->get_content();
                            $obj = $fs->create_file_from_string($filerecord, $content);

                            $default[$item->shortname] = $obj->get_itemid();
                        }
                    }
                }

                break;
            case 'originality':
                if (isset($data['sourceurl']) && !empty($data['sourceurl'])) {
                    $default[$item->shortname . '_checkbox'] = 'true';
                    $default[$item->shortname] = $data['sourceurl'];
                } else {
                    $default[$item->shortname . '_checkbox'] = 'false';
                    $default[$item->shortname] = '';
                }
                break;
            case 'multiselect':
                if ($item->multiselecttype == 'single') {
                    $default[$item->shortname] = isset($data[$item->shortname]) ? $data[$item->shortname] : '';
                }

                if ($item->multiselecttype == 'multi') {
                    $default[$item->shortname] = isset($data[$item->shortname]) ? json_decode($data[$item->shortname]) : [];
                }
                break;
            case 'multimenu':
                $default[$item->shortname] = isset($data[$item->shortname]) ? json_decode($data[$item->shortname]) : [];
                break;
            case 'checkbox':
                $default[$item->shortname] = isset($data[$item->shortname]) && $data[$item->shortname] == 1 ? true : false;
                break;
            case 'durationactivity':
                $default[$item->shortname] = isset($data[$item->shortname]) ? $data[$item->shortname] : '';
                break;
            case 'textarea':
                $default[$item->shortname] = isset($data[$item->shortname]) ? $data[$item->shortname] : '';
                break;
        }
    }

    $formdata = array_merge($formdata, $default);

    // Upload form.
    $uploadmform = new sharewith_form(null, ['cmid' => $args->cmid, 'courseid' => $args->courseid], 'post', '',
            null, true, $formdata);

    $uploadhtml = '';
    ob_start();
    $uploadmform->display();
    $uploadhtml .= ob_get_contents();
    ob_end_clean();

    $uploadhtml = str_replace('col-md-3', '', $uploadhtml);
    $uploadhtml = str_replace('col-md-9', 'col-md-12', $uploadhtml);
    $uploadhtml = str_replace('</form>', '', $uploadhtml);

    // Number of sections.
    $numberofsections = get_config('community_sharewith', 'numberofsections');
    $numberofsections = !empty($numberofsections) ? $numberofsections : 1;

    // Check if activity in oer catalog.
    list($categories, $courses, $activities) = \community_oer\main_oer::get_main_structure_elements();
    $oercmid = \local_metadata\mcontext::module()->get($args->cmid, 'ID');

    $warningcmidpresent = in_array($args->cmid, $activities) || in_array(intval($oercmid), $activities) ? true : false;

    $data = array(
            'uploadhtml' => $uploadhtml,
            'uniqueid' => time(),
            'activity_id' => $args->cmid,
            'course_id' => $args->courseid,
            'number_sections' => $numberofsections,
            'warning_cmid_present' => $warningcmidpresent,
            'default_section' => json_encode($defaultsection),
            'default_competencies' => json_encode($defaultcompetencies)
    );

    return $OUTPUT->render_from_template('community_sharewith/uploadactivity', $data);
}