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
 * Plugin capabilities are defined here.
 *
 * @package     quiz_assessmentdiscussion
 * @category    access
 * @copyright   2022 Devlion <info@devlion.co>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__ . '/../../../../config.php');

defined('MOODLE_INTERNAL') || die();

require_login();

$cmid = optional_param('cmid', 0, PARAM_INT);
$qid = optional_param('qid', 0, PARAM_INT);
$attemptid = optional_param('attemptid', 0, PARAM_INT);
$slot = optional_param('slot', 0, PARAM_INT);

if (!$cmid || !$attemptid || !$slot) {
    throw new \moodle_exception('error');
}

$preview = new \quiz_assessmentdiscussion\preview($cmid, $qid);

echo $OUTPUT->header();

echo '<div class="preview-answer-block">';
echo $preview->preview_answer($attemptid, $slot);
echo '</div>';

echo '
    <style>
        .navbar {
            display: none;
        }
    
        #page-footer{
            display: none!important;
        } 
    </style>

';

echo $OUTPUT->footer();