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
    'core/toast'
], function ($, Str, Ajax, Notification, ModalEvents, ModalFactory, Templates, toast) {

    var unique_id;

    return {
        init: function (currentuserid) {
            let self = this;

            $(document).on('click', '.create_course_template', function () {
                self.checkUserIdNumber(currentuserid, function(res){
                    if(res){
                        self.openPopup(currentuserid);
                    }else{
                        self.openMessagePopup();
                    }
                })
            });
        },

        checkUserIdNumber: function (currentuserid, callback) {
            var self = this;
            var data = {
                currentuserid: currentuserid
            };

            Ajax.call([{
                methodname: 'local_petel_check_user_idnumber',
                args: data,
                done: function (response) {

                    if (response.result) {
                        callback(true);
                    }else{
                        callback(false);
                    }
                },
                fail: Notification.exception
            }]);
        },

        openMessagePopup: function () {
            var self = this;

            // Set new unique_id.
            unique_id = Date.now();

            let titlePromise = Str.get_string('error');

            Str.get_strings([
                { key: 'createcourseerror', component: 'local_petel' }
            ]).done(function (strings) {

                return ModalFactory.create({
                    type: ModalFactory.types.CANCEL,
                    body: strings[0],
                    title: titlePromise,
                    buttons: {
                    },
                    removeOnClose: true,
                })
                    .then(modal => {
                        modal.show();
                        return modal;
                    });
            });
        },

        openPopup: function (currentuserid) {
            var self = this;

            // Set new unique_id.
            unique_id = Date.now();

            let titlePromise = Str.get_string('createcourseteacher', 'local_petel');
            let name = 'course_name_' + unique_id;

            Str.get_strings([
                { key: 'coursename', component: 'local_petel' },
                { key: 'error' },
            ]).done(function (strings) {

                let template = '<input id="'+name+'" name="'+name+'"  class="w-100" type="text" placeholder="'+strings[0]+'">';
                template += '<span style="display: none;" class="label text-danger error-block">'+strings[1]+'</span>';

                return ModalFactory.create({
                    type: ModalFactory.types.SAVE_CANCEL,
                    body: template,
                    title: titlePromise,
                    buttons: {
                        save: titlePromise,
                    },
                    removeOnClose: true,
                })
                    .then(modal => {
                        modal.getRoot().on(ModalEvents.save, () => self.submitPopup(currentuserid, name));
                        modal.show();
                        return modal;
                    });
            });
        },

        submitPopup: function (currentuserid, name) {

            let coursename = $( 'input[name*="'+name+'"]' ).val();

            if(coursename.length != 0) {
                var data = {
                    coursename: coursename,
                    currentuserid: currentuserid
                };

                Ajax.call([{
                    methodname: 'local_petel_create_course_for_teacher',
                    args: data,
                    done: function (response) {

                        if (response.result) {
                            Str.get_strings([
                                {key: 'waitcoursecreate', component: 'local_petel'}
                            ]).done(function (strings) {

                                // Popup success.
                                let template = strings[0];
                                let titlePromise = Str.get_string('createcourseteacher', 'local_petel');

                                return ModalFactory.create({
                                    type: ModalFactory.types.ALERT,
                                    body: template,
                                    title: titlePromise,
                                    buttons: {
                                        cancel: Str.get_string('createcoursesubmit', 'local_petel'),
                                    },
                                    removeOnClose: true,
                                }).then(modal => {
                                    modal.show();
                                    return modal;
                                });
                            })
                        }
                    },
                    fail: Notification.exception
                }]);
            }else{
                $('input[name*="'+name+'"]').parent().find('.error-block').show();
                return false;
            }
        },
    };
});