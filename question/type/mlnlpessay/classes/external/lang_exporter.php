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
class lang_exporter extends exporter {

    /**
     * Return the list of properties.
     *
     * @return array
     */
    protected static function define_properties() {
        return [
            'id' => [
                'type' => PARAM_INT,
                'description' => 'Lang id',
            ],
            'name' => [
                'type' => PARAM_TEXT,
                'description' => 'Lang name',
            ],
            'code' => [
                'type' => PARAM_NOTAGS,
                'description' => 'Lang code',
                'null' => NULL_ALLOWED,
            ],
            'langactions' => [
                'type' => PARAM_RAW,
                'description' => 'Issuer contact',
                'null' => NULL_ALLOWED,
            ],
        ];
    }

    protected static function define_related() {
        // We cache the context so it does not need to be retrieved from the framework every time.
        return [
            'context' => '\\context',
            'activeint' => 'int'
        ];
    }

    /**
     * Return the list of additional properties.
     *
     * @return array
     */
    protected static function define_other_properties() {
        return [
            'active' => [
                'type' => PARAM_RAW,
                'description' => 'Is active',
            ]
        ];
    }

    /**
     * Get the additional values to inject while exporting.
     *
     * @param renderer_base $output The renderer.
     * @return array Keys are the property names, values are their values.
     */
    protected function get_other_values(renderer_base $output) {
        global $OUTPUT;
        return [
            'active' => $OUTPUT->render_from_template('qtype_mlnlpessay/visibility_toggler', ['id' => $this->data->id, 'action' => 'langs', 'activeint' => $this->related['activeint']]),
            'langactions' => $OUTPUT->render_from_template('qtype_mlnlpessay/actions', ['id' => $this->data->id, 'action' => 'langs'])
        ];
    }
}
