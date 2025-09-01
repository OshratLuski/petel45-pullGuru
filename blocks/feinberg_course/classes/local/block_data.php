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

namespace block_feinberg_course\local;
defined('MOODLE_INTERNAL') || die();
/**
* Class to fetch data from the database
 */
class block_data {
    public static function fetch_course_data($id) {
        global $DB;
        $sql = "SELECT cfield.shortname as field, cdata.value as data
                FROM {customfield_data} cdata
                JOIN {customfield_field} cfield ON cdata.fieldid = cfield.id
                WHERE cdata.instanceid = :id
                ORDER BY cfield.sortorder";
        return $DB->get_records_sql($sql, ['id' => $id]);
    }
}