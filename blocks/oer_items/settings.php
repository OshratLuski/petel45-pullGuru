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
 * Course list block settings
 *
 * @package    block_oer_items
 * @copyright  2019 Devlion <info@devlion.co>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

if ($ADMIN->fulltree) {

    $options = [];
    $options[0] = get_string('none');
    $options[1] = get_string('month');
    $options[2] = get_string('twomonths', 'block_oer_items');
    $options[3] = '3 ' . get_string('months');
    $options[4] = '4 ' . get_string('months');
    $options[5] = '5 ' . get_string('months');
    $options[6] = '6 ' . get_string('months');
    $options[7] = '7 ' . get_string('months');
    $options[8] = '8 ' . get_string('months');
    $options[9] = '9 ' . get_string('months');
    $options[10] = '10 ' . get_string('months');
    $options[11] = '11 ' . get_string('months');
    $options[12] = '12 ' . get_string('months');

    $settings->add(new admin_setting_configselect('block_oer_items/range', get_string('settingsrange',
            'block_oer_items'), '', 2, $options));
}
