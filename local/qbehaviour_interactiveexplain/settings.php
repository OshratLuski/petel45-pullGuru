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
 * @package     local_qbehaviour_interactiveexplain
 * @copyright   2020 Marcus Green
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {
    $settings = new admin_settingpage(
    'local_qbehaviour_interactiveexplain', get_string('pluginname', 'local_qbehaviour_interactiveexplain')
    );
    if ($ADMIN->fulltree) {
        $settings->add(new admin_setting_configcheckbox('local_qbehaviour_interactiveexplain/useeditor',
          get_string('useeditor', 'local_qbehaviour_interactiveexplain'),
          get_string('useeditor_text', 'local_qbehaviour_interactiveexplain'), 1));
        $settings->add(new admin_setting_configcheckbox('local_qbehaviour_interactiveexplain/starthidden',
          get_string('starthidden', 'local_qbehaviour_interactiveexplain'),
          get_string('starthidden_text', 'local_qbehaviour_interactiveexplain'), 1));

        $problemheader = get_string('problem_with_question_header', 'local_qbehaviour_interactiveexplain');
        $settings->add(new admin_setting_configtextarea('local_qbehaviour_interactiveexplain/problemheader',
          get_string('header', 'local_qbehaviour_interactiveexplain'),
          get_string('header_text', 'local_qbehaviour_interactiveexplain'),
          get_string('problem_with_question_header', 'local_qbehaviour_interactiveexplain'), PARAM_RAW, 100, 2),
           $problemheader);

        $problemheaderdetails = get_string('problem_with_question_header', 'local_qbehaviour_interactiveexplain');
        $settings->add(new admin_setting_configtextarea('local_qbehaviour_interactiveexplain/problemheaderdetails',
          get_string('details', 'local_qbehaviour_interactiveexplain'),
          get_string('details_text', 'local_qbehaviour_interactiveexplain'),
          get_string('problem_with_question_details', 'local_qbehaviour_interactiveexplain'), PARAM_TEXT, 100, 2),
           $problemheaderdetails);
    }
      $ADMIN->add('localplugins', $settings);

}
