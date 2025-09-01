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

namespace filter_hotwords;

if (class_exists('\core_filters\text_filter')) {
    class_alias('\core_filters\text_filter', 'filter_hotwords_base_text_filter');
} else {
    class_alias('\moodle_text_filter', 'filter_hotwords_base_text_filter');
}

/**
 * A Moodle text filter to embed questions from the bank in content.
 *
 * @package   filter_hotwords
 * @copyright 2024 Devlion.co
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class text_filter extends \filter_hotwords_base_text_filter {

    protected $page;

    public function setup($page, $context) {
        global $PAGE;

        static $jsloaded = false;

        if (!$jsloaded) {
            $PAGE->requires->js_call_amd('filter_hotwords/modal', 'init');
        }
        $jsloaded = true;

        $this->page = $page;

    }

    public function filter($text, array $options = []) {
        return $text;
    }
}
