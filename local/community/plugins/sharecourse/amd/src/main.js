/* eslint-disable camelcase */
/* eslint-disable no-unused-vars */
define([
    'jquery',
    'core/yui',
    'core/str',
    'core/modal_factory',
    'core/modal_events',
    'core/ajax',
    'core/templates',
    'core/notification',
    'core/fragment',
    'community_sharequestion/sendtoteacher',
    'community_sharecourse/select2'

], function($, Y, Str, ModalFactory, ModalEvents, Ajax, Templates, Notification, Fragment, SendToTeacher) {


    const sesskey = M.cfg.sesskey;
    const selected_questions = [];
    const currentcourseid = null;
    const currentcoursecontext = null;
    const flag_background_enable = false;
    const large = false;
    const context_template = {};

    function build_select2(modal, sclass) {
        var select2Target = modal.body.find(sclass);
        var dropdownParent = select2Target.closest('.select-wrapper');
        select2Target.select2({
            dropdownAutoWidth: true,
            dropdownParent: dropdownParent
        });
    }

    function open_selector(currentcourseid = null, currentcoursecontext = null, type = null) {
        let self = this;

        if (currentcourseid != null) {
            this.currentcourseid = currentcourseid;
        }
        if (currentcoursecontext != null) {
            this.currentcoursecontext = currentcoursecontext;
        }

        if(self.context_template === undefined) {
            switch (type) {
                case 'admin-all':
                    self.large = true;
                    self.context_template = {
                        courseupload: true,
                        copycoursetoteacher: true,
                        sharecoursecommunity: true,
                        copycoursetocategory: true,
                        sharecoursepombim: true,
                    };
                    break;
                case 'admin-all-nooer':
                    self.large = false;
                    self.context_template = {
                        courseupload: false,
                        copycoursetoteacher: true,
                        sharecoursecommunity: true,
                        copycoursetocategory: true,
                        sharecoursepombim: true,
                    };
                    break;
                default:
                    self.large = false;
                    self.context_template = {
                        courseupload: false,
                        copycoursetoteacher: true,
                        sharecoursecommunity: true,
                        copycoursetocategory: true,
                        sharecoursepombim: true,
                    };
            }
        }

        Str.get_strings([
            {key: 'menupopuptitle', component: 'community_sharecourse'},
            {key: 'cancel', component: 'community_sharecourse'},
        ]).done(function(strings) {
            Templates.render('community_sharecourse/selector', self.context_template)
                .done(function(html) {
                    var modalPromise = ModalFactory.create({
                        type: ModalFactory.types.DEFAULT,
                        large: self.large,
                        title: strings[0],
                        body: html
                    });

                    $.when(modalPromise).then(function(modal) {
                        modal.setButtonText('cancel', strings[1]);
                        modal.getRoot()[0].classList.add('sharemodal');
                        modal.getRoot()[0].classList.add('sharecoursemodal');
                        // $(modal.getRoot()[0]).find('.modal-header .close').html('<i class="fa-light fa-circle-xmark"></i>');
                        modal.getRoot().find('.modal-dialog').addClass('modal-dialog-centered');
                        modal.show();

                        modal.body.find('button').on("click", function(e) {
                            e.stopPropagation();
                            switch (e.currentTarget.dataset.handler) {
                                case 'uploadCourseToCatalog':
                                    self.flag_background_enable = true;
                                    modal.destroy();
                                    open_fragment(false);
                                    break;

                                case 'copyCourseToMyCategory':
                                    self.flag_background_enable = true;
                                    modal.destroy();

                                    $('.modal-backdrop.in').removeClass('show');
                                    $('.modal-backdrop.in').addClass('hide');
                                    open_course_copy(self.currentcourseid);
                                    break;

                                case 'copyCourseToTeacher':
                                    self.flag_background_enable = true;
                                    modal.destroy();

                                    $('.modal-backdrop.in').removeClass('show');
                                    $('.modal-backdrop.in').addClass('hide');
                                    open_copy_to_teacher();
                                    break;

                                case 'shareCourseCommunity':
                                    self.flag_background_enable = true;
                                    modal.destroy();

                                    $('.modal-backdrop.in').removeClass('show');
                                    $('.modal-backdrop.in').addClass('hide');
                                    open_share_to_community();
                                    break;

                                case 'shareCoursePombim':
                                    self.flag_background_enable = true;
                                    modal.destroy();

                                    $('.modal-backdrop.in').removeClass('show');
                                    $('.modal-backdrop.in').addClass('hide');
                                    open_share_to_social();
                                    break;
                            }
                        });

                        modal.getRoot().on(ModalEvents.hidden, function(e) {
                            if (self.flag_background_enable == true) {
                                $(".modal-backdrop").removeClass('hide');
                                $(".modal-backdrop").addClass('show');
                            } else {
                                $(".modal-backdrop").removeClass('show');
                                $(".modal-backdrop").addClass('hide');
                            }
                        });

                        modal.getRoot().on(ModalEvents.shown, function(e) {
                            self.flag_background_enable = false;
                        });

                        return modal;
                    }).fail(Notification.exception);
                })
                .fail(Notification.exception);
        });

    }

    function open_fragment(typeshare) {
        let self = this;

        const getBody = function() {

            // Get the content of the modal.
            var params = {courseid: self.currentcourseid, typeshare: typeshare};
            return Fragment.loadFragment('community_sharecourse', 'upload_course_to_catalog', self.currentcoursecontext, params);
        };

        Str.get_strings([
            {key: 'share_course_catalog_title', component: 'community_sharecourse'},
        ]).done(function(strings) {
            var modalPromise = ModalFactory.create({
                type: ModalFactory.types.DEFAULT,
                title: strings[0],
                body: getBody()
            });

            $.when(modalPromise).then(function(fmodal) {

                fmodal.setLarge();

                var root = fmodal.getRoot();
                root.on(ModalEvents.bodyRendered, function() {
                    root.find('.modal-body').animate({
                        scrollTop: 0
                    }, 0);

                    setTimeout(function() {
                        root.find('input:not([type=hidden])').first().focus();
                    }, 300);
                });

                return fmodal;
            }).done(function(modal) {
                modal.show();
            }).fail(Notification.exception);
        });

    }

    function open_copy_to_teacher() {
        let self = this;

        if (this.currentcourseid.length !== 0) {
            Str.get_strings([
                {key: 'copycoursetoteacher', component: 'community_sharecourse'},
                {key: 'send', component: 'community_sharecourse'},
                {key: 'back', component: 'community_sharecourse'},
                {key: 'copycoursesuccess', component: 'community_sharecourse'},
            ]).done(function(strings) {

                // Set html in modal.
                Ajax.call([{
                    methodname: 'community_sharequestion_copy_to_teacher_html',
                    args: {
                        currentcourseid: self.currentcourseid,
                        questionids: JSON.stringify([])
                    },
                    done: function(response) {

                        ModalFactory.create({
                            type: ModalFactory.types.SAVE_CANCEL,
                            title: strings[0],
                            body: response
                        }).done(function(modal) {
                            modal.setSaveButtonText(strings[1]);
                            modal.setButtonText('cancel', strings[2]);
                            $(modal.body).closest('.modal-content').addClass('share-with-teacher-modal');

                            // Send to teacher.
                            modal.getRoot().on(ModalEvents.save, function(e) {
                                e.preventDefault();
                                self.flag_background_enable = true;
                                modal.body.find('.error-teachers').hide();

                                var message = modal.body.find('[data-handler="messageForTeacher"]').val();

                                var teachersId = [];
                                modal.body.find('.tag-wrapper [data-teacherid]').each(function() {
                                    teachersId.push($(this).attr('data-teacherid'));
                                });

                                // Validation
                                var errors = [];
                                if (!teachersId.length) {
                                    errors.push('No users');
                                }

                                if (errors.length === 0) {
                                    Ajax.call([{
                                        methodname: 'community_sharecourse_submit_teachers',
                                        args: {
                                            courseid: self.currentcourseid,
                                            teachersid: JSON.stringify(teachersId),
                                            message: message
                                        },
                                        done: function(response) {
                                            let data = JSON.parse(response);
                                            if (data.result) {
                                                modal.destroy();
                                                open_success_popup(strings[0], strings[3]);
                                            }
                                        },
                                        fail: Notification.exception
                                    }]);
                                } else {
                                    self.flag_background_enable = false;
                                    modal.body.find('.error-teachers').show();
                                }
                            });

                            // Back button.
                            modal.getRoot().on(ModalEvents.cancel, function(e) {
                                self.flag_background_enable = true;
                                open_selector();
                            });

                            modal.getRoot().on(ModalEvents.hidden, function(e) {
                                if (self.flag_background_enable === true) {
                                    $(".modal-backdrop").removeClass('hide');
                                    $(".modal-backdrop").addClass('show');
                                } else {
                                    $(".modal-backdrop").removeClass('show');
                                    $(".modal-backdrop").addClass('hide');
                                }
                            });

                            modal.getRoot().on(ModalEvents.shown, function(e) {
                                self.flag_background_enable = false;
                                SendToTeacher.init(modal);
                            });

                            modal.show();
                        });

                    },
                    fail: Notification.exception
                }]);
            });
        }
    }

    function open_share_to_community() {
        let self = this;

        if (this.currentcourseid.length !== 0) {
            Str.get_strings([
                {key: 'sharecommunityteachers', component: 'community_sharecourse'},
                {key: 'share', component: 'community_sharecourse'},
                {key: 'back', component: 'community_sharecourse'},
                {key: 'copycoursesuccess', component: 'community_sharecourse'},
            ]).done(function(strings) {

                Ajax.call([{
                    methodname: 'community_sharewith_get_community',
                    args: {
                        activityid: 1,
                        courseid: self.currentcourseid
                    },
                    done: function(response) {

                        let data = JSON.parse(response);

                        Templates.render('community_sharecourse/sharecommunity', data)
                            .done(function (html, js) {
                                ModalFactory.create({
                                    type: ModalFactory.types.SAVE_CANCEL,
                                    title: strings[0],
                                    body: html
                                }).done(function(modal) {
                                    modal.setSaveButtonText(strings[1]);
                                    modal.setButtonText('cancel', strings[2]);
                                    $(modal.body).closest('.modal-content').addClass('share-with-teacher-modal');

                                    if(data.courses_enable === 0) {
                                        $(modal.footer).find('[data-action="save"]').hide()
                                    }

                                    // Send to community.
                                    modal.getRoot().on(ModalEvents.save, function(e) {
                                        e.preventDefault();
                                        self.flag_background_enable = true;

                                        modal.body.find('#error_share_to_community').hide();

                                        var message = modal.body.find('#message_for_teacher').val();
                                        var courseid = modal.body.find('#community_courses').val();

                                        // Validation.
                                        let errors = [];
                                        if(courseid.length === 0){
                                            errors.push('No course');
                                            modal.body.find('#error_share_to_community').show();
                                        }

                                        if (errors.length === 0) {
                                            Ajax.call([{
                                                methodname: 'community_sharecourse_submit_teachers',
                                                args: {
                                                    courseid: self.currentcourseid,
                                                    teachersid: JSON.stringify([]),
                                                    message: message
                                                },
                                                done: function(response) {
                                                    let data = JSON.parse(response);
                                                    if (data.result) {
                                                        modal.destroy();
                                                        open_success_popup(strings[0], strings[3]);
                                                    }
                                                },
                                                fail: Notification.exception
                                            }]);
                                        } else {
                                            self.flag_background_enable = false;
                                            modal.body.find('.error-teachers').show();
                                        }
                                    });

                                    // Back button.
                                    modal.getRoot().on(ModalEvents.cancel, function(e) {
                                        self.flag_background_enable = true;
                                        open_selector();
                                    });

                                    modal.getRoot().on(ModalEvents.hidden, function(e) {
                                        if (self.flag_background_enable === true) {
                                            $(".modal-backdrop").removeClass('hide');
                                            $(".modal-backdrop").addClass('show');
                                        } else {
                                            $(".modal-backdrop").removeClass('show');
                                            $(".modal-backdrop").addClass('hide');
                                        }
                                    });

                                    modal.getRoot().on(ModalEvents.shown, function(e) {
                                        self.flag_background_enable = false;

                                        var dropdownParent = modal.body.find(".smartselect2").closest('.select-wrapper');
                                        modal.body.find(".smartselect2").select2({
                                            dropdownAutoWidth: true,
                                            dropdownParent: dropdownParent
                                        });
                                    });

                                    modal.show();
                                });
                            })
                    },
                    fail: Notification.exception
                }]);
            });
        }
    }

    function open_share_to_social() {
        let self = this;

        if (this.currentcourseid.length !== 0) {

            Str.get_strings([
                {key: 'titlesharecoursepombim', component: 'community_sharecourse'},
                {key: 'approval', component: 'community_sharecourse'},
            ]).done(function(strings) {

                let data = {};
                Templates.render('community_sharecourse/sharesocial', data)
                    .done(function (html, js) {
                        ModalFactory.create({
                            type: ModalFactory.types.SAVE_CANCEL,
                            title: strings[0],
                            body: html
                        }).done(function(modal) {
                            modal.setSaveButtonText(strings[1]);
                            //modal.setButtonText('cancel', strings[2]);
                            $(modal.body).closest('.modal-content').addClass('share-with-teacher-modal');

                            // Send to social.
                            modal.getRoot().on(ModalEvents.save, function(e) {
                                e.preventDefault();
                                self.flag_background_enable = true;


                                let ifcopy = false;

                                let form = modal.getRoot().find('#popup_share_courses_pombim');
                                $(form).find(`input:checked`).each((count, obj) => {
                                    if ($(obj).attr("name") === 'ifcopy') {
                                        ifcopy = true;
                                    }
                                });

                                Ajax.call([{
                                    methodname: 'community_social_share_corse_pombim',
                                    args: {
                                        courseid: self.currentcourseid,
                                        ifcopy: ifcopy,
                                    },
                                    done: function(response) {
                                        modal.destroy();
                                    },
                                    fail: Notification.exception
                                }]);

                            });

                            modal.getRoot().on(ModalEvents.hidden, function(e) {
                                modal.destroy();
                            });

                            modal.show();
                        });
                    })
            });
        }
    }

    function open_success_popup(title, html) {
        let self = this;

        Str.get_strings([
            {key: 'back', component: 'community_sharecourse'},
            {key: 'end', component: 'community_sharecourse'},
        ]).done(function(strings) {
            var modalPromise = ModalFactory.create({
                type: ModalFactory.types.SAVE_CANCEL,
                // Large: true,
                title: title,
                body: html
            });

            $.when(modalPromise).then(function(modal) {
                modal.setSaveButtonText(strings[0]);
                modal.setButtonText('cancel', strings[1]);
                modal.show();

                // Back button.
                modal.getRoot().on(ModalEvents.save, function(e) {
                    self.flag_background_enable = true;
                    open_selector();
                });

                modal.getRoot().on(ModalEvents.hidden, function(e) {
                    if (self.flag_background_enable === true) {
                        $(".modal-backdrop").removeClass('hide');
                        $(".modal-backdrop").addClass('show');
                    } else {
                        $(".modal-backdrop").removeClass('show');
                        $(".modal-backdrop").addClass('hide');
                    }
                });

                modal.getRoot().on(ModalEvents.shown, function(e) {
                    self.flag_background_enable = false;
                });

                return modal;
            }).fail(Notification.exception);
        });
    }

    function open_course_copy(currentcourseid) {

        var renderPopupCopyCourse = function(response, typeshare) {
            var context = {
                showAllCategories: response.isadmin,
                categories: JSON.parse(response.categories),
                teachercatid: JSON.parse(response.teachercatid)
            };

            Templates.render('community_sharecourse/copy_to_category', context)
                .done(function(html, js) {

                    Str.get_strings([
                        {key: 'selectioncategories', component: 'community_sharecourse'},
                    ]).done(function(strings) {

                        ModalFactory.create({
                            type: ModalFactory.types.DEFAULT,
                            title: strings[0],
                            body: html
                        }).done(function(modal) {
                            modal.getRoot().find('.modal-dialog').addClass('copy-course-to-my-category-modal');
                            modal.setLarge();

                            modal.getRoot().on(ModalEvents.shown, function(e) {
                                var dropdownParent = modal.body.find(".smartselect2").closest('.select-wrapper');
                                modal.body.find(".smartselect2").select2({
                                    dropdownAutoWidth: true,
                                    dropdownParent: dropdownParent
                                });
                            });

                            // Close button.
                            modal.getRoot().find('button.close-button').on('click', function(e) {
                                modal.destroy();
                            });

                            // Success button.
                            modal.getRoot().find('button.success-button').on('click', function(e) {

                                let categoryid = modal.getRoot().find(':selected').attr('data-categoryid');

                                let type = '';
                                if(typeshare){
                                    type = 'coursecopy_share';
                                }else{
                                    type = 'coursecopy';
                                }

                                Ajax.call([{
                                    methodname: 'community_sharecourse_add_sharecourse_task',
                                    args: {
                                        sourcecourseid: Number(currentcourseid),
                                        categoryid: Number(categoryid),
                                        type: type
                                    },
                                    done: function(response) {
                                        modal.destroy();

                                        Str.get_strings([
                                            {key: 'eventcoursecopy', component: 'community_sharecourse'},
                                            {key: 'course_copied_to_category', component: 'community_sharecourse'},
                                            {key: 'finish', component: 'community_sharecourse'},
                                        ]).done(function(strings) {
                                            ModalFactory.create({
                                                type: ModalFactory.types.ALERT,
                                                title: strings[0],
                                                body: strings[1]
                                            }).done(function(modal) {
                                                modal.setButtonText('cancel', strings[2]);
                                                modal.show();
                                            });
                                        });
                                    },
                                    fail: Notification.exception
                                }]);
                            });

                            modal.show();
                        });
                    });
                })
                .fail(Notification.exception);
        }

        var parseResponse = function(response) {

            if (response.typeshare === true) {
                Str.get_strings([
                    {key: 'coursereuploadtocatalog', component: 'community_sharecourse'},
                    {key: 'couse_copied_from_catalog', component: 'community_sharecourse'},
                    {key: 'disablepopupsubmit', component: 'community_sharecourse'},
                ]).done(function(strings) {
                    var modalPromise = ModalFactory.create({
                        type: ModalFactory.types.SAVE_CANCEL,
                        title: strings[0],
                        body: strings[1]
                    });

                    $.when(modalPromise).then(function(fmodal) {

                        fmodal.setSaveButtonText(strings[2]);

                        // Handle save event.
                        fmodal.getRoot().on(ModalEvents.save, function(e) {
                            open_fragment(true);
                            //renderPopupCopyCourse(response, true);
                        });

                        // Handle cancel event.
                        fmodal.getRoot().on(ModalEvents.cancel, function(e) {
                            renderPopupCopyCourse(response, false);
                        });

                        return fmodal;
                    }).done(function(modal) {
                        modal.show();
                    }).fail(Notification.exception);
                });
            } else {
                renderPopupCopyCourse(response, false);
            }

            return true;
        };

        Ajax.call([{
            methodname: 'community_sharecourse_popup_copy_course',
            args: {
                courseid: Number(currentcourseid)
            },
            done: parseResponse,
            fail: Notification.exception
        }]);
    }

    return {
        init: function(currentcourseid, currentcoursecontext, visiblebuttons) {
            let self = this;

            // Event on button share.
            $('.btn-share-course').on("click", function(e) {
                open_selector(currentcourseid, currentcoursecontext, visiblebuttons);
            });

            // Event on button disable share.
            $('.btn-disable-share-course').on("click", function(e) {

                Str.get_strings([
                    {key: 'disablepopuptitle', component: 'community_sharecourse'},
                    {key: 'disablepopupbody', component: 'community_sharecourse'},
                    {key: 'disablepopupsubmit', component: 'community_sharecourse'},
                ]).done(function(strings) {
                    var modalPromise = ModalFactory.create({
                        type: ModalFactory.types.SAVE_CANCEL,
                        title: strings[0],
                        body: strings[1]
                    });

                    $.when(modalPromise).then(function(modal) {
                        modal.setSaveButtonText(strings[2]);
                        modal.show();

                        // Submit button.
                        modal.getRoot().on(ModalEvents.save, function(e) {

                            Ajax.call([{
                                methodname: 'community_sharecourse_unshare_course',
                                args: {
                                    courseid: currentcourseid,
                                },
                                done: function(response) {
                                    // Modal.destroy();

                                    // Redirect.
                                    window.location.replace(M.cfg.wwwroot + '/course/view.php?id=' + currentcourseid);
                                },
                                fail: Notification.exception
                            }]);
                        });

                        return modal;
                    }).fail(Notification.exception);
                });
            });
        },

        message_edit_init: function(currentcourseid, currentcoursecontext) {

            // Event on button.
            $('body').on("click", function(e) {
                if ($(e.target).data('handler') === 'copyCourseFromMessage') {
                    let courseid = $(e.target).data('courseid');
                    open_course_copy(courseid);
                }
            });
        },

        download_from_catalog: function(courseid) {
            open_course_copy(courseid);
        },
    };
});
