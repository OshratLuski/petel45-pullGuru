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
 * @package    community_social
 * @copyright  2018 Devlion <info@devlion.co>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define('CLI_SCRIPT', true);
require_once(__DIR__ . '/../../../../config.php');
require_once(__DIR__ . '/lib.php');
require_once($CFG->libdir.'/clilib.php');

// Build images.
$PAGE->theme->force_svg_use(1);

//$admins = get_admins();
//$admin = null;
//foreach ($admins as $user) {
//    $admin = $user;
//    break;
//}
//
//// Emulate normal session.
//\core\cron::setup_user($admin);

$social = new \community_social\social();

$DB->execute("TRUNCATE TABLE {community_social_usr_dtls}");

foreach ($DB->get_records('user_preferences', ['name' => 'community_social_enable']) as $item) {
    $social->social_recalculate_in_db($item->userid);
}

$social->recalculate_data_in_cache();
