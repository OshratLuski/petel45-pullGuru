/* eslint-disable no-console */
/* eslint-disable no-unused-vars */
define([
    'jquery',
    'core/ajax',
    'core/notification',
    'core/modal_factory',
    'core/modal_events',
    'core/str',
    'quiz_assessmentdiscussion/preset',
    'quiz_assessmentdiscussion/render',
    'quiz_assessmentdiscussion/loading',
],
    function ($, Ajax, Notification, ModalFactory, ModalEvents, Str, Preset, Render, Loading) {

        const mainBlock = document.querySelector(`body`);

        return {

            'scriptsForPage': function () {

                // Back button.
                $('.answerOverlayBackBtn').on('click', (e) => {
                    const anonymousState = $(e.target).closest('.answeroverlay')
                        .find('.assessmentdiscussion_report-toggle input')[0].checked;
                    $('.answeroverlay').remove();

                    $('#anonymousmodeToggler')[0].checked = anonymousState;

                    Loading.show();
                    Render.attempts_area(function (data) {
                        Loading.remove();
                    });
                });
            },

            'init': function (def) {

                // Set default preset.
                def = JSON.parse(def);
                Preset.setDefault(def);

                mainBlock.addEventListener(`click`, function (event) {
                    let target = event.target;
                    while (target !== mainBlock) {

                        if (target === null) {
                            break;
                        }
                        // Handle overlay dashboard.
                        if (target.dataset.handler === `open_overlay`) {
                            Loading.show();

                            Preset.set('overlay_qid', target.dataset.qid);

                            // Set default.
                            Preset.set('overlay_show_answers', def.overlay_show_answers);
                            Preset.set('overlay_tab', def.overlay_tab);
                            Preset.set('overlay_view_list', def.overlay_view_list);

                            // Set anonymous mode.
                            Preset.set('anonymous_mode', 1);

                            Render.overlay_dashboard(function (data) {
                                Loading.remove();
                            });

                            return;
                        }

                        // Handle overlay change question.
                        if (target.dataset.handler === `overlay_change_question`) {
                            Loading.show();

                            Preset.set('overlay_qid', target.dataset.qid);

                            // Set default.
                            Preset.set('overlay_tab', def.overlay_tab);
                            Preset.set('overlay_view_list', def.overlay_view_list);

                            Render.overlay_dashboard(function (data) {
                                Loading.remove();
                            });

                            return;
                        }

                        // Handle change anonymous on overlay.
                        if (target.dataset.handler === `overlay_anonymous`) {

                            let checked = 0;
                            if ($(target).is(':checked')) {
                                checked = 1;
                            }

                            if (!checked) {
                                var reqStrings = [
                                    {key: 'changedisplaymodemodaltitle', component: 'quiz_assessmentdiscussion'},
                                    {key: 'changedisplaymodemodaltext', component: 'quiz_assessmentdiscussion'},
                                    {key: 'ok', component: 'quiz_assessmentdiscussion'},
                                ];

                                var stringsPromise = Str.get_strings(reqStrings);
                                var modalPromise = ModalFactory.create({ type: ModalFactory.types.SAVE_CANCEL });

                                // eslint-disable-next-line no-loop-func
                                $.when(stringsPromise, modalPromise).then(function (strings, modal) {
                                    modal.setTitle(strings[0]);
                                    modal.setBody(strings[1]);
                                    modal.setSaveButtonText(strings[2]);
                                    modal.getRoot().on(ModalEvents.shown, function () {
                                        modal.getRoot()[0].setAttribute('style', 'z-index: 99999999;');
                                    });
                                    modal.getRoot().on(ModalEvents.save, function () {
                                        Loading.show();

                                        let checked = 0;
                                        if ($(target).is(':checked')) {
                                            checked = 1;
                                        }
                                        Preset.set('anonymous_mode', checked);

                                        Render.overlay_dashboard(function (data) {
                                            Loading.remove();
                                        });
                                    });
                                    modal.show();
                                    return modal;
                                }).fail(Notification.exception);
                            } else {
                                Loading.show();

                                let checked = 0;
                                if ($(target).is(':checked')) {
                                    checked = 1;
                                }
                                Preset.set('anonymous_mode', checked);

                                Render.overlay_dashboard(function (data) {
                                    Loading.remove();
                                });
                            }

                        }

                        // Handle overlay tab.
                        if (target.dataset.handler === `overlay_tab`) {
                            Loading.show();

                            Preset.set('overlay_tab', target.dataset.id);

                            Render.overlay_dashboard(function (data) {
                                Loading.remove();
                            });
                            return;
                        }

                        // Handle overlay button list view.
                        if (target.dataset.handler === `overlay_button_list_view`) {
                            Loading.show();

                            Preset.set('overlay_view_list', target.dataset.id);

                            Render.overlay_dashboard(function (data) {
                                Loading.remove();
                            });
                            return;
                        }

                        // Handle overlay button hide state.
                        if (target.dataset.handler === `overlay_hide_state`) {

                            $(target).toggleClass('active');
                            $('.answeroverlay-content').toggleClass('blured');

                            if ($(target).hasClass('active')) {
                                Preset.set('overlay_show_answers', 1);
                            } else {
                                Preset.set('overlay_show_answers', 0);
                            }
                        }

                        target = target.parentNode;
                    }
                });
                return true;
            }
        };
    });
