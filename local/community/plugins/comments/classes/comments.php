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
 * External interface library for customfields component
 *
 * @package   community_comments
 * @copyright   2019 Devlion <info@devlion.co>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/datalib.php');

class comments {

    private $cmid;
    private $userid;
    private $templatecontext;
    private $error;
    private $sort;
    private $sortdata;

    public function __construct() {
        $this->error = false;
        $this->sort = 1;

        $this->sortdata = array(
                array('value' => 1, 'name' => get_string('newest', 'community_comments'), 'selected' => ''),
                array('value' => 2, 'name' => get_string('oldest', 'community_comments'), 'selected' => ''),
                array('value' => 3, 'name' => get_string('sortab', 'community_comments'), 'selected' => ''),

        );
    }

    public function set_config_data_ajax($cmid) {
        global $USER;

        list($oercategories, $oercourses, $oeractivities) = \community_oer\main_oer::get_main_structure_elements();

        if (in_array($cmid, $oeractivities)) {
            $this->cmid = $cmid;
        } else {
            $this->error = true;
        }

        $this->userid = $USER->id;
    }

    public function set_template_context() {
        if (!$this->error) {
            $this->create_template_context();
        }
    }

    public function set_sort($sort) {
        $this->sort = $sort;
    }

    public function create_template_context() {
        global $DB;

        $this->templatecontext = array();

        switch ($this->sort) {
            case 1:
                $order = "ORDER BY c.timecreated DESC";
                break;
            case 2:
                $order = "ORDER BY c.timecreated ASC";
                break;
            case 3:
                $order = "ORDER BY c.content ASC";
                break;
            default:
                $order = "ORDER BY c.timecreated DESC";
        }

        $query = "
			SELECT c.*, u.firstname, u.lastname FROM {comments} c
			LEFT JOIN {user} u ON (u.id=c.userid)
			WHERE c.contextid=" . $this->cmid . "
			AND c.component='my_dashboard'
			AND c.commentarea='page_comments'
			" . $order . "
			";

        $comments = $DB->get_records_sql($query);

        $this->templatecontext['activityid'] = $this->cmid;
        $this->templatecontext['count_comments'] = count($comments);

        // Add time format and short style.
        $newcommments = array();
        foreach ($comments as $item) {
            $item->time_format = date("Y-m-d H:i:s", $item->timecreated);

            $arr = preg_split("/\\r\\n|\\r|\\n/", $item->content);
            $item->short_content = $arr[0];
            $item->content_count = count($arr);

            if (count($arr) > 1) {
                $item->show_bugget = true;
            } else {
                $item->show_bugget = false;
            }
            $item->content = nl2br($item->content);

            if (count($arr) > 1) {
                $item->short_content = $item->short_content . ' ...';
            }

            $newcommments[] = $item;
        }

        $this->templatecontext['comments'] = array_values($newcommments);

        $sort = array();
        foreach ($this->sortdata as $key => $item) {
            if ($this->sort == $item['value']) {
                $item['selected'] = 'selected';
            } else {
                $item['selected'] = '';
            }

            $sort[] = $item;
        }

        $this->templatecontext['sort_data_menu'] = $sort;

        return 1;
    }

    public function render_mustache() {
        global $OUTPUT;

        if ($this->error) {
            $html = '';
        } else {
            $html = $OUTPUT->render_from_template('community_comments/comments', $this->templatecontext);
        }

        return $html;
    }
}
