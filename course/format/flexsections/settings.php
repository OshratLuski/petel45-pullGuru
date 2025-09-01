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
 * Settings for format_flexsections
 *
 * @package    format_flexsections
 * @copyright  2023 Marina Glancy
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

use format_flexsections\constants;

require_once("$CFG->dirroot/course/format/flexsections/lib.php");

if ($ADMIN->fulltree) {

    $settings = new admin_settingpage(
            'format_flexsections',
            get_string('settings:name', 'format_flexsections')
    );

    $url = new moodle_url('/admin/course/resetindentation.php', ['format' => 'flexsections']);
    $link = html_writer::link($url, get_string('resetindentation', 'admin'));
    $settings->add(new admin_setting_configcheckbox(
        'format_flexsections/indentation',
        new lang_string('indentation', 'format_topics'),
        new lang_string('indentation_help', 'format_topics').'<br />'.$link,
        1
    ));
    $settings->add(new admin_setting_configtext('format_flexsections/maxsectiondepth',
        get_string('maxsectiondepth', 'format_flexsections'),
        get_string('maxsectiondepthdesc', 'format_flexsections'), 10, PARAM_INT, 7));
    $settings->add(new admin_setting_configcheckbox('format_flexsections/showsection0titledefault',
        get_string('showsection0titledefault', 'format_flexsections'),
        get_string('showsection0titledefaultdesc', 'format_flexsections'), 0));
    $options = [
        constants::COURSEINDEX_FULL => get_string('courseindexfull', 'format_flexsections'),
        constants::COURSEINDEX_SECTIONS => get_string('courseindexsections', 'format_flexsections'),
        constants::COURSEINDEX_NONE => get_string('courseindexnone', 'format_flexsections'),
    ];
    $settings->add(new admin_setting_configselect('format_flexsections/courseindexdisplay',
        get_string('courseindexdisplay', 'format_flexsections'),
        get_string('courseindexdisplaydesc', 'format_flexsections'), 0, $options));
    $settings->add(new admin_setting_configcheckbox('format_flexsections/accordion',
        get_string('accordion', 'format_flexsections'),
        get_string('accordiondesc', 'format_flexsections'), 0));
    $settings->add(new admin_setting_configcheckbox('format_flexsections/cmbacklink',
        get_string('cmbacklink', 'format_flexsections'),
        get_string('cmbacklinkdesc', 'format_flexsections'), 0));

    $settings->add(new admin_setting_configselect('format_flexsections/section0',
            get_string('form:course:section0', 'format_flexsections'),
            get_string('form:course:section0_help', 'format_flexsections'),
            FORMAT_FLEXSECTIONS_SECTION0_COURSEPAGE,
            [
                    FORMAT_FLEXSECTIONS_SECTION0_COURSEPAGE => get_string('form:course:section0:coursepage', 'format_flexsections'),
                    FORMAT_FLEXSECTIONS_SECTION0_ALLPAGES => get_string('form:course:section0:allpages', 'format_flexsections')
            ]
    ));

    $settings->add(new admin_setting_configselect('format_flexsections/showsummary',
            get_string('form:course:showsummary', 'format_flexsections'),
            '',
            FORMAT_FLEXSECTIONS_SHOWSUMMARY_SHOW,
            [
                    FORMAT_FLEXSECTIONS_SHOWSUMMARY_SHOW => get_string('form:course:showsummary:show', 'format_flexsections'),
                    FORMAT_FLEXSECTIONS_SHOWSUMMARY_HIDE => get_string('form:course:showsummary:hide', 'format_flexsections'),
                    FORMAT_FLEXSECTIONS_SHOWSUMMARY_SHOWFULL => get_string('form:course:showsummary:showfull', 'format_flexsections')
            ]
    ));

    $settings->add(new admin_setting_configselect('format_flexsections/cardorientation',
            get_string('form:course:cardorientation', 'format_flexsections'),
            '',
            FORMAT_FLEXSECTIONS_ORIENTATION_VERTICAL,
            [
                    FORMAT_FLEXSECTIONS_ORIENTATION_VERTICAL => get_string('form:course:cardorientation:vertical', 'format_flexsections'),
                    FORMAT_FLEXSECTIONS_ORIENTATION_HORIZONTAL => get_string('form:course:cardorientation:horizontal', 'format_flexsections')
            ]
    ));

    $settings->add(new admin_setting_configselect('format_flexsections/showprogress',
            get_string('form:course:showprogress', 'format_flexsections'),
            '',
            FORMAT_FLEXSECTIONS_SHOWPROGRESS_SHOW,
            [
                    FORMAT_FLEXSECTIONS_SHOWPROGRESS_SHOW => get_string('form:course:showprogress:show', 'format_flexsections'),
                    FORMAT_FLEXSECTIONS_SHOWPROGRESS_HIDE => get_string('form:course:showprogress:hide', 'format_flexsections')
            ]
    ));

    $settings->add(new admin_setting_configselect('format_flexsections/progressformat',
            get_string('form:course:progressformat', 'format_flexsections'),
            '',
            FORMAT_FLEXSECTIONS_PROGRESSFORMAT_PERCENTAGE,
            [
                    FORMAT_FLEXSECTIONS_PROGRESSFORMAT_COUNT => get_string('form:course:progressformat:count', 'format_flexsections'),
                    FORMAT_FLEXSECTIONS_PROGRESSFORMAT_PERCENTAGE => get_string('form:course:progressformat:percentage', 'format_flexsections')
            ]
    ));

    $settings->add(new admin_setting_configselect('format_flexsections/progressmode',
            get_string('form:course:progressmode', 'format_flexsections'),
            '',
            FORMAT_FLEXSECTIONS_PROGRESSMODE_CIRCLE,
            [
                    FORMAT_FLEXSECTIONS_PROGRESSMODE_CIRCLE => get_string('form:course:progressmode:circle', 'format_flexsections'),
                    FORMAT_FLEXSECTIONS_PROGRESSMODE_LINE => get_string('form:course:progressmode:line', 'format_flexsections')
            ]
    ));

    $settings->add(new admin_setting_configselect('format_flexsections/sectionviewoption',
            get_string('form:course:sectionviewoption', 'format_flexsections'),
            '',
            FORMAT_FLEXSECTIONS_SECTIONVIEW_CARDS,
            [
                    FORMAT_FLEXSECTIONS_SECTIONVIEW_CARDS => get_string('form:course:sectionview:cards', 'format_flexsections'),
                    FORMAT_FLEXSECTIONS_SECTIONSVIEW_LIST => get_string('form:course:sectionview:list', 'format_flexsections')
            ]
    ));
}
