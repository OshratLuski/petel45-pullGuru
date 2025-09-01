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
 * Course list block.
 *
 * @package    block_sharedwithme
 * @copyright  2019 Devlion <info@devlion.co>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/course/lib.php');

function block_sharedwithme_render_content_block() {
    global $OUTPUT;

    // Build select.
    $perpage = array(
            array('name' => get_string('onpage3', 'block_sharedwithme'), 'active' => true, 'value' => '3'),
            array('name' => get_string('onpage5', 'block_sharedwithme'), 'active' => false, 'value' => '5'),
            array('name' => get_string('onpage10', 'block_sharedwithme'), 'active' => false, 'value' => '10'),
            array('name' => get_string('onpage15', 'block_sharedwithme'), 'active' => false, 'value' => '15'),
            array('name' => get_string('onpage25', 'block_sharedwithme'), 'active' => false, 'value' => '25'),
            array('name' => get_string('onpage50', 'block_sharedwithme'), 'active' => false, 'value' => '50'),
    );

    $pagevalue = 3;
    $render = '';

    $data = new \StdClass();
    $data->perpage = $perpage;
    $data->has_select_menu = !empty($perpage) ? true : false;
    $data->content = $render;
    $data->content_empty = !empty($render) ? false : true;
    $data->page_value = $pagevalue;
    $data->pix_no_courses = $OUTPUT->image_url('courses', 'block_sharedwithme');

    return $OUTPUT->render_from_template('block_sharedwithme/layout', $data);
}

function block_sharedwithme_render_courses_content($perpage = 1) {
    global $OUTPUT, $USER, $DB, $CFG;

    $html = '';
    $mycourses = [];

    // Courses from oer_course.
    $oercourses = [];

    if (class_exists('\community_oer\course_oer')) {
        $courseoer = new \community_oer\course_oer;
        foreach ($courseoer->query()->get() as $item) {
            $oercourses[] = $item->cid;
        }
    }

    $oercourses = array_unique($oercourses);

    $allowroles = ['teachercolleague'];

    foreach (enrol_get_my_courses('*', 'id DESC') as $course) {

        if (!in_array($course->id, $oercourses)) {
            $context = context_course::instance($course->id);
            if ($roles = get_user_roles($context, $USER->id)) {
                foreach ($roles as $role) {
                    if (in_array($role->shortname, $allowroles)) {

                        $course->enrol_userid = $role->modifierid;
                        $course->enrol_time = $role->timemodified;
                        $course->countcollegues = count(get_role_users($role->roleid, $context));

                        $mycourses[] = $course;
                        break;
                    }
                }
            }
        }
    }

    foreach ($mycourses as $key => $course) {
        $context = context_course::instance($course->id);

        if (!empty($course->enrol_userid)) {
            $user = $DB->get_record('user', ['id' => $course->enrol_userid]);
            $course->userid = $user->id;
            $course->username = $user->firstname . ' ' . $user->lastname;
            $course->userimageurl = $CFG->wwwroot . '/user/pix.php/' . $user->id . '/f1.jpg';
        }

        $fs = get_file_storage();
        $files = $fs->get_area_files($context->id, 'course', 'overviewfiles', 0);

        // Default image.
        $course->imageurl = $OUTPUT->image_url('default-square', 'block_sharedwithme')->out(false);

        foreach ($files as $file) {
            if ($file->is_valid_image()) {
                $course->imageurl = $CFG->wwwroot . '/pluginfile.php/' . $context->id . '/' . $file->get_component() . '/' .
                        $file->get_filearea() . '/' . $file->get_filename();
            }
        }

        $course->courseurl = $CFG->wwwroot . '/course/view.php?id=' . $course->id;

        if (strlen($course->summary) > 200) {
            $course->summary = mb_substr(strip_tags($course->summary), 0, 200) . '...';
        }
    }

    usort($mycourses, "block_sharedwithme_cmp");

    // Perpage.
    for ($i = 0; $i < $perpage; $i++) {
        if (isset($mycourses[$i])) {
            $html .= $OUTPUT->render_from_template('block_sharedwithme/course-item', $mycourses[$i]);
        }
    }

    return $html;
}

function block_sharedwithme_cmp($a, $b) {
    if ($a->enrol_time == $b->enrol_time) return 0;
    return ($a->enrol_time < $b->enrol_time) ? 1 : -1;
}
