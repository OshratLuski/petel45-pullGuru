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
 * The configuration for theme_petel is defined here.
 *
 * @package     theme_petel
 * @copyright   2023 Devlion <info@devlion.co>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once(__DIR__ . '/lib.php');

$THEME->name = 'petel';
$THEME->doctype = '1';
$THEME->parents = array('boost', 'boost_union');
$THEME->sheets = array('patches', 'custom');
$THEME->enable_dock = false;

$THEME->scss = function($theme) {
    return theme_petel_get_main_scss_content($theme);
};

global $COURSE, $CFG;

// TODO update layouts

$THEME->layouts = [

        'embedded' => array(
                'file' => 'embedded.php',
                'regions' => array(),
                'defaultregion' => '',
        ),

    //// Main course page.
    //'course' => array(
    //        'file' => 'course.php',
    //        'regions' => array('side-pre'),
    //        'defaultregion' => 'side-pre',
    //        'options' => array('langmenu' => true),
    //),
    //'clean' => array(
    //        'file' => 'clean.php',
    //        'regions' => array('side-pre'),
    //        'defaultregion' => 'side-pre',
    //        'options' => array('langmenu' => true),
    //),
    //// Incourse page.
    //'incourse' => array(
    //        'file' => 'incourse.php',
    //        'regions' => array('side-pre'),
    //        'defaultregion' => 'side-pre',
    //        'options' => array('langmenu' => true),
    //),
    //// My dashboard page.
    //'mydashboard' => [
    //    'file' => 'columns2.php',
    //    'regions' => array('side-pre', 'topblocks'),
    //    'defaultregion' => 'side-pre',
    //    'options' => array('nonavbar' => true, 'langmenu' => true, 'nocontextheader' => true),
    //],
];

//if ($COURSE->id > 1) {
//    $THEME->layouts['report'] = [
//        'file' => 'course.php',
//        'regions' => array('side-pre'),
//        'defaultregion' => 'side-pre',
//        'options' => array('langmenu' => true),
//    ];
//}

// TODO set on theme settings.
$CFG->list_navbar_plugin_output_custom = [
    'sharewith',
    'oer',
    'social',
];

$THEME->rendererfactory = 'theme_overridden_renderer_factory';
$THEME->csspostprocess = 'theme_petel_process_css';
$THEME->addblockposition = BLOCK_ADDBLOCK_POSITION_FLATNAV;
// Remove redundant YUI styles.
$THEME->yuicssmodules = array('cssnormalize');
$THEME->haseditswitch = true;
$THEME->removedprimarynavitems = ['home', 'myhome','courses', 'siteadminnode'];
$THEME->iconsystem = '\\theme_petel\\output\\icon_system_fontawesome';
