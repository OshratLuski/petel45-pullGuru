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
 * Plugin version and other meta-data are defined here.
 *
 * @package     tiny_styles
 * @copyright   2025 Oshrat Luski <oshrat.luski@weizmann.ac.il>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['pluginname'] = 'Tiny Styles';
$string['stylebutton'] = 'Style';
$string['buttontitle'] = 'Style';
$string['privacy:metadata'] = 'The Styles pluging for TinyMCE does not store any personal data.';
$string['clearstyle'] = 'Clear style';
$string['settings'] = 'Configure Tiny Styles plugin';
$string['config'] = 'Custom styles configuration';
$string['config_desc'] = 'Define the available styles for this plugin in JSON format.

Each style must include:
- "title": The name displayed in the dropdown.
- "type": "block" or "inline".
- "classes": One or more CSS class names, separated by spaces.

Example:
[
    {
        "title": "Green box",
        "type": "block",
        "classes": "attostylesbox attostylesbox-outline-green"
    },
    {
        "title": "Yellow highlight",
        "type": "inline",
        "classes": "attostylestextmarker attostylestextmarker-yellow"
    }
]

The defined CSS classes must exist in your theme or be added via Additional HTML.';