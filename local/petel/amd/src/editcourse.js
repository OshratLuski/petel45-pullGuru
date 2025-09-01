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

    let default_value;
    let flag;

    return {
        init: function () {
            this.popup();
        },

        popup: function () {
            let self = this;
            default_value = $('select[name="format"]').val();

            $('form').one('submit', function(e) {
                e.preventDefault();

                let form = $(this);
                let value = $('select[name="format"]').val();

                if(value !== default_value){

                    Str.get_strings([
                        { key: 'editcoursetitle', component: 'local_petel' },
                        { key: 'editcoursebody', component: 'local_petel' },
                        { key: 'editcourseapprove', component: 'local_petel' },
                    ]).done(function (strings) {

                        return ModalFactory.create({
                            type: ModalFactory.types.SAVE_CANCEL,
                            title: strings[0],
                            body: strings[1],
                            buttons: {
                                save: strings[2],
                            },
                            removeOnClose: true,
                        })
                            .then(modal => {
                                modal.getRoot().on(ModalEvents.save, () => self.submit(form, true));
                                modal.getRoot().on(ModalEvents.hidden, () => self.revert());
                                modal.show();
                                return modal;
                            });
                    });

                }else{
                    this.submit(form);
                }
            });
        },

        revert: function () {
            if(flag !== true) {
                $('select[name="format"]').val(default_value);
                this.popup();
            }
        },

        submit: function (form, flag_submit) {
            flag = flag_submit;
            form.submit()
        },
    };
});