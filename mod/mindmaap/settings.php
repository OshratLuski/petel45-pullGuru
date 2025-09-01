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
 * Administration settings definitions for the mod_mindmaap module.
 *
 * @package   mod_mindmaap
 * @copyright   2019 Devlion <info@devlion.co>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$settings->add(new admin_setting_configtext('mod_mindmaap/token',
    get_string('mindmaap_token', 'mod_mindmaap'),
    get_string('mindmaap_token_desc', 'mod_mindmaap'),
    'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJkYXRhIjp7ImVtYWlsIjoiY2xpZW50MkBkZXZsaW9uLmNvIiwiZmlyc3RfbmFtZSI6IkNsaWVudDIiLCJsYXN0X25hbWUiOiJDbGllbnQifSwiaWF0IjoxNTcxMjk4Nzc4LCJleHAiOjMzMTI4MjI0Nzc4fQ.RqWY0fSjbSWHyTyP_OYDuK5vMNFOqvbdWqZDWhOnyuU',
    PARAM_TEXT
));

$settings->add(new admin_setting_configtext('mod_mindmaap/url',
        get_string('mindmaap_url', 'mod_mindmaap'),
    get_string('mindmaap_url_desc', 'mod_mindmaap'),
    'https://app.mindmaap.com/',
    PARAM_TEXT
));

