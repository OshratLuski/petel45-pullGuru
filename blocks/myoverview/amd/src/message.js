// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope context it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Javascript to initialise the selectors for the myoverview block.
 *
 * @package    block_myoverview
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define([
    'jquery',
    'core/str',
    'core/modal_factory',
    'core/modal_events',
    'core/templates',
    'core/notification',
    'core/ajax'
],
function(
    $,
    Str,
    ModalFactory,
    ModalEvents,
    Templates,
    Notification,
    Ajax
) {

    var Message = function() {

    };

    Message.prototype.modal = null;

    /**
     * Send a message to these users.
     *
     * @method submitSendMessage
     * @private
     * @param {int[]} users
     * @param {Event} e Form submission event.
     * @return {Promise}
     */
    Message.prototype.submitSendMessage = function(courseId, target) {
        var messageText = this.modal.getRoot().find('form textarea').val();

        return Ajax.call([{
            methodname: 'block_myoverview_send_course_messages',
            args: {
                message: messageText,
                courseid: courseId,
                target: target
            }
        }])[0].then(function(messageIds) {
            if (messageIds.length == 1) {
                return Str.get_string('sendbulkmessagesentsingle', 'core_message');
            } else {
                return Str.get_string('sendbulkmessagesent', 'core_message', messageIds.length);
            }
        }).then(function(msg) {
            Notification.addNotification({
                message: msg,
                type: "success"
            });
            return true;
        }).catch(Notification.exception);
    };

    Message.prototype.openForm = function(courseId, target) {
        if (target === 'class') {
            var titlePromise = Str.get_string('send_message_to_course_users', 'block_myoverview');
        } else {
            var titlePromise = Str.get_string('send_message_to_teacher', 'block_myoverview');
        }

        return $.when(
            ModalFactory.create({
                type: ModalFactory.types.SAVE_CANCEL,
                body: Templates.render('block_myoverview/petel/send_course_message', {})
            }),
            titlePromise
        ).then(function(modal, title) {
            // Keep a reference to the modal.
            this.modal = modal;

            this.modal.setTitle(title);
            this.modal.setSaveButtonText(title);

            // We want to focus on the action select when the dialog is closed.
            this.modal.getRoot().on(ModalEvents.hidden, function() {
                this.modal.getRoot().remove();
            }.bind(this));

            this.modal.getRoot().on(ModalEvents.save, this.submitSendMessage.bind(this, courseId, target));

            this.modal.show();

            return this.modal;
        }.bind(this));
    };

    return {
        'init': function(options) {
            return new Message(options);
        }
    };
});
