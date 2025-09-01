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
 * @package moodlecore
 * @subpackage backup-helper
 * @copyright 2010 onwards Eloy Lafuente (stronk7) {@link http://stronk7.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();
require_once($CFG->dirroot . '/backup/util/xml/parser/processors/grouped_parser_processor.class.php');

/**
 * helper implementation of grouped_parser_processor that will
 * return all the information present in the course.xml file
 * accumulating it for later generation of controller->info
 *
 * TODO: Complete phpdocs
 */
class restore_course_flexsections_parser_processor extends grouped_parser_processor {

    protected $accumchunks;

    public function __construct() {
        $this->accumchunks = array();
        parent::__construct();
        // Let's add all the paths we are interested on.
        $this->add_path('/course', true); // Everything will be grouped below this.
        $this->add_path('/course/flexsectionsimages');
        $this->add_path('/course/flexsectionsimages/flexsectionsimage');
    }

    protected function dispatch_chunk($data) {
        $this->accumchunks[] = $data;
    }

    protected function notify_path_start($path) {
        // Nothing to do.
    }

    protected function notify_path_end($path) {
        // Nothing to do.
    }

    public function get_all_chunks() {
        return $this->accumchunks;
    }

}
