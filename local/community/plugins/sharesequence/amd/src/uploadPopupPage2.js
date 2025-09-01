/* eslint-disable require-jsdoc */
define([
    'community_sharesequence/main',
    'jquery',
    'core/ajax',
    'core/str',
    'core/templates',
    'core/notification',
    'core/modal_factory',
    'core/modal_events',
    'core/fragment',
    'jqueryui',

], function(Main, $, Ajax, Str, Templates, Notification, ModalFactory, ModalEvents, Fragment) {

    return {

        init: function(uniqueid) {
            var form = $('#sharing_sequence_form_' + uniqueid),
                self = this;

            // Submit form.
            form.find('.uploadsequencesubmit').click(function(event) {
                self.uploadSequence(event, form, uniqueid);
            });

            // Close modal factory popup.
            form.find('.uploadsequenceclose').click(function() {
                self.closeModalFactory(form);
            });

            // Back to page 1.
            form.find('.backtoform1').click(function() {

                var serializedForm = form.serializeArray(),
                    data = {};

                serializedForm.forEach(function(item) {
                    data[item.name] = data[item.name] ? data[item.name] + ',' + item.value : item.value;
                });

                self.closeModalFactory(form);
                Main.init_page_1(data);
            });

        },

        uploadSequence: function(e, form, uniqueid) {

            // Functions.
            var checkAvailability = function(activities, data) {

                Ajax.call([{
                    methodname: 'community_sharesequence_check_availability',
                    args: {
                        activities: JSON.stringify(activities)
                    },
                    done: function(result) {
                        result = JSON.parse(result.result);

                        if (result.status === true) {
                            self.removeBtnSpinner(form);

                            Str.get_strings([
                                {key: 'popupcheckavailabilitytitle', component: 'community_sharesequence'},
                                {key: 'popupcheckavailabilitycontent', component: 'community_sharesequence'},
                                {key: 'popupcheckavailabilitybuttonsave', component: 'community_sharesequence'},
                            ]).done(function(strings) {

                                let body = strings[1];
                                $.each(result.activities, function(index, value) {
                                    body += '<br>' + value;
                                });

                                var modalPromise = ModalFactory.create({type: ModalFactory.types.SAVE_CANCEL});
                                $.when(modalPromise).then(function(modal) {
                                    modal.setTitle(strings[0]);
                                    modal.setBody(body);
                                    modal.setSaveButtonText(strings[2]);
                                    modal.getRoot().on(ModalEvents.save, function() {
                                        self.addBtnSpinner(form);
                                        createSequence(activities, data);
                                    });
                                    modal.show();
                                    return modal;
                                }).fail(Notification.exception);
                            });
                        } else {
                            createSequence(activities, data);
                        }
                    }.bind(this),
                    fail: Notification.exception
                }]);
            }

            var parseResponse = function(response) {
                if (response.result) {
                    Str.get_strings([
                        {key: 'popupmessagesuccesstitle', component: 'community_sharesequence'},
                        {key: 'popupmessagesuccesscontent', component: 'community_sharesequence'},
                    ]).done(function(strings) {
                        // Close modal factory popup.
                        self.closeModalFactory(form);

                        let title = strings[0];
                        let text = strings[1];
                        self.informationPopup(title, text);
                    })
                } else {
                    // Close modal factory popup.
                    self.closeModalFactory(form);

                    let title = M.util.get_string('error', 'community_sharesequence');
                    let text = M.util.get_string('system_error_contact_administrator', 'community_sharesequence');
                    self.informationPopup(title, text);
                }
            };

            var createSequence = function(activities, data) {
                Ajax.call([{
                    methodname: 'community_sharesequence_submit_sequence_page_2',
                    args: {
                        activities: JSON.stringify(activities),
                        data: JSON.stringify(data)
                    },
                    done: parseResponse,
                    fail: Notification.exception
                }]);
            }

            let self = this;
            e.preventDefault();

            // Remove errors.
            form.find('.sequence-item-list-alert').hide();
            self.addBtnSpinner(form);

            let activities = this.getDataFormSequences(uniqueid);
            var serializedForm = form.serializeArray();

            let t = {};
            serializedForm.forEach(function(item) {
                t[item.name] = t[item.name] ? t[item.name] + ',' + item.value : item.value;
            });

            let data = t.default_data;

            if (activities.length !== 0) {
                checkAvailability(activities, data);
            } else {
                form.find('.sequence-item-list-alert').show();
                self.removeBtnSpinner(form);
            }
        },

        informationPopup: function(title, html) {
            var modalPromise = ModalFactory.create({
                type: ModalFactory.types.ALERT,
                title: title,
                body: html
            });

            $.when(modalPromise).then(function(fmodal) {
                fmodal.show();
                return fmodal;
            }).fail(Notification.exception);
        },

        closeModalFactory: function(form) {
            form.parent().parent().parent().find('.btn-close').click();
        },

        /**
         * Show spinner.
         *
         * @method addSpinner
         */
        addBtnSpinner: function(form) {
            form.find('.modalspinner').removeClass('d-none');
            form.find('.modalspinner').addClass('loading');
            form.find('.modalspinner').parent().prop('disabled', true);
        },

        /**
         * Remove spinner.
         *
         * @method addSpinner
         */
        removeBtnSpinner: function(form) {
            form.find('.modalspinner').removeClass('loading');
            form.find('.modalspinner').addClass('d-none');
            form.find('.modalspinner').parent().prop('disabled', false);
        },
        getDataFormSequences: function(uniqueid) {

            let data = [];
            let sequences = $('#sequence-item-list-' + uniqueid).find('li.sequence-item');

            sequences.each((index, el) => {
                let elData = {};
                let cmid = $(el).find('input').data('cmid').split('-')[0];
                elData.name = $(el).find('input').val();
                elData.cmid = cmid;
                elData.index = $(el).find('input').data('index');
                data.push(elData);
            });

            return data;
        },
    };
});
