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
 * Event observers supported by this module
 *
 * @package    community_sharequestion
 * @copyright  2018 Devlion <info@devlion.co>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Event observers supported by this module
 *
 * @package    community_sharequestion
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class community_sharequestion_observer {

    public static function update_metadata(\local_metadata\event\update_metadata $event) {

        //$cache = cache::make('community_sharequestion', 'sharequestion_cache');
        //$cachekey = 'menu_data';
        //
        //if($event->contextlevel == CONTEXT_COURSECAT){
        //
        //    // Menu data.
        //    $categoryid = local_community_get_sharequestion_categoryid();
        //    foreach (community_sharequestion_get_courses_by_tat_categories($categoryid) as $item) {
        //        if($item['cat_id'] == $event->objectid){
        //            $cache->delete($cachekey);
        //        }
        //    }
        //}
        //
        //if($event->contextlevel == CONTEXT_COURSE){
        //
        //    // Menu data.
        //    $categoryid = local_community_get_sharequestion_categoryid();
        //    foreach (community_sharequestion_get_courses_by_tat_categories($categoryid) as $item) {
        //        foreach($item['courses'] as $course){
        //            if($course->id == $event->objectid){
        //                $cache->delete($cachekey);
        //            }
        //        }
        //    }
        //}
    }

    public static function question_created(\core\event\question_created $event) {
        global $DB;

        $row = $DB->get_record('question', ['id' => $event->objectid]);

        if (!empty($row) && empty($row->idnumber)) {
            $row->idnumber = $event->objectid;
            $DB->update_record('question', $row);
        }
    }
}
