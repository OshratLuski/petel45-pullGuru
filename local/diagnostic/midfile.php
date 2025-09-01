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
 * Redirect to question preview.
 *
 * @package    local_diagnostic
 * @copyright  2024 Devlion Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(__DIR__ . '/../../config.php');
require_once($CFG->dirroot . '/question/editlib.php');

require_login();


$mid = required_param('mid', PARAM_INT);

$filename = get_config('local_diagnostic', 'midfile' . $mid);

$fs = get_file_storage();
$syscontext = context_system::instance();
if ($file = $fs->get_file($syscontext->id, 'local_diagnostic', 'midfile', $mid, '/', $filename)) {
    send_stored_file($file);
}