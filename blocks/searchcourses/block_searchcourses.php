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
 * Admin Bookmarks Block page.
 *
 * @package    block
 * @subpackage searchcourses
 * @copyright  University of Bath 2013
 * @author      Hittesh Ahuja
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */

/**
 * The Search Courses Autocomplete block class
 */
class block_searchcourses extends block_base
{
    
    public function init()
    {
        $this->title = get_string('pluginname', 'block_searchcourses');
    }
    
    private function autocomplete_js()
    {
        global $PAGE, $CFG;
        $autocomplete = $CFG->wwwroot . '/blocks/searchcourses/js/module.js';
        $url          = new moodle_url($autocomplete);
        return $PAGE->requires->js($url);
    }
    public function applicable_formats()
    {
        return array(
            'site' => true,
            'course-view' => true,
            'site-index' => true,
            'my' => true
        );
    }
    public function get_content()
    {
        global $CFG;
        $this->content = new stdClass();
        $params = array();
        $count = "15";
        $module = array(
            'name' => 'course_search_ac',
            'fullpath' => '/blocks/searchcourses/js/module.js'
        );
        if (!is_null($this->config)) {
            if (($count = $this->config->course_count) == ''){
                $count = '15';
            }

            $params = array(
                'course_count' => $count
            );

            $this->page->requires->data_for_js('ac_course_count', array(
                'count' => $count
            ));
        }

        $systemcontext = context_system::instance();
        $isadmin = has_capability('moodle/site:config', $systemcontext);
        if ($isadmin) {
            // Admin, can see a list of all courses.
            $mycoursesflag = 'false';
        } else {
            $mycoursesflag = 'true';
        }

        $form_html = "";
        $form_html .= $this->page->requires->js_init_call('M.search_autocomplete.init', array(
            $params
        ), false, $module);
        $form_html .= "<div id=\"course_search_ac\">";
        $form_html .= "<label for=\"ac-input\">" . get_string('searchlabelcust', 'block_searchcourses') . "</label>";
        $form_html .= "<div class=\"petel-search\">";
        $form_html .= '<input id="ac-input" class="form-control petel-search-input ml-0 w-100" type = "text" placeholder = "' . get_string('searchplaceholder', 'block_searchcourses') . '">';
        $form_html .= "<div class=\"position-absolute border-0 petel-search-icon\">
                        <svg xmlns=\"http://www.w3.org/2000/svg\" width=\"16\" height=\"16\" viewBox=\"0 0 16 16\" fill=\"none\">
                            <path d=\"M15.7824 13.8234L12.6666 10.7081C12.5259 10.5674 12.3353 10.4893 12.1353 10.4893H11.6259C12.4884 9.38631 13.001 7.99895 13.001 6.48972C13.001 2.89945 10.0914 -0.00964355 6.50048 -0.00964355C2.90959 -0.00964355 0 2.89945 0 6.48972C0 10.08 2.90959 12.9891 6.50048 12.9891C8.00996 12.9891 9.39756 12.4766 10.5008 11.6142V12.1235C10.5008 12.3235 10.5789 12.5141 10.7195 12.6547L13.8354 15.7701C14.1292 16.0638 14.6042 16.0638 14.8948 15.7701L15.7793 14.8858C16.0731 14.5921 16.0731 14.1171 15.7824 13.8234ZM6.50048 10.4893C4.29094 10.4893 2.50018 8.70201 2.50018 6.48972C2.50018 4.28056 4.28781 2.49011 6.50048 2.49011C8.71001 2.49011 10.5008 4.27744 10.5008 6.48972C10.5008 8.69888 8.71314 10.4893 6.50048 10.4893Z\" fill=\"#554283\"/>
                        </svg>
                    </div>";
        $form_html .= "</div>";
        $form_html .= "<input type=\"hidden\" id=\"my_courses_flag\"  name=\"my_courses_flag\" value=\"$mycoursesflag\"/>";
        $form_html .= "<input type=\"hidden\" id=\"course_count\" value=\"$count\" />";
        $form_html .= "</div>";
        $this->content->text = $form_html;
        return $this->content;
    }
}
