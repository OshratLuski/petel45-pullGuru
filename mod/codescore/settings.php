<?php
// This file is part of Moodle - https://moodle.org/
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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Plugin administration pages are defined here.
 *
 * @package     mod_codescore
 * @rubric    admin
 * @copyright   2024 Devlion <info@devlion.co>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use core_admin\local\externalpage\accesscallback;
use core_reportbuilder\permission;

defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {
    if ($ADMIN->fulltree) {
        require_once("$CFG->libdir/resourcelib.php");
        $langs = array(
                '0' => 'Javascript',
                '1' => 'C++',
                '2' => 'Python',
                '3' => 'Java',
                '4' => 'GO',
                '5' => 'C#',
                '6' => 'PHP',
                '7' => 'HTML',
                '8' => 'Typescript',
                '9' => 'Visual Basic',
                '10' => 'Ruby',
                '11' => 'SQL',
                '12' => 'Assembly',
        );
        $settings->add(
                new admin_setting_configpasswordunmask(
                        'mod_codescore/apikey',
                        get_string('apikey', 'mod_codescore'),
                        get_string('apikeyexplain', 'mod_codescore'),
                        '78943f4e0d975b797de17bc980ee402037d6d2a3',
                        PARAM_TEXT
                )
        );
        $settings->add(
                new admin_setting_configtext(
                        'mod_codescore/apiurl',
                        get_string('apiurl', 'mod_codescore'),
                        get_string('apiurlexplain', 'mod_codescore'),
                        'https://apireprot.learnapp.io/LionAI/index.php',
                        PARAM_TEXT
                )
        );
        $settings->add(
                new admin_setting_configmultiselect(
                        'mod_codescore/alowedlanguages',
                        get_string('alowedlanguages', 'mod_codescore'),
                        null,
                        array('0', '1', '2', '3', '4', '5', '6', '7'),
                        $langs
                )
        );

        $settings->add(new admin_setting_configselect('mod_codescore/rubricscount', get_string('rubricscount', 'mod_codescore'), '',
                3, [1, 2, 3, 4, 5, 6, 7, 8, 9, 10]));

        $numberofcategories = (int) (get_config('mod_codescore', 'rubricscount')) + 1;

        $defaults = array(
                1 => get_string('syntaxgradetable', 'mod_codescore'),
                2 => get_string('outputgradetable', 'mod_codescore'),
                3 => get_string('problemgradetable', 'mod_codescore'),
                4 => get_string('casesgradetable', 'mod_codescore'),
        );

        $defaultvalue = (int) (100 / $numberofcategories);
        for ($i = 1; $i <= $numberofcategories; $i++) {
            $innersetting = new admin_setting_heading(
                    'rubric' . $i,
                    get_string('rubricblock', 'mod_codescore') . ' ' . (string) $i,
                    ''
            );
            $settings->add($innersetting);
            $indexname = "indextitle" . ($i - 1);
            $indexdescription = '';
            $setting = new admin_setting_description($indexname, '', $indexdescription);
            $settings->add($setting);
            // Set rubric name and tag
            $settings->add(
                    new admin_setting_configtext(
                            'mod_codescore/rubric' . $i . 'name',
                            get_string('rubricname', 'mod_codescore', $i),
                            '',
                            isset($defaults[$i]) ? $defaults[$i] : '',
                            PARAM_TEXT
                    )
            );
            $settings->add(
                    new admin_setting_configtext(
                            'mod_codescore/rubric' . $i . 'prompt',
                            get_string('rubricprompt', 'mod_codescore', $i),
                            '',
                            $i < 5 ? get_string('rubric' . $i . 'prompt', 'mod_codescore') : '',
                            PARAM_TEXT
                    )
            );
            $setting = new admin_setting_configtext(
                    'mod_codescore/' . 'maximumgrade' . $i,
                    get_string('maximumgrade'),
                    '',
                    $defaultvalue,
                    PARAM_INT);
            $settings->add($setting);

        }
    }
    $PAGE->requires->js_call_amd('mod_codescore/adminsettings', 'init', []);
}
