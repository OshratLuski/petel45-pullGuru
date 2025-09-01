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
 * Javascript settings handler.
 *
 * @module     local_jsoneditor/usage
 * @package    local_jsoneditor
 * @copyright  2022 Devlionco <info@devlion.co>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @since      3.6
 */

define([
    'jquery', 'local_jsoneditor/jsoneditor'
], function ($, JSONEditor) {

    return {
        init: function (object, json, options = {}) {
            $.getJSON(M.cfg.wwwroot + '/local/jsoneditor/locales/he.json', function(langjson) {

                let lang = document.documentElement.lang;
                if(M.cfg.jsoneditor.lang !== undefined){
                    lang = M.cfg.jsoneditor.lang;
                }

                let direction = (lang === 'he' || lang === 'he_kids') ? 'rtl' : 'ltr';
                if(M.cfg.jsoneditor.direction !== undefined){
                    direction = M.cfg.jsoneditor.direction;
                }

                $(object).addClass('direction-' + direction);

                var defaultoptions = {
                    mode: 'tree',
                    modes: ['code', 'form', 'text', 'tree'],

                    languages: {
                        'he': langjson.he,
                    },

                    language: lang,
                };

                options.languages = defaultoptions.languages;

                if(options.mode === undefined){
                    options.mode = defaultoptions.mode;
                }

                if(options.modes === undefined){
                    options.modes = defaultoptions.modes;
                }

                if(options.language === undefined){
                    options.language = defaultoptions.language;
                }

                return new JSONEditor(object, options, json);
            });
        }
    };
});
