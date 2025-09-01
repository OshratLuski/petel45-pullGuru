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
 * Plugin internal classes, functions and constants are defined here.
 *
 * @package     metadatafieldtype_treeselect
 * @category    local
 * @copyright   2019 Devlion  <info@devlion.co
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

class class_treeselect {

    public static function build_tree($param, &$ids) {

        $result = [];
        $lang = current_language();

        foreach($param as $key => $data){

            // Title.
            if(isset($data['lang'][$lang]) && !empty($data['lang'][$lang])){
                $title = trim($data['lang'][$lang]);
            }else{
                $title = trim($key);
            }

            $tmp = [
                    "id" => $key,
                    "title" => $title,
                    "value" => $key,
            ];

            if(isset($data['branches']) && !empty($data['branches'])){
                $tmp['children'] = self::build_tree($data['branches'], $ids);
            }else{
                $tmp['children'] = [];
                $ids[] = $key;
            }

            $result[] = $tmp;

        }

        return $result;
    }

    public static function get_options($param) {

        $options = $ids = [];
        $data = json_decode($param, true);

        if(json_last_error() === JSON_ERROR_NONE){
            $options = [
                    "id" => "",
                    "title" => "",
                    "value" => "1",
                    "children" => self::build_tree($data, $ids)
                    ];
        }

        return [$options, $ids];
    }
}