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
 * This is a one-line short description of the file
 *
 * You can have a rather longer description of the file as well,
 * if you like, and it can span multiple lines.
 *
 * @package   community_social
 * @copyright 2019 Devlion <info@devlion.co>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../../../config.php');

require_login();
$PAGE->set_context(context_system::instance());

$strname = get_string('pluginname', 'community_social');
$PAGE->set_url('/local/community/plugins/social/admin_reset_request.php', array());

$PAGE->set_title($strname);

$requestid = optional_param('resetuserrequestid', null, PARAM_RAW);

// Check if admin.
$admins = get_admins();
$isadmin = false;

foreach ($admins as $admin) {
    if ($USER->id == $admin->id) {
        $isadmin = true;
        break;
    }
}

if ($isadmin && $requestid) {
    $ok = $DB->set_field('community_social_requests', 'status', '1', array('id' => $requestid));
    if ($ok) {
        echo $OUTPUT->notification('User request to be colleague teacher... was reset', 'notifysuccess');
    }
} else {
    echo 'Oups... non admin are not allowed here!';
}
