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
 * Javascript controller for the aside blocks.
 *
 * @module     theme_petel/course_search
 * @package
 * @copyright  2023 Devlion <info@devlion.co>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @since      3.7
 */

define([
    'jquery',
    'core/ajax',
    'core/str',
    'core/notification',
    'jqueryui',
], function($, Ajax, Str, Notification) {

    let actimer;

    let strnoresults;
    let strfoundxresults;

    Str.get_strings([
        {key: 'noresults', component: 'theme_petel'},
        {key: 'foundxresults', component: 'theme_petel'}
    ]).done(function(strings) {
        strnoresults = strings[0];
        strfoundxresults = strings[1];
        }).fail(Notification.exception);

    // Copied from lib/jquery/ui-1.12.1/jquery-ui.js
    $.widget("ui.autocomplete", $.ui.autocomplete, {
        options: {
            messages: {
                noResults: strnoresults,
                results: function(amount) {
                    return amount + strfoundxresults;
                }
            }
        },

        __response: function(content) {
            var message;
            this._superApply(arguments);
            if (this.options.disabled || this.cancelSearch) {
                return;
            }
            if (content && content.length) {
                message = this.options.messages.results(content.length);
            } else {
                message = this.options.messages.noResults;
            }
            this.liveRegion.children().hide();
            $("<div>").text(message).appendTo(this.liveRegion);
        }
    });

    var widgetsAutocomplete = $.ui.autocomplete;

    const Selector = {
        TOP_PAGE_ELEMENT: 'body',
        PAGE_HEADER: '#page-header',
        SEARCH_INPUT: '#course_activity_search-input',
        MENU_ITEM: '.ui-menu-item-wrapper',
    };

    const Class = {
        AUTOCOMPLETE: 'ui-autocomplete',
        AUTOCOMPLETE_PETEL: 'ui-autocomplete-petel ml-auto'
    };

    const getData = function(request, response) {

        const courseid = $(Selector.SEARCH_INPUT).data('courseid');

        Ajax.call([{
            methodname: 'theme_petel_course_search',
            args: {
                'courseid': courseid,
                'term': request.term
            },
            done: function(r) {
                const res = JSON.parse(r);
                if (res.response > 0) {
                    response(res.data);
                } else {
                    Str.get_string('noresults', 'theme_petel')
                    .done(function(s) {
                        response([]);
                        // Response([{
                        //     url: "#",
                        //     name: s,
                        // }]);
                    });
                }
            },
            fail: Notification.exception
        }]);
    };

    const bindSearch = function() {
        $(Selector.SEARCH_INPUT).autocomplete({
            source: getData,
            minLength: 2,
            classes: {
                [Class.AUTOCOMPLETE]: Class.AUTOCOMPLETE_PETEL,
            },
            focus: function() {
                const menu = $(this).data("uiAutocomplete").menu.element;
                const focusedElement = menu.find("a.ui-state-active");

                $(Selector.SEARCH_INPUT).attr('aria-activedescendant', focusedElement.attr('id'));
                menu.find(Selector.MENU_ITEM).attr('aria-selected', 'false');
                focusedElement.attr('aria-selected', 'true');
            },
            create: function() {
                $(this).data(Class.AUTOCOMPLETE)._renderItem = function(ul, item) {
                    let row;
                    ul.attr('role', 'listbox');
                    if (item.url !== '#') {
                        const regexp = new RegExp(item.term, "i");
                        row = $('<li class="border-bottom" role="presentation">')
                            .append(`
                                <div class="link-wrapper"
                                <a href="${item.url}" role = "option" aria-label="${item.name}">
                                    ${item.name.replace(regexp, match => `<b>${match}</b>`)}
                                </a>
                                </div>
                            `)
                            .appendTo(ul);
                    } else {
                        row = $('<li class="border-bottom" role = "presentation">')
                            .append(`
                                <div class="link-wrapper"    
                                    <a href="#" role = "option" aria-label="${item.name}">${item.name}</a>
                                </div>`)
                            .appendTo(ul);
                    }
                    return row;
                };
            },
            select: function(event, ui) {
                if (ui.item.url !== '#') {
                    window.location.href = ui.item.url;
                }
            },
            open: function() {
                // Resize menu width.
                $('.ui-autocomplete-petel').css('width', $('#course_activity_search').outerWidth());
            },
        });
    };

    const bindShortSearch = function() {
        $(Selector.SEARCH_INPUT).on('change keyup keydown', function() {
            clearTimeout(actimer);
            $(Selector.SEARCH_INPUT).autocomplete("option", "minLength", 2);
            const term = $(this).val();
            if (term.trim().length === 1) {
                    actimer = setTimeout(function() {
                    $(Selector.SEARCH_INPUT).autocomplete("option", "minLength", 1);
                    $(Selector.SEARCH_INPUT).autocomplete("search");
                }, 2000);
            }
        });
    };

    const init = function() {
        bindSearch();
        bindShortSearch();
    };

    return {
        init: init
    };

});
