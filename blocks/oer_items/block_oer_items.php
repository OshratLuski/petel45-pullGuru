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
 * @package     block_oer_items
 * @copyright   2019 Devlion <info@devlion.co>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

class block_oer_items extends block_list {
    public function init() {
        $this->title = get_string('pluginname', 'block_oer_items');
    }

    public function has_config() {
        return true;
    }

    /* Available_to_cohort_teachers() return bool = true
     * It is a support method to allow block multiblock decide if this block will be displayed
     * block multiblock will also check if user is in the system "teachers" cohort.
    */
    public function available_to_cohort_teachers(): bool {
        return true;
    }

    public function get_content() {
        if ($this->content !== null) {
            return $this->content;
        }

        $this->content = new stdClass;
        $this->content->items = [
                $this->items_render_content_block()
        ];
        $this->content->icons = [];

        return $this->content;
    }

    /**
     * Returns the role that best describes the course list block.
     *
     * @return string
     */
    public function get_aria_role() {
        return 'navigation';
    }

    private function items_render_content_block() {
        global $OUTPUT, $DB, $USER;

        // Build select.
        $courses = $defaultcourse = [];

        // Get default course.
        $savedcourses = $DB->get_records('block_oer_items', array('userid' => $USER->id));
        foreach ($savedcourses as $item) {
            $defaultcourse[] = $item->courseid;
        }

        // Add option "All courses".
        $option = [
                'name' => get_string('selectallcourses', 'block_oer_items'),
                'courseid' => 0,
                'active' => in_array(0, $defaultcourse) ? true : false
        ];
        $courses[] = $option;

        $menumaagar = \community_oer\main_oer::structure_main_catalog();
        foreach ($menumaagar as $obj) {
            foreach ($obj['courses'] as $course) {
                $tmp = array();
                $tmp['name'] = $course->fullname;
                $tmp['courseid'] = $course->id;
                $tmp['active'] = in_array($course->id, $defaultcourse) ? true : false;
                $courses[] = $tmp;
            }
        }

        if (empty($defaultcourse)) {
            foreach ($courses as $key => $item) {
                if ($item['courseid'] == 0) {
                    $courses[$key]['active'] = true;
                }
            }
        }

        $render = '';

        $data = new \StdClass();
        $data->courses = $courses;
        $data->has_select_menu = !empty($courses) ? true : false;
        $data->content = $render;
        $data->content_empty = !empty($render) ? false : true;
        $data->pix_no_courses = $OUTPUT->image_url('courses', 'block_oer_items');

        $defaultlang = 'עברית';
        $langoptions = [];

        $langoptions[] = [
                'key' => 'all',
                'name' => get_string('selectalllanguages', 'block_oer_items'),
                'active' => 'all' == $defaultlang ? true : false
        ];

        if ($language = \local_metadata\mcontext::module()->getField('language')) {
            $res = preg_split('/\R/', $language->param1);

            foreach (array_unique($res) as $lang) {
                $langoptions[] = [
                        'key' => $lang,
                        'name' => $lang,
                        'active' => $lang == $defaultlang ? true : false
                ];
            }

        }

        $data->langoptions = $langoptions;

        return $OUTPUT->render_from_template('block_oer_items/layout', $data);
    }
}
