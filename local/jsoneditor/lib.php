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
 * @package local_jsoneditor
 * @copyright 2022 Devlion.co
 * @author Devlion
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

function local_jsoneditor_render_navbar_output() {
    global $PAGE;

    $language = current_language();
    $lang = get_parent_language($language) ?: $language;

    $direction = ($lang == 'he') ? 'rtl' : 'ltr';

    $PAGE->requires->js_amd_inline('
        require(["jquery"], function($) {
            M.cfg.jsoneditor = {
                lang: "'.$lang.'",
                direction: "'.$direction.'"
            };
        });
    ');
}
