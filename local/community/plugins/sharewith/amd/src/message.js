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
 * @module     community_sharewith/message
 * @package
 * @copyright  2018 Devlion <info@devlion.co>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @since      3.6
 */

define([
    'jquery',
    'core/str',
    'core/notification'
], function ($, Str, Notification) {

    var message = {

        /**
         * Type a message on the specific page .
         *
         * @method typeMessage
         */
        type: function () {
            var urlString = window.location.href,
                url = new URL(urlString),
                param = url.searchParams.get('mess');

            if (param) {
                var input = document.querySelector('textarea[data-region="send-message-txt"]'),
                    speed = 30, /* The speed/duration of the effect in milliseconds */
                    data = {
                        modname: url.searchParams.get('modname'),
                        coursename: url.searchParams.get('coursename')
                    };
                Str.get_string('ask_question_before_copying', 'community_sharewith', data).done(function (message) {
                    var i = 0;
                    (function typeWriter() {
                        if (i < message.length) {
                            input.innerHTML += message.charAt(i);
                            i++;
                            setTimeout(typeWriter, speed);
                        } else {
                            input.focus();
                        }
                    })();
                }).fail(Notification.exception);
            }
        },
    };

    return message;
});
