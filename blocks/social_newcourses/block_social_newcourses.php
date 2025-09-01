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

require_once($CFG->dirroot . '/blocks/social_newcourses/lib.php');

class block_social_newcourses extends block_list {
    public function init() {
        $this->title = get_string('pluginname', 'block_social_newcourses');
    }

    public function has_config() {
        return true;
    }

    /* available_to_cohort_teachers() return bool = true
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
        $this->content->items = array(
                block_social_newcourses_render_content_block()
        );
        $this->content->icons = array();

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
}
