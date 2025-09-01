define([
        'jquery',
        'core/str',
        'core/templates',
        'core/notification',
        'core/ajax',
        'quiz_assessmentdiscussion/preset',
        'quiz_assessmentdiscussion/loading',
    ],
    function ($, Str, Templates, Notification, Ajax, Preset, Loading) {

        let DASHBOARD = {
            main: '.block_assessmentdiscussion_report',
            mainTabs: '.block_assessmentdiscussion_report #questionsTabs',

        };

        let ASIDE = {
            questionlist: '.block_assessmentdiscussion_report .question-block-wrapper',
        };

        let ANSWERSAREA = {
            main: '.block_assessmentdiscussion_report .answer-block-wrapper',
            tabs: '.block_assessmentdiscussion_report .answers-tabs-wrapper',
            attempts: '.block_assessmentdiscussion_report .attepts-area-wrapper',
        };

        return {

            'dashboard': function (callback) {

                let preset = Preset.get();

                Ajax.call([{
                    methodname: 'quiz_assessmentdiscussion_render_main_block',
                    args: {
                        'cmid' : preset.cmid,
                        'groupid' : preset.groupid,
                        'tabid' : preset.dashboard_tab,
                        'anonymousmode' : preset.anonymous_mode,
                    },
                    done: function (response) {

                        let data = JSON.parse(response.data);

                        Templates.render('quiz_assessmentdiscussion/dashboard/main', data)
                            .done(function (html, js) {
                                Templates.replaceNodeContents(DASHBOARD.main, html, js);

                                callback(data);
                            })
                            .fail(function (response) {
                                Loading.remove();
                                Notification.exception(response);
                            });
                    },
                    fail: function (response) {
                        Loading.remove();
                        Notification.exception(response);
                    },
                }]);

                return true;
            },

            'dashboard_tabs': function (callback) {

                let preset = Preset.get();

                Ajax.call([{
                    methodname: 'quiz_assessmentdiscussion_render_main_block',
                    args: {
                        'cmid' : preset.cmid,
                        'groupid' : preset.groupid,
                        'tabid' : preset.dashboard_tab,
                        'anonymousmode' : preset.anonymous_mode,
                    },
                    done: function (response) {

                        let data = JSON.parse(response.data);

                        Templates.render('quiz_assessmentdiscussion/dashboard/tabs', data)
                            .done(function (html, js) {
                                Templates.replaceNodeContents(DASHBOARD.mainTabs, html, js);

                                callback(data);
                            })
                            .fail(function (response) {
                                Loading.remove();
                                Notification.exception(response);
                            });
                    },
                    fail: function (response) {
                        Loading.remove();
                        Notification.exception(response);
                    },
                }]);

                return true;
            },

            'aside_questions': function (callback) {

                let preset = Preset.get();

                Ajax.call([{
                    methodname: 'quiz_assessmentdiscussion_render_main_block',
                    args: {
                        'cmid' : preset.cmid,
                        'groupid' : preset.groupid,
                        'tabid' : preset.dashboard_tab,
                        'anonymousmode' : preset.anonymous_mode,
                        'sort' : preset.sort_question,
                    },
                    done: function (response) {

                        let data = JSON.parse(response.data);

                        Templates.render('quiz_assessmentdiscussion/aside/questionlist', data)
                            .done(function (html, js) {
                                Templates.replaceNodeContents(ASIDE.questionlist, html, js);

                                callback(data);
                            })
                            .fail(function (response) {
                                Loading.remove();
                                Notification.exception(response);
                            });
                    },
                    fail: function (response) {
                        Loading.remove();
                        Notification.exception(response);
                    },
                }]);

                return true;
            },

            'answers_area': function (defstate, callback) {

                $(ANSWERSAREA.main).hide();

                let preset = Preset.get();

                if (defstate) {
                    Preset.set('answers_tab', preset.answers_tab_default);
                    Preset.set('sort_answers', preset.sort_answers_default);

                    preset = Preset.get();
                }

                Ajax.call([{
                    methodname: 'quiz_assessmentdiscussion_render_answer_area_block',
                    args: {
                        'cmid' : preset.cmid,
                        'groupid' : preset.groupid,
                        'qid' : preset.qid,
                        'anonymousmode' : preset.anonymous_mode,
                        'sort' : preset.sort_answers,
                        'tabid' : preset.answers_tab,
                    },
                    done: function (response) {

                        let data = JSON.parse(response.data);

                        Templates.render('quiz_assessmentdiscussion/answersarea/main', data)
                            .done(function (html, js) {
                                Templates.replaceNodeContents(ANSWERSAREA.main, html, js);
                                $(ANSWERSAREA.main).show();

                                callback(data);
                            })
                            .fail(function (response) {
                                Loading.remove();
                                $(ANSWERSAREA.main).show();
                                Notification.exception(response);
                            });
                    },
                    fail: function (response) {
                        Loading.remove();
                        $(ANSWERSAREA.main).show();
                        Notification.exception(response);
                    },
                }]);

                return true;
            },

            'answers_tabs': function (callback) {

                let preset = Preset.get();

                Ajax.call([{
                    methodname: 'quiz_assessmentdiscussion_render_answer_area_block',
                    args: {
                        'cmid' : preset.cmid,
                        'groupid' : preset.groupid,
                        'qid' : preset.qid,
                        'anonymousmode' : preset.anonymous_mode,
                        'sort' : preset.sort_answers,
                        'tabid' : preset.answers_tab,
                    },
                    done: function (response) {

                        let data = JSON.parse(response.data);

                        Templates.render('quiz_assessmentdiscussion/answersarea/tabs', data)
                            .done(function (html, js) {
                                Templates.replaceNodeContents(ANSWERSAREA.tabs, html, js);

                                callback(data);
                            })
                            .fail(function (response) {
                                Loading.remove();
                                $(ANSWERSAREA.main).show();
                                Notification.exception(response);
                            });
                    },
                    fail: function (response) {
                        Loading.remove();
                        $(ANSWERSAREA.main).show();
                        Notification.exception(response);
                    },
                }]);

                return true;
            },

            'attempts_area': function (callback) {

                let preset = Preset.get();

                Ajax.call([{
                    methodname: 'quiz_assessmentdiscussion_render_answer_area_block',
                    args: {
                        'cmid' : preset.cmid,
                        'groupid' : preset.groupid,
                        'qid' : preset.qid,
                        'anonymousmode' : preset.anonymous_mode,
                        'sort' : preset.sort_answers,
                        'tabid' : preset.answers_tab,
                    },
                    done: function (response) {

                        let data = JSON.parse(response.data);

                        Templates.render('quiz_assessmentdiscussion/answersarea/attempts', data)
                            .done(function (html, js) {
                                Templates.replaceNodeContents(ANSWERSAREA.attempts, html, js);

                                callback(data);
                            })
                            .fail(function (response) {
                                Loading.remove();
                                $(ANSWERSAREA.main).show();
                                Notification.exception(response);
                            });
                    },
                    fail: function (response) {
                        Loading.remove();
                        $(ANSWERSAREA.main).show();
                        Notification.exception(response);
                    },
                }]);

                return true;
            },

            'overlay_dashboard': function (callback) {

                let preset = Preset.get();

                Ajax.call([{
                    methodname: 'quiz_assessmentdiscussion_render_overlay_block',
                    args: {
                        'cmid' : preset.cmid,
                        'qid' : preset.overlay_qid,
                        'groupid' : preset.groupid,
                        'tabid' : preset.overlay_tab,
                        'anonymousmode' : preset.anonymous_mode,
                        'viewlist' : preset.overlay_view_list,
                        'showanswers' : preset.overlay_show_answers,
                    },
                    done: function (response) {

                        let data = JSON.parse(response.data);

                        Templates.render('quiz_assessmentdiscussion/overlay/main', data)
                            .done(function (html, js) {
                                Templates.replaceNodeContents('.answeroverlay-parent', html, js);

                                callback(data);
                            })
                            .fail(function (response) {
                                Loading.remove();
                                Notification.exception(response);
                            });
                    },
                    fail: function (response) {
                        Loading.remove();
                        Notification.exception(response);
                    },
                }]);

                return true;
            },
        };
    });
