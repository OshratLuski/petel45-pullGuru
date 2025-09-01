/* eslint-disable require-jsdoc */
define([
    'jquery',
    'core/ajax',
    'core/str',
    'core/notification',
    'community_sharewith/modal',
    'community_sharewith/storage',
    'core/modal_factory',
    "core/modal_events",
    'core/fragment',
    'jqueryui',

], function($, Ajax, str, Notification, modal, St, ModalFactory, ModalEvents, Fragment) {

    var contextid = 0;

    return /** @alias module:community_sharewith/sendToCatalog */ {

        init: function(contextid) {
            this.contextid = contextid;

            var root = modal.modalWrapper,
                self = this;
            root.addEventListener('click', function(e) {
                var target = e.target;
                while (root.contains(target)) {

                    switch (target.dataset.handler) {
                        // Copy Section to course.
                        case 'selectUploadActivity':
                            this.selectUploadActivity();
                            break;
                        case 'addAssociation':
                            this.addAssociation(target);
                            break;
                        case 'removeAssociation':
                            $(target).parent().remove();
                            break;
                        case 'selectCategoryOnCatalog':
                            this.selectCategory(target);
                            break;
                    }
                    target = target.parentNode;
                }
            }.bind(this));

            root.addEventListener('change', function(e) {
                var target = e.target;
                while (root.contains(target)) {
                    if (target.dataset.handler === 'selectCourseOnCatalog') {
                        this.selectCourse(target);
                        return;
                    }
                    if (target.dataset.handler === 'selectSectionOnCatalog') {
                        $(target).next().removeClass('d-none');
                        return;
                    }
                    target = target.parentNode;
                }
            }.bind(this));
        },
        /**
         * Choose a course for copying the activity.
         *
         * @method selectUploadActivity
         * @param {Node} target element.
         */
        selectUploadActivity: function() {
            let self = this;

            const getBody = function() {

                // Get the content of the modal.
                var params = {cmid: St.cmid, courseid: St.getCurrentCourse()};
                return Fragment.loadFragment('community_sharewith', 'upload_activity_maagar', self.contextid, params);
            };

            var modalPromise = ModalFactory.create({
                type: ModalFactory.types.DEFAULT,
                title: M.util.get_string('share_national_shared', 'community_sharewith'),
                body: getBody()
            });

            $.when(modalPromise).then(function(fmodal) {

                // TODO needed close correctly
                $('#modalSharewith .close').click();

                fmodal.setLarge();

                var root = fmodal.getRoot();
                root.on(ModalEvents.bodyRendered, function() {
                    root.find('.modal-body').animate({
                        scrollTop: 0
                    }, 200);
                    /* $(root).find('.first-paragraph').delay(200).trigger('focus'); */
                });

                return fmodal;
            }).done(function(modal) {
                modal.show();
            }).fail(Notification.exception);

        },

        selectCourse: function(target) {
            var courseid = $(target).val();
            var selectedItem = (th, e, value, courseid) => {
                compid = competencies.indexOf(value);
                if (value && compid > 0) {
                    var competency = $('<div class = "competencies-item">' + value +
                        '<input type = "hidden" name = "choosedcompetencies-' + courseid + '" value = "' + compid + '"></div>');
                    competency.css("background-color", '#053468');
                    competency.appendTo(th.parent());
                    competency.on('click', function() {
                        competency.remove();
                    });
                    th.val('');
                    e.preventDefault();
                }
            };

            var parseResponse = function(response) {
                var section = response.sections;
                var oldSection = $(target).next();
                oldSection.replaceWith($(section));
                var cc = JSON.parse(response.competencies);
                var compcontainer = $(target).closest('.select-course').find('.coursecompetencies');
                if (cc.length !== 0) {
                    compcontainer.html(response.competencieshtml);
                    competencies = [];
                    Object.keys(cc).map(function(key) {
                        competencies[Number(key)] = cc[key];
                        return;
                    });
                    $("#coursecompetencies-" + courseid).autocomplete(
                        {
                            source: function(request, response) {
                                var results = $.ui.autocomplete.filter(Object.values(competencies), request.term);
                                response(results.slice(0, 10));
                            },
                            select: function(e, ui) {
                                selectedItem($(this), e, ui.item.value, courseid);
                            },
                            minLength: 0,
                            response: function(event, ui) {
                                if (!ui.content.length) {
                                    var noResult = '';
                                    ui.content.push(noResult);
                                }
                            }
                        }).click(function() {
                        if (this.value === "") {
                            $(this).autocomplete("search");
                        }
                    });
                    var root = modal.modalWrapper,
                        self = this;
                } else {
                    compcontainer.html('');
                }

            };
            Ajax.call([{
                methodname: 'community_sharewith_get_sections_html',
                args: {courseid: courseid},
                done: parseResponse,
                fail: Notification.exeption
            }]);
        },

        addAssociation: function(target) {
            var association = $(target).parent().clone();
            var catid = Number($(target).parent().attr('data-catid'));

            if ($(target).parent().siblings('[data-catid=' + catid + ']').length >= 2) {
                return;
                /* TODO association.find('button').toggleClass('d-none'); */
            }
            $(target).toggleClass('btn-outline-primary btn-outline-danger');
            $(target).attr('data-handler', 'removeAssociation');

            var compcontainer = association.closest('.select-course').find('.coursecompetencies');
            compcontainer.html('');

            association.insertAfter($(target).parent());
        },

        selectCategory: function(target) {
            var catid = $(target).val();
            $('#selectCourse').find('.select-course').addClass('d-none');
            $('#selectCourse').find('select').attr('disabled', 'true');
            $('#selectCourse')
                .find('.select-course[data-catid=' + catid + ']')
                .removeClass('d-none')
                .find('select').attr('disabled', false);
            $('#selectCourse').removeClass('d-none');
        },
    };
});
