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
 * @module     community_sharewith/sharewithcommunity
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

        init: function() {

            var root = modal.modalWrapper;
            root.addEventListener('click', function(e) {
                var target = e.target;
                while (root.contains(target)) {
                    switch (target.dataset.handler) {
                        case 'sendToTeachersCommunity':
                            this.submitCommunity();
                            break;
                        case 'shareCommunity':
                            this.shareCommunity();
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
        shareCommunity: function() {
            var self = this;
            var parseResponse = function(response) {
                var context = JSON.parse(response);
                modal.render(modal.template.sharecommunity, context)
                    .done(function() {
                        self.resultBlock = document.querySelector('.result-block');
                        self.tagWrapper = document.querySelector('.tag-wrapper');
                        self.input = document.querySelector('input[data-handler = "selectTeacher"]');
                    });
            };

            Ajax.call([{
                methodname: 'community_sharewith_get_community',
                args: {
                    activityid: Number(St.cmid),
                    courseid: Number(St.getCurrentCourse())
                },
                done: parseResponse,
                fail: Notification.exception
            }]);
        },

        submitCommunity: function() {
            var teachersId = [];
            var coursesId = [];

            $.each($('#teachers_courses').select2('data'), function(index, obj) {
                if (obj.id.length !== 0) {
                    coursesId.push(obj.id);
                }
            });

            // Validate.
            if (coursesId.length === 0) {
                $('#error_share_to_community').show();
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
    };
});
