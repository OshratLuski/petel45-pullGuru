/* eslint-disable no-loop-func */
/* eslint-disable no-unused-vars */
define([
        'jquery',
        'core/ajax',
        'core/notification',
        'core/str',
        'quiz_assessmentdiscussion/preset',
        'quiz_assessmentdiscussion/render',
        'quiz_assessmentdiscussion/loading',
    ],
    function($, Ajax, Notification, Str, Preset, Render, Loading) {

        const mainBlock = document.querySelector(`#region-main .block_assessmentdiscussion_report`);
        const dashboardSort = document.querySelector(`#region-main .block_assessmentdiscussion_report #questionSortingDropdown`);
        const dashboardGroup = document.querySelector(`#region-main .block_assessmentdiscussion_report #questionGroupDropdown`);
        const answerSort = document.querySelector(`#region-main .block_assessmentdiscussion_report #answersSortingDropdown`);

        const changeQuestionListIcon = (qid, state) => {
            $('.question-block-item').each(function() {
                let currentqid = $(this).data('qid');

                if (Number(currentqid) === Number(qid)) {
                    if (state === true) {
                        $(this).find('.question-block-title-icon').removeClass('d-none');
                    } else {
                        $(this).find('.question-block-title-icon').addClass('d-none');
                    }
                }
            });
        };

        const changeQuestionInfoIcon = (qid, state) => {

            let obj = $('.question-head').find('.discussion-state-btn');
            let currentqid = obj.data('qid');

            if (state === true) {
                obj.addClass('active');
            } else {
                obj.removeClass('active');
            }
        };

        const createBootstrapAlert = (message, dismissText) => {
            const alert = document.createElement('div');
            alert.className = 'grade-area-block-alert alert alert-warning alert-block fade in show d-flex alert-dismissible border-0 position-absolute';
            alert.setAttribute('role', 'alert');
            alert.setAttribute('data-aria-autofocus', 'true');

            alert.innerHTML = `
                <div class="alert-color-stripe bg-orange"></div>
                <div class="alert-inner d-flex align-items-center p-2">
                    <i class="alert-icon text-body fa-light fa-triangle-exclamation"></i>
                    <div class="alert-text text-body p-1">
                        ${message}
                    </div>
                    <button type="button" class="close" data-dismiss="alert">
                        <i class="fa-light fa-circle-xmark text-body"></i>
                        <span class="sr-only">${dismissText}</span>
                    </button>
                </div>
            `;

            $(".grade-area-block").after(alert);
        };

        const changeUrl = () => {
            let preset = Preset.get();

            let url = '?id=' + preset.cmid + '&mode=assessmentdiscussion&qid=' + preset.qid;
            window.history.pushState("", "", url);
        };

        return {

            'init': function(def) {

                // Set default preset.
                def = JSON.parse(def);
                Preset.setDefault(def);

                // First start.
                changeUrl();

                Loading.show();
                Render.answers_area(true, function(data) {
                    Loading.remove();
                });

                mainBlock.addEventListener(`click`, function(event) {
                    let target = event.target;
                    while (target !== mainBlock) {

                        // Handle dashboard tab.
                        if (target.dataset.handler === `dashboard_tab`) {
                            Loading.show();

                            Preset.set('dashboard_tab', target.dataset.id);
                            Render.dashboard(function(data) {
                                Preset.set('qid', data.activeqid);

                                changeUrl();

                                Render.answers_area(true, function(data) {
                                    Loading.remove();
                                });
                            });
                            return;
                        }

                        // Handle dashboard group.
                        if (target.dataset.handler === `dashboard_group`) {
                            let value = target.dataset.value;
                            let name = target.dataset.name;

                            $(dashboardGroup).data('value', value);
                            $(dashboardGroup).data('name', name);
                            $('#questionGroupDropdown').find('span').html(name);
                            $(dashboardGroup).addClass('selected');

                            Loading.show();

                            Preset.set('groupid', value);
                            Render.dashboard(function(data) {
                                Preset.set('qid', data.activeqid);

                                changeUrl();

                                Render.answers_area(true, function(data) {
                                    Loading.remove();
                                });
                            });
                            return;
                        }

                        // Handle dashboard sorting.
                        if (target.dataset.handler === `dashboard_sort`) {
                            let value = target.dataset.value;
                            let name = target.dataset.name;

                            $(dashboardSort).data('value', value);
                            $(dashboardSort).data('name', name);
                            $('#questionSortingDropdown').find('span').html(name);
                            $(dashboardSort).addClass('selected');

                            Loading.show();

                            Preset.set('sort_question', value);
                            Render.aside_questions(function(data) {
                                Preset.set('qid', data.activeqid);

                                changeUrl();

                                Render.answers_area(true, function(data) {
                                    Loading.remove();
                                });
                            });
                            return;
                        }

                        // Handle change anonymous.
                        if (target.dataset.handler === `dashboard_anonymous`) {
                            Loading.show();

                            let checked = 0;
                            if ($(target).is(':checked')) {
                                checked = 1;
                            }

                            Preset.set('anonymous_mode', checked);
                            Render.attempts_area(function(data) {
                                Loading.remove();
                            });
                        }

                        // Handle change grades.
                        if (target.dataset.handler === `dashboard_change_grades`) {

                            let targetgrades = target;

                            Str.get_strings([
                                {key: 'buttonenablegrades', component: 'quiz_assessmentdiscussion'},
                                {key: 'buttondisablegrades', component: 'quiz_assessmentdiscussion'},
                            ]).done(function (strings) {

                                Loading.show();

                                let state = targetgrades.dataset.action;

                                if (targetgrades.dataset.action === 'enable') {
                                    targetgrades.dataset.action = 'disable';
                                    $(targetgrades).html(strings[1]);
                                    $(targetgrades).removeClass('btn-primary').addClass('btn-secondary');
                                } else {
                                    targetgrades.dataset.action = 'enable';
                                    $(targetgrades).html(strings[0]);
                                    $(targetgrades).removeClass('btn-secondary').addClass('btn-primary');
                                }

                                let preset = Preset.get();

                                Ajax.call([{
                                    methodname: 'quiz_assessmentdiscussion_change_grades',
                                    args: {
                                        'cmid': preset.cmid,
                                        'state': state,

                                    },
                                    done: function(response) {

                                        let res = JSON.parse(response.data);

                                        Loading.remove();
                                    },
                                    fail: function(response) {
                                        Loading.remove();
                                        Notification.exception(response);
                                    },
                                }]);
                            })
                        }

                        // Handle select question.
                        if (target.dataset.handler === `select_question`) {
                            Loading.show();

                            // Add "active".
                            $('.question-block-wrapper .question-block-item').each(function() {
                                $(this).removeClass('active');
                                $(this).attr('data-handler', 'select_question');
                            });
                            $(target).addClass('active');
                            $(target).attr('data-handler', 'disable');

                            Preset.set('qid', target.dataset.qid);

                            changeUrl();

                            Render.answers_area(true, function(data) {
                                Loading.remove();
                            });
                            return;
                        }

                        // Handle answers tab.
                        if (target.dataset.handler === `answers_tab`) {
                            Loading.show();

                            let preset = Preset.get();
                            Preset.set('sort_answers', preset.sort_answers_default);
                            Preset.set('answers_tab', target.dataset.id);

                            Render.attempts_area(function(data) {
                                Loading.remove();
                            });
                            return;
                        }

                        // Handle answers sorting.
                        if (target.dataset.handler === `answers_sort`) {
                            let value = target.dataset.value;
                            let name = target.dataset.name;

                            $(answerSort).data('value', value);
                            $(answerSort).data('name', name);
                            $('#answersSortingDropdown').find('span').html(name);
                            $(answerSort).addClass('selected');

                            Loading.show();

                            Preset.set('sort_answers', value);
                            Render.attempts_area(function(data) {
                                Loading.remove();
                            });
                            return;
                        }

                        // Handle change discussion for question.
                        if (target.dataset.handler === `change_question_discussion`) {

                            Loading.show();

                            if ($(target).hasClass('active')) {
                                $(target).removeClass('active');
                            } else {
                                $(target).addClass('active');
                            }

                            let preset = Preset.get();

                            Ajax.call([{
                                methodname: 'quiz_assessmentdiscussion_change_discussion',
                                args: {
                                    'cmid': preset.cmid,
                                    'groupid': preset.groupid,
                                    'qid': target.dataset.qid
                                },
                                done: function(response) {

                                    let res = JSON.parse(response.data);

                                    // If dashboard tab is tab discussion.
                                    if (preset.dashboard_tab_discussion_value === preset.dashboard_tab) {
                                        Render.dashboard(function(data) {
                                            Preset.set('qid', data.activeqid);

                                            changeUrl();

                                            Render.answers_area(true, function(data) {
                                                Loading.remove();
                                            });
                                        });
                                    } else {
                                        Render.dashboard_tabs(function(data) {
                                            Render.answers_tabs(function(data) {
                                                changeQuestionListIcon(target.dataset.qid, res.question_enable);

                                                // Remove class 'active'.
                                                if (res.question_enable === false) {
                                                    $(document.querySelectorAll('[data-handler="change_answer_discussion"]')).each(function(index, item) {
                                                        $(item).removeClass('active');
                                                    });

                                                    $(document.querySelectorAll('[data-handler="change_attempt_discussion"]')).each(function(index, item) {
                                                        $(item).removeClass('active');
                                                    });
                                                }

                                                Loading.remove();
                                            });
                                        });
                                    }
                                },
                                fail: function(response) {
                                    Loading.remove();
                                    Notification.exception(response);
                                },
                            }]);
                            return;
                        }

                        // Handle change discussion for answers.
                        if (target.dataset.handler === `change_answer_discussion`) {

                            Loading.show();

                            let preset = Preset.get();
                            let button = $(target);

                            Ajax.call([{
                                methodname: 'quiz_assessmentdiscussion_change_discussion',
                                args: {
                                    'cmid': preset.cmid,
                                    'groupid': preset.groupid,
                                    'qid': preset.qid,
                                    'userid': target.dataset.userid
                                },
                                done: function(response) {

                                    let res = JSON.parse(response.data);

                                    // If answers tab is tab discussion.
                                    if (preset.answer_tab_discussion_value === preset.answers_tab) {
                                        Render.dashboard_tabs(function(data) {
                                            Render.answers_tabs(function(data) {
                                                Render.answers_area(false, function(data) {
                                                    changeQuestionListIcon(preset.qid, res.question_enable);
                                                    changeQuestionInfoIcon(preset.qid, res.question_enable);
                                                    Loading.remove();
                                                });
                                            });
                                        });
                                    } else {
                                        Render.dashboard_tabs(function(data) {
                                            Render.answers_tabs(function(data) {
                                                changeQuestionListIcon(preset.qid, res.question_enable);
                                                changeQuestionInfoIcon(preset.qid, res.question_enable);

                                                // Remove class 'active'.
                                                if (res.answer_enable === false) {
                                                    let objs = button.parent().parent().find('[data-handler="change_attempt_discussion"]');
                                                    $(objs).each(function(index, item) {
                                                        $(item).removeClass('active');
                                                    });
                                                }

                                                if (button.hasClass('active')) {
                                                    button.removeClass('active');
                                                } else {
                                                    button.addClass('active');
                                                }

                                                Loading.remove();
                                            });
                                        });
                                    }
                                },
                                fail: function(response) {
                                    Loading.remove();
                                    Notification.exception(response);
                                },
                            }]);

                        }

                        if (target.dataset.handler === `change_attempt_discussion`) {

                            Loading.show();

                            let preset = Preset.get();
                            let button = $(target);

                            Ajax.call([{
                                methodname: 'quiz_assessmentdiscussion_change_discussion',
                                args: {
                                    'cmid': preset.cmid,
                                    'groupid': preset.groupid,
                                    'qid': preset.qid,
                                    'userid': target.dataset.userid,
                                    'attemptid': target.dataset.attemptid
                                },
                                done: function(response) {

                                    let res = JSON.parse(response.data);

                                    // If answers tab is tab discussion.
                                    if (preset.answer_tab_discussion_value === preset.answers_tab) {
                                        Render.dashboard_tabs(function(data) {
                                            Render.answers_tabs(function(data) {
                                                Render.answers_area(false, function(data) {
                                                    changeQuestionListIcon(preset.qid, res.question_enable);
                                                    changeQuestionInfoIcon(preset.qid, res.question_enable);
                                                    Loading.remove();
                                                });
                                            });
                                        });
                                    } else {
                                        Render.dashboard_tabs(function(data) {
                                            Render.answers_tabs(function(data) {
                                                changeQuestionListIcon(preset.qid, res.question_enable);
                                                changeQuestionInfoIcon(preset.qid, res.question_enable);

                                                if (button.hasClass('active')) {
                                                    button.removeClass('active');
                                                } else {
                                                    button.addClass('active');
                                                }

                                                let answerbutton = button.parent().parent().parent().find('.discussion-state-btn');

                                                answerbutton.removeClass('active');
                                                if (res.question_enable === true) {
                                                    answerbutton.addClass('active');
                                                }

                                                Loading.remove();
                                            });
                                        });
                                    }
                                },
                                fail: function(response) {
                                    Loading.remove();
                                    Notification.exception(response);
                                },
                            }]);

                        }

                        // Handle submit grade.
                        if (target.dataset.handler === `submit_grade`) {

                            let form = $(target).parent().parent();
                            let formdata = form.serializeArray();
                            let alertinfo = form.find('.alert');

                            let tmp = {};
                            $(formdata).each(function(index, item) {
                                tmp[item.name] = item.value;
                            });

                            // Prepare data for Tiny.
                            tmp.comment = window.tinyMCE.get(tmp.uniqueid).getContent();
                            delete tmp.uniqueid;

                            let grade = tmp.grade.trim();
                            if (grade.length === 0 || grade > tmp.maxmark || grade < 0) {

                                const reqStrings = [
                                    {key: 'notpossibletoenterascore', component: 'quiz_assessmentdiscussion'},
                                    {key: 'dismissnotification', component: 'core'},
                                ];

                                const stringsPromise = Str.get_strings(reqStrings);

                                $.when(stringsPromise).then(function (strings) {
                                   createBootstrapAlert(strings[0], strings[1]);
                                   return setTimeout(() => {
                                    $('.grade-area-block .alert-warning').alert('close');
                                   }, 1000);
                                }).fail(Notification.exception);

                                return;
                            }

                            Loading.show();

                            let data = [];
                            data.push(tmp);

                            let preset = Preset.get();

                            Ajax.call([{
                                methodname: 'quiz_assessmentdiscussion_save_grades',
                                args: {
                                    'cmid': preset.cmid,
                                    'qid': preset.qid,
                                    'grades': data
                                },
                                done: function(response) {
                                    Render.dashboard_tabs(function(data) {
                                        Render.answers_tabs(function(data) {
                                            Render.attempts_area(function(data) {
                                                Loading.remove();
                                            });
                                        });
                                    });
                                },
                                fail: function(response) {
                                    Loading.remove();
                                    Notification.exception(response);
                                },
                            }]);
                        }

                        // Handle submit all grades.
                        if (target.dataset.handler === `submit_all_grades`) {

                            let data = [];
                            $('.grade-area-block').each(function(index, item) {

                                let formdata = $(this).serializeArray();

                                let tmp = {};
                                $(formdata).each(function(index, item) {
                                    tmp[item.name] = item.value;
                                });

                                // Prepare data for Tiny.
                                tmp.comment = window.tinyMCE.get(tmp.uniqueid).getContent();
                                delete tmp.uniqueid;

                                data.push(tmp);
                            });

                            Loading.show();

                            let preset = Preset.get();

                            Ajax.call([{
                                methodname: 'quiz_assessmentdiscussion_save_grades',
                                args: {
                                    'cmid': preset.cmid,
                                    'qid': preset.qid,
                                    'grades': data
                                },
                                done: function(response) {
                                    Render.dashboard_tabs(function(data) {
                                        Render.answers_tabs(function(data) {
                                            Render.attempts_area(function(data) {
                                                Loading.remove();
                                            });
                                        });
                                    });
                                },
                                fail: function(response) {
                                    Loading.remove();
                                    Notification.exception(response);
                                },
                            }]);

                        }

                        target = target.parentNode;
                    }
                });

                return true;
            },

            'createAlert': function() {
                const reqStrings = [
                    {key: 'notpossibletoenterascore', component: 'quiz_assessmentdiscussion'},
                    {key: 'dismissnotification', component: 'core'},
                ];

                const stringsPromise = Str.get_strings(reqStrings);

                $.when(stringsPromise).then(function (strings) {
                    createBootstrapAlert(strings[0], strings[1]);
                    return setTimeout(() => {
                        $('.grade-area-block-alert').remove();
                    }, 8000);
                }).fail(Notification.exception);
            }
        };
    });
