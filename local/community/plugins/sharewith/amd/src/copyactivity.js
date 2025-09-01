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
 * @module     community_sharewith/copyactivity
 * @package
 * @copyright  2018 Devlion <info@devlion.co>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @since      3.6
 */

define([
    'jquery',
    'core/ajax',
    'core/notification',
    'community_sharewith/modal',
    'community_sharewith/storage'

], function($, Ajax, Notification, modal, St) {

    return /** @alias module:community_sharewith/copyActivity */ {

        cmid: null,
        firstCmid: null,
        sectionid: null,
        activityChain: [],
        flagCopyFromMessage: 0,
        messageId: 0,

        init: function() {
            var root = document.querySelector('body');

            root.addEventListener('click', function(e) {
                var target = e.target;
                while (root.contains(target)) {
                    switch (target.dataset.handler) {
                        // Copy Section to course.
                        case 'selectCourseForSection':
                            St.hassubsections = $(target).closest('li.section').hasClass('hassubsections') || $(target).closest('li.tile').hasClass('hassubsections');
                            if (St.hassubsections) {
                                this.subOrNot(target);
                            } else {
                                this.selectCourseForSection(target);
                            }
                            break;

                        case 'copyLinkSection':
                            let courseid = $(target).data('courseid');
                            let sectionid = $(target).data('sectionid');

                            const url = M.cfg.wwwroot + '/course/view.php?id=' + courseid + '&sectionid=' + sectionid;
                            let temp = $("<input>");
                            $("body").append(temp);
                            temp.val(url).select();
                            document.execCommand("copy");
                            temp.remove();

                            let title = M.util.get_string('copylinksectionsuccess', 'community_sharewith');
                            alert(title);
                            break;

                        case 'selectCourseForSectionSub':
                            this.selectCourseForSectionSub(target);
                            break;

                        case 'copySectionToCourse':
                            this.copySectionToCourse();
                            break;

                        // Copy Activity.
                        case 'openDialog':
                            this.openDialog(target);
                            break;

                        case 'saveChain':
                            this.saveChain(target);
                            break;

                        case 'selectCourse':
                            this.flagCopyFromMessage = 0;
                            this.messageId = 0;

                            this.selectCourse(target);
                            break;
                        case 'selectCourseForNotification':
                            this.flagCopyFromMessage = 1;
                            this.messageId = target.dataset.message_id;

                            this.selectCourseForNotification(target);
                            break;
                        case 'selectSection':
                            this.selectSection();
                            break;
                        case 'copyActivityToCourse':
                            this.copyActivityToCourse();
                            break;
                    }
                    target = target.parentNode;
                }
            }.bind(this));
        },

        selectCourseForSection: function(target) {
            St.initState();
            St.sectionid = $(target).data('sectionid');

            var parseResponse = function(response) {
                var context = {
                        copySection: true,
                        courses: JSON.parse(response.courses),
                        hidebtn: true,
                        copysub: St.copysub,
                    },
                    template = modal.template.copyinstance;

                if (!response.result) {
                    context.text = M.util.get_string('no_matching_courses_found', 'community_sharewith');
                    context.isAmit = true;
                    template = modal.template.empty;
                }

                modal.render(template, context).done(modal.triggerBtn.click());

            };

            if (!St.sectionid) {
                Ajax.call([{
                    methodname: 'community_sharewith_get_sectionid',
                    args: {
                        courseid: Number(St.getCurrentCourse()),
                        firstcmid: $(target).data('firstcmid')
                    },
                    done: function(response) {
                        St.sectionid = response.sectionid ? response.sectionid : null;
                    },
                    fail: Notification.exception
                }]);
            }

            Ajax.call([{
                methodname: 'community_sharewith_get_courses',
                args: {},
                done: parseResponse,
                fail: Notification.exception
            }]);
        },

        selectCourseForSectionSub: function(target) {
            St.initState();
            St.sectionid = $(target).data('sectionid');
            St.copysub = $(target).data('copysub');

            var parseResponse = function(response) {
                var context = {
                        copySection: true,
                        courses: JSON.parse(response.courses),
                        hidebtn: true,
                        copysub: St.copysub,
                    },
                    template = modal.template.copyinstance;

                if (!response.result) {
                    context.text = M.util.get_string('no_matching_courses_found', 'community_sharewith');
                    context.isAmit = true;
                    template = modal.template.empty;
                }

                modal.render(template, context);
            };

            if (!St.sectionid) {
                Ajax.call([{
                    methodname: 'community_sharewith_get_sectionid',
                    args: {
                        courseid: Number(St.getCurrentCourse()),
                        firstcmid: $(target).data('firstcmid')
                    },
                    done: function(response) {
                        St.sectionid = response.sectionid ? response.sectionid : null;
                    },
                    fail: Notification.exception
                }]);
            }

            Ajax.call([{
                methodname: 'community_sharewith_get_courses',
                args: {},
                done: parseResponse,
                fail: Notification.exception
            }]);
        },

        subOrNot: function(target) {
            var context = {
                    title: M.util.get_string('settingssectionscopy', 'community_sharewith'),
                    text: M.util.get_string('copy_all_sub', 'community_sharewith'),
                    sectionid: $(target).data('sectionid'),
                },
                template = modal.template.subornot;

            modal.render(template, context).done(modal.triggerBtn.click());
        },

        /**
         * Copy section to selected course.
         *
         * @method copySectionToCourse
         */
        copySectionToCourse: function() {
            var modalContent = $(modal.modalContent),
                courseid = modalContent.find(':selected').data('courseid');
            modal.addBtnSpinner();
            var parseResponse = function(response) {
                var template = modal.template.error,
                    context = {
                        title: M.util.get_string('eventsectioncopy', 'community_sharewith'),
                        text: M.util.get_string('system_error_contact_administrator', 'community_sharewith'),
                        hidebtnback: true
                    };
                if (response.result) {
                    template = modal.template.confirm;
                    context.text = M.util.get_string('section_copied_to_course', 'community_sharewith');
                }
                modal.render(template, context).done(()=>{$('#modalSharewith .modal-body span').addClass('px-3 text-center mx-auto')});
            };

            Ajax.call([{
                methodname: 'community_sharewith_add_sharewith_task',
                args: {
                    courseid: Number(courseid),
                    sourcecourseid: Number(St.getCurrentCourse()),
                    sourcesectionid: Number(St.sectionid),
                    type: 'sectioncopy',
                    copysub: true, // St.copysub
                },
                done: parseResponse,
                fail: Notification.exception
            }]);
        },

        openDialog: function(target) {
            St.initState();
            St.cmlink = $(target).data('cmlink');
            St.cmid = $(target).data('cmid');
            St.amit = $(target).data('amit') ? true : false;
            St.sequence = $(target).data('sequence') ? true : false;
            St.haveviewlink = true;

            var methodname = 'community_sharewith_check_cm_status',
                data = {cmid: St.cmid},
                context = {amit: St.amit},
                template = modal.template.selector;

            if (St.amit) {
                methodname = 'community_sharewith_get_amit_teacher';
                data = {
                    cmid: St.cmid,
                    courseid: St.getCurrentCourse()
                };
            }

            var parseResponse = function(data) {
                if (data.cmstatus === 'chain') {
                    context = JSON.parse(data.data);
                    template = modal.template.chain;
                } else if (data.isamit) {
                    context.amit = JSON.parse(data.amit);
                } else if (data.cmstatus === 'wrongquizcategory') {
                    template = modal.template.error;
                    context.text = data.data;
                    context.title = M.util.get_string('eventactivitycopy', 'community_sharewith');
                }

                if (data.cmstatus === '') {
                    context.haveviewlink = data.haveviewlink;
                    St.haveviewlink = data.haveviewlink;
                }

                modal.render(template, context)
                    .done(modal.triggerBtn.click());
            };

            Ajax.call([{
                methodname: methodname,
                args: data,
                done: parseResponse,
                fail: Notification.exception
            }]);
        },

        saveChain: function(target) {
            $.each($("input[name='cmid[]']:checked"), function() {
                St.activityChain.push(Number($(this).val()));
            });
            if (St.sequence) {
                this.selectCourse(target);
            } else {
                modal.render(modal.template.selector, {});
            }
        },

        /**
         * Choose a course for copying the activity.
         *
         * @method selectCourse
         * @param {Node} target element.
         */
        selectCourse: function(target) {
            var self = this;

            var parseResponse = function(response) {
                var context = {
                    courses: JSON.parse(response.courses),
                    copyActivity: true
                };
                if (!context.courses.length) {
                    context.text = M.util.get_string('no_matching_courses_found', 'community_sharewith');
                    modal.render(modal.template.empty, context);
                    return;
                }
                if (target.dataset.activitysharing) {
                    St.cmid = target.dataset.activitysharing;
                    context.hidebtn = true;
                    modal.render(modal.template.copyinstance, context)
                        .done(modal.triggerBtn.click())
                        .done(self.selectSection);
                } else {
                    modal.render(modal.template.copyinstance, context)
                        .done(self.selectSection);
                }
            };

            Ajax.call([{
                methodname: 'community_sharewith_get_courses',
                args: {},
                done: parseResponse,
                fail: Notification.exception
            }]);
        },

        /**
         * Choose a course for copying the activity.
         *
         * @method selectCourse
         * @param {Node} target element.
         */
        selectCourseForNotification: function(target) {
            var self = this;

            var parseResponse = function(response) {
                var context = {
                    courses: JSON.parse(response.courses),
                    copyActivity: true
                };

                if (target.dataset.activitysharing) {
                    St.cmid = target.dataset.activitysharing;
                    context.hidebtn = true;
                    modal.render(modal.template.copyinstance, context)
                        .done(modal.triggerBtn.click())
                        .done(self.selectSection);
                } else {
                    modal.render(modal.template.copyinstance, context)
                        .done(self.selectSection);
                }
            };

            Ajax.call([{
                methodname: 'community_sharewith_get_courses',
                args: {},
                done: parseResponse,
                fail: Notification.exception
            }]);
        },

        /**
         * Choose a section for copying the activity.
         *
         * @method selectSection
         */
        selectSection: function() {
            var modalContent = $(modal.modalContent),
                courseid = modalContent.find(':selected').attr('data-courseid');

            if (!courseid) {
                courseid = St.getCurrentCourse();
            }

            var parseResponse = function(response) {
                var sections = JSON.parse(response.sections);
                modalContent.find('.sections').html('');
                sections.forEach(function(section) {
                    modalContent.find('.sections')
                        .append($('<option data-sectionid =' + section.section_id + '>' + section.section_name + '</option>'));
                });
            };

            Ajax.call([{
                methodname: 'community_sharewith_get_sections',
                args: {
                    courseid: Number(courseid)
                },
                done: parseResponse,
                fail: Notification.exception
            }]);
        },

        /**
         * Copy activity to selected course.
         *
         * @method copyActivityToCourse
         */

        copyActivityToCourse: function() {
            let self = this;

            var modalContent = $(modal.modalContent),
                courseid = modalContent.find('.courses option:selected').attr('data-courseid'),
                sectionid = modalContent.find('.sections option:selected').attr('data-sectionid');

            modal.addBtnSpinner();
            var parseResponse = function(response) {
                var context = {
                    title: M.util.get_string('eventdublicatetoteacher', 'community_sharewith'),
                };
                var template = modal.template.error;

                switch (response.result) {
                    case 0:
                        context.text = M.util.get_string('system_error_contact_administrator', 'community_sharewith');
                        break;
                    case 1:
                        context.text = M.util.get_string('error_coursecopy', 'community_sharewith');
                        break;
                    case 2:
                        context.text = M.util.get_string('error_sectioncopy', 'community_sharewith');
                        break;
                    case 3:
                        context.text = M.util.get_string('error_activitycopy', 'community_sharewith');
                        break;
                    case 4:
                        context.text = M.util.get_string('error_permission_allow_copy', 'community_sharewith');
                        break;
                    case 10:
                        if (self.flagCopyFromMessage === 1) {
                            context.text = M.util.get_string('activity_copied_to_course', 'community_sharewith');

                            let link = M.cfg.wwwroot + '/user/profile.php?id=' + response.userid;
                            let a = {
                                'link': link,
                                'userfirstname': response.userfirstname,
                                'userlastname': response.userlastname
                            };
                            context.text2 = M.util.get_string('activity_copied_to_course_from_message', 'community_sharewith', a);

                            context.userid = response.userid;
                            context.modname = response.modname;
                            context.coursename = response.coursename;

                            template = modal.template.confirm2;
                        } else {
                            context.text = M.util.get_string('activity_copied_to_course', 'community_sharewith');
                            template = modal.template.confirm;
                        }

                        break;
                }
                modal.render(template, context);
            };

            Ajax.call([{
                methodname: 'community_sharewith_add_sharewith_task',
                args: {
                    courseid: Number(courseid),
                    sourcecourseid: Number(St.getCurrentCourse()),
                    sectionid: Number(sectionid),
                    sourceactivityid: Number(St.cmid),
                    type: 'activitycopy',
                    chain: JSON.stringify(St.activityChain),
                    messageid: self.messageId
                },
                done: parseResponse,
                fail: Notification.exception
            }]);
        },
    };
});
