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
            $('.popover-region-container').unbind('click');
            $('.popover-region-container').bind('click', function (event) {

                if (event.target.dataset.handler === `approve_social_message`) {
                    let messageid = event.target.dataset.message_id;

                        Ajax.call([{
                            methodname: 'community_social_approve_message_from_teacher',
                            args: {
                                'messageid': Number(messageid)
                            },
                            done: function () {
                                callback(messageid);
                            },
                            fail: Notification.exception
                        }]);

                    return;
                }

                if (event.target.dataset.handler === `decline_social_message`) {
                    let messageid = event.target.dataset.message_id;

                    Ajax.call([{
                        methodname: 'community_social_decline_message_from_teacher',
                        args: {
                            'messageid': Number(messageid)
                        },
                        done: function () {
                            callback(messageid);
                        },
                        fail: Notification.exception
                    }]);

                    return;
                }
            });
        }
    };
});
