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

namespace qtype_mlnlpessay\external;

defined('MOODLE_INTERNAL') || die();

use core\external\exporter;
use renderer_base;
use moodle_url;

/**
 * Class for displaying a badge issued to a user.
 *
 * @package   core_badges
 * @copyright 2018 Dani Palou <dani@moodle.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class csvupload_exporter extends exporter {

    /**
     * Return the list of properties.
     *
     * @return array
     */
    protected static function define_properties() {
        return [
            'name' => [
                'type' => PARAM_TEXT,
                'description' => 'Category name',
            ],
            'modelid' => [
                'type' => PARAM_NOTAGS,
                'description' => 'Model type',
                'null' => NULL_ALLOWED,
            ],
            'modelname' => [
                'type' => PARAM_NOTAGS,
                'description' => 'Model name',
                'null' => NULL_ALLOWED,
            ],
            'tag' => [
                'type' => PARAM_NOTAGS,
                'description' => 'Category tag',
                'null' => NULL_ALLOWED,
            ],
            'description' => [
                'type' => PARAM_RAW,
                'description' => 'Cat description',
                'optional' => true,
                'default' => 0,
            ],
            'lang' => [
                'type' => PARAM_LANG,
                'description' => 'Cat lang',
            ],
            'topics' => [
                'type' => PARAM_RAW,
                'description' => 'topics',
                'null' => NULL_ALLOWED,
            ],
            'subtopics' => [
                'type' => PARAM_RAW,
                'description' => 'topics',
                'null' => NULL_ALLOWED,
            ],
        ];
    }
}
