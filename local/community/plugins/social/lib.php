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
 * Local plugin "OER catalog" - Library
 *
 * @package     community_social
 * @copyright   2019 Devlion <info@devlion.co>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Allow plugins to provide some content to be rendered in the primarynav.
 * The plugin must define a PLUGIN_get_primarynav_output function that returns
 * the array with params for rendering output.
 *
 * @return array for primarynav navbar.
 */
function community_social_get_primarynav_output() {
    global $USER, $PAGE;

    if (!\community_social\funcs::has_permission($USER->id)) {
        return [];
    }

    $isactive = $PAGE->pagetype === 'local-community-plugins-social-teachers'
        || $PAGE->pagetype === 'local-community-plugins-social-profile';

    return [
        'title' => get_string('thesocialarea', 'community_social'),
        'url' => new moodle_url('/local/community/plugins/social/index.php'),
        'text' => get_string('thesocialarea', 'community_social'),
        'icon' => '',
        'isactive' => $isactive,
        'key' => 'social',
        'classes' => ['social-popup-btn'],
    ];
}