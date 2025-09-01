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

namespace tool_inplacetranslate;

/**
 * Nasty hack to let us force the language for one page only.
 *
 * To use this, call custom_string_manager::hook();
 *
 * @package   tool_inplacetranslate
 * @copyright 2021 Devlion <info@devlion.co>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class custom_string_manager extends \core_string_manager_standard {

    protected $stringsdisabled = [
        'locale/langconfig',
        'thisdirection/langconfig',
        'iso6391/core_langconfig',
        'asia/jerusalem/core_timezones',
    ];

    public static function hook() {
        global $CFG;

        $CFG->config_php_settings['customstringmanager'] = self::class;

        if (!isset($CFG->langstrings)) {
            $CFG->langstrings = [];
        }

        get_string_manager(true);

    }

    public function get_string($identifier, $component = 'core', $a = null, $lang = null) {
        global $CFG;

        $result = parent::get_string($identifier, $component, $a, $lang);

        $fullstringid = $identifier . '/' . $component;
        if (in_array($fullstringid, $this->stringsdisabled)) {
            return $result;
        }
        $CFG->langstrings[$fullstringid] = [
            'identifier' => $identifier,
            'component' => $component,
            'text' => $result,
        ];

        return $result;
    }
}
