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
 * Plugin administration pages are defined here.
 *
 * @package     community_social
 * @category    admin
 * @copyright   2018 Devlion <info@devlion.co>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

global $CFG, $DB;

if ($hassiteconfig) {

    $page = new admin_settingpage('community_social',
            get_string('pluginname', 'community_social', null, true));

    if ($ADMIN->fulltree) {
        $options = [];
        $options[0] = get_string('off', 'community_social');
        for ($i = 10; $i <= 200; $i+=10) {
            $options[$i] = $i;
        }

        $page->add(new admin_setting_configselect('community_social/maxuserspage', get_string('maxuserspage',
                'community_social'), '', 20,
                $options));
    }

    // Add settings page to the appearance settings category.
    $ADMIN->add('localplugins', $page);
}
