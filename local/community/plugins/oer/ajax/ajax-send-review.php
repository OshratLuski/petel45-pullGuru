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
 * View the poster instance
 *
 * @package    community_oer
 * @copyright  2018 Devlion <info@devlion.co>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(__DIR__ . '/../../../../../config.php');

require_sesskey();

$context = context_system::instance();
$PAGE->set_context($context);

$post['objid'] = optional_param('objid', 0, PARAM_INT);
$post['reviewtype'] = optional_param('reviewtype', '', PARAM_TEXT);
$post['courseid'] = required_param('courseid', PARAM_INT);
$post['requestid'] = required_param('requestid', PARAM_INT);
$post['recommendation'] = optional_param('recommendation', null, PARAM_INT);
$post['reviewdata'] = required_param('reviewdata', PARAM_TEXT);
$post['errorreporting'] = optional_param('errorreporting', '', PARAM_TEXT);
$post['issuedescr'] = optional_param('issuedescr', '', PARAM_TEXT);

$result = \community_oer\reviews_oer::add_review_ajax($post, $_FILES);

// Event data.
$eventdata = array(
        'type' => $post['reviewtype'],
        'userid' => $USER->id,
        'objid' => $post['objid'],
        'content' => $post['reviewdata'],
);
\community_oer\event\oer_reviews_addmessage::create_event($eventdata)->trigger();

echo json_encode($result);
