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

require_once('../../../../config.php');
require_login();

/**
 * This page consists of data for annotator.mustache. These definitions are used in annotator.php.
 *
 * @package    qtype_essayannotate
 * @copyright  2024 IIT Palakkad
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author     Nideesh N, VM Sreeram
 *    Moved functions inside the class qtype_essayannotate_annotator_mustache_config.
 */
class qtype_essayannotate_annotator_mustache_config {
    /**
     * Generates and returns the data for annotator.mustache.
     *
     * @return stdClass The data for annotator.mustache.
     * @copyright  2024 IIT Palakkad
     * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
     * @package qtype_essayannotate
     */
    public static function get_annotator_mustache_config() {
        $annotatormustacheconfig = new stdClass();

        $annotatormustacheconfig->font_sizes = [
            ['value' => '10'],
            ['value' => '12'],
            ['value' => '16', 'selected' => true],
            ['value' => '18'],
            ['value' => '24'],
            ['value' => '32'],
            ['value' => '48'],
            ['value' => '64'],
            ['value' => '72'],
            ['value' => '108'],
        ];
        $annotatormustacheconfig->color_tools = [
            ['color' => 'rgb(0, 0, 0)', 'active' => true],
            ['color' => 'rgb(251, 17, 17)'],
            ['color' => 'rgb(2, 2, 182)'],
            ['color' => 'rgb(13, 93, 13)'],
            ['color' => 'rgb(255, 255, 0)'],
        ];
        $annotatormustacheconfig->other_tools = [
            ['other_tools_id' => 'select', 'icon_class' => 'fa fa-hand-pointer-o', 'tooltip' => 'Select', 'selected' => true],
            ['other_tools_id' => 'pencil', 'icon_class' => 'fa fa-pencil', 'tooltip' => 'Pen'],
            ['other_tools_id' => 'text', 'icon_class' => 'fa fa-font', 'tooltip' => 'Add Text'],
            ['other_tools_id' => 'rectangle', 'icon_class' => 'fa fa-square-o', 'tooltip' => 'Highlight Box'],
        ];
        $annotatormustacheconfig->other_tools_extra = [
            ['select_danger' => true, 'other_tools_extra_id' => 'deletebtn', 'icon_class' => 'fa fa-trash'],
            ['select_light' => true, 'other_tools_extra_id' => 'savebtn', 'icon_class' => 'fa fa-save',
                'select_savenexit' => true, 'save_exit' => get_string('save_exit', 'qtype_essayannotate')],
        ];

        $annotatormustacheconfig->annotator_text = get_string('annotator', 'qtype_essayannotate');
        $annotatormustacheconfig->font_size = get_string('font_size', 'qtype_essayannotate');
        $annotatormustacheconfig->file_saved_message_text = get_string('file_saved_message', 'qtype_essayannotate');
        $annotatormustacheconfig->file_not_saved_message_text = get_string('file_not_saved_message', 'qtype_essayannotate');
        return $annotatormustacheconfig;
    }
}
