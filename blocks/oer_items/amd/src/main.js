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
 * @package    block_oer_items
 * @copyright  2018 Bas Brands <bas@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define([
        'jquery',
        'core/ajax',
        'core/notification',
        'local_petel/inview',
        'core/templates',
    ],
    function($, Ajax, Notification, inView, Templates) {

        return {
            selector: {
                wrapper: '#selectCourse',
                language: '#selectLanguage',
                blocks: '.block_oer_items',
                mainBlock: '.content-oer-activities',
                selectedCourse: '#selectedCourse',
                exampleCourse: 'button.example',
                selectItems: '#selectCourse .dropdown-item',
                spinner: '#selectCourse .spinner-border',
            },

            /**
             * Initialise all of the modules for the overview block.
             *
             * @param {object} courseids The root element for the overview block.
             */
            init: function() {

                var root = $(this.selector.wrapper),
                    self = this;

                // First run.
                inView('.block-oer-items-inview')
                    .on('enter', function(e) {
                        if (!$(e).hasClass('inview-done')) {
                            $(e).addClass('inview-done');

                            self.renderBlock();
                        }
                    })
                    .on('exit', el => {
                    });

                root.on('click', function(e) {
                    var target = $(e.target);
                    while ($.contains(root[0], target[0])) {
                        if (target.data('handler') === 'setCourse') {
                            self.setCourse(target);
                            return;
                        }
                        if (target.data('handler') === 'removeCourse') {
                            self.removeCourse(target);
                            return;
                        }
                        target = target.parent();
                    }
                });

                $(this.selector.language).change(function() {
                    self.renderBlock();
                });

            },

            setCourse: function(target) {
                var courseid = target.attr('data-courseid');

                if (target.hasClass('active')) {
                    target.removeClass('active');
                    this.removeCourse(target);
                    this.renderBlock();
                    return;
                }

                if (courseid === '0') {
                    $(this.selector.selectItems).removeClass('active');
                    $(this.selector.selectedCourse + ' button').each(function() {
                        if (!$(this).hasClass('example')) {
                            $(this).remove();
                        }
                    });
                } else {
                    $(this.selector.selectItems).parent()
                        .find('[data-courseid=0]').removeClass('active');
                    $(this.selector.selectedCourse)
                        .find('[data-courseid=0]').remove();
                }

                var btnCourse = $(this.selector.exampleCourse).clone(),
                    wrapper = $(this.selector.selectedCourse),
                    name = target.attr('data-name');

                btnCourse.attr('data-courseid', courseid);
                btnCourse.find('span').text(name);
                btnCourse.removeClass('d-none example');
                wrapper.append(btnCourse);
                target.toggleClass('active');

                this.renderBlock();
            },

            removeCourse: function(target) {
                var courseid = target.attr('data-courseid');
                $(this.selector.selectItems).parent()
                    .find('[data-courseid=' + courseid + ']')
                    .removeClass('active');
                $(this.selector.selectedCourse)
                    .find('[data-courseid=' + courseid + ']')
                    .remove();
                this.renderBlock();
            },

            renderBlock: function() {
                var allCourses = $(this.selector.selectItems),
                    self = this,
                    selectedCoures = [];

                this.toggleSpinner(true);

                allCourses.each(function() {
                    if ($(this).hasClass('active')) {
                        selectedCoures.push($(this).attr('data-courseid'));
                    }
                });

                Ajax.call([{
                    methodname: 'block_oer_items_render_activity_block',
                    args: {
                        courseids: JSON.stringify(selectedCoures),
                        language: $(this.selector.language).val()
                    },
                    done: function(response) {
                        self.toggleSpinner(false);

                        Templates.render('block_oer_items/content-activities', JSON.parse(response))
                            .done(function(html, js) {
                                Templates.replaceNodeContents(self.selector.mainBlock, html, js);
                            })
                            .fail(Notification.exception);
                    },
                    fail: Notification.exception
                }]);
            },

            toggleSpinner: function(status) {
                var spinner = $(this.selector.spinner);
                var borderColor = status ? 'currentColor' : 'transparent';
                spinner.css('border-color', borderColor);
            }
        };
    });
