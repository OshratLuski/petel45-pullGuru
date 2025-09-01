<?php
// This file is part of Moodle - https://moodle.org/
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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Common values helper for the Moodle tiny_embedhtml plugin.
 *
 * @module      tiny_embedhtml/lib
 * @copyright   2025 Nadav Kavalerchik <nadav.kavalerchik@weizmann.ac.il>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

 function tiny_embedhtml_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, array $options = []) {

    if ($filearea !== 'content') {
        return false;
    }

    if ($context->contextlevel !== CONTEXT_COURSE &&
        $context->contextlevel !== CONTEXT_USER &&
        $context->contextlevel !== CONTEXT_SYSTEM) {
        return false;
    }


    $itemid = array_shift($args);
    if (!is_numeric($itemid)) {
        return false;
    }

    $filename = array_pop($args);
    $filepath = '/' . implode('/', $args) . '/';

    $fs = get_file_storage();
    $file = $fs->get_file($context->id, 'tiny_embedhtml', $filearea, $itemid, $filepath, $filename);

    if (!$file || $file->is_directory()) {
        return false;
    }

    send_stored_file($file, 0, 0, false, $options);
}

