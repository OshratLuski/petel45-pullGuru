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
 * Javascript controller for the "Actions" panel at the bottom of the page.
 *
 * @module     community_sharewith/sharewithteacher
 * @package
 * @copyright  2018 Devlion <info@devlion.co>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @since      3.6
 */

define([
    'jquery',
    'core/ajax',
    'core/notification',
    'community_sharewith/modal',
    'community_sharewith/storage'
], function($, Ajax, Notification, modal, St) {

    /** @alias module:community_sharewith/sharewithteacher */
    return {

        resultBlock: '',
        tagWrapper: '',
        input: '',
        numOfSings: 3,

        init: function() {

            var root = modal.modalWrapper;
            root.addEventListener('click', function(e) {
                var target = e.target;
                while (root.contains(target)) {
                    switch (target.dataset.handler) {
                        case 'addTag':
                            this.addTag(target);
                            break;
                        case 'removeTag':
                            this.removeTag(target);
                            break;
                        case 'sendLinkToTeachers':
                            this.submitTeachers();
                            break;
                        case 'shareActivity':
                            this.shareActivity();
                            break;
                    }
                    target = target.parentNode;
                }
            }.bind(this));

            root.addEventListener('input', function(e) {
                var target = e.target;
                while (root.contains(target)) {
                    switch (target.dataset.handler) {
                        case 'selectTeacher':
                            this.autocompleteTeachers(target);
                            break;
                    }
                    target = target.parentNode;
                }
            }.bind(this));

        },
        /**
         * Choose a teacher for copying the activity.
         *
         * @method shareActivity
         * @param {Node} target element.
         */
        shareActivity: function() {
            var self = this;
            var parseResponse = function(response) {
                var context = JSON.parse(response);
                modal.render(modal.template.shareteacher, context)
                    .done(function() {
                        self.resultBlock = document.querySelector('.result-block');
                        self.tagWrapper = document.querySelector('.tag-wrapper');
                        self.input = document.querySelector('input[data-handler = "selectTeacher"]');
                    });
            };

            Ajax.call([{
                methodname: 'community_sharewith_get_teachers',
                args: {
                    activityid: Number(St.cmid),
                    courseid: Number(St.getCurrentCourse())
                },
                done: parseResponse,
                fail: Notification.exception
            }]);
        },

        keySelect: function(container) {

            var currentItem = 0;
            var tagWrapper = document.querySelector('.tag-wrapper');
            var items = Array.from(container.children);
            items.forEach(function(item) {
                item.tabIndex = 0;
            });

            container.onmouseover = function(e) {
                e.target.focus();
                items.forEach(function(item, index) {
                    item.onfocus = function() {
                        currentItem = index;
                    };
                });
            };

            var setBlur = function() {
                items[currentItem].blur();
            };
            var setFocus = function() {
                items[currentItem].focus();
            };

            var goUp = function() {
                if (currentItem <= 0) {
                    return;
                } else {
                    setBlur();
                    currentItem--;
                    setFocus();
                }
            };
            var goDown = function() {
                if (currentItem >= items.length - 1) {
                    return;
                } else {
                    setBlur();
                    currentItem++;
                    setFocus();
                }
            };
            var selectItem = function() {
                var event = new Event('click', {bubbles: true});
                items[currentItem].dispatchEvent(event);

            };
            var hideAll = function() {
                container.innerHTML = '';
                container.classList.add('d-none');
                currentItem = -1;
                document.removeEventListener('click', closeBlockResult);
                document.removeEventListener('keydown', keyCodeHandler);
            };

            var keyCodeHandler = function(e) {
                switch (e.keyCode) {
                    case 38: // Arrow up.
                        goUp();
                        break;
                    case 40: // Arrow down.
                        goDown();
                        break;
                    case 13: // Enter.
                        selectItem();
                        break;
                    case 27: // Esc.
                        hideAll();
                        break;
                }
            };

            var closeBlockResult = function(e) {
                if (container.contains(e.target) || (e.path !== undefined && e.path.indexOf(tagWrapper) !== -1)) {
                    return;
                }
                hideAll();
            };

            document.addEventListener('click', closeBlockResult);
            document.addEventListener('keydown', keyCodeHandler);
        },

        showSearchResult: function(response) {
            this.resultBlock.innerHTML = '';

            var teachers = JSON.parse(response);

            teachers.forEach(function(teacher) {
                var unit = document.createElement('li');
                unit.dataset.teacherid = teacher.teacher_id;
                unit.dataset.teachername = teacher.teacher_name;
                unit.dataset.handler = 'addTag';
                unit.classList.add('btn', 'btn-secondary', 'd-flex', 'mb-1');
                unit.innerHTML = '<div class = "sw-img" >' +
                    '<img src = "' + M.cfg.wwwroot + teacher.teacher_url + '" alt = "">' +
                    '</div><span class = "pl-2">' + teacher.teacher_name + '</span>';
                if (this.tagWrapper.querySelector('.btn[data-id="' + teacher.teacher_id + '"]')) {
                    unit.classList.add('active');
                }
                this.resultBlock.classList.remove('d-none');
                this.resultBlock.appendChild(unit);
            }.bind(this));

            this.keySelect(this.resultBlock);
        },

        autocompleteTeachers: function(target) {
            var inputValue = target.value;

            // Close result block.
            this.resultBlock.classList.add('d-none');

            // If (!this.resultBlock.childElementCount && !inputValue) {
            //     this.resultBlock.classList.add('d-none');
            // }

            if (inputValue.length >= this.numOfSings) {
                Ajax.call([{
                    methodname: 'community_sharewith_autocomplete_teachers',
                    args: {
                        searchstring: inputValue
                    },
                    done: this.showSearchResult.bind(this),
                    fail: Notification.exception
                }]);
            }
        },

        submitTeachers: function() {
            var chosenBlocks = $(this.tagWrapper).find('.btn:not(.example)');
            var teachersId = [];
            var coursesId = [];

            chosenBlocks.each(function() {
                teachersId.push($(this).attr('data-teacherid'));
            });

            // Validate.
            if (teachersId.length === 0) {
                $('#error_share_to_teacher').show();
                return;
            }
            modal.addBtnSpinner();
            var message = $("#message_for_teacher").val();
            var parseResponse = function(response) {
                var template = modal.template.error,
                    context = {
                        title: M.util.get_string('eventcopytoteacher', 'community_sharewith'),
                        text: M.util.get_string('system_error_contact_administrator', 'community_sharewith'),
                    };
                if (response) {
                    template = modal.template.confirm;
                    context.text = M.util.get_string('succesfullyshared', 'community_sharewith');
                }
                modal.render(template, context);
            };

            Ajax.call([{
                methodname: 'community_sharewith_submit_teachers',
                args: {
                    activityid: Number(St.cmid),
                    courseid: Number(St.getCurrentCourse()),
                    teachersid: JSON.stringify(teachersId),
                    coursesid: JSON.stringify(coursesId),
                    message: message,
                    sequence: JSON.stringify(St.activityChain)
                },
                done: parseResponse,
                fail: Notification.exception
            }]);

        },

        addTag: function(target) {
            var teacherid = target.dataset.teacherid,
                tag = $(this.tagWrapper).find('[data-teacherid=' + teacherid + ']');

            if (tag.length) {
                this.removeTag(tag[0]);
                return;
            }

            var teacherTag = $(this.tagWrapper).find('.example').clone();
            teacherTag.attr('data-teacherid', teacherid);
            teacherTag.append('<span>' + target.dataset.teachername + '</span>');
            teacherTag.removeClass('example d-none');

            target.classList.add('active');
            $(this.tagWrapper).append(teacherTag);
            this.input.value = '';

            // Close result block.
            this.resultBlock.classList.add('d-none');
        },

        removeTag: function(target) {
            var teacherid = target.dataset.teacherid;
            $(this.resultBlock).find('[data-teacherid=' + teacherid + ']')
                .removeClass('active');
            $(target).remove();
        },
    };
});
