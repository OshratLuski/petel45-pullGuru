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
 * @module     local_petel/init
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
    'core/modal_events',
    'core/modal_factory',
    'core/templates',
], function ($, Str, Ajax, Notification, ModalEvents, ModalFactory, Templates) {


    function renederPopup(html) {
        Str.get_strings([
            { key: 'titlepopupupdatecoursemetadata', component: 'local_petel' },
            { key: 'approve', component: 'local_petel' }
        ]).done(function (strings) {

            ModalFactory.create({
                type: ModalFactory.types.SAVE_CANCEL,
                title: strings[0],
                body: html
            }).done(function (modal) {

                modal.setSaveButtonText(strings[1]);

                // Handle save event.
                modal.getRoot().on(ModalEvents.save, function (e) {
                    e.preventDefault();

                    let courseid = modal.getRoot().find('#courseid').val();
                    let cclass = modal.getRoot().find('#cclass').val();
                    let cclasslevel = modal.getRoot().find('#cclasslevel').val();

                    Ajax.call([{
                        methodname: 'local_petel_update_course_metadata',
                        args: {
                            courseid: courseid,
                            cclass: cclass,
                            cclasslevel: cclasslevel,
                        },
                        done: function (response) {
                            modal.destroy();
                        },
                        fail: Notification.exception
                    }]);

                });

                modal.getRoot().on(ModalEvents.hidden, function() {
                    modal.destroy();
                })

                modal.show();
            });
        });
    }

    return {
        openPopup: function (courseid) {
            Ajax.call([{
                methodname: 'local_petel_popup_update_course_metadata',
                args: {
                    courseid: courseid
                },
                done: function (response) {
                    let data = JSON.parse(response);

                    Templates.render('local_petel/update_course_metadata_popup', data)
                        .done(function (html, js) {
                            renederPopup(html);
                        })
                },
                fail: Notification.exception
            }]);
        }
    };
});