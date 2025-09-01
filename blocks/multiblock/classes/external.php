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
 * External course API
 *
 * @package    block_multiblock_external
 * @category   external
 * @copyright  2009 Petr Skodak
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

require_once($CFG->libdir . '/externallib.php');


class block_multiblock_external extends external_api {

    public static function send_event_tab_click_parameters() {
        return new external_function_parameters(
            array(
                'type' => new external_value(PARAM_RAW, 'Type of clicked block'),
                'title' => new external_value(PARAM_RAW, 'Title of clicked block'),
            )
        );
    }

    public static function send_event_tab_click($type, $title) {
        global $USER;

        $context = \context_system::instance();
        self::validate_context($context);

        $params = self::validate_parameters(self::send_event_tab_click_parameters(),
            array(
                'type' => $type,
                'title' => $title,
            )
        );

        $eventdata = [];
        $eventdata['context'] = $context;
        $eventdata['userid'] = $USER->id;
        $eventdata['other'] = ['type' => $params['type'], 'title' => $params['title'], 'subject' => ''];
        $eventdata['objectid'] = $context->instanceid;

        \block_multiblock\event\tab_click::create($eventdata)->trigger();

        return  '';
    }

    public static function send_event_tab_click_returns() {
        return new external_value(PARAM_RAW, 'Result');
    }
}
