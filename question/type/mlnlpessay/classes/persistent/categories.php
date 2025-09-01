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
 * @package    local
 * @subpackage diagnostic
 * @copyright  2021 Devlion.co
 * @author  Evgeniy Voevodin
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace qtype_mlnlpessay\persistent;

use core\persistent;

defined('MOODLE_INTERNAL') || die();

class categories extends persistent
{
    const TABLE = 'qtype_mlnlpessay_categories';

    var string $topics;
    var string $subtopics;
    var string $lang;
    /**
     * Return the list of properties.
     *
     * @return array
     */
    protected static function define_properties()
    {
        return array(
            'name' => array(
                'type' => PARAM_TEXT,
                'null' => NULL_ALLOWED,
                'default' => ''
            ),
            'modelid' => array(
                'type' => PARAM_TEXT,
                'null' => NULL_ALLOWED,
                'default' => ''
            ),
            'model' => array(
                'type' => PARAM_TEXT,
            ),
            'tag' => array(
                'type' => PARAM_TEXT,
            ),
            'description' => array(
                'type' => PARAM_RAW,
                'null' => NULL_ALLOWED,
                'default' => ''
            ),
            'langid' => array(
                'type' => PARAM_INT,
                'default' => 0
            ),
            'active' => array(
                'type' => PARAM_INT,
                'default' => 1
            ),
            'disabled' => array(
                'type' => PARAM_INT,
                'default' => 0
            ),
        );
    }

    static function get_records_with_topics($params) {
        $records = self::get_records($params);
        foreach ($records as $record) {
            $record->get_template_data();
        }

        return $records;
    }

    function get_template_data() {
        $topics = categories_topics::get_records(['categoryid' => $this->get('id')]);
        $this->topics = implode(', ', array_map(function($topicdata) {
            $topic = topics::get_record(['id' => $topicdata->get('topicid')]);
            return $topic ? $topic->get('name') : '';
        }, $topics));
        $subtopics = categories_subtopics::get_records(['categoryid' => $this->get('id')]);
        $this->subtopics = implode(', ', array_map(function($subtopicdata) {
            $subtopic = subtopics::get_record(['id' => $subtopicdata->get('subtopicid')]);
            return $subtopic ? $subtopic->get('name') : '';
        }, $subtopics));

        $lang = langs::get_record(['id' => $this->get('langid')]);
        $this->lang = $lang ? $lang->get('name') : get_string('none', 'qtype_mlnlpessay');
    }
}


