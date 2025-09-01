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
 * Settings for the myoverview block
 *
 * @package    block_myoverview
 * @copyright  2019 Tom Dickman <tomdickman@catalyst-au.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

if ($ADMIN->fulltree) {
    require_once($CFG->dirroot . '/blocks/myoverview/lib.php');

    // Presentation options heading.
    $settings->add(new admin_setting_heading('block_myoverview/appearance',
            get_string('appearance', 'admin'),
            ''));

    // Display Course Categories on Dashboard course items (cards, lists, summary items).
    $settings->add(new admin_setting_configcheckbox(
            'block_myoverview/displaycategories',
            get_string('displaycategories', 'block_myoverview'),
            get_string('displaycategories_help', 'block_myoverview'),
            1));

    // Enable / Disable available layouts.
    $choices = array(BLOCK_MYOVERVIEW_VIEW_PETEL => get_string('petel', 'block_myoverview'),
            BLOCK_MYOVERVIEW_VIEW_CARD => get_string('card', 'block_myoverview'),
            BLOCK_MYOVERVIEW_VIEW_LIST => get_string('list', 'block_myoverview'),
            BLOCK_MYOVERVIEW_VIEW_SUMMARY => get_string('summary', 'block_myoverview'));
    $settings->add(new admin_setting_configmulticheckbox(
            'block_myoverview/layouts',
            get_string('layouts', 'block_myoverview'),
            get_string('layouts_help', 'block_myoverview'),
            $choices,
            $choices));
    unset ($choices);

    // Enable / Disable course filter items.
    $settings->add(new admin_setting_heading('block_myoverview/availablegroupings',
            get_string('availablegroupings', 'block_myoverview'),
            get_string('availablegroupings_desc', 'block_myoverview')));

    $settings->add(new admin_setting_configcheckbox(
            'block_myoverview/displaygroupingallincludinghidden',
            get_string('allincludinghidden', 'block_myoverview'),
            '',
            0));

    $settings->add(new admin_setting_configcheckbox(
            'block_myoverview/displaygroupingall',
            get_string('all', 'block_myoverview'),
            '',
            1));

    $settings->add(new admin_setting_configcheckbox(
            'block_myoverview/displaygroupinginprogress',
            get_string('inprogress', 'block_myoverview'),
            '',
            1));

    $settings->add(new admin_setting_configcheckbox(
            'block_myoverview/displaygroupingpast',
            get_string('past', 'block_myoverview'),
            '',
            1));

    $settings->add(new admin_setting_configcheckbox(
            'block_myoverview/displaygroupingfuture',
            get_string('future', 'block_myoverview'),
            '',
            1));

    $settings->add(new admin_setting_configcheckbox(
            'block_myoverview/displaygroupingcustomfield',
            get_string('customfield', 'block_myoverview'),
            '',
            0));

    $choices = \core_customfield\api::get_fields_supporting_course_grouping();
    if ($choices) {
        $choices  = ['' => get_string('choosedots')] + $choices;
        $settings->add(new admin_setting_configselect(
                'block_myoverview/customfiltergrouping',
                get_string('customfiltergrouping', 'block_myoverview'),
                '',
                '',
                $choices));
    } else {
        $settings->add(new admin_setting_configempty(
                'block_myoverview/customfiltergrouping',
                get_string('customfiltergrouping', 'block_myoverview'),
                get_string('customfiltergrouping_nofields', 'block_myoverview')));
    }
    $settings->hide_if('block_myoverview/customfiltergrouping', 'block_myoverview/displaygroupingcustomfield');

    $settings->add(new admin_setting_configcheckbox(
            'block_myoverview/displaygroupingfavourites',
            get_string('favourites', 'block_myoverview'),
            '',
            1));

    $settings->add(new admin_setting_configcheckbox(
            'block_myoverview/displaygroupinghidden',
            get_string('hiddencourses', 'block_myoverview'),
            '',
            1));

    $name = 'block_myoverview/links';
    $title = get_string('links', 'block_myoverview');
    $description = '';
    $setting = new admin_setting_heading($name, $title, $description);
    $settings->add($setting);

    $name = 'block_myoverview/badgelink';
    $title = get_string('badgelink', 'block_myoverview');
    $description = get_string('badgelinkdesc', 'block_myoverview');
    $default = 1;
    $choices = array(
            0 => get_string('hide', 'block_myoverview'),
            1 => get_string('show', 'block_myoverview'),
    );
    $settings->add(new admin_setting_configselect($name, $title, $description, $default, $choices));


    $name = 'block_myoverview/gradelink';
    $title = get_string('gradelink', 'block_myoverview');
    $description = get_string('gradelinkdesc', 'block_myoverview');
    $default = 1;
    $choices = array(
            0 => get_string('hide', 'block_myoverview'),
            1 => get_string('show', 'block_myoverview'),
    );
    $settings->add(new admin_setting_configselect($name, $title, $description, $default, $choices));


    $name = 'block_myoverview/studentlistlink';
    $title = get_string('studentlist', 'block_myoverview');
    $description = get_string('studentlistdesc', 'block_myoverview');
    $default = 1;
    $choices = array(
            0 => get_string('hide', 'block_myoverview'),
            1 => get_string('show', 'block_myoverview'),
    );
    $settings->add(new admin_setting_configselect($name, $title, $description, $default, $choices));

    $name = 'block_myoverview/links';
    $title = get_string('links', 'block_myoverview');
    $description = '';
    $setting = new admin_setting_heading($name, $title, $description);
    $settings->add($setting);

    $name = 'block_myoverview/badgelink';
    $title = get_string('badgelink', 'block_myoverview');
    $description = get_string('badgelinkdesc', 'block_myoverview');
    $default = 1;
    $choices = array(
            0 => get_string('hide'),
            1 => get_string('show', 'block_myoverview'),
    );
    $settings->add(new admin_setting_configselect($name, $title, $description, $default, $choices));


    $name = 'block_myoverview/gradelink';
    $title = get_string('gradelink', 'block_myoverview');
    $description = get_string('gradelinkdesc', 'block_myoverview');
    $default = 1;
    $choices = array(
            0 => get_string('hide'),
            1 => get_string('show', 'block_myoverview'),
    );
    $settings->add(new admin_setting_configselect($name, $title, $description, $default, $choices));


    $name = 'block_myoverview/studentlistlink';
    $title = get_string('studentlist', 'block_myoverview');
    $description = get_string('studentlistdesc', 'block_myoverview');
    $default = 1;
    $choices = array(
            0 => get_string('hide'),
            1 => get_string('show', 'block_myoverview'),
    );
    $settings->add(new admin_setting_configselect($name, $title, $description, $default, $choices));

    $name = 'block_myoverview/events';
    $title = get_string('events', 'block_myoverview');
    $description = get_string('eventsdesc', 'block_myoverview');
    $default = 1;
    $choices = array(
            0 => get_string('hide'),
            1 => get_string('show', 'block_myoverview'),
    );
    $settings->add(new admin_setting_configselect($name, $title, $description, $default, $choices));

    $name = 'block_myoverview/cache';
    $title = get_string('cache', 'block_myoverview');
    $description = '';
    $setting = new admin_setting_heading($name, $title, $description);
    $settings->add($setting);

    $name = 'block_myoverview/cacheenable';
    $title = get_string('cacheenable', 'block_myoverview');
    $description = get_string('cacheenabledesc', 'block_myoverview');
    $default = 1;
    $choices = array(
            1 => get_string('yes'),
            0 => get_string('no'),
    );
    $settings->add(new admin_setting_configselect($name, $title, $description, $default, $choices));

    $name = 'block_myoverview/cachetimeout';
    $title = get_string('cachetimeout', 'block_myoverview');
    $description = get_string('cachetimeoutdesc', 'block_myoverview');
    $default = 0;
    $settings->add(new admin_setting_configtext($name, $title, $description, $default));

    $name = 'block_myoverview/tags';
    $title = get_string('tags');
    $description = '';
    $setting = new admin_setting_heading($name, $title, $description);
    $settings->add($setting);

    $name = 'block_myoverview/excludetags';
    $title = get_string('excludetags', 'block_myoverview');
    $description = get_string('excludetagsdesc', 'block_myoverview');
    $default = '';
    $settings->add(new admin_setting_configtext($name, $title, $description, $default));
}
