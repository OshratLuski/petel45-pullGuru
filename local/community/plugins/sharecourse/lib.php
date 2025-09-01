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
 * @package    community_sharecourse
 * @copyright   2019 Devlion <info@devlion.co>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Allow plugins to provide some content to be rendered in the navbar.
 * The plugin must define a PLUGIN_render_navbar_output function that returns
 * the HTML they wish to add to the navbar.
 *
 * @return string HTML for the navbar
 */
function community_sharecourse_render_navbar_output() {
    global $PAGE, $COURSE, $USER, $CFG;

    $context = \context_course::instance($COURSE->id);

    require_once($CFG->dirroot . '/cohort/lib.php');

    // Check if user is member of cohort that allows to share questions to the OER question catalog.
    $availabletocohort = get_config('community_sharecourse', 'availabletocohort');
    $isadmin = is_siteadmin();

    if (cohort_is_member($availabletocohort, $USER->id) || $isadmin) {
        if (\community_oer\course_oer::funcs()::if_course_shared($COURSE->id)) {
            $visiblebuttons = 'admin-all-nooer';
        } else {
            $visiblebuttons = 'admin-all';
        }
    } else {
        $visiblebuttons = 'admin-all-nooer';
    }

    if (has_capability('community/sharecourse:coursecopy', \context_course::instance($COURSE->id), $USER->id)) {
        $PAGE->requires->js_call_amd('community_sharecourse/main', 'init', [$COURSE->id, $context->id, $visiblebuttons]);
    }

    $PAGE->requires->js_call_amd('community_sharecourse/main', 'message_edit_init', [$COURSE->id, $context->id]);

    return '';
}

/**
 * Hook function to extend the course settings navigation. Call all context functions
 *
 * @param object $parentnode
 * @param object $course
 * @param object $context
 */
function community_sharecourse_extend_navigation_course($parentnode, $course, $context) {
    global $USER, $COURSE;

    if (has_capability('community/sharecourse:coursecopy', \context_course::instance($COURSE->id), $USER->id)) {

        $flagcourse = false;
        $roles = get_user_roles(\context_course::instance($COURSE->id), $USER->id, false);
        foreach ($roles as $role) {
            if ($role->shortname == 'editingteacher') {
                $flagcourse = true;
            }
        }

        // Check if admin.
        $isadmin = is_siteadmin();

        if ($flagcourse || $isadmin) {
            $strmetadata = get_string('menucoursenode', 'community_sharecourse');

            $url = new \moodle_url('Javascript:void(0)');
            $courseduplicatenode = \navigation_node::create($strmetadata, $url, \navigation_node::TYPE_CUSTOM,
                    'courseduplicate', 'courseduplicate', new \pix_icon('t/copy', $strmetadata)
            );

            $class = 'btn-share-course';
            $courseduplicatenode->title($class);
            $courseduplicatenode->add_class($class);

            $parentnode->add_node($courseduplicatenode);
        }
    }
}

function community_sharecourse_output_fragment_upload_course_to_catalog($args) {
    global $CFG, $OUTPUT, $USER;

    require_once($CFG->dirroot . '/local/community/plugins/sharecourse/upload_course_to_catalog.php');

    $args = (object) $args;
    $context = $args->context;

    $formdata = [];
    if (!empty($args->jsonformdata)) {
        $serialiseddata = json_decode($args->jsonformdata);
        parse_str($serialiseddata, $formdata);
    }

    $default = [];
    $sharecourse = new \community_sharecourse\sharecourse();
    $sharecourse->prepare_active_fields();

    $data = (array) \local_metadata\mcontext::course()->get($args->courseid);

    // Default values.

    // Share type.
    $formdata = array_merge($formdata, ['typeshare' => $args->typeshare]);

    foreach ($sharecourse->get_active_fields() as $item) {
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
                    $cmcontext = \context_course::instance($args->courseid);
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
                $default[$item->shortname . '_checkbox'] =
                        isset($data[$item->shortname . '_checkbox']) ? $data[$item->shortname . '_checkbox'] : '';
                $default[$item->shortname] = isset($data[$item->shortname]) ? $data[$item->shortname] : '';
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
                $default[$item->shortname] = isset($data[$item->shortname]) ? $data[$item->shortname] : 0;
                break;
            case 'textarea':
                $default[$item->shortname] = isset($data[$item->shortname]) ? $data[$item->shortname] : '';
                break;
        }
    }

    $course = get_course($args->courseid);

    // Fullname.
    if (empty(trim($default['cfullname'])) && !empty(trim($course->fullname))) {
        $default['cfullname'] = trim($course->fullname);
    }

    // Description.
    if (empty(trim($default['cdescription'])) && !empty(trim($course->summary))) {
        $default['cdescription'] = trim($course->summary);
    }

    // Selector course.
    $default['selectcourses'] = isset($data['csubject']) && !empty($data['csubject']) ? explode(',', $data['csubject']) : [];

    $formdata = array_merge($formdata, $default);

    // Upload form.
    $uploadmform = new upload_course_to_catalog(null, ['courseid' => $args->courseid],
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

    $data = array(
            'uploadhtml' => $uploadhtml,
            'uniqueid' => time(),
            'courseid' => $args->courseid,
    );

    return $OUTPUT->render_from_template('community_sharecourse/upload_course_to_catalog', $data);
}
