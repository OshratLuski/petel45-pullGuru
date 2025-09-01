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

require(__DIR__ . '/../../config.php');

$fromcourseid = optional_param('fromcourseid', 0, PARAM_INT);
$tocourseid = optional_param('tocourseid', 0, PARAM_INT);

// Some security.
require_login();

if ($fromcourseid !== 0 && $tocourseid !== 0) {
    for ($courseid = $fromcourseid; $courseid <= $tocourseid; $courseid++) {
        echo "Creating special categories in courseid=" . $courseid;
        \local_petel\funcs::create_course_special_grade_categories($courseid, true);
    }
} else if ($fromcourseid !== 0) {
    echo "Creating special categories in courseid=" . $fromcourseid;
    \local_petel\funcs::create_course_special_grade_categories($fromcourseid, true);
}

echo " Finished ";
