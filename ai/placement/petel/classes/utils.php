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

namespace aiplacement_petel;

use core_ai\manager;

/**
 * AI Placement HTML editor utils.
 *
 * @package    aiplacement_petel
 * @copyright  Weizmann 2025
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class utils {

    /**
     * Check if AI Placement HTML editor action is available for the context.
     *
     * @param \context $context The context.
     * @param string $actionname The name of the action.
     * @param string $actionclass The class name of the action.
     * @return bool True if the action is available, false otherwise.
     */
    public static function is_petel_placement_action_available(
        \context $context,
        string $actionname,
        string $actionclass
    ): bool {
        [$plugintype, $pluginname] = explode('_', \core_component::normalize_componentname('aiplacement_petel'), 2);
        $manager = \core_plugin_manager::resolve_plugininfo_class($plugintype);

        if ($manager::is_plugin_enabled($pluginname)) {
            if (
                has_capability("aiplacement/petel:{$actionname}", $context)
                && manager::is_action_available($actionclass)
                && manager::is_action_enabled('aiplacement_petel', $actionclass)
            ) {
                return true;
            }
        }

        return false;
    }
}
