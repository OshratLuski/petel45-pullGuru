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
 * @package    block_pdc
 * @copyright  2019 Devlion <info@devlion.co>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/course/lib.php');

function block_pdc_render_content_block() {
    global $OUTPUT;

    // Build select.
    $sorting = array(
            array('name' => get_string('latest', 'block_pdc'), 'active' => true, 'value' => 'desc'),
            array('name' => get_string('earliest', 'block_pdc'), 'active' => false, 'value' => 'asc'),
    );

    $render = '';

    $data = new \StdClass();
    $data->sorting = $sorting;
    $data->has_select_menu = !empty($sorting) ? true : false;
    $data->content = $render;
    $data->content_empty = !empty($render) ? false : true;
    $data->pix_no_courses = $OUTPUT->image_url('courses', 'block_pdc');

    return $OUTPUT->render_from_template('block_pdc/layout', $data);
}

function block_pdc_render_courses_content($sort = 'desc') {
    global $OUTPUT, $USER, $DB, $CFG;

    $html = '';
    $studentdata = array();

    $tag = get_config('block_pdc', 'tag');
    $tag = trim($tag);

    if (!empty($tag)) {

        $query = "
        SELECT
            ti.itemid AS courseid,
            c.timecreated AS timecreated
        FROM {tag} t
        LEFT JOIN {tag_instance} ti ON (t.id = ti.tagid)
        LEFT JOIN {course} c ON (c.id = ti.itemid)
        WHERE ti.component = 'core' AND ti.itemtype = 'course' AND t.name = :tag
        GROUP BY ti.itemid
        ORDER BY c.timemodified " . $sort;

        $data = $DB->get_records_sql($query, array('tag' => $tag));

        // Check if user is student on course.
        foreach ($data as $item) {
            $context = context_course::instance($item->courseid);
            $roles = get_user_roles($context, $USER->id);

            $insertflag = false;
            foreach ($roles as $role) {
                $insertflag = true;
            }

            if ($insertflag) {
                $studentdata[] = $item;
            }
        }
    }

    // Check data.
    if (empty($studentdata)) {
        return $html;
    }

    foreach ($studentdata as $item) {

        $course = $DB->get_record('course', array('id' => $item->courseid));
        if (!empty($course)) {
            $course->courselink = $CFG->wwwroot . '/course/view.php?id=' . $course->id;
            $course->courseimage = block_pdc_get_course_image($item->courseid);

            $html .= $OUTPUT->render_from_template('block_pdc/course-item', $course);
        }
    }

    return $html;
}

function block_pdc_get_course_image($courseid) {
    global $CFG, $OUTPUT;
    require_once($CFG->libdir . '/filelib.php');

    $url = '';

    $context = context_course::instance($courseid);
    $fs = get_file_storage();
    $files = $fs->get_area_files($context->id, 'course', 'overviewfiles', 0);

    foreach ($files as $f) {
        if ($f->is_valid_image()) {
            $url = moodle_url::make_pluginfile_url($f->get_contextid(), $f->get_component(), $f->get_filearea(), null,
                    $f->get_filepath(), $f->get_filename(), false);
        }
    }

    // Default.
    if (empty($url)) {
        $url = $OUTPUT->image_url('default-square', 'block_pdc');
    }

    return $url;
}
