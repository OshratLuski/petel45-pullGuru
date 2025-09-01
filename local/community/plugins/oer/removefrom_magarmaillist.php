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
 * Local plugin "sandbox" - Task definition
 *
 * @package    community_oer
 * @copyright  2018 Devlion <info@devlion.co>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require("../../../../config.php");
require_once("../../../../cohort/lib.php");

global $DB, $PAGE, $USER, $CFG;

$PAGE->set_url(new moodle_url('/local/community/plugins/oer/removefrom_magarmaillist.php'));
$PAGE->set_context(context_system::instance());

require_login();
$ok = false;
$usercohorts = cohort_get_user_cohorts($USER->id);
foreach ($usercohorts as $key => $obj) {
    if ($obj->idnumber === $CFG->defaultcohortscourserequest) {
        $ok = set_user_preference('remove_from_magar_mailing_list', time(), $USER);
    }
}
$msg = ($ok) ? get_string('oerremovemsg', 'community_oer') : get_string('erroroerremovemsg', 'community_oer');
redirect(new moodle_url('/my'), $msg, 20);
