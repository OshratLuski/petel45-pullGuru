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
 * Site recommendations for the activity chooser.
 *
 * @package    local_petel
 * @copyright  2020 Adrian Greeve
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once("../../config.php");

$context = context_system::instance();
$url = new moodle_url('/local/petel/sessiontimeout.php');

$userid = required_param('userid', PARAM_INT);

$pageheading = format_string($SITE->fullname, true, ['context' => $context]);

$PAGE->set_context($context);
$PAGE->set_url($url);
$PAGE->set_pagelayout('admin');

$PAGE->set_title(get_string('sessiontimeouttitle', 'local_petel'));
$PAGE->set_heading($pageheading);

require_login();

$sessiontimeout = new \local_petel\forms\session_timeout_form();

if ($userid != $USER->id) {
    throw new \moodle_exception('nopermission');
}

if ($sessiontimeout->is_cancelled()) {
    $redirect = new moodle_url('/user/profile.php', ['id' => $userid]);
    redirect($redirect);

} else {
    $timeout = get_user_preferences('session_timeout', 1, $userid);
    $sessiontimeout->set_data([
            'timeout' => $timeout,
            'userid' => $userid
    ]);

    if ($data = $sessiontimeout->get_data()) {
        set_user_preference('session_timeout', $data->timeout, $userid);
    }

    $strheading = get_string('sessiontimeouttitle', 'local_petel');

    echo $OUTPUT->header();
    echo $OUTPUT->heading($strheading);
    $sessiontimeout->display();
    echo $OUTPUT->footer();

}
