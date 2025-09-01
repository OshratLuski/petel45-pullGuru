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
 * Administration settings definitions for the mod_sharedgeogebra module.
 *
 * @package   mod_sharedgeogebra
 * @copyright   2019 Devlion <info@devlion.co>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$settings->add(new admin_setting_configtext('mod_sharedgeogebra/token',
    get_string('sharedgeogebra_token', 'mod_sharedgeogebra'),
    get_string('sharedgeogebra_token_desc', 'mod_sharedgeogebra'),
    'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJkYXRhIjp7ImVtYWlsIjoiZGVtbzJAZGV2bGlvbi5jbyIsImZpcnN0X25hbWUiOiJEZW1vMiIsImxhc3RfbmFtZSI6IkRldmxpb24yIn0sImlhdCI6MTU4MjgxMDAwMywiZXhwIjozMzEzOTczNjAwM30.sLsXLilgrpQ1t5e9ot-DPN50I1YGlJeEDH4duReSKKU',
    PARAM_TEXT
));

$settings->add(new admin_setting_configtext('mod_sharedgeogebra/url',
        get_string('sharedgeogebra_url', 'mod_sharedgeogebra'),
    get_string('sharedgeogebra_url_desc', 'mod_sharedgeogebra'),
    'https://geogebra.learnapp.io/',
    PARAM_TEXT
));

