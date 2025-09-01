define([
    'jquery',
    'core/str',
    'core/modal_factory',
    'core/modal_events',
    'core/ajax',
    'core/templates',
    'core/notification',
    'local_redmine/html2canvas',
    'local_redmine/pageProperties',

], function($, Str, ModalFactory, ModalEvents, Ajax, Templates, Notification, html2canvas, pageProperties) {

    let screenshot;
    let popup_disable = false;
    let myip = '';

    const spinner = `<div class="spinner-border text-white align-items-center justify-conten-center" role="status" style="width: 1.5rem; height: 1.5rem; display:flex;">
                        <span class="sr-only">Loading...</span>
                    </div>`;

    return {
        init: function() {
            let self = this;

            // Render issue counter for user.
            self.renderIssueCounter();

            // Get IP.
            pageProperties.getIP(function(ip){
                myip = ip;
            })

            $('#support-btn').click(() => {
                self.supportPopup()
                return false;
            });

            $('#support-btn-student').click((e) => {
                $(e.target).addClass('active disabled');
                self.insertSpinner('#support-btn-student');
                self.supportPopupStudent();
                return false;
            });
        },

        supportPopup: function (def) {
            const self = this;
            if(popup_disable) return;
            popup_disable = true;

            let context = {};
            switch (def) {
                case 1:
                    context = {'default_question': true};
                    break;
                case 2:
                    context = {'default_pedagogical_help': true};
                    break;
                case 3:
                    context = {'default_suggest_improvement': true};
                    break;
                case 4:
                    context = {'default_contenterror_report': true};
                    break;
                case 5:
                    context = {'default_error_report': true};
                    break;
                default:
                    context = {'default_question': true};
            }

            Str.get_strings([
                {key: 'have_you_a_question', component: 'local_redmine'},
                {key: 'send', component: 'local_redmine'},
            ]).done(function (strings) {

                var modalPromise = ModalFactory.create({
                    type: ModalFactory.types.SAVE_CANCEL,
                    title: strings[0],
                });

                $.when(modalPromise).then(function(fmodal) {
                 /*    fmodal.setLarge(); */
                    fmodal.setSaveButtonText(strings[1]);

                    Templates.render("local_redmine/support/supportPopup", context)
                        .done(function (html, js) {
                            fmodal.setBody(html);
                        })

                    // Popup rendered.
                    fmodal.getRoot().on(ModalEvents.bodyRendered, function () {
                        let support_activities_div = fmodal.body.find('#support_activities');
                        let support_activities_title = fmodal.body.find('#support_activities_title');

                        popup_disable = false;

                        let delay = (callback, ms) => {
                            var timer = 0;
                            return function() {
                                var context = this, args = arguments;
                                clearTimeout(timer);
                                timer = setTimeout(function () {
                                    callback.apply(context, args);
                                }, ms || 0);
                            };
                        }

                        $(fmodal.body.find('#more_info')).keyup(delay(function (e) {
                            support_activities_div.hide();
                            support_activities_title.hide();

                            var query = $(this).val().trim();
                            if (query.length > 0) {
                                get_activities(query);
                            }
                        }, 500));

                        let get_activities = (query) => {
                            Ajax.call([{
                                methodname: 'local_redmine_get_support_activities',
                                args: {
                                    query: query,
                                },
                                done: render_activities,
                            }]);
                        };

                        let render_activities = (list) => {
                            list = JSON.parse(list);
                            if (list.length > 0) {
                                support_activities_div.show();
                                support_activities_title.show();
                            } else {
                                support_activities_div.hide();
                                support_activities_title.hide();
                            }
                            support_activities_div.html('');
                            list.forEach(act => {
                                let link = $('<a>', {
                                    'href': act.link,
                                    'html': act.title + ' <i class="fa fa-external-link" aria-hidden="true"></i>',
                                    'target': 'blank',
                                });
                                support_activities_div.append(link);
                            });
                        };

                    });

                    // Save button.
                    fmodal.getRoot().on(ModalEvents.save, function(e) {
                        e.preventDefault();

                        fmodal.body.find('.more_info_error').hide();
                        fmodal.body.find('.question_type_error').hide();

                        var more_info = fmodal.body.find('#more_info').val();
                        var upload_info = fmodal.body.find('#upload_info').val();
                        var question = fmodal.body.find('input[name="questionType"]:checked').val();

                        // Validation
                        var errors = [];
                        if(!more_info.trim().length){
                            fmodal.body.find('.more_info_error').show();
                            fmodal.body.find('#more_info').focus();
                            errors.push('Error more_info');
                        }

                        if(question === undefined ){
                            fmodal.body.find('.question_type_error').show();
                            fmodal.body.find('#rg1').focus();
                            errors.push('Error question');
                        }

                        if (errors.length === 0) {

                            // Insert spinner.
                            self.insertSpinner($(fmodal.footer).find('[data-action="save"]'));

                            Ajax.call([{
                                    methodname: "local_redmine_support_request",
                                    args: {
                                        userBrowserName: pageProperties.getBrowser().name,
                                        userBrowserVersion: pageProperties.getBrowser().version,
                                        userIP: myip,
                                        moreInfo: more_info,
                                        uploadInfo: upload_info,
                                        resolution:  pageProperties.getResolution(),
                                        pageurl: pageProperties.getPageUrl(),
                                        questionType: question,
                                    },
                                    done: function(response) {

                                        self.removeSpinner($(fmodal.footer).find('[data-action="save"]'));
                                        fmodal.destroy();

                                        // Success popup.
                                        Str.get_strings([
                                            {key: 'supportsuccesssendtitle', component: 'local_redmine'},
                                            {key: 'supportsuccesssendcontent', component: 'local_redmine'},
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
                                        })

                                    },
                                    fail: Notification.exception
                                }
                            ]);
                        }
                    });

                    return fmodal;
                }).done(function(modal) {
                    modal.show();
                }).fail(Notification.exception);
            });
        },

        supportPopupStudent: function () {
            const self = this;
            if(popup_disable) return;
            popup_disable = true;

            Str.get_strings([
                {key: 'question_to_teacher', component: 'local_redmine'},
                {key: 'send', component: 'local_redmine'},
            ]).done(function (strings) {

                const screen = document.querySelector("body");
                html2canvas(screen).then(canvas => {
                    screenshot = canvas.toDataURL("image/jpeg");

                    var modalPromise = ModalFactory.create({
                        type: ModalFactory.types.SAVE_CANCEL,
                        title: strings[0],
                    });

                    $.when(modalPromise).then(function(fmodal) {
                       /*  fmodal.setLarge(); */
                        fmodal.setSaveButtonText(strings[1]);

                        let context = {screenshot: screenshot};
                        Templates.render("local_redmine/support/supportPopupStudent", context)
                            .done(function (html, js) {
                                fmodal.setBody(html);
                                popup_disable = false;
                            })

                        // Save button.
                        fmodal.getRoot().on(ModalEvents.save, function(e) {
                            e.preventDefault();

                            fmodal.body.find('.more_info_error').hide();

                            var more_info = fmodal.body.find('#more_info').val();
                            var upload_info = fmodal.body.find('#upload_info').val();

                            // Validation
                            var errors = [];
                            if (!more_info.trim().length) {
                                errors.push('Error more_info');
                            }

                            if (errors.length === 0) {

                                // Insert spinner.
                                self.insertSpinner($(fmodal.footer).find('[data-action="save"]'));

                                Ajax.call([{
                                        methodname: "local_redmine_support_student_request",
                                        args: {
                                            userBrowserName: pageProperties.getBrowser().name,
                                            userBrowserVersion: pageProperties.getBrowser().version,
                                            userIP: myip,
                                            screenshot: screenshot,
                                            moreInfo: more_info,
                                            uploadInfo: upload_info,
                                            resolution:  pageProperties.getResolution(),
                                            pageurl: pageProperties.getPageUrl(),
                                            courseid: pageProperties.getCourseID(),
                                        },
                                        done: function(response) {
                                            
                                            self.removeSpinner($(fmodal.footer).find('[data-action="save"]'));
                                            fmodal.destroy();

                                            // Success popup.
                                            Str.get_strings([
                                                {key: 'supportstudentsuccesssendtitle', component: 'local_redmine'},
                                                {key: 'supportstudentsuccesssendcontent', component: 'local_redmine'},
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
                                            })

                                        },
                                        fail: Notification.exception
                                    }
                                ]);
                            } else {
                                fmodal.body.find('.more_info_error').show();
                                fmodal.body.find('#more_info').focus();
                            }
                        });

                        return fmodal;
                    }).done(function(modal) {
                        modal.show();
                        $('#support-btn-student').removeClass('active disabled');
                        self.removeSpinner('#support-btn-student');
                    }).fail(Notification.exception);
                });
            });
        },

        insertSpinner: function (parent) {
            if ($(parent).find('span').length == 0) {
                let innerText = $(parent).html();
                innerText = `<span class="btn-text" style="display:none;"> ${innerText} </span>${spinner}`;
                $(parent).html(innerText).addClass('disabled').attr('disabled', 'disabled');
            } else {
                $(parent).find('.btn-text').hide();
                $(parent).find('.spinner-border').show();
                $(parent).addClass('disabled').attr('disabled', 'disabled');
            }
        },

        removeSpinner: function (parent) {
            $(parent).find('.btn-text').show();
            $(parent).find('.spinner-border').hide();
            $(parent).removeClass('disabled').removeAttr('disabled');
        },

        renderIssueCounter: function (){
            Str.get_strings([
                {key: 'have_you_a_question', component: 'local_redmine'},
                {key: 'send', component: 'local_redmine'},
            ]).done(function (strings) {

                Ajax.call([{
                    methodname: "local_redmine_issues_counter_user",
                    args: {
                    },
                    done: function(response) {
                        let data = JSON.parse(response);
                        if(data.counter > 0){
                            $('#issues_counter_user').text(data.counter).show();
                            $('#issues_counter_user_span').text(data.counter);
                            $('#issues_counter_user_block').show();
                        }else{
                            $('#issues_counter_user').hide();
                            $('#issues_counter_user_block').hide();
                        }
                    },
                    fail: Notification.exception
                }])
            })
        }
    };
});
