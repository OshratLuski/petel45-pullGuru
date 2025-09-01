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
    'community_sharequestion/select2'

], function($, Y, Str, ModalFactory, ModalEvents, Ajax, Templates, Notification, Fragment, SendToTeacher) {
    `use strict`;

    const sesskey = M.cfg.sesskey;
    const selected_questions = [];
    const currentcourseid = null;
    const currentcoursecontext = null;
    const flag_background_enable = false;
    const large = false;
    const context_template = {};

    /**
     * @param modal
     * @param sclass
     */
    function build_select2(modal, sclass) {
        var select2Target = modal.body.find(sclass);
        var dropdownParent = select2Target.closest('.select-wrapper');
        select2Target.select2({
            dropdownAutoWidth: true,
            dropdownParent: dropdownParent
        });
    }

    /**
     * @param selected
     * @param currentcourseid
     * @param currentcoursecontext
     * @param type
     */
    function open_selector(selected = null, currentcourseid = null, currentcoursecontext = null, type = null) {
        let self = this;

        if (selected !== null) {
            this.selected_questions = selected;
        }
        if (currentcourseid !== null) {
            this.currentcourseid = currentcourseid;
        }
        if (currentcoursecontext !== null) {
            this.currentcoursecontext = currentcoursecontext;
        }

        switch (type) {
            case 'all':
                self.large = true;
                self.context_template = {
                    questionupload: true,
                    copyquestionstoteacher: true,
                    copyquestionstocategory: true,
                    copyquestionstoquiz: true,
                };
                break;
            case 'all-nooer':
                self.large = true;
                self.context_template = {
                    questionupload: false,
                    copyquestionstoteacher: true,
                    copyquestionstocategory: true,
                    copyquestionstoquiz: true,
                };
                break;
            case 'message':
                self.large = false;
                self.context_template = {
                    copyquestionstocategory: true,
                    copyquestionstoquiz: true,
                };
                break;
            case 'oer':
                self.large = false;
                self.context_template = {
                    // Copyquestionstoteacher: true,
                    copyquestionstocategory: true,
                    copyquestionstoquiz: true,
                };
                break;
        }

        if (this.selected_questions.length !== 0) {
                Str.get_strings([
                    {key: 'menupopuptitle', component: 'community_sharequestion'},
                    {key: 'cancel', component: 'community_sharequestion'},
                ]).done(function(strings) {
                    Templates.render('community_sharequestion/selector', self.context_template)
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
                                modal.getRoot()[0].classList.add('sharequestionmodal');
                                // $(modal.getRoot()[0]).find('.modal-header .close').html('<i class="fa-light fa-circle-xmark"></i>');
                                modal.getRoot().find('.modal-dialog').addClass('modal-dialog-centered');
                                modal.show();
                                modal.body.find('button').on("click", function(e) {
                                    e.stopPropagation();
                                    switch (e.currentTarget.dataset.handler) {
                                        case 'copyQuestionsToQuiz':
                                            self.flag_background_enable = true;
                                            modal.destroy();
                                            open_copy_to_quiz();
                                            break;
                                        case 'copyQuestionsToCategory':
                                            self.flag_background_enable = true;
                                            modal.destroy();
                                            open_copy_to_category();
                                            break;
                                        case 'uploadQuestionsToMaagar':
                                            self.flag_background_enable = true;
                                            modal.destroy();
                                            open_upload_to_catalog();
                                            break;
                                        case 'copyQuestionsToTeacher':
                                            self.flag_background_enable = true;
                                            modal.destroy();
                                            open_copy_to_teacher();
                                            break;
                                    }
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
                        })
                        .fail(Notification.exception);
            });
        }
    }

    /**
     *
     */
    function open_copy_to_quiz() {
        let self = this;

        if (this.selected_questions.length !== 0) {
            Str.get_strings([
                {key: 'copyquestionstoquiz', component: 'community_sharequestion'},
                {key: 'copy', component: 'community_sharequestion'},
                {key: 'back', component: 'community_sharequestion'},
                {key: 'copyquestionstoquizsuccess', component: 'community_sharequestion'},
            ]).done(function(strings) {

                // Set html in modal.
                Ajax.call([{
                    methodname: 'community_sharequestion_copy_to_quiz_html',
                    args: {
                        currentcourseid: self.currentcourseid
                    },
                    done: function(response) {

                        ModalFactory.create({
                            type: ModalFactory.types.SAVE_CANCEL,
                            title: strings[0],
                            body: response
                        }).done(function(modal) {
                            modal.setSaveButtonText(strings[1]);
                            modal.setButtonText('cancel', strings[2]);

                            // Build select2.
                            modal.getRoot().addClass('copy-question-to-cat-modal');
                            build_select2(modal, ".select-course");
                            build_select2(modal, ".select-quiz");

                            // Event change course.
                            modal.body.find('.select-course').on("change", function(e) {
                                modal.body.find('.select-quiz-error').hide();

                                let courseid = this.value;
                                Ajax.call([{
                                    methodname: 'community_sharequestion_get_quizes_by_course',
                                    args: {
                                        courseid: courseid,
                                    },
                                    done: function(response) {
                                        let data = JSON.parse(response);

                                        // Fill options.
                                        modal.body.find('.select-quiz').empty();
                                        $.each(data.activities, function(index, obj) {
                                            modal.body.find('.select-quiz').append('<option value="' + obj.cmid + '">' + obj.name + '</option>');
                                        });
                                    },
                                    fail: Notification.exception
                                }]);
                            });

                            // Send to cron.
                            modal.getRoot().on(ModalEvents.save, function(e) {
                                e.preventDefault();

                                self.flag_background_enable = true;

                                let value = modal.body.find('.select-quiz').val();

                                if (value !== null) {

                                    // Save to cron table.
                                    Ajax.call([{
                                        methodname: 'community_sharequestion_save_questions_to_cron',
                                        args: {
                                            type: 'copy_to_quiz',
                                            targetid: value,
                                            questionids: JSON.stringify(self.selected_questions)
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
                                    modal.body.find('.select-quiz-error').show();
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
                            });

                            modal.show();
                        });

                    },
                    fail: Notification.exception
                }]);
            });
        }
    }

    /**
     *
     */
    function open_copy_to_category() {
        let self = this;

        if (this.selected_questions.length !== 0) {
            Str.get_strings([
                {key: 'copyquestionstocategory', component: 'community_sharequestion'},
                {key: 'copy', component: 'community_sharequestion'},
                {key: 'back', component: 'community_sharequestion'},
                {key: 'copyquestionstoquizsuccess', component: 'community_sharequestion'},
            ]).done(function(strings) {

                // Set html in modal.
                Ajax.call([{
                    methodname: 'community_sharequestion_copy_to_category_html',
                    args: {
                        currentcourseid: self.currentcourseid
                    },
                    done: function(response) {

                        ModalFactory.create({
                            type: ModalFactory.types.SAVE_CANCEL,
                            title: strings[0],
                            body: response
                        }).done(function(modal) {
                            modal.setSaveButtonText(strings[1]);
                            modal.setButtonText('cancel', strings[2]);

                            // Build select2.
                            modal.getRoot().addClass('copy-question-to-cat-modal');
                            build_select2(modal, ".select-course");
                            build_select2(modal, ".select-category");

                            // Event change course.
                            modal.body.find('.select-course').on("change", function(e) {
                                modal.body.find('.select-category-error').hide();

                                let courseid = this.value;
                                Ajax.call([{
                                    methodname: 'community_sharequestion_get_categories_by_course',
                                    args: {
                                        courseid: courseid,
                                    },
                                    done: function(response) {
                                        let data = JSON.parse(response);

                                        // Fill options.
                                        modal.body.find('.select-category').empty();
                                        $.each(data.categories, function(index, obj) {
                                            modal.body.find('.select-category').append('<option value="' + obj.id + '">' + obj.name + '</option>');
                                        });
                                    },
                                    fail: Notification.exception
                                }]);
                            });

                            // Send to cron.
                            modal.getRoot().on(ModalEvents.save, function(e) {
                                e.preventDefault();

                                self.flag_background_enable = true;

                                let value = modal.body.find('.select-category').val();

                                if (value != null) {

                                    // Save to cron table.
                                    Ajax.call([{
                                        methodname: 'community_sharequestion_save_questions_to_cron',
                                        args: {
                                            type: 'copy_to_category',
                                            targetid: value,
                                            questionids: JSON.stringify(self.selected_questions)
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
                                    modal.body.find('.select-category-error').show();
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
                            });

                            modal.show();
                        });

                    },
                    fail: Notification.exception
                }]);
            });
        }
    }

    /**
     *
     */
    function open_upload_to_catalog() {
        let self = this;

        if (this.selected_questions.length !== 0) {

            const getBody = function() {

                // Get the content of the modal.
                var params = {courseid: self.currentcourseid, selected_questions: JSON.stringify(self.selected_questions)};
                return Fragment.loadFragment('community_sharequestion', 'upload_questions_maagar', self.currentcoursecontext, params);
            };

            Str.get_strings([
                {key: 'share_national_shared', component: 'community_sharequestion'},
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
    }

    /**
     * @param title
     * @param html
     */
    function open_success_popup(title, html) {
        let self = this;

        Str.get_strings([
            {key: 'back', component: 'community_sharequestion'},
            {key: 'end', component: 'community_sharequestion'},
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

                // Close popup on timeout.
                // setTimeout(function(){
                //     self.flag_background_enable = false;
                //     modal.destroy()
                // }, 4000);

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

    /**
     *
     */
    function open_copy_to_teacher() {
        let self = this;

        if (this.selected_questions.length !== 0) {
            Str.get_strings([
                {key: 'copyquestionstoteacher', component: 'community_sharequestion'},
                {key: 'send', component: 'community_sharequestion'},
                {key: 'back', component: 'community_sharequestion'},
                {key: 'copyquestionstoquizsuccess', component: 'community_sharequestion'},
            ]).done(function(strings) {

                // Set html in modal.
                Ajax.call([{
                    methodname: 'community_sharequestion_copy_to_teacher_html',
                    args: {
                        currentcourseid: self.currentcourseid,
                        questionids: JSON.stringify(self.selected_questions)
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
                                        methodname: 'community_sharequestion_submit_teachers',
                                        args: {
                                            questionids: JSON.stringify(self.selected_questions),
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

    return {
        question_edit_init: function(currentcourseid, currentcoursecontext, visiblebuttons) {

            // Insert button via JS.
            $('.modulespecificbuttonscontainer').find('input').each(function(index) {
                if (index === 1) {

                    let self = this;

                    Str.get_strings([
                        {key: 'sharingbutton', component: 'community_sharequestion'},
                    ]).done(function(strings) {

                        let disabled = 'disabled="1"';

                        let element = '<input type=button class="btn-share-questions btn btn-secondary mr-1" value="' + strings[0] + '" data-action="toggle" data-togglegroup="qbank" data-toggle="action" ' + disabled + '>';
                        $(element).insertBefore($(self));

                        // Event on button.
                        $('.btn-share-questions').on("click", function(e) {
                            // Get selected questions.
                            var checkedVals = $('#categoryquestions').find('input:checked').map(function() {
                                return this.name;
                            }).get();

                            let selected = [];
                            $.each(checkedVals, function(index, value) {
                                var sd = value.replace(/[^0-9]/gi, '');
                                if (sd.length !== 0) {
                                    var number = parseInt(sd, 10);
                                    selected.push(number);
                                }
                            });

                            open_selector(selected, currentcourseid, currentcoursecontext, visiblebuttons);
                        });
                    });
                }
            });
        },

        mod_quiz_edit_init: function(currentcourseid, currentcoursecontext, visiblebuttons) {

            mod_quiz_edit_toogle_buttons();

            // Event on button share.
            $('.btn-share-questions').on("click", function(e) {

                // Selected_questions.
                const questionsContainer = document.querySelector('div.mod-quiz-edit-content');
                const checkboxes = questionsContainer.querySelectorAll('input.select-multiple-checkbox:checked');
                const selected_questions = [];
                checkboxes.forEach(checkbox => {
                    const questionContainer = checkbox.closest('li.activity');
                    if (questionContainer) {
                        const activityInstance = questionContainer.querySelector('div.activityinstance');
                        if (activityInstance) {
                            const link = activityInstance.querySelector('a[href*="id="]');
                            if (link) {
                                const questionHref = link.getAttribute('href');
                                const questionIdMatch = questionHref.match(/[?&]id=(\d+)/);
                                if (questionIdMatch) {
                                    const questionRealId = questionIdMatch[1];
                                    selected_questions.push(questionRealId);
                                }
                            }
                        }
                    }
                });

                open_selector(selected_questions, currentcourseid, currentcoursecontext, visiblebuttons);
            });

            /**
             *
             */
            function mod_quiz_edit_recalculate_page() {
                let pageid = '';

                $('.section li').each(function(index) {
                    if ($(this).hasClass('pagenumber')) {
                        pageid = $(this).attr('id');
                    }

                    $(this).find('input').data('pageid', pageid);
                });
            }

            /**
             *
             */
            function mod_quiz_edit_toogle_buttons() {

                let flag = false;

                $('.select-multiple-checkbox-share').each(function(index) {
                    if ($(this).find('input').is(':checked')) {
                        flag = true;
                    }
                });

                if (flag) {
                    $('.btn-delete-questions').attr('disabled', false);
                    $('.btn-share-questions').attr('disabled', false);
                } else {
                    $('.btn-delete-questions').attr('disabled', true);
                    $('.btn-share-questions').attr('disabled', true);
                }

                if ($('.btn-delete-questions').data('hasattempts') === 1) {
                    $('.btn-delete-questions').attr('disabled', true);
                }
            }

            // Select all checkboxes in group.
            $('.checkbox-select-all-questions').on("click", function(e) {

                mod_quiz_edit_recalculate_page();

                let pageid = $(this).data('pageid');

                if ($(this).is(':checked')) {
                    $('.select-multiple-checkbox-share').each(function(index) {
                        if ($(this).find('input').data('pageid') === pageid) {
                            $(this).find('input').prop("checked", true);
                        }
                    });
                } else {
                    $('.select-multiple-checkbox-share').each(function(index) {
                        if ($(this).find('input').data('pageid') === pageid) {
                            $(this).find('input').prop("checked", false);
                        }
                    });
                }

                mod_quiz_edit_toogle_buttons();
            });

            // Click on single button.
            $('.select-multiple-checkbox').on("click", function(e) {

                mod_quiz_edit_recalculate_page();

                if (!$(this).is(':checked')) {
                    let pageid = $(this).data('pageid');

                    $('.checkbox-select-all-questions').each(function(index) {
                        if ($(this).data('pageid') === pageid) {
                            $(this).prop("checked", false);
                        }
                    });
                }

                mod_quiz_edit_toogle_buttons();
            });
        },

        message_edit_init: function(currentcourseid, currentcoursecontext) {

            // Event on button.
            $('body').on("click", function(e) {
                if ($(e.target).data('handler') === 'copyQuestionsFromMessage') {

                    let selected = $(e.target).data('questionids');
                    open_selector(selected, currentcourseid, currentcoursecontext, 'message');
                }
            });
        },

        community_oer_question: function(selected) {
            open_selector(selected, 0, 0, 'oer');
        },
    };
});
