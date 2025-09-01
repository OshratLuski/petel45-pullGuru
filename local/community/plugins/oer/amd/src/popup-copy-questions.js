define([
    'jquery',
    'core/str',
    'core/modal_factory',
    'core/modal_events',
    'core/ajax',
    'core/templates',
    'core/notification',
    'core/fragment'

], function ($, Str, ModalFactory, ModalEvents, Ajax, Templates,
    Notification, Fragment) {
    `use strict`;

    let uniqueid;
    let cmid;

    let SELECTORS = {
        buttonEvent: '.addquestionfromoer'
    };

    function updateCountSelected() {
        let form = $('#copy_from_my_courses_' + uniqueid);
        let counter = 0;

        form.find('.checkbox-select-question').each(function (index) {
            if ($(this).prop('checked')) {
                counter++;
            }
        });

        $('#checked-question-counter-num-' + uniqueid).html(counter);
    }

    // Render loading.
    function loadingIcon(target) {
        Templates.render('community_oer/loading', {}).done(function (html, js) {
            Templates.replaceNodeContents(target, html, js);
        }).fail(Notification.exception);
    }

    return {
        init: function (currentcourseid, currentcoursecontext) {

            // Get the content of the modal.
            const getBody = function (coursemoduleid) {
                uniqueid = Date.now();
                cmid = coursemoduleid;

                let params = { cmid: coursemoduleid, uniqueid: uniqueid };
                return Fragment.loadFragment('community_oer', 'copy_questions_from_catalog', currentcoursecontext, params);
            };

            $(SELECTORS.buttonEvent).on("click", function (e) {
                e.preventDefault();

                let cmid = $(this).data('cmid');

                Str.get_strings([
                    { key: 'copyquestionsfromoer', component: 'community_oer' },
                    { key: 'qshare', component: 'community_oer' },
                ]).done(function (strings) {
                    var modalPromise = ModalFactory.create({
                        type: ModalFactory.types.SAVE_CANCEL,
                        title: strings[0],
                        body: getBody(cmid)
                    });

                    $.when(modalPromise).then(function (fmodal) {

                        fmodal.setSaveButtonText(strings[1]);

                        // Handle save event.
                        fmodal.getRoot().on(ModalEvents.save, function (e) {
                            e.preventDefault();

                            let selected = JSON.parse($('#questions_selected').val());

                            if (selected.length) {

                                // Close popup.
                                fmodal.destroy();

                                // Success popup.
                                Str.get_strings([
                                    { key: 'popupmessagesuccesstitle', component: 'community_oer' },
                                    { key: 'popupmessagesuccesscontent', component: 'community_oer' },
                                    { key: 'popupbuttondisabled', component: 'community_oer' },
                                    { key: 'popupbuttonenabled', component: 'community_oer' }
                                ]).done(function (strings) {

                                    var modalPromise = ModalFactory.create({
                                        type: ModalFactory.types.ALERT,
                                        title: strings[0],
                                        body: strings[1]
                                    });

                                    $.when(modalPromise).then(function (fmodal) {
                                        fmodal.show();
                                        let root = fmodal.getRoot();

                                        // Refresh page.
                                        root.on(ModalEvents.cancel, function() {
                                            location.reload();
                                        })

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
                                                done: function (response) {
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
                                    { key: 'popupmessagefailtitle', component: 'community_oer' },
                                    { key: 'popupmessagefailcontent', component: 'community_oer' }
                                ]).done(function (strings) {

                                    var modalPromise = ModalFactory.create({
                                        type: ModalFactory.types.ALERT,
                                        title: strings[0],
                                        body: strings[1]
                                    });

                                    $.when(modalPromise).then(function (fmodal) {
                                        fmodal.show();
                                        return fmodal;
                                    }).fail(Notification.exception);
                                });
                            }

                        });

                        fmodal.getModal().addClass('modal-xlg');
                        fmodal.getModal().addClass('popup-copy-questions-modal import-questions-modal');
                        var root = fmodal.getRoot();
                        root.on(ModalEvents.shown, function () {

                        });

                        root.on(ModalEvents.hidden, function () {
                            fmodal.destroy();
                        });

                        return fmodal;
                    }).done(function (modal) {
                        modal.show();
                    }).fail(Notification.exception);
                });

            })
        },
    }
});
