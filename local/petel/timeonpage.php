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
 * This file processes AJAX requests and returns JSON
 *
 * This is a server part of yui permissions manager module
 *
 * @package core_role
 * @copyright 2018 Nadav Kavalerchik
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define('AJAX_SCRIPT', true);

defined('MOODLE_INTERNAL') || die;

ignore_user_abort(true);

require(__DIR__ . '/../../config.php');

$contextid = required_param('contextid', PARAM_INT);
$timespent = required_param('timespent', PARAM_INT);
$userid = required_param('userid', PARAM_INT);

if (isguestuser()) {
    die;
}

// Security.
list($context, $course, $cm) = get_context_info_array($contextid);
require_login($course->id, false, $cm);

$eventdata = array();
$eventdata['context'] = $context;
$eventdata['other']['timespent'] = $timespent;
$eventdata['courseid'] = $course->id;
$eventdata['userid'] = $userid;
$eventdata['objectid'] = $context->instanceid;

$event = \local_petel\event\timeonpage_viewed::create($eventdata)->trigger();

echo 'timespent focusing on page: ' . $timespent;
