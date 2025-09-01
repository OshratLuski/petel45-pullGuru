/* eslint-disable no-undef */
/* eslint-disable no-implicit-globals */
/* eslint-disable no-unused-vars */
/* eslint-disable max-len */
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
 * @module     local_petel/events
 * @package    local_petel
 * @copyright  2019 Devlionco <info@devlion.co>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @since      3.6
 */

//import {add as notifyUser} from "../../../../lib/amd/src/toast";

define([
    'jquery',
    'core/str',
    'core/ajax',
    'core/notification',
], function ($, Str, Ajax, Notification) {

    return {

        init: function () {

            // Event click icon notifications.
            $( ".popover-region-notifications" ).click(function() {
                if($(this).hasClass('collapsed')){
                    Ajax.call([{
                        methodname: 'local_petel_send_event',
                        args: {
                            type: 'notification',
                        },
                        done: function (response) {

                        },
                        fail: Notification.exeption
                    }]);
                }
            });

            // Event click icon chat.
            $("*[data-region='popover-region-messages']").click(function() {
                let obj = $("*[data-region='message-drawer']").parent();
                if(!obj.hasClass("hidden")){
                    Ajax.call([{
                        methodname: 'local_petel_send_event',
                        args: {
                            type: 'chat',
                        },
                        done: function (response) {

                        },
                        fail: Notification.exeption
                    }]);
                }
            });
        },
    };
});