/* eslint-disable no-console */
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
 * @module     community_sharewith/custombuttons
 * @package
 * @copyright  2018 Devlion <info@devlion.co>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @since      3.6
 */

define([
    'jquery',
    'core/ajax',
    'core/notification',
    'core/templates',
    'community_sharewith/modal',
    'community_sharewith/storage'
], function ($, Ajax, Notification, Templates, modal, St) {

    /** @alias module:community_sharewith/sharewithteacher */
    return {

        resultBlock: '',
        tagWrapper: '',
        input: '',

        init: function () {

            var root = modal.modalWrapper;
            root.addEventListener('click', function (e) {
                var target = e.target;
                while (root.contains(target)) {
                    switch (target.dataset.handler) {
                        case 'petelMessage':
                            this.petelMessage();
                            break;
                        case 'toWhatsapp':
                            this.toWhatsapp();
                            break;
                        case 'copyToClipboard':
                            this.copyToClipboard();
                            break;
                    }
                    target = target.parentNode;
                }
            }.bind(this));

            $('#modalSharewith').on('hide.bs.modal', function () {
                if (document.querySelector('.copytoclipboard_alert')) {
                    $('.copytoclipboard_alert').alert('close');
                }
            });
        },

        petelMessage: function () {
            let url = M.cfg.wwwroot + '/local/resourcenotif/resourcenotif.php?id=' + St.cmid;

            let win = window.open(url, '_blank');
            if (win) {
                // Browser has allowed it to be opened.
                win.focus();
            }
        },

        toWhatsapp: function () {
            let text = St.cmlink;
            let url = 'https://api.whatsapp.com/send/?text=' + text + '&type=custom_url&app_absent=0';

            let win = window.open(url, '_blank');
            if (win) {
                // Browser has allowed it to be opened.
                win.focus();
            }
        },

        copyToClipboard: function () {
            let text = St.cmlink;
            let context = {};

            navigator.clipboard.writeText(text).then(
                () => {
                    return Templates.render('community_sharewith/copytoclipboard_alert', context)
                        .done(function (html, js) {
                            Templates.appendNodeContents('body', html, js);

                            setTimeout(() => {
                                $('.copytoclipboard_alert').alert('close');
                            }, 2000);


                        })
                        .fail(Notification.exception);
                }, () => {});
        },
    };
});
