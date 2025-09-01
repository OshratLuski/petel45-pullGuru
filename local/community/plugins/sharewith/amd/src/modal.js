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
 * @module     community_sharewith/modal
 * @package
 * @copyright  2018 Devlion <info@devlion.co>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @since      3.6
 */

define([
    'jquery',
    'core/templates',
    'core/notification',
    'community_sharewith/storage',
    'local_petel/inview',
], function($, Templates, Notification, St, inView) {

    var SELECTORS = {
        modalWrapper: '#modalSharewith',
        modalContent: '#modalContentSharewith',
        triggerBtn: '#triggerModalSharewith',
        menuSection: '.course-content .section_action_menu [role="menu"]',
        sectionTitleTiles: '.tile-clickable h3',
        shareActivityButton: 'share-button-activity-area',
        shareSectionButton: 'share-button-section-area'
    };

    return /** @alias module:community_sharewith/modal */ {

        template: {
            modalwrapper: 'community_sharewith/modalwrapper',
            chain: 'community_sharewith/chain',
            selector: 'community_sharewith/selector',
            copyinstance: 'community_sharewith/copyinstance',
            subornot: 'community_sharewith/subornot',
            error: 'community_sharewith/error',
            empty: 'community_sharewith/empty',
            confirm: 'community_sharewith/confirm',
            confirm2: 'community_sharewith/confirm2',
            shareteacher: 'community_sharewith/shareteacher',
            sharecommunity: 'community_sharewith/sharecommunity',
            uploadactivity: 'community_sharewith/uploadactivity'
        },

        modalInit: false,
        triggerBtn: '',
        modalContent: '',
        modalWrapper: '',
        courseFormat: 'topic',

        /**
         * Add share button to the activity.
         *
         * @method addShareButtonActivity
         * @param {Object} attr additional attr for node.
         * @param {string} node selector for parent node element.
         */
        addShareActivityButton: function(attr = null, node = '') {

            let self = this;

            const renderButton = target => {
                let shareBtn = self.addSharedBtn();
                let cmid = target.data("cmid");
                let cmlink = target.data("cmlink");

                shareBtn
                    .attr('data-handler', 'openDialog')
                    .attr('data-cmid', cmid)
                    .attr('data-cmlink', cmlink);

                for (const prop in attr) {
                    shareBtn.attr(`data-${prop}`, attr[prop]);
                }

                target.html(shareBtn);
            };

            // On load.
            if (attr !== null && attr.amit) {
                setTimeout(function() {
                    $(`${node} [data-region="${SELECTORS.shareActivityButton}"]`).each(function() {
                        renderButton($(this));
                    });

                }, 2000);
            } else {
                $(`${node} [data-region="${SELECTORS.shareActivityButton}"]`).each(function() {
                    renderButton($(this));
                });
            }

            // Observer on action.
            let observerNodeTargets = document.querySelectorAll('.section'),
                observerConfig = {attributes: false, childList: true, subtree: false};

            observerNodeTargets.forEach(function(target) {
                new MutationObserver(function(type) {
                    var nodes = type[0].addedNodes;
                    nodes.forEach((node) => {
                        let elem = $(node).find('*[data-region="' + SELECTORS.shareActivityButton + '"]');
                        renderButton(elem);
                    });
                }).observe(target, observerConfig);
            });
        },

        /**
         * Clone and add the button for copying section.
         *
         * @method addCopySectionButtonInline
         * @param {jquery} root The root element.
         */
        addCopySectionButtonInline: function() {
            let string = M.util.get_string('eventsectioncopy', 'community_sharewith'),
                self = this;

            const renderButton = target => {
                let shareBtn = self.addSharedBtn(string);
                let sectionid = target.data("sectionid");

                shareBtn
                    .addClass('mr-2 ml-2')
                    .attr('data-handler', 'selectCourseForSection')
                    .attr('data-sectionid', sectionid)
                    .attr('data-inview', 'done');
                target.append(shareBtn);
            };

            // On load.
            $('*[data-region="' + SELECTORS.shareSectionButton + '"]').each(function() {
                renderButton($(this));
            });

            // Inview.
            inView('*[data-region="' + SELECTORS.shareSectionButton + '"]').on("enter", function(e) {
                if ($(e).find('button').data('inview') !== 'done') {
                    renderButton($(e));
                }
            });

            // Observer on action.
            let observerNodeTargets = document.querySelectorAll('.flexsections'),
                observerConfig = {attributes: false, childList: true, subtree: false};

            observerNodeTargets.forEach(function(target) {
                new MutationObserver(function(type) {
                    var nodes = type[0].addedNodes;
                    nodes.forEach((node) => {
                        let elem = $(node).find('*[data-region="' + SELECTORS.shareSectionButton + '"]');
                        renderButton(elem);
                    });
                }).observe(target, observerConfig);
            });
        },

        /**
         * Clone and add the button for copying section.
         *
         * @method addCopySectionButtonCollegueTeacher
         * @param {jquery} root The root element.
         */
        addCopySectionButtonCollegueTeacher: function() {
            var string = M.util.get_string('eventsectioncopy', 'community_sharewith'),
                sectionTitle,
                self = this;

            if (this.courseFormat === 'tiles') {
                sectionTitle = $(document).find(SELECTORS.sectionTitleTiles);
                sectionTitle.each(function() {
                    var shareBtn = self.addSharedBtn(string),
                        str = $(this).parents('li.tile-clickable').data('section'),
                        sectionid = $(this).parents('.tile').data('sectionidshare');
                    if (sectionid) {
                        shareBtn
                            .attr('data-handler', 'selectCourseForSection')
                            .attr('data-firstcmid', str)
                            .attr('data-sectionid', sectionid);
                        shareBtn.insertAfter($(this));
                    }
                });
            }

        },

        /**
         * Clone and add the button for copying section.
         *
         * @method addCopySectionButtonCollegueTeacherInView
         * @param {jquery} root The root element.
         */
        addCopySectionButtonCollegueTeacherInView: function() {
            var string = M.util.get_string("eventsectioncopy", "community_sharewith"),
                sectionTitle,
                self = this;

            if (this.courseFormat === "tiles") {
                setInterval(function() {
                    $('.tiles_section_inview').each(function() {

                        if ($(this).length && $(this).is(":visible")) {

                            if (!$(this).hasClass("inview-selectCourseForSection-done")) {
                                var shareBtn = self.addSharedBtn(string);
                                var sectionid = $(this).data('sectionid');

                                shareBtn
                                    .attr("data-handler", "selectCourseForSection")
                                    .attr("data-sectionid", sectionid)
                                    .attr("data-amit", "true");
                                shareBtn.addClass('ml-auto mr-2');

                                // $(this).addClass('ml-2');

                                shareBtn.appendTo($(this));

                                $(this).addClass("inview-selectCourseForSection-done");

                            }

                        }

                    });

                }, 500);
            }

            if (this.courseFormat === "grid") {
                inView(".grid_section_inview").on("enter", function(e) {
                    setTimeout(() => {
                        var sectiontitle = $(e);

                        if (!sectiontitle.hasClass("inview-selectCourseForSection-done")) {
                            var shareBtn = self.addSharedBtn(string);
                            var sectionid = sectiontitle.data('sectionid');
                            shareBtn
                                .attr("data-handler", "selectCourseForSection")
                                .attr("data-sectionid", sectionid)
                                .attr("data-amit", "true");
                            shareBtn.addClass('ml-auto mr-2');
                            sectiontitle.find("div.content h2.sectionname").addClass('w-100 d-flex align-items-center');
                            shareBtn.appendTo(sectiontitle.find("div.content h2.sectionname"));
                            sectiontitle.addClass("inview-selectCourseForSection-done");

                        }
                    }, 500);
                });
            } else if (this.courseFormat === "topic") {
                inView(".topics_section_inview").on("enter", function(e) {
                    setTimeout(() => {
                        var sectiontitle = $(e);

                        if (!sectiontitle.hasClass("inview-selectCourseForSection-done")) {
                            var shareBtn = self.addSharedBtn(string);

                            var sectionid = sectiontitle.data('sectionid');

                            shareBtn
                                .attr("data-handler", "selectCourseForSection")
                                .attr("data-sectionid", sectionid)
                                .attr("data-amit", "true");

                            shareBtn.addClass('ml-auto mr-2');
                            sectiontitle.closest('li.section').find("div.content h2.sectionname span").addClass('w-100 d-flex align-items-center');
                            shareBtn.appendTo(sectiontitle.closest('li.section').find("div.content h2.sectionname span"));
                            sectiontitle.addClass("inview-selectCourseForSection-done");
                        }
                    }, 500);
                });
            } else if (this.courseFormat === "flexsections") {
                inView(".flexsections_section_inview").on("enter", function(e) {
                    setTimeout(() => {
                        var sectiontitle = $(e);
                        let sectionID = sectiontitle.data(sectionid).sectionid;
                        let element = $(`[data-sectionidshare="${sectionID}"]`);
                        if (!sectiontitle.hasClass("inview-selectCourseForSection-done")) {
                            var shareBtn = self.addSharedBtn(string);

                            var sectionid = sectiontitle.data('sectionid');

                            shareBtn
                                .attr("data-handler", "selectCourseForSection")
                                .attr("data-sectionid", sectionid)
                                .attr("data-amit", "true");


                            shareBtn.addClass('ml-auto mr-2');
                            if ($(sectiontitle).children('ul.section').find('li').length > 0) {
                                sectiontitle.closest('.content').find("h2.sectionname").parent().addClass('w-100 d-flex align-items-center');
                                shareBtn.appendTo(sectiontitle.closest('.content').find("h2.sectionname").parent());
                            } else if ($(sectiontitle).siblings('.flexsections-level-1').length > 0) {
                                $(sectiontitle).addClass('w-100 d-flex align-items-center');
                                shareBtn.appendTo(sectiontitle);
                            } else {
                                // $(sectiontitle).siblings('.d-flex.align-items-center.justify-content-between').find('h2.sectionname').addClass('w-100 d-flex align-items-center');
                                shareBtn.appendTo($(sectiontitle).siblings('.d-flex.align-items-center.justify-content-between'));
                            }

                            sectiontitle.addClass("inview-selectCourseForSection-done");

                        }
                    }, 500);
                });
            }
        },

        /**
         * Insert modal markup on the page.
         *
         * @method insertTemplates
         * @return {Promise|boolean}
         */
        insertTemplates: function() {
            var context = {},
                self = this;

            return Templates.render('community_sharewith/modalwrapper', context)
                .done(function(html, js) {
                    if (!self.modalInit) {
                        Templates.appendNodeContents('body', html, js);
                        self.modalInit = true;
                        self.modalWrapper = document.querySelector(SELECTORS.modalWrapper);
                        self.modalContent = document.querySelector(SELECTORS.modalContent);
                        self.triggerBtn = document.querySelector(SELECTORS.triggerBtn);
                    }
                })
                .fail(Notification.exception);
        },

        /**
         * Insert modal markup on the page.
         *
         * @method render
         * @param {string} template The template name.
         * @param {object} context The context for template.
         * @return {Promise}
         */
        render: function(template, context) {
            var self = this;
            context.wwwroot = M.cfg.wwwroot;
            self.modalContent.innerHTML = '';
            return Templates.render(template, context)
                .done(function(html, js) {
                    Templates.replaceNodeContents(self.modalContent, html, js);
                })
                .fail(Notification.exception);
        },

        /**
         * Add necessary nodes to the DOM.
         *
         * @method addActionNode
         * @param {object} actions The context for template.
         * @return {boolean}
         */
        addActionNode: function(actions) {
            this.checkCourseFormat();

            // TODO show buttons depending on all conditions
            var result = 0;
            // if (actions.sectioncopyenable) {
            //     this.addCopySectionButtonInline();
            //     result++;
            // }
            if (actions.activitycopyenable && !actions.teachercolleague) {
                /* Adding share btn for each activity on the page */
                this.addShareActivityButton();
                result++;
            }
            if (actions.teachercolleague) {
                var attr = {amit: true},
                    self = this;
                this.addShareActivityButton(attr);
                //this.addCopySectionButtonCollegueTeacher();

                if (this.courseFormat === 'tiles') {
                    var observerNodeTargets = document.querySelectorAll('.section.moveablesection'),
                        observerConfig = {attributes: false, childList: true, subtree: false};

                    observerNodeTargets.forEach(function(target) {
                        new MutationObserver(function(type) {
                            var nodes = type[0].addedNodes;
                            nodes.forEach((node) => {
                                if ($(node).hasClass('content')) {
                                    self.addShareActivityButton(attr);
                                }
                            });
                        }).observe(target, observerConfig);
                    });
                }
                result++;
            }
            return result ? true : false;
        },

        /**
         * Show spinner.
         *
         * @method addSpinner
         */
        addBtnSpinner: function() {
            let obj = $('#modalspinner');
            obj.removeClass('d-none').addClass('loading');
            obj.parent().prop('disabled', true);
        },

        /**
         * Remove spinner.
         *
         * @method addSpinner
         */
        removeBtnSpinner: function() {
            let obj = $('#modalspinner');
            obj.removeClass('loading').addClass('d-none');
            obj.parent().prop('disabled', false);
        },

        /**
         * Create sharedBtn.
         *
         * @method addSharedBtn
         * @param {string} string button text or default value.
         * @return {jquery} shareBtn.
         */
        addSharedBtn: function(string = null) {
            var text = string || M.util.get_string('share', 'community_sharewith'),
                shareBtn = $(`<button><i class="fa-light fa-share-nodes"></i></button>`);
            shareBtn
                .addClass('sharebtn')
                .attr('data-sharebtn', true);
            shareBtn
                .find('.icon')
                .attr('title', text)
                .attr('aria-label', text);
            return shareBtn;
        },

        /**
         * Check and set course format.
         *
         * @method checkCourseFormat
         */
        checkCourseFormat: function() {
            if ($('body').hasClass('format-tiles')) {
                this.courseFormat = 'tiles';
            }
            if ($('body').hasClass('format-grid')) {
                this.courseFormat = 'grid';
            }
            if ($('body').hasClass('format-topic')) {
                this.courseFormat = 'topic';
            }
            if ($('body').hasClass('format-flexsections')) {
                this.courseFormat = 'flexsections';
            }
        },

        /**
         * Return to the main window.
         *
         * @method goBack
         */
        goBack: function() {
            var context = {amit: St.amit, haveviewlink: St.haveviewlink};
            this.render(this.template.selector, context);
        },
    };
});
