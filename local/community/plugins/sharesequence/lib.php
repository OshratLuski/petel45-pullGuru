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
 * @package    community_sharesequence
 * @copyright  2017 Kathrin Osswald, Ulm University <kathrin.osswald@uni-ulm.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Allow plugins to provide some content to be rendered in the navbar.
 * The plugin must define a PLUGIN_render_navbar_output function that returns
 * the HTML they wish to add to the navbar.
 *
 * @return string HTML for the navbar
 */
function community_sharesequence_render_navbar_output() {
    global $PAGE, $COURSE, $USER, $CFG;

    return '';
}

function community_sharesequence_output_fragment_upload_sequence_catalog_page_1($args) {
    global $CFG, $OUTPUT;

    require_once($CFG->dirroot . '/local/community/plugins/sharesequence/upload_sequence_to_catalog.php');

    $args = (object) $args;

    $formdata = [];
    if (!empty($args->jsonformdata)) {
        $serialiseddata = json_decode($args->jsonformdata);
        parse_str($serialiseddata, $formdata);
    }

    // Prepare default data.
    $data = json_decode($args->default_data);

    if (isset($data->default_data)) {
        $data = (array) json_decode($data->default_data);
    } else {
        $data = [];
    }

    $default = [];
    $sharesequence = new sharesequence();
    $sharesequence->prepare_active_fields();

    foreach ($sharesequence->get_active_fields() as $item) {
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
                break;
            case 'originality':
                $default[$item->shortname . '_checkbox'] =
                        isset($data[$item->shortname . '_checkbox']) ? $data[$item->shortname . '_checkbox'] : '';
                $default[$item->shortname] = isset($data[$item->shortname]) ? $data[$item->shortname] : '';
                break;
            case 'multiselect':
                if ($item->multiselecttype == 'single') {
                    $default[$item->shortname] = isset($data[$item->shortname]) ? $data[$item->shortname] : '';
                }

                if ($item->multiselecttype == 'multi') {
                    $default[$item->shortname] = isset($data[$item->shortname]) ? explode(',', $data[$item->shortname]) : [];
                }
                break;
            case 'multimenu':
                $default[$item->shortname] = isset($data[$item->shortname]) ? explode(',', $data[$item->shortname]) : [];
                break;
            case 'checkbox':
                $default[$item->shortname] = isset($data[$item->shortname]) && $data[$item->shortname] == 1 ? true : false;
                break;
            case 'durationactivity':
                $default[$item->shortname] = isset($data[$item->shortname]) ? $data[$item->shortname] : 0;
                break;
            case 'textarea':
                foreach ($data as $key => $t) {
                    if (strpos($key, $item->shortname) !== false && strpos($key, '[text]') !== false) {
                        $default[$item->shortname] = $t;
                        break;
                    }
                }
                break;
        }
    }

    $formdata = array_merge($formdata, $default);

    // Upload form.
    $uploadmform = new upload_sequence_to_catalog(null, ['courseid' => $args->courseid],
            'post', '', null, true, $formdata);

    $uploadhtml = '';
    ob_start();
    $uploadmform->display();
    $uploadhtml .= ob_get_contents();
    ob_end_clean();

    $uploadhtml = str_replace('col-md-3', '', $uploadhtml);
    $uploadhtml = str_replace('col-md-9', 'col-md-12', $uploadhtml);
    $uploadhtml = str_replace('<form ', '<div ', $uploadhtml);
    $uploadhtml = str_replace('</form>', '</div>', $uploadhtml);

    // Number of sections.
    $numberofsections = get_config('community_sharesequence', 'numberofsections');
    $numberofsections = !empty($numberofsections) ? $numberofsections : 1;

    $selectedsections = '';
    if (isset($data['selected_sections']) && !empty($data['selected_sections'])) {
        $selectedsections = json_encode($data['selected_sections']);
    }

    $data = array(
            'uploadhtml' => $uploadhtml,
            'uniqueid' => time(),
            'courseid' => $args->courseid,
            'coursecontext' => $args->coursecontext,
            'number_sections' => $numberofsections,
            'selected_sections' => $selectedsections,
    );

    return $OUTPUT->render_from_template('community_sharesequence/upload_to_catalog_page_1', $data);
}

function community_sharesequence_output_fragment_upload_sequence_catalog_page_2($args) {
    global $CFG, $OUTPUT, $DB;

    $args = (object) $args;

    $formdata = [];
    if (!empty($args->jsonformdata)) {
        $serialiseddata = json_decode($args->jsonformdata);
        parse_str($serialiseddata, $formdata);
    }

    $mainsections = [];
    try {
        $course = get_course($args->courseid);
        $mainsections = \community_sharesequence\funcs::get_main_sections($course);
    } catch (Exception $e) {
        throw new \moodle_exception('error');
    }

    $data = array(
            'uniqueid' => time(),
            'courseid' => $args->courseid,
            'coursecontext' => $args->coursecontext,
            'default_data' => $args->default_data,
            'main_sections' => $mainsections
    );

    return $OUTPUT->render_from_template('community_sharesequence/upload_to_catalog_page_2', $data);
}
