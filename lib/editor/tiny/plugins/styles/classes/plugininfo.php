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
 * TinyMCE plugininfo for the Tiny Styles plugin.
 *
 * @package     tiny_styles
 * @copyright   2025 Oshrat Luski <oshrat.luski@weizmann.ac.il>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace tiny_styles;

use editor_tiny\plugin;
use editor_tiny\plugin_with_menuitems;
use editor_tiny\plugin_with_buttons;
use editor_tiny\plugin_with_configuration;
use context;
use editor_tiny\editor;

/**
 * Plugininfo class for tiny_styles plugin.
 */
class plugininfo extends plugin implements plugin_with_menuitems, plugin_with_buttons, plugin_with_configuration {

    /**
     * Returns a list of menu items provided by this plugin.
     *
     * @return string[]
     */
    public static function get_available_menuitems(): array {
        return ['styles'];
    }

    /**
     * Returns a list of buttons provided by this plugin.
     *
     * @return string[]
     */
    public static function get_available_buttons(): array {
        return ['styles'];
    }

    /**
     * Returns configuration for the plugin for the given context.
     *
     * @param context $context
     * @param array $options
     * @param array $fpoptions
     * @param editor|null $editor
     * @return array
     */
    public static function get_plugin_configuration_for_context(
        context $context,
        array $options,
        array $fpoptions,
        ?editor $editor = null
    ): array {
        $rawjson = get_config('tiny_styles', 'configuration');
        $styles = json_decode($rawjson, true);

        if (!is_array($styles)) {
            $styles = [];
        }

        return [
            'styles' => $styles
        ];
    }
}
