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
 * Callbacks for hooks.
 *
 * @package    tool_inplacetranslate
 * @copyright  2024 Andrew Lyons <andrew@nicols.co.uk>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace tool_inplacetranslate;

use core\hook\after_config;
use core\hook\output\before_footer_html_generation;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/admin/tool/inplacetranslate/locallib.php');

/**
 * Callbacks for hooks.
 *
 * @package    tool_mfa
 * @copyright  2024 Andrew Lyons <andrew@nicols.co.uk>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class hook_callbacks {
    /**
     * Listener for the after_config hook.
     *
     * @param after_config $hook The hook instance.
     */
    public static function after_config(after_config $hook): void {

        if (during_initial_install()) {
            return;
        }

        if (is_admin_and_page_edit() && get_config('tool_inplacetranslate', 'enabled')) {
            \tool_inplacetranslate\custom_string_manager::hook();
        }
    }

    /**
     * Injects the in-place translation UI before footer HTML generation.
     *
     * @param before_footer_html_generation $hook The hook instance.
     */
    public static function before_footer_html_generation(before_footer_html_generation $hook): void {
        global $OUTPUT, $CFG;

        if (during_initial_install()) {
            return;
        }

        if (is_admin_and_page_edit() && get_config('tool_inplacetranslate', 'enabled') && !empty($CFG->langstrings)) {
            // Add plugin's own strings to the langstrings array.
            $selfstrings = [
                'searchandtranslatethewords',
                'search',
                'setnewtranslate',
                'availablelangs',
                'original',
                'cancel',
                'refresh',
                'stringwasupdated',
                'string_updated',
            ];

            foreach ($selfstrings as $string) {
                $result = get_string($string, 'tool_inplacetranslate');
                $fullstringid = $string . '/tool_inplacetranslate';
                $CFG->langstrings[$fullstringid] = [
                    'identifier' => $string,
                    'component' => 'tool_inplacetranslate',
                    'text' => $result,
                ];
            }

            // Render and inject the string menu template.
            $html = $OUTPUT->render_from_template('tool_inplacetranslate/stringmenu', [
                'langstrings' => array_values($CFG->langstrings),
            ]);
            $hook->add_html($html);
        }
    }
}