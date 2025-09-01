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
 * Plugin strings are defined here.
 *
 * @package     tiny_hotwords
 * @category    string
 * @copyright   2025 Devlion <devlion@devlion.co>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Callback to register TinyMCE plugin.
 */
function tinymce_hotwords_register_provided_editors() {
    return [
        'hotwords' => [
            'name' => 'My Tool',
            'initcallback' => 'tinymce_hotwords/init',
        ]
    ];
}

/**
 * Server side controller used by core Fragment javascript to return a moodle form html.
 * This is used for the question selection form displayed in the hotwords tinymce dialogue.
 * Reference https://docs.moodle.org/dev/Fragment.
 * Based on similar function in mod/assign/lib.php.
 *
 * @param array $args Must contain contextid
 * @return null|string
 */
function tiny_hotwords_output_fragment_form($args) {
    global $CFG;

    require_once($CFG->dirroot . '/filter/hotwords/filter.php');
    $context = context::instance_by_id($args['contextId']);

    $mform = new \filter_hotwords\form\editor_form(null, ['context' => $context]);
    $urltext = $args['urltext'];
    $currentvalue = $args['existingcode'];
    if ($currentvalue || $urltext) {
        $mform->set_data((object) ['urltext' => $urltext, 'content' => ['text' => $currentvalue]]);
    }

    return $mform->render();
}