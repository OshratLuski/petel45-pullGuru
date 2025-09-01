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
 * Javascript to initialise the myoverview block.
 *
 * @package    block_pdc
 * @copyright  2018 Bas Brands <bas@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define([
        'jquery',
        'core/ajax',
        'core/notification',
        'local_petel/inview'
    ],
    function ($, Ajax, Notification, inView) {

        return {
            selector: {
                wrapper: '#block-pdc-selectSort',
                mainBlock: '.block-pdc-content-courses',
                selectItems: '#block-pdc-selectSort .dropdown-item',
                spinner: '#block-pdc-selectSort .spinner-border',
            },

            /**
             * Initialise all of the modules for the overview block.
             *
             * @param {object} courseids The root element for the overview block.
             */
            init: function () {

                var root = $(this.selector.wrapper),
                    self = this;

                // First run.
                inView('.block-pdc-inview')
                    .on('enter', function (e) {
                        if (!$(e).hasClass('inview-done')) {
                            $(e).addClass('inview-done');

                            var sort = $('#block-pdc-selectSort .dropdown-menu .active').data('value');
                            self.renderBlock(sort);
                        }
                    })
                    .on('exit', el => {
                    });

                root.on('click', function (e) {
                    var target = $(e.target);
                    while ($.contains(root[0], target[0])) {
                        if (target.data('handler') === 'setSorting') {

                            $(self.selector.selectItems).removeClass('active');
                            $(target).addClass('active');

                            self.renderBlock(target.data('value'));
                            return;
                        }

                        target = target.parent();
                    }

                });
            },

            renderBlock: function (sort) {

                var self = this;
                this.toggleSpinner(true);

                Ajax.call([{
                    methodname: 'block_pdc_render_courses_block',
                    args: {
                        sort: sort
                    },
                    done: function (response) {
                        self.toggleSpinner(false);
                        if (response.status) {
                            $(self.selector.mainBlock).html(response.content);
                        }
                    },
                    fail: Notification.exception
                }]);
            },

            toggleSpinner: function (status) {
                var spinner = $(this.selector.spinner);
                var borderColor = status ? '' : 'transparent';
                spinner.css('border-color', borderColor);
            }
        };
    });
