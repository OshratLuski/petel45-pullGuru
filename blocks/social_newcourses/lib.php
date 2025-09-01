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
 * @package    block_social_newcourses
 * @copyright  2019 Devlion <info@devlion.co>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/course/lib.php');

function block_social_newcourses_render_content_block() {
    global $OUTPUT;

    // Build select.
    $perpage = array(
            array('name' => get_string('onpage3', 'block_social_newcourses'), 'active' => true, 'value' => '3'),
            array('name' => get_string('onpage5', 'block_social_newcourses'), 'active' => false, 'value' => '5'),
            array('name' => get_string('onpage10', 'block_social_newcourses'), 'active' => false, 'value' => '10'),
            array('name' => get_string('onpage15', 'block_social_newcourses'), 'active' => false, 'value' => '15'),
            array('name' => get_string('onpage25', 'block_social_newcourses'), 'active' => false, 'value' => '25'),
            array('name' => get_string('onpage50', 'block_social_newcourses'), 'active' => false, 'value' => '50'),
    );

    $pagevalue = 3;
    $render = '';

    $data = new \StdClass();
    $data->perpage = $perpage;
    $data->has_select_menu = !empty($perpage) ? true : false;
    $data->content = $render;
    $data->content_empty = !empty($render) ? false : true;
    $data->page_value = $pagevalue;
    $data->pix_no_courses = $OUTPUT->image_url('courses', 'block_social_newcourses');

    return $OUTPUT->render_from_template('block_social_newcourses/layout', $data);
}

function block_social_newcourses_render_courses_content($perpage = 1) {
    global $OUTPUT, $USER, $DB, $CFG;

    $html = '';

    $coursespombim = [];

    if (class_exists('\community_social\funcs')) {

        $followers = \community_social\funcs::teachers_get_followers();
        foreach ($followers as $follower) {
            foreach ($follower->courses_pombim as $course) {

                // Exclude courses.
                $shared = $DB->get_record('community_social_shrd_crss',
                        array('userid' => $follower->userid, 'courseid' => $course->id));

                if ($course->button_send_request) {
                    $course->userid = $follower->userid;
                    $course->username = $follower->fullname;
                    $course->userimageurl = $follower->image_url;
                    $course->sharedtime = $shared->timecreated;
                    $course->countcollegues = count($course->collegues);
                    $course->currentuserid = $USER->id;

                    if (strlen($course->summary) > 200) {
                        $course->summary = mb_substr(strip_tags($course->summary), 0, 200) . '...';
                    }

                    $coursespombim[] = $course;
                }
            }
        }

        usort($coursespombim, "block_social_newcourses_cmp");

        // Perpage.
        for ($i = 0; $i < $perpage; $i++) {
            if (isset($coursespombim[$i])) {
                $html .= $OUTPUT->render_from_template('block_social_newcourses/course-item', $coursespombim[$i]);
            }
        }
    }

    return $html;
}

function block_social_newcourses_cmp($a, $b) {
    if ($a->sharedtime == $b->sharedtime) return 0;
    return ($a->sharedtime < $b->sharedtime) ? 1 : -1;
}
