/* eslint-disable require-jsdoc */
define([
    'community_sharesequence/main',
    'jquery',
    'core/ajax',
    'core/str',
    'core/templates',
    'core/notification',
    'core/modal_factory',
    'core/fragment',
    'jqueryui',

], function(Main, $, Ajax, str, Templates, Notification, ModalFactory, Fragment) {

    return {

        init: function(uniqueid) {
            var form = $('#sharing_sequence_form_' + uniqueid),
                self = this;

            form.on('keydown', 'input[type="text"]', function(e) {
                if (e.keyCode === 13) {
                    e.preventDefault();
                }
            });

            form.delegate('[data-descr="addtag"]', 'keydown', function(e) {
                if (e.keyCode === 13 && $(this).val()) {
                    var tag = $('<div class = "tags-item">' + $(this).val() +
                        '<input type = "hidden" name = "tags[]" value = "' + $(this).val() + '"></div>');
                    tag.css("background-color", self.getRandColor());
                    tag.appendTo($(this).parent());
                    tag.on('click', function() {
                        $(this).remove();
                    });
                    $(this).val('');
                }
            });

            form.find('.uploadsequencesubmit').click(function(event) {
                self.uploadSequence(event, form);
            });

            form.find('.uploadsequenceclose').click(function() {

                // Close modal factory popup.
                self.closeModalFactory(form);
            });

        },

        uploadSequence: function(e, form) {
            let self = this;

            e.preventDefault();

            // Remove errors.
            form.find('.invalid-feedback').hide();

            self.addBtnSpinner(form);

            var serializedForm = form.serializeArray(),
                data = {};

            serializedForm.forEach(function (item) {
                data[item.name] = data[item.name] ? data[item.name] + ',' + item.value : item.value;
            });

            // Serialized selected sections.
            let selectedsections = [];
            form.find('.selected-section-block').each(function() {
                if (!$(this).hasClass('hidden')) {
                    selectedsections.push({
                        'cat_id': $(this).data("cat_id"),
                        'course_id': $(this).data("course_id"),
                        'section_id': $(this).data("section_id"),
                        'cat_name': $(this).find(".selected-category-name").html(),
                        'course_name': $(this).find(".selected-course-name").html(),
                        'section_name': $(this).find(".selected-section-name").html(),
                    });
                }
            });

            data['selected_sections'] = selectedsections;

            // Serialized selected competencies.
            let selectedcompetencies = [];
            form.find('.selected-competency-block').each(function() {
                if (!$(this).hasClass('hidden')) {
                    selectedcompetencies.push({
                        'competency_id': $(this).data("comp_id"),
                        'section_id': $(this).data("section_id"),
                    });
                }
            });

            data['selected_competencies'] = selectedcompetencies;

            var parseResponse = function(response) {
                if (response.result) {

                    if (!response.validation) {
                        let firstNameError = '';
                        let errors = JSON.parse(response.errors);
                        $.each(errors, function(index, value) {
                            if (index === 0) {
                                firstNameError = value;
                            }
                            form.find('.error-' + value).show();
                        });
                        // Scroll to first error.
                        var parentModal = form.closest('.modal-body');
                        var uploadsequenceOffset = +parentModal.find('.uploadsequence').offset().top * (-1);
                        var targetOffset = parentModal.find('.error-' + firstNameError).closest('.form-group').offset().top * (-1);
                        var result = uploadsequenceOffset - targetOffset;
                        parentModal.closest('.modal-body').animate({scrollTop: result}, 500);

                        self.removeBtnSpinner(form);
                        return;
                    } else {

                        // Close modal factory popup.
                        self.closeModalFactory(form);

                        // Init page 2.
                        Main.init_page_2(data);
                    }
                } else {

                    // Close modal factory popup.
                    self.closeModalFactory(form);

                    let title = M.util.get_string('error', 'community_sharesequence');
                    let text = M.util.get_string('system_error_contact_administrator', 'community_sharesequence');
                    self.informationPopup(title, text);
                }
            };

            Ajax.call([{
                methodname: 'community_sharesequence_submit_sequence_page_1',
                args: {
                    data: JSON.stringify(data)
                },
                done: parseResponse,
                fail: Notification.exception
            }]);
        },

        getRandColor: function() {
            var color = Math.floor(Math.random() * Math.pow(256, 3)).toString(16);
            while (color.length < 6) {
                color = "0" + color;
            }
            return "#" + color;
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
    };
});
