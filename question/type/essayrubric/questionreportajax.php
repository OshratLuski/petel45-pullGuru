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
 * system the code checker from the web.
 *
 * @package    qtype_essayrubric
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once __DIR__ . '/../../../config.php';

require_once ($CFG->dirroot . '/question/type/essayrubric/locallib.php');

$questionid = required_param('id', PARAM_INT);
$limit = optional_param('limit', 20, PARAM_INT);
$offset = optional_param('offset', 0, PARAM_INT);
$sort = optional_param('sort', 'ASC', PARAM_TEXT);
$col = optional_param('col', '', PARAM_TEXT);
$search = optional_param('search', '', PARAM_TEXT);

require_login();

$courseid = qtype_essayrubric_get_courseid($questionid);
$context = context_course::instance($courseid);

// check user capablity for course update
require_capability('moodle/course:update', $context, $USER);

$data = qtype_essayrubric_get_questionattempts_w_categories($questionid, $limit, $offset, $sort, $col, $search);

echo json_encode($data);
