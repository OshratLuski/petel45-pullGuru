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
 * @module     community_sharewith/init
 * @package
 * @copyright  2018 Devlion <info@devlion.co>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @since      3.6
 */

define([
    'jquery',
    'community_sharewith/modal',
    'community_sharewith/sharewithteacher',
    'community_sharewith/sharewithcommunity',
    'community_sharewith/sendtocatalog',
    'community_sharewith/copyactivity',
    'community_sharewith/message',
    'community_sharewith/custombuttons',
], function ($, modal, shareWithTeacher, shareWithCommunity, sendToCatalog, copyActivity, message, customButtons) {

    var root = document.querySelector('body');

    return {

        init: function (actions, contextid) {

            if (actions.teachercolleague) {
                modal.checkCourseFormat();
                modal.addCopySectionButtonCollegueTeacherInView();
            }

            // Add modal bootstap markup to the DOM
            modal.insertTemplates().done(function () {
                modal.addActionNode(actions);
                shareWithTeacher.init();
                shareWithCommunity.init();
                sendToCatalog.init(contextid);
                copyActivity.init();
                customButtons.init();
                message.type();

                root.addEventListener('click', function (e) {
                    var target = e.target;
                    while (root.contains(target)) {
                        switch (target.dataset.handler) {
                            case 'goBack':
                                modal.goBack();
                                break;
                        }
                        target = target.parentNode;
                    }
                });
            });
        }
    };
});
