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

namespace tool_ribbons;

use core\hook\output\before_standard_footer_html_generation;
use core\hook\output\before_standard_top_of_body_html_generation;

/**
 * Allows the plugin to add any elements to the footer.
 *
 * @package    tool_ribbons
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class hook_callbacks {
    /**
     * Add the guest consent form to the top of the body.
     *
     * @param before_standard_top_of_body_html_generation $hook
     */
    public static function before_standard_top_of_body_html_generation(before_standard_top_of_body_html_generation $hook): void {
        $output = '';

        // Load the ribbons.
        $ribbons = \tool_ribbons\ribbon::all(true);

        // Display them on the page.
        foreach ($ribbons as $ribbon) {
            $output .= $ribbon->display();
        }
        $hook->add_html($output);
    }

    /**
     * Add the user policy settings link to the footer.
     *
     * @param before_standard_footer_html_generation $hook
     */
    public static function before_standard_footer_html_generation(before_standard_footer_html_generation $hook): void {
        $output = '<style type="text/css">';

        // Load the ribbons.
        $ribbons = \tool_ribbons\ribbon::all(true);

        // Display them on the page.
        foreach ($ribbons as $ribbon) {
            $output .= $ribbon->css();
        }

        $output .= '</style>';
        $hook->add_html($output);
    }
}
