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
 * block_feinberg_course main file
 *
 * @package   block_feinberg_course
 * @copyright  Matan Berkovitch <matan.berkovitch@weizmann.ac.il>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
use \block_feinberg_course\local\block_data;

defined('MOODLE_INTERNAL') || die();

class block_feinberg_course extends block_base {

    function init() {
        $this->title = get_string('pluginname', 'block_feinberg_course');
    }


    function get_content() {
        if ($this->content !== null) {
            return $this->content;
        }

        if (empty($this->instance)) {
            $this->content = '';
            return $this->content;
        }

        $this->content = new stdClass();

        $courseid = $this->page->course->id;
        $renderer = $this->page->get_renderer('block_feinberg_course');
        $records = block_data::fetch_course_data($courseid);
        $this->content->text = $renderer->display_course_data($records);
        return $this->content;
    }

    public function applicable_formats() {
        return array('all' => false, 'course-view' => true);
    }

    function instance_allow_multiple() {
        return true;
    }

    function has_config() {
        return false;
    }
}