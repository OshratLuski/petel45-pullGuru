define([
    'jquery',
    'core/str',
    'core/modal_factory',
    'core/modal_events',
    'core/ajax',
    'core/templates',
    'core/notification',
    'core/fragment',
    'community_sharequestion/inview'

], function($, Str, ModalFactory, ModalEvents, Ajax, Templates,
    Notification, Fragment, inView) {
    `use strict`;

    let uniqueid;
    let cmid;

    let SELECTORS = {
        buttonEvent: '.addquestionfrommycourses'
    };

    /**
     *
     */
    function updateCountSelected() {
        let form = $('#copy_from_my_courses_' + uniqueid);
        let counter = 0;

        form.find('.checkbox-select-question').each(function(index) {
            if ($(this).prop('checked')) {
                counter++;
            }
        });

        $('#checked-question-counter-num-' + uniqueid).html(counter);
    }

    // Render loading.
    /**
     * @param target
     */
    function loadingIcon(target) {
        Templates.render('community_sharequestion/loading', {}).done(function(html, js) {
            Templates.replaceNodeContents(target, html, js);
        }).fail(Notification.exception);
    }

    // Render courses.
    /**
     *
     */
    function renderCoursesForUser() {

        loadingIcon($('#popup_block_content_courses_' + uniqueid));

        let search = $('#search_' + uniqueid).val();
        if (search === undefined) {
            search = "";
        }

        Ajax.call([{
            methodname: 'community_sharequestion_get_courses_by_user',
            args: {
                search: search,
                uniqueid: uniqueid
            },
            done: function(response) {

                let data = JSON.parse(response);
                // Render courses.
                Templates.render('community_sharequestion/copy_from_my_courses/courses', data.result)
                    .done(function(html, js) {

                        Templates.replaceNodeContents($('#popup_block_content_courses_' + uniqueid), html, js);

                        $('#all_checkboxes_' + uniqueid).prop('checked', false);
                        updateCountSelected();
                    })
                    .fail(Notification.exception);
            },
            fail: Notification.exception
        }]);
    }

    return {
        init: function(currentcourseid, currentcoursecontext) {

            // Get the content of the modal.
            const getBody = function(coursemoduleid) {
                uniqueid = Date.now();
                cmid = coursemoduleid;

                let params = {cmid: coursemoduleid, uniqueid: uniqueid};
                return Fragment.loadFragment('community_sharequestion', 'copy_questions_from_my_courses', currentcoursecontext, params);
            };

            $(SELECTORS.buttonEvent).on("click", function(e) {
                e.preventDefault();

                let cmid = $(this).data('cmid');

                Str.get_strings([
                    {key: 'copyquestionsfrommycourses', component: 'community_sharequestion'},
                    {key: 'qshare', component: 'community_sharequestion'},
                ]).done(function(strings) {
                    var modalPromise = ModalFactory.create({
                        type: ModalFactory.types.SAVE_CANCEL,
                        title: strings[0],
                        body: getBody(cmid)
                    });

                    $.when(modalPromise).then(function(fmodal) {

                        fmodal.setSaveButtonText(strings[1]);

                        // Handle save event.
                        fmodal.getRoot().on(ModalEvents.save, function(e) {
                            e.preventDefault();

                            let form = $('#copy_from_my_courses_' + uniqueid);
                            let selected = [];

                            form.find('.checkbox-select-question').each(function(index) {
                                if ($(this).prop('checked')) {
                                    selected.push($(this).data('qid'));
                                }
                            });

                            if (selected.length) {

                                // Close popup.
                                fmodal.destroy();

                                // Success popup.
                                Str.get_strings([
                                    {key: 'popupmessagesuccesstitle', component: 'community_sharequestion'},
                                    {key: 'popupmessagesuccesscontent', component: 'community_sharequestion'},
                                    {key: 'popupbuttondisabled', component: 'community_sharequestion'},
                                    {key: 'popupbuttonenabled', component: 'community_sharequestion'}
                                ]).done(function(strings) {

                                    var modalPromise = ModalFactory.create({
                                        type: ModalFactory.types.ALERT,
                                        title: strings[0],
                                        body: strings[1]
                                    });

                                    $.when(modalPromise).then(function(fmodal) {
                                        fmodal.show();
                                        let root = fmodal.getRoot();

                                        // Refresh page.
                                        root.on(ModalEvents.cancel, function() {
                                            location.reload();
                                        });

                                        // When shown popup.
                                        root.on(ModalEvents.shown, function() {
                                            root.find('*[data-action="cancel"]').text(strings[2]);
                                            root.find('*[data-action="cancel"]').prop('disabled', true);

                                            Ajax.call([{
                                                methodname: 'community_sharequestion_save_questions_to_quiz',
                                                args: {
                                                    cmid: cmid,
                                                    qids: JSON.stringify(selected)
                                                },
                                                done: function(response) {
                                                    let data = JSON.parse(response);

                                                    root.find('*[data-action="cancel"]').text(strings[3]);
                                                    root.find('*[data-action="cancel"]').prop('disabled', false);
                                                },
                                                fail: Notification.exception
                                            }]);

                                        });

                                        return fmodal;
                                    }).fail(Notification.exception);
                                });

                            } else {
                                // Fail popup.
                                Str.get_strings([
                                    {key: 'popupmessagefailtitle', component: 'community_sharequestion'},
                                    {key: 'popupmessagefailcontent', component: 'community_sharequestion'}
                                ]).done(function(strings) {

                                    var modalPromise = ModalFactory.create({
                                        type: ModalFactory.types.ALERT,
                                        title: strings[0],
                                        body: strings[1]
                                    });

                                    $.when(modalPromise).then(function(fmodal) {
                                        fmodal.show();
                                        return fmodal;
                                    }).fail(Notification.exception);
                                });
                            }

                        });

                        fmodal.getModal().addClass('modal-xlg');
                        fmodal.getModal().addClass('import-questions-modal');
                        var root = fmodal.getRoot();
                        root.on(ModalEvents.shown, function() {

                            setTimeout(function() {
                                    root.find('.modal-body').animate({
                                        scrollTop: 0
                                    }, 200);

                                    // Render courses.
                                    renderCoursesForUser();
                                }, 500);
                        });

                        return fmodal;
                    }).done(function(modal) {
                        modal.show();
                    }).fail(Notification.exception);
                });

            });
        },

        actionsMain: function() {
            let form = $('#copy_from_my_courses_' + uniqueid);

            // Search input change.
            let search = $('#search_' + uniqueid);
            search.change(function() {

                // Render courses.
                renderCoursesForUser();

                if (search.val() === '') {
                    // Hide close icon and show search icon.
                    search.siblings('.question-search-icon').show();
                    search.siblings('.question-search-close-icon').hide();
                } else {
                    // Hide search icon and show close icon.
                    search.siblings('.question-search-icon').hide();
                    search.siblings('.question-search-close-icon').show();
                }
            });

            // Clear search input
            form.find('.question-search-close-icon').on('click keydown', (e) => {
                if (e.type === "click" || e.type === "keydown" && e.which === 13) {

                    const targetInput = $("#search_" + uniqueid);

                    // Refreash search results
                    $('#search_' + uniqueid).val('');
                    renderCoursesForUser();

                    // Show search icon
                    targetInput.siblings('.question-search-close-icon').hide();
                    targetInput.siblings('.question-search-icon').show();
                }
            });

            // Checkbox select all.
            $('#all_checkboxes_' + uniqueid).change(function() {
                if (this.checked) {
                    form.find('.checkbox-select-question').each(function(index) {
                        $(this).prop('checked', true);
                    });
                } else {
                    form.find('.checkbox-select-question').each(function(index) {
                        $(this).prop('checked', false);
                    });
                }

                updateCountSelected();
            });
        },

        actionsCategories: function() {
            let form = $('#copy_from_my_courses_' + uniqueid);

            // Open collapse - all course categories
            $(form).find('.all-course-categories-wrapper-collapse').on('show.bs.collapse', function(e) {
                e.stopPropagation();
                let target = $(e.target);
                let courseid = target.data('courseid');
                let search = $('#search_' + uniqueid).val();

                // Open course categories.
                if (!target.hasClass('view-done') && target.hasClass('all-course-categories-wrapper-collapse')) {
                    loadingIcon(target);

                    Ajax.call([{
                        methodname: 'community_sharequestion_get_question_categories_by_course',
                        args: {
                            courseid: courseid,
                            uniqueid: uniqueid,
                            search: search
                        },
                        done: function(response) {
                            let data = JSON.parse(response);
                            Templates.render('community_sharequestion/copy_from_my_courses/course-categories', data.result)
                                .done(function(html, js) {
                                    Templates.replaceNodeContents(target, html, js);
                                    target.addClass('view-done');
                                })
                                .fail(Notification.exception);
                            },
                        fail: Notification.exception
                    }]);
                } else if (!target.hasClass('view-done') && target.hasClass('cat-wrapper-collapse')) {

                    // Open category questions.
                    loadingIcon(target);

                    let catid = target.data('catid');

                    Ajax.call([{
                        methodname: 'community_sharequestion_get_questions_by_category',
                        args: {
                            catid: catid,
                            uniqueid: uniqueid,
                            search: search
                        },
                        done: function(response) {
                                let data = JSON.parse(response);
                                // Render block category questions.
                                Templates.render('community_sharequestion/copy_from_my_courses/category-questions', data.result)
                                    .done(function(html, js) {
                                        Templates.replaceNodeContents(target, html, js);
                                        target.addClass('view-done');
                                    })
                                    .fail(Notification.exception);
                            },
                        fail: Notification.exception
                    }]);
                }
            });

        },

        actionsBankCategories: function() {
            let form = $('#copy_from_my_courses_' + uniqueid);

            // Open collapse - all course categories
            $(form).find('.all-bank-categories-wrapper-collapse').on('show.bs.collapse', function(e) {
                e.stopPropagation();
                let target = $(e.target);
                let courseid = target.data('courseid');
                let search = $('#search_' + uniqueid).val();

                // Open course categories.
                if (!target.hasClass('view-done') && target.hasClass('all-bank-categories-wrapper-collapse')) {
                    loadingIcon(target);

                    Ajax.call([{
                        methodname: 'community_sharequestion_get_bank_categories_on_course',
                        args: {
                            courseid: courseid,
                            uniqueid: uniqueid,
                            search: search
                        },
                        done: function(response) {
                            let data = JSON.parse(response);
                            Templates.render('community_sharequestion/copy_from_my_courses/bank-categories', data.result)
                                .done(function(html, js) {
                                    Templates.replaceNodeContents(target, html, js);
                                    target.addClass('view-done');
                                })
                                .fail(Notification.exception);
                        },
                        fail: Notification.exception
                    }]);
                } else if (!target.hasClass('view-done') && target.hasClass('bank-categories-wrapper-collapse')) {

                    // Open category questions.
                    loadingIcon(target);

                    let catid = target.data('catid');

                    Ajax.call([{
                        methodname: 'community_sharequestion_get_questions_by_category',
                        args: {
                            catid: catid,
                            uniqueid: uniqueid,
                            search: search
                        },
                        done: function(response) {
                            let data = JSON.parse(response);
                            // Render block category questions.
                            Templates.render('community_sharequestion/copy_from_my_courses/category-questions', data.result)
                                .done(function(html, js) {
                                    Templates.replaceNodeContents(target, html, js);
                                    target.addClass('view-done');
                                })
                                .fail(Notification.exception);
                        },
                        fail: Notification.exception
                    }]);
                }
            });

        },

        actionsQuestions: function() {
            let form = $('#copy_from_my_courses_' + uniqueid);

            // Open iframe question.
            inView('#copy_from_my_courses_' + uniqueid + ' .question-collapse').on('enter', function(e) {
                let parent = $(e);
                if (!parent.hasClass('inview-done')) {
                    parent.addClass('inview-done');
                    let qid = parent.data('qid');
                    let spinner = parent.find('.raspberry_loading');
                    spinner.show();
                    let html = '<iframe id="iframe' + qid + uniqueid + '" scrolling="no" style="width:100%; height:300px;" src="' + M.cfg.wwwroot + '/local/community/plugins/oer/previewquestion.php?id=' + qid + '&behaviour=adaptive"></iframe>';
                    parent.find('.question-collapse-inner').html(html);
                    document.querySelector('#iframe' + qid + uniqueid).addEventListener('load', function() {
                        spinner = parent.find('.raspberry_loading');
                        spinner.remove();

                        const btn = parent.find('.inner-copy-question-btn');
                        btn.show();
                    });
                }

            }).on('exit', el => { });


            $('.course-wrapper-name').on('click', function() {
                const target = $(this);
                if (target.hasClass('collapsed')) {
                    return;
                } else {
                    const catCollapse = target.next('.course-wrapper-name-collapse').find('.cat-wrapper-collapse');
                    catCollapse.each(function(el) {
                        $(this).collapse('hide');
                    });
                }
            });

            $('.type-cell, .name-cell, .create-cell, .create-date-cell, .update-date-cell').on("click", function(e) {
                const target = $(e.currentTarget);
                const parent = target.closest('.question-item-wrapper');
                const collapseBtn = parent.find('.collapse-question-btn');
                collapseBtn.trigger('click');
            });

            form.find('.checkbox-select-question').change(function() {
                updateCountSelected();

                let flagselected = true;
                form.find('.checkbox-select-question').each(function(index) {
                    if ($(this).prop('checked') === false) {
                        flagselected = false;
                    }
                });

                if (flagselected === false) {
                    $('#all_checkboxes_' + uniqueid).prop('checked', false);
                } else {
                    $('#all_checkboxes_' + uniqueid).prop('checked', true);
                }
            });

        },

    };
});
