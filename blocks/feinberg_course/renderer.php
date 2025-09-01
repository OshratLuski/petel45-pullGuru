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
 * block_feinberg_course renderer
 *
 * @package   block_feinberg_course
 * @copyright  Matan Berkovitch <matan.berkovitch@weizmann.ac.il>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


class block_feinberg_course_renderer extends plugin_renderer_base{
    function display_course_data($records){
        $data = new stdClass();
        foreach($records as $record){
            $field = $record->field;
            $field_name = $record->field.'_feild';
            $data->$field_name = get_string($field,'block_feinberg_course');
            $data->$field = $record->data;
        }
        return $this->render_from_template('block_feinberg_course/block_course_content',
            $data);
    }
}