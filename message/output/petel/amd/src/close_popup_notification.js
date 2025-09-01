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
 * @module     message_petel/close_notification
 * @package    message_petel
 * @copyright  2019 Devlionco <info@devlion.co>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @since      3.6
 */

define([
    'jquery',
    'core/ajax',
    'core/notification'
], function ($, Ajax, Notification) {

    return {
        init: function (callback) {
            $('.close').unbind('click');
            $('.close').bind('click', function () {

                var messageid = $(this).prop('rel');

                Ajax.call([{
                    methodname: 'message_petel_close_popup_notification',
                    args: {
                        'messageid': Number(messageid)
                    },
                    done: function (data) {
                        callback(data);
                    },
                    fail: Notification.exception
                }]);
            });
        }
    };
});
