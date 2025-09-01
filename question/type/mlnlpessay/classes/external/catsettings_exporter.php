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
class catsettings_exporter extends exporter {

    /**
     * Return the list of properties.
     *
     * @return array
     */
    protected static function define_properties() {
        return [
            'id' => [
                'type' => PARAM_INT,
                'description' => 'Category id',
                'optional' => true,
            ],
            'name' => [
                'type' => PARAM_TEXT,
                'description' => 'Category name',
            ],
            'modelid' => [
                'type' => PARAM_NOTAGS,
                'description' => 'Model type',
                'null' => NULL_ALLOWED,
            ],
            'model' => [
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
            'langid' => [
                'type' => PARAM_INT,
                'description' => 'Cat lang id',
            ],
            'catactions' => [
                'type' => PARAM_RAW,
                'description' => 'Issuer contact',
                'null' => NULL_ALLOWED,
            ],
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
            ],
            'disabled' => [
                'type' => PARAM_TEXT,
                'description' => 'Is disabled',
            ],
            'lang' => [
                'type' => PARAM_TEXT,
                'description' => 'Lang name',
            ],
            'topic' => [
                'type' => PARAM_TEXT,
                'description' => 'Topic name',
            ],
            'subtopic' => [
                'type' => PARAM_TEXT,
                'description' => 'Subtopic name',
            ]
        ];
    }

    protected static function define_related() {
        // We cache the context so it does not need to be retrieved from the framework every time.
        return [
            'context' => '\\context',
            'activeint' => 'int',
            'disabledint' => 'int',
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
        $return = [];
        foreach (['lang'] as $field) {
            $classname = 'qtype_mlnlpessay\persistent\\' . $field  . 's';
            $persistent = $classname::get_record(['id' => $this->data->{$field . 'id'}]);
            $return[$field] = $persistent ? $persistent->get('name') : get_string('none', 'qtype_mlnlpessay');
        }

        foreach (['topic', 'subtopic'] as $field) {
            $$field = [];
            $classname = 'qtype_mlnlpessay\persistent\\categories_' . $field . 's';
            $fieldclassname = 'qtype_mlnlpessay\persistent\\' . $field . 's';
            foreach ($classname::get_records(['categoryid' => $this->data->id]) as $persistent) {
                $fieldpersistent = $fieldclassname::get_record(['id' => $persistent->get($field . 'id')]);
                $$field[] = $fieldpersistent ? $fieldpersistent->get('name') :  get_string('none', 'qtype_mlnlpessay');
            }
            $return[$field] = implode(', ', $$field);
        }
        $return['disabled'] = $this->related['disabledint'] ? get_string('yes') : get_string('no');
        $return['active'] = $OUTPUT->render_from_template('qtype_mlnlpessay/visibility_toggler', ['id' => $this->data->id, 'action' => 'categories', 'activeint' => $this->related['activeint']]);
        $return['catactions'] = $OUTPUT->render_from_template('qtype_mlnlpessay/actions', ['id' => $this->data->id, 'action' => 'categories', 'nodelete' => true]);

        return $return;
    }
}
