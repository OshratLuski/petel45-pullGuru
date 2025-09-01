define(['jquery', 'core/str', 'core/modal_factory', 'core/modal_events', 'core/templates', 'core/ajax', 'core/yui', 'core/notification', 'local_diagnostic/buuble-animation', 'local_diagnostic/ufo-filter', 'local_diagnostic/document-read', 'local_diagnostic/daterangepicker', 'local_diagnostic/bootstrap-multiselect'],
    function($, Str, ModalFactory, ModalEvents, Templates, Ajax, Y, Notification, BuubleAnimation, ufoFilter) {

        /**
         * Constructor
         *
         * @param {String} selector used to find triggers for the new modal.
         *
         * Each call to init gets it's own instance of this class.
         * @param courseid
         */
        var Diagnostic = function(selector, courseid, adParams) {
            //this.stage = stage;
           // this.stage1 = stage;
            //this.stage2 = stage2;
            this.courseid = courseid;

            // console.log('adParams ', adParams);

            this.adParams = adParams;
            this.init(selector);
        };

        /**
         * @var {Modal} modal
         * @private
         */
        Diagnostic.prototype.modal = null;
        Diagnostic.prototype.courseid = 0;
        Diagnostic.prototype.stage = '';
        Diagnostic.prototype.stage1 = '';
        Diagnostic.prototype.stage2 = '';
        Diagnostic.prototype.formdata = {};
        Diagnostic.prototype.daterangePlaceholder = true;

        /**
         * Initialise the class.
         *
         * @param {String} selector used to find triggers for the new group modal.
         * @private
         * @return {Promise}
         */
        Diagnostic.prototype.init = function(selector) {
            let self = this;
            var triggers = $(selector);

            return Str.get_strings([
                {
                    key: 'pluginname',
                    component: 'local_diagnostic'
                },
                {
                    key: 'cancel',
                    component: 'local_diagnostic'
                },
                {
                    key: 'submit',
                    component: 'local_diagnostic'
                },
            ]).then(function(translateArr) {
                // Create the modal.
                return ModalFactory.create({
                    title: translateArr[0],
                    footer: `
                    <button id="main_cancel" data-dismiss="modal" type="button" class="btn btn-primary m-1">${translateArr[1]}</button><button type="button" id="main_submit" class="btn btn-success btn-save m-1">${translateArr[2]}</button>
                    `
                }, triggers);
            }.bind(this)).then(function(modal) {
                // Keep a reference to the modal.
                this.modal = modal;

                modal.root.addClass('popup-local-diagnostic-main-modal');

                modal.modal.addClass('popup-local-diagnostic popup-local-diagnostic-main-p');

                this.modal.getRoot().on('cancel.daterangepicker', '.daterange', { diagnostic:this }, function(e) {
                    let dateNow = e.data.diagnostic.dateFormat(new Date());
                    Str.get_string('daterange','local_diagnostic').then((string) => {
                        $(".diagnostic input.daterange").val(string);
                    });
                    Diagnostic.prototype.daterangePlaceholder = true;

                    var formData = {
                        courseid: e.data.diagnostic.courseid,
                        daterange: `${dateNow} - ${dateNow}`,
                    };

                    e.data.diagnostic.getQuizzes(formData);
                });

                this.modal.getRoot().on('apply.daterangepicker', '.daterange', { diagnostic:this }, function(e) {

                    let startDate = e.data.diagnostic.dateFormat($(this).data('daterangepicker').startDate._d);
                    let endDate = e.data.diagnostic.dateFormat($(this).data('daterangepicker').endDate._d);

                    var formData = {
                        courseid: e.data.diagnostic.courseid,
                        daterange: `${startDate} - ${endDate}`
                        // daterange: $(this).val()
                    };

                    Diagnostic.prototype.daterangePlaceholder = false;

                    e.data.diagnostic.getQuizzes(formData);
                });

                this.modal.getRoot().on(ModalEvents.shown, { diagnostic:this }, async function(e) {
                    var formdata = {
                        courseid: e.data.diagnostic.courseid
                    };

                    e.data.diagnostic.getQuizzes(formdata);

                    if ($('div.local-diagnostic-base-block').length){
                        $('div.local-diagnostic-base-block').html('');
                    } else {
                        $("body .popup-local-diagnostic-main-modal").after('<div class="local-diagnostic-base-block"><div/>');
                    }

                    await Templates.render('local_diagnostic/base', {}).done(function(data) {
                        $("body .local-diagnostic-base-block").html(data);
                    });
                });

                // We want to reset the form every time it is opened.
                this.modal.getRoot().on(ModalEvents.hidden, function() {
                    if (typeof this.modal.getRoot().find('.daterange').data('daterangepicker') !== 'undefined') {
                        this.modal.getRoot().find('.daterange').data('daterangepicker').remove();
                    }

                    this.modal.setBody('');
                }.bind(this));

                this.modal.getRoot().on(ModalEvents.save, this.submitForm.bind(this));
                // We also catch the form submit event and use it to submit the form with ajax.
                this.modal.getRoot().on('submit', 'form', this.submitFormAjax.bind(this));

                return this.modal;
            }.bind(this));
        };

        Diagnostic.prototype.dateFormat = function(date){
            var d = new Date(date),
                month = '' + (d.getMonth() + 1),
                day = '' + d.getDate(),
                year = d.getFullYear();

            if (month.length < 2)
                month = '0' + month;
            if (day.length < 2)
                day = '0' + day;

            return [month, day, year].join('/');
        }

        /**
         * @method getBody
         * @private
         * @return {Promise}
         */
        Diagnostic.prototype.getBody = function(formdata) {
            if (typeof formdata === "undefined") {
                formdata = {};
            }

            formdata.imageTop = M.util.image_url('a/ufo_t', 'local_diagnostic');
            formdata.imageContent = M.util.image_url('a/ufo_100', 'local_diagnostic');
            formdata.secondarycolor = this.adParams[1].secondarylight;

            // Get the content of the modal.
            return Templates.render('local_diagnostic/popup', formdata);
        };

        /**
         * @method handleFormSubmissionResponse
         * @private
         * @return {Promise}
         */

        Diagnostic.prototype.handleFormSubmissionResponse = function(formData, response) {
            if (response.result) {
                let startDate = new Date();
                let endDate = new Date();
                if ('daterange' in formData) {
                    startDate = new Date(formData.daterange.substr(0, 10));
                    endDate = new Date(formData.daterange.substr(13));
                }
                this.formdata = response.templatedata;
                let diag = this;
                this.getBody(response.templatedata).then(function(result){
                    diag.modal.setBody(result);
                    ufoFilter();

                    const bodyEl = $('.popup-local-diagnostic-main-p .modal-body').first();
                    if (bodyEl){
                        bodyEl.addClass('modal-body-main');
                    }

                    diag.modal.getRoot().find('.modal-dialog').removeClass('modal-dialog-scrollable');
                    diag.modal.getRoot().find('.daterange').daterangepicker({
                        parentEl: ".popup-local-diagnostic-main-p .modal-body-main",
                        startDate,
                        endDate,
                        locale: {
                            format: "DD MMMM",
                            direction: 'daterange-custom',
                        },
                    });

                    diag.initQuizzes(response.templatedata.sections);
                });
            } else {
                this.modal.hide();
                Notification.addNotification({
                    message: response.message,
                    type: 'error'
                });
            }
        };

        /**
         * @method handleFormSubmissionFailure
         * @private
         * @return {Promise}
         */
        Diagnostic.prototype.initQuizzes = function(activitiesNow) {
            let diag = this;
            Str.get_strings([
                {
                    key: 'submittext',
                    component: 'local_diagnostic'
                }, {
                    key: 'events',
                    component: 'local_diagnostic'
                }, {
                    key: 'noselection',
                    component: 'local_diagnostic'
                }, {
                    key: 'daterange',
                    component: 'local_diagnostic'
                }, {
                    key: 'activities',
                    component: 'local_diagnostic'
                }
            ]).then(function(arr) {
                if (Diagnostic.prototype.daterangePlaceholder){
                    $(".diagnostic input.daterange").val(arr[3]);
                }

                diag.modal.getRoot().find('.cmids').multiselect(
                    {
                        enableClickableOptGroups: true,
                        enableCollapsibleOptGroups: false,
                        disableIfEmpty: true,
                        includeSubmitOption: true,
                        submitText: arr[0],
                        includeSubmitDivider: true,
                        nonSelectedText: arr[1],
                        buttonWidth: '258px',
                        templates: {
                            popupContainer:
                                `<div class="multiselect-container dropdown-menu">
                                        <div class="multiselect-container-text">
                                        </div>
                                    </div>`
                        },
                        onInitialized: function() {
                            let btnActivities = diag.modal.getRoot().find('button.multiselect');
                            if (!activitiesNow || activitiesNow.length === 0){
                                // activities is empty
                                btnActivities.attr('disabled', true);
                            } else {
                                btnActivities.removeAttr('disabled');
                            }
                        },
                        onChange: function(e) {
                            console.log('CHANGED');
                            console.log('REGISTER');

                            if (!e[0] || !e[0].value) {
                                return;
                            }

                            let value = e[0].value;

                            var formData = {
                                courseid: diag.courseid,
                                cmids: value ? [value] : []
                            };

                            diag.getClusters(formData);

                            let parent = diag.modal.getRoot().find('.multiselect-container.dropdown-menu');
                            parent.removeClass('show');
                        },
                        buttonText: function(options, select) {
                            // let value = options[0].firstChild.data;
                            if (options &&
                                options.length > 0 &&
                                options[0] &&
                                options[0].firstChild &&
                                options[0].firstChild.data
                                ){
                                return options[0].firstChild.data
                            } else {
                                return arr[4];
                            }
                        },
                        onDropdownShown: function(options, select) {
                            // div.modal-body
                            let popupEl = $("div.popup-local-diagnostic-main-p div.modal-body");
                            let menu = $(".diagnostic .dropdown-menu");

                            menu.css("width", popupEl.width());
                        },
                    }
                );
            });
        };

        /**
         * @method handleFormSubmissionFailure
         * @private
         * @return {Promise}
         */
        Diagnostic.prototype.getSelectedOptions = function(select) {
            return select.val();
        };

        /**
         * @method handleFormSubmissionFailure
         * @private
         * @return {Promise}
         */
        Diagnostic.prototype.handleFormSubmissionFailure = function(stage, data) {
            // Oh noes! Epic fail :(
            // Ah wait - this is normal. We need to re-display the form with errors!
            this.modal.setBody(this.getBody(stage, data));
        };

        /**
         * Private method
         *
         * @method submitFormAjax
         * @private
         * @param {Event} e Form submission event.
         */
        Diagnostic.prototype.submitFormAjax = function(e) {
            // We don't want to do a real form submission.
            e.preventDefault();
            let submitbtn = this.modal.getRoot().find('button.btn-primary');
            submitbtn.attr('disabled', true);

            var changeEvent = document.createEvent('HTMLEvents');
            changeEvent.initEvent('change', true, true);

            // Prompt all inputs to run their validation functions.
            // Normally this would happen when the form is submitted, but
            // since we aren't submitting the form normally we need to run client side
            // validation.
            this.modal.getRoot().find(':input').each(function (index, element) {
                element.dispatchEvent(changeEvent);
            });

            // Now the change events have run, see if there are any "invalid" form fields.
            var invalid = $.merge(
                this.modal.getRoot().find('[aria-invalid="true"]'),
                this.modal.getRoot().find('.error')
            );

            // If we found invalid fields, focus on the first one and do not submit via ajax.
            if (invalid.length) {
                invalid.first().focus();
                return;
            }

            this.modal.getRoot().find('input[type="checkbox"]').each(function (i, checkbox) {
                if ($(checkbox).is(':checked')) {
                    $(checkbox).prop('checked', true);
                } else {
                    $(checkbox).prop('checked', false);
                }
            });
            // Convert all the form elements values to a serialised string.
            var formData = this.modal.getRoot().find('form');
            var objectData = {};

            $.each(formData, function () {
                var self = this,
                    json = {},
                    push_counters = {},
                    patterns = {
                        "validate": /^[a-zA-Z][a-zA-Z0-9_]*(?:\[(?:\d*|[a-zA-Z0-9_]+)\])*$/,
                        "key": /[a-zA-Z0-9_]+|(?=\[\])/g,
                        "push": /^$/,
                        "fixed": /^\d+$/,
                        "named": /^[a-zA-Z0-9_]+$/
                    };


                this.build = function (base, key, value) {
                    base[key] = value;
                    return base;
                };

                this.push_counter = function (key) {
                    if (push_counters[key] === undefined) {
                        push_counters[key] = 0;
                    }
                    return push_counters[key]++;
                };

                $.each($(this).serializeArray(), function () {

                    // Skip invalid keys
                    if (!patterns.validate.test(this.name)) {
                        return;
                    }

                    var k,
                        keys = this.name.match(patterns.key),
                        merge = this.value,
                        reverse_key = this.name;

                    while ((k = keys.pop()) !== undefined) {

                        // Adjust reverse_key
                        reverse_key = reverse_key.replace(new RegExp("\\[" + k + "\\]$"), '');



                        // Push
                        if (k.match(patterns.push)) {
                            merge = self.build([], self.push_counter(reverse_key), merge);
                        } else {
                            merge = self.build({}, k, merge);
                        }
                        /*
                        // Fixed
                        else if (k.match(patterns.fixed)) {
                            merge = self.build([], k, merge);
                        }

                        // Named
                        else if (k.match(patterns.named)) {
                            merge = self.build({}, k, merge);
                        }
                        */
                    }

                    json = $.extend(true, json, merge);
                });

                objectData = json;
            });

            var submitData = {
                jsonformdata: JSON.stringify(objectData)
            };

            var submit = true;

            if (submit) {
                Ajax.call([{
                    methodname: 'local_diagnostic_submit_' + this.stage + '_form',
                    args: submitData,
                    done: this.handleFormSubmissionResponse.bind(this, submitData),
                    fail: Notification.exception
                }]);
            }

            submitbtn.prop('disabled', false);
        };

        Diagnostic.prototype.getQuizzes = function(submitData) {
            Ajax.call([{
                methodname: 'local_diagnostic_get_quizzes',
                args: submitData,
                done: this.handleFormSubmissionResponse.bind(this, submitData),
                fail: Notification.exception
            }]);
        };

        Diagnostic.prototype.getClusters = function(submitData) {
            /*
            Ajax.call([{
                methodname: 'local_diagnostic_get_quizzes',
                args: submitData,
                done: this.handleFormSubmissionResponse.bind(this, submitData),
                fail: Notification.exception
            }]);
            */

            let self = this;
            let loadingIcon = this.modal.getRoot().find('[data-region="loading-icon-container"]');
            let svgСhartsEl = this.modal.getRoot().find('.svgСharts');
            svgСhartsEl.html('');
            loadingIcon.removeClass('hidden');

            // http://m39.petel.learnapp.io/dev2/clusters.php
            var request = {
                methodname: 'local_diagnostic_get_clusters',
                args: submitData
            };

            Ajax.call([request])[0].done(function(data) {
                if (data.result) {
                    loadingIcon.addClass('hidden');
                    let jsondata = JSON.parse(data.json);

                    // console.log('jsondata ', jsondata);
                    BuubleAnimation(".svgСharts", jsondata, "div.popup-local-diagnostic", self.adParams, self.courseid, jsondata.attempt, jsondata.mid, jsondata.cmid);
                } else {
                    Notification.addNotification({
                        message: data.message,
                        type: 'error'
                    });
                }
            }).fail(Notification.exception);
        };

        /**
         * This triggers a form submission, so that any mform elements can do final tricks before the form submission is processed.
         *
         * @method submitForm
         * @param {Event} e Form submission event.
         * @private
         */
        Diagnostic.prototype.submitForm = function(e) {
            e.preventDefault();
            this.modal.getRoot().find('form').submit();
        };

        Diagnostic.prototype.object_build = function(base, key, value){
            base[key] = value;
            return base;
        };

        Diagnostic.prototype.push_counter = function(key){
            if(push_counters[key] === undefined){
                push_counters[key] = 0;
            }
            return push_counters[key]++;
        };

        return /** @alias module:core_group/newgroup */ {
            // Public variables and functions.
            /**
             * Attach event listeners to initialise this module.
             *
             * @method init
             * @param {string} selector The CSS selector used to find nodes that will trigger this module.
             * @param courseid
             * @return {Promise}
             */
            init: function(selector, courseid, adParams) {
                var DiagnosticInstance = new Diagnostic($(selector), courseid, adParams);
            }
        };
    });
