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
 * This page contains navigation hooks for local_diagnostic.
 *
 * @package local_diagnostic
 * @copyright 2021 Devlion.co
 * @author Evgeniy Voevodin
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Map icons for font-awesome themes.
 */
function local_diagnostic_get_fontawesome_icon_map() {
    return [
        'local_diagnostic:i/network' => 'fa-chart-network'
    ];
}

// View image file.
function local_diagnostic_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, array $options=array()) {

    require_login();
    //require_login($course, false, $cm);

    $fs = get_file_storage();
    $file = $fs->get_file($context->id, 'local_diagnostic', $filearea, $args[0], '/', $args[1]);

    send_file($file, $args[1], 0, $forcedownload, $options);
}
