/* eslint-disable no-undef */
/* eslint-disable no-implicit-globals */
/* eslint-disable no-unused-vars */
/* eslint-disable max-len */
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
 * Javascript main event handler.
 *
 * @module     local_petel/moodle_plugins
 * @package    local_petel
 * @copyright  2019 Devlionco <info@devlion.co>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @since      3.6
 */

define([
    'jquery',
    'core/str',
], function ($, Str) {

    return {
        mod_checklist_add_tab_settings: function (cmid) {

            Str.get_strings([
                {key: 'settings'},
            ]).done(function(strings) {
                let tag = '<li class="nav-item"><a class="nav-link" href="'+M.cfg.wwwroot+'/course/modedit.php?update='+cmid+'" title="'+strings[0]+'">'+strings[0]+'</a></li>';
                $('ul.nav-tabs li').last().after(tag);
            })
        },

        mod_questionnaire_add_tab_settings: function (cmid) {

            Str.get_strings([
                {key: 'settings'},
            ]).done(function(strings) {
                let tag = '<li class="nav-item"><a class="nav-link" href="'+M.cfg.wwwroot+'/course/modedit.php?update='+cmid+'" title="'+strings[0]+'">'+strings[0]+'</a></li>';
                $('ul.nav-tabs li').last().after(tag);
            })
        },

        mod_checklist_view_add_tab_settings: function (cmid) {

            Str.get_strings([
                {key: 'settings'},
            ]).done(function(strings) {
                let tag = '<div class="allresponses ml-2"><a class="btn btn-primary" href="'+M.cfg.wwwroot+'/course/modedit.php?update='+cmid+'" title="'+strings[0]+'">'+strings[0]+'</a></div>';
                $('.allresponses').last().after(tag);
            })
        },
    };
});