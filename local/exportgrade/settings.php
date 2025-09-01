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
 * @package local_exportgrade
 * @copyright 2021 Devlion.co
 * @author Devlion
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {

    // New settings page.
    $settings = new admin_settingpage('local_exportgrade', get_string('pluginname', 'local_exportgrade'));

    $settings->add(new admin_setting_configcheckbox('local_exportgrade/debug',
        new lang_string('debug', 'local_exportgrade'),
        new lang_string('debug_desc', 'local_exportgrade'), '0'));

    $settings->add(new admin_setting_configcheckbox('local_exportgrade/smsverification',
        new lang_string('smsverification', 'local_exportgrade'),
        new lang_string('smsverification_desc', 'local_exportgrade'), '0'));


    $dboptions = [0, 1, 2, 3, 4, 5, 6, 7, 8];
    $settings->add(new admin_setting_configselect('local_exportgrade/webserviceinstances',
        new lang_string('webserviceinstances', 'local_exportgrade'),
        new lang_string('webserviceinstances_desc', 'local_exportgrade'), '0', $dboptions));


    $numbers = get_config('local_exportgrade', 'webserviceinstances');
    if ($numbers) {
        for ($i = 0; $i < $numbers; $i++) {
            $a = new stdClass();
            $a->count = $i + 1;
            $settings->add(
                new admin_setting_heading(
                    'catsegory' . $i,
                    get_string('instancenamecategory', 'local_exportgrade', $a),
                    ""
                )
            );

            $settings->add(
                new admin_setting_configtext(
                    'local_exportgrade/instancename_' . $i,
                    get_string('instancename', 'local_exportgrade', $a),
                    get_string('instancename_desc', 'local_exportgrade'),
                    ''
                )
            );

            $settings->add(
                new admin_setting_configtext(
                    'local_exportgrade/webservicename_' . $i,
                    get_string('webservicename', 'local_exportgrade', $a),
                    get_string('webservicename_desc', 'local_exportgrade'),
                    ''
                )
            );

            $settings->add(
                new admin_setting_configtext(
                    'local_exportgrade/webserviceurl_' . $i,
                    get_string('webserviceurl', 'local_exportgrade', $a),
                    get_string('webserviceurl_desc', 'local_exportgrade'),
                    $CFG->wwwroot
                )
            );

            $settings->add(
                new admin_setting_configtext(
                    'local_exportgrade/webservicetoken_' . $i,
                    get_string('webservicetoken', 'local_exportgrade', $a),
                    get_string('webservicetoken_desc', 'local_exportgrade'),
                    ""
                )
            );

            $settings->add(
                new admin_setting_heading(
                    'space',
                    '    ',
                    "  "
                )
            );


        }
    }

    $dboptions = [0, 1, 2, 3];
    $settings->add(new admin_setting_configselect('local_exportgrade/clientsnumber',
        new lang_string('clientsnumber', 'local_exportgrade'),
        new lang_string('clientsnumber_desc', 'local_exportgrade'), '0', $dboptions));
    $numbers = get_config('local_exportgrade', 'clientsnumber');

    if ($numbers) {
        for ($i = 0; $i < $numbers; $i++) {
            $a = new stdClass();
            $a->count = $i + 1;
            $settings->add(
                new admin_setting_heading(
                    'category' . $i,
                    get_string('clientcategory', 'local_exportgrade', $a),
                    ''
                )
            );

            $settings->add(
                new admin_setting_configtext(
                    'local_exportgrade/clientkey_' . $i,
                    get_string('clientkey', 'local_exportgrade', $a),
                    get_string('clientkey_desc', 'local_exportgrade'), ''
                )
            );

            $settings->add(
                new admin_setting_configtext(
                    'local_exportgrade/clientsecret_' . $i,
                    get_string('clientsecret', 'local_exportgrade', $a),
                    get_string('clientsecret_desc', 'local_exportgrade'), md5(time() * 44)
                )
            );

            $settings->add(
                new admin_setting_configtext(
                    'local_exportgrade/clientips_' . $i,
                    get_string('clientips', 'local_exportgrade', $a),
                    get_string('clientips_desc', 'local_exportgrade'), '')

            );

            $settings->add(
                new admin_setting_configtext(
                    'local_exportgrade/clientexpired_' . $i,
                    get_string('clientexpired', 'local_exportgrade', $a),
                    get_string('clientexpired_desc', 'local_exportgrade'), '')

            );

            $settings->add(
                new admin_setting_heading(
                    'space',
                    '    ',
                    "  "
                )
            );
        }
    }

    $ADMIN->add('localplugins', $settings);
}