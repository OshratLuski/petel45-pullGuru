define([
    'jquery',
    'core/yui',
    'core/str',
    'core/modal_factory',
    'core/modal_events',
    'core/ajax',
    'core/templates',
    'core/notification',

], function ($, Y, Str, ModalFactory, ModalEvents, Ajax, Templates, Notification) {
    `use strict`;

    let SELECTORS = {
        mainAside: '.main-aside',
        mainDashboard: '.main-dashboard',
        mainTitle: '.main-title',
        filterPills: '.checked-option-pills',
        filterButtonArea: '.activity-filters',
        totalItems: '.total-activities-block',
        blockContent: '.activity-blocks-content',
        mainTotalElementsActivity: '.main-total-elements-activity',
        mainTotalElementsQuestion: '.main-total-elements-question',
        mainTotalElementsSequence: '.main-total-elements-sequence',
        mainTotalElementsCourse: '.main-total-elements-course',
    };

    let current_aside_activity = {};
    let prev_search_activity = {};
    let default_filters_activity = {};
    let if_iframe = false;
    let if_scroll_up = false;

    return {
        init: function (callback) {

            // Get status of iframe.
            if_iframe = $('#main-page-iframe').val();

            // Get instance activity.
            Ajax.call([{
                methodname: 'community_oer_get_activity_instance',
                args: {},
                done: function (response) {
                    callback(response);
                },
                fail: Notification.exception
            }]);
        },

        mergeInstanceWithPreset: function (instance_data, preset_data, callback) {
            callback(instance_data);
        },

        renderInstance: function (instance_data, current_aside, prev_search, preset_data, callback) {
            let self = this;

            default_filters_activity = instance_data.default_filters;
            current_aside_activity = current_aside;
            prev_search_activity = prev_search;

            this.mergeInstanceWithPreset(instance_data, preset_data, function (data) {

                // Render aside.
                Templates.render('community_oer/activity/aside', data)
                    .done(function (html, js) {

                        if (current_aside_activity.render_aside) {
                            Templates.replaceNodeContents(SELECTORS.mainAside, html, js);
                        }

                        // Render dashboard.
                        Templates.render('community_oer/activity/dashboard', data)
                            .done(function (html, js) {
                                Templates.replaceNodeContents(SELECTORS.mainDashboard, html, js);

                                if (prev_search_activity.length > 0) {
                                    $.each(prev_search_activity, function (index, value) {

                                        // Set without split.
                                        value = '"' + value + '"';

                                        let param = value;
                                        self.createPillSearch(param, value, function () {
                                            self.defaultRenderBlocks();
                                            callback();
                                        });
                                    });
                                } else {
                                    self.defaultRenderBlocks();
                                    callback();
                                }
                            })
                            .fail(Notification.exception);
                    })
                    .fail(Notification.exception);
            });
        },

        defaultRenderBlocks: function () {
            let self = this;

            // Default filters
            let filters = $("*").filter(function () {
                return ($(this).data("plugin") === 'activity' && $(this).data("area") === 'filters');
            });

            $.each(default_filters_activity , function(key, value) {
                $(filters).each(function () {
                    if($(this).data("action") === value.filter && $(this).data("value") === value.value){
                        $(this).data("selected", "1");
                        $(this).prop('checked', true);
                        let text = $(this).parent().find('label').html().trim();
                        self.createPill($(this).attr("id"), text);
                    }
                });
            });

            // Default render blocks.
            let elements = $("*").filter(function () {
                return ($(this).data("plugin") === 'activity' && $(this).data("area") === 'sidemenu');
            });

            $(elements).each(function () {
                if ($(this).data("action") === current_aside_activity.type && $(this).data("value") === current_aside_activity.value) {
                    $(this).click();
                }
            });
        },

        actionOnClick: function (object) {
            let data = $(object).data();
            let self = this;

            if (data.area === 'sidemenu') {
                let elements = $("*").filter(function () {
                    return ($(this).data("plugin") === 'activity' && $(this).data("area") === 'sidemenu');
                });

                $(elements).each(function () {
                    $(this).removeData("selected");
                });

                $(object).data("selected", "1");
            }

            if (data.area === 'filters') {
                if ($(object).data("selected") !== undefined) {
                    $(object).removeData("selected");
                    this.removePill($(object).attr("id"));
                } else {
                    $(object).data("selected", "1");
                    let text = $(object).parent().find('label').html().trim();
                    this.createPill($(object).attr("id"), text);
                }
            }

            // Paging.
            if (data.area === 'paging') {
                let elements = $("*").filter(function () {
                    return ($(this).data("plugin") === 'activity' && $(this).data("area") === 'paging');
                });

                $(elements).each(function () {
                    $(this).data("selected", 0);
                });

                $(object).data("selected", "1");

                if_scroll_up = true;
            }

            if (data.area !== 'paging') {
                let elements = $("*").filter(function () {
                    return ($(this).data("plugin") === 'activity' && $(this).data("area") === 'paging');
                });

                $(elements).each(function () {
                    $(this).data("selected", 0);
                });
            }

            // Remove pill.
            if (data.area === 'pill') {
                let id = data.value;
                $(SELECTORS.filterButtonArea).find("#" + id).trigger('click');

                // Remove button remove all pills.
                let buttons = $(SELECTORS.filterPills).find('button');
                if (buttons.length === 1) {
                    buttons.remove();
                }
            }

            // Remove pill search.
            if (data.area === 'pillsearch') {
                self.removePillSearch(object);
            }

            // Remove all pills.
            if (data.area === 'removeallpills') {
                let elements = $("*").filter(function () {
                    return ($(this).data("area") === 'filters');
                });

                $(elements).each(function () {
                    $(this).removeData("selected");
                    $(this).prop('checked', false);
                    self.removePill($(this).attr("id"));
                });

                let elementssearch = $("*").filter(function () {
                    return ($(this).data("area") === 'pillsearch');
                });

                $(elementssearch).each(function () {
                    self.removePillSearch(this);
                });

            }

            if (data.area === 'sorting') {

                let flag = true;
                if (parseInt($(object).data("selected")) === 1) {
                    flag = false;
                }

                let elements = $("*").filter(function () {
                    return ($(this).data("plugin") === 'activity' && $(this).data("area") === 'sorting');
                });

                $(elements).each(function () {
                    $(this).data("selected", '0');
                });

                if (flag) {
                    $(object).data("selected", '1');
                }
            }

            if (data.area === 'mainsearch') {
                let param = data.value;
                this.createPillSearch(param, data.value, function () {
                    self.renderBlocks();
                });
            } else {
                this.renderBlocks();
            }

            // Change title breadcrumbs.
            this.changeTitleBreadcrumbs(object);
        },

        returnPreset: function () {

            // Get selected.
            let selected = $("*").filter(function () {
                return ($(this).data("plugin") === 'activity' && ($(this).data("selected") === '1' || $(this).data("selected") === 1));
            });

            let dataselected = {};
            $(selected).each(function (index) {
                dataselected[index] = $(this).data();
            });

            return dataselected;
        },

        renderBlocks: function () {

            let returnpreset = this.returnPreset();

            // Remove block items.
            $(SELECTORS.blockContent).find('.activity-item').remove();
            $(SELECTORS.blockContent).find('.pagination').remove();

            // Render loading icon.
            Templates.render('community_oer/loading', {})
                .done(function (html, js) {
                    Templates.replaceNodeContents(SELECTORS.blockContent, html, js);
                })
                .fail(Notification.exception);

            Ajax.call([{
                methodname: 'community_oer_get_activity_blocks',
                args: {
                    presets: JSON.stringify(returnpreset)
                },
                done: function (response) {
                    let result = JSON.parse(response);

                    // Check if run in iframe.
                    result.iframe = (parseInt(if_iframe) === 1);

                    // Checkbox selected.
                    if(result.iframe) {
                        let selected = $('#oer_activity_items_selected').val();

                        let data = [];
                        if(selected.length !== 0){
                            data = JSON.parse(selected);
                        }

                        $.each(result.blocks, function (index, value) {

                            result.blocks[index].checkbox_selected = false;
                            $.each(data, function (index2, value2) {
                                if(value2 === value.cmid){
                                    result.blocks[index].checkbox_selected = true;
                                }
                            });
                        });
                    }

                    Str.get_strings([
                        { key: 'resultsearch', component: 'community_oer' },
                        { key: 'items', component: 'community_oer' },
                    ]).done(function (strings) {

                        // Update total activities.
                        let str = strings[0] + ': ' + result.total_blocks + ' ' + strings[1];
                        $(SELECTORS.totalItems).html(str);

                        // Update total menu block.
                        $(SELECTORS.mainTotalElementsActivity).html('(' + result.activity_total_all_blocks + ')');
                        $(SELECTORS.mainTotalElementsQuestion).html('(' + result.question_total_all_blocks + ')');
                        $(SELECTORS.mainTotalElementsSequence).html('(' + result.sequence_total_all_blocks + ')');
                        $(SELECTORS.mainTotalElementsCourse).html('(' + result.course_total_all_blocks + ')');

                        // Render blocks.
                        Templates.render('community_oer/activity/block', result)
                            .done(function (html, js) {
                                Templates.replaceNodeContents(SELECTORS.blockContent, html, js);

                                if (if_scroll_up) {
                                    const element = document.querySelector('.activity-block-header');
                                    element.scrollIntoView({behavior: "smooth", block: "end", inline: "nearest"});
                                    if_scroll_up = false;
                                }

                                callback();
                            })
                            .fail(Notification.exception);
                    });
                },
                fail: Notification.exception
            }]);
        },

        changeTitleBreadcrumbs: function (object) {
            let title = $(object).data('breadcrumbs');
            $(SELECTORS.mainTitle).html(title);
        },

        createPill: function (id, text) {

            Str.get_strings([
                { key: 'removeallpills', component: 'community_oer' },
            ]).done(function (strings) {

                let pillTarget = $(SELECTORS.filterPills);

                // Add remove all.
                if (!pillTarget.find('button').length) {
                    let pillremoveall = $(`
                    <button type="button" data-plugin="activity" data-area="removeallpills" class="removeallpills btn btn-secondary mr-3 py-1 px-3 mb-2">
                        ${strings[0]}                       
                    </button>`);

                    pillTarget.append(pillremoveall);
                }

                let pill = $(`
                <div class="checked-option-pill rounded-pill bg-light text-primary border border-primary mr-3 py-1 px-3 mb-2" data-id="${id}">
                    <span class="checked-option-pill-name">${text}</span>
                    <button type="button" class="close ml-3" aria-label="Close">
                        <span aria-hidden="true" data-plugin="activity" data-area="pill" data-value="${id}">&times;</span>
                    </button>
                </div>`);

                pillTarget.append(pill);
            });
        },

        removePill: function (id) {
            $(SELECTORS.filterPills).find(`[data-id="${id}"]`).remove();

            // Remove button remove all pills.
            let buttons = $(SELECTORS.filterPills).find('button');
            if (buttons.length === 1) {
                buttons.remove();
            }
        },

        createPillSearch: function (query, text, callback) {

            Str.get_strings([
                { key: 'removeallpills', component: 'community_oer' },
            ]).done(function (strings) {

                let pillTarget = $(SELECTORS.filterPills);

                // Add remove all.
                if (!pillTarget.find('button').length) {
                    let pillremoveall = $(`
                    <button type="button" data-plugin="activity" data-area="removeallpills" class="removeallpills btn btn-secondary mr-3 py-1 px-3 mb-2">
                        ${strings[0]}                       
                    </button>`);

                    pillTarget.append(pillremoveall);
                }

                let simpleword = false;
                if (query.length > 0 && query[0] === '"' && query[query.length - 1] === '"') {
                    simpleword = true;
                    query = query.substring(1, query.length - 1);
                }

                if (simpleword) {
                    let pill = $(`
                        <div class="checked-option-pill search-pill rounded-pill bg-light text-primary border border-primary mr-3 py-1 px-3 mb-2" data-id="${query}">
                            <span class="checked-option-pill-name">${query}</span>
                            <button type="button" class="close ml-3" aria-label="Close">
                                <span aria-hidden="true" data-plugin="activity" data-area="pillsearch" data-value="${query}" data-selected="1">&times;</span>
                            </button>
                        </div>`);

                    pillTarget.append(pill);
                } else {
                    query.split(' ').forEach(function(value) {
                        if (value.length > 0) {
                            let pill = $(`
                                <div class="checked-option-pill search-pill rounded-pill bg-light text-primary border border-primary mr-3 py-1 px-3 mb-2" data-id="${value}">
                                    <span class="checked-option-pill-name">${value}</span>
                                    <button type="button" class="close ml-3" aria-label="Close">
                                        <span aria-hidden="true" data-plugin="activity" data-area="pillsearch" data-value="${value}" data-selected="1">&times;</span>
                                    </button>
                                </div>`);

                            pillTarget.append(pill);
                        }
                    });
                }

                callback();
            });
        },

        removePillSearch: function (object) {
            $(object).parent().parent().remove();

            // Remove button remove all pills.
            let buttons = $(SELECTORS.filterPills).find('button');
            if (buttons.length === 1) {
                buttons.remove();
            }
        },


        openDropdownMenuForBlock: function () {

            let DROPDOWN_MENU = {};

            $('.dropdown.copy-activity-dropdown-wrapper').on('show.bs.dropdown', function () {
                const target = $(this).parent();
                renderDropdown(target.parent());
            });

            $('.dropdown.copy-activity-dropdown-wrapper').on('hide.bs.dropdown', function () {
                DROPDOWN_MENU = {};
                const target = $(this).parent();
                target.find('.copy-activity-dropdown').html('');
            });

            $('.dropdown.copy-activity-dropdown-wrapper .dropdown-menu').on('click', function (e) {
                e.stopPropagation();
            });

            function renderDropdown(target) {
                Ajax.call([{
                    methodname: 'community_oer_get_my_courses_and_sections',
                    args: {},
                    done: function (response) {
                        renderCourses(JSON.parse(response), target);
                    },
                    fail: Notification.exception
                }]);
            }

            function renderCourses(resp, target) {
                Templates.render('community_oer/activity/copy-activity-dropdown', resp)
                    .then(function (html, js) {
                        Templates.replaceNodeContents(target.find('.copy-activity-dropdown'), html, js);
                        target.find('.preloading-wrapper').hide();
                        target.find('.dropdown-courses').show();
                        DROPDOWN_MENU.targetDropdown = target.find('.dropdown');
                        DROPDOWN_MENU.courses = target.find('.dropdown-item.course-item');
                        DROPDOWN_MENU.sections = target.find('.dropdown-item.section-item');
                        DROPDOWN_MENU.closeBtn = target.find('button.close');
                        DROPDOWN_MENU.backBtn = target.find('button.back');
                        DROPDOWN_MENU.submitBtn = target.find('button.submit-btn');
                        DROPDOWN_MENU.topArea = target.find('.drodpdown-menu-top');
                        DROPDOWN_MENU.width = DROPDOWN_MENU.targetDropdown.outerWidth();
                        DROPDOWN_MENU.strWidth = 1;
                        dropdownActions(DROPDOWN_MENU);
                        checkCourseWidth(DROPDOWN_MENU.courses);
                    }).fail(function (ex) {
                        console.log('ERROR');
                    });
            }

            function checkCourseWidth(courses) {
                DROPDOWN_MENU.strWidth = 1;
                courses.each(function () {
                    var width = $(this).find('span').outerWidth();
                    if (width > DROPDOWN_MENU.strWidth) {
                        DROPDOWN_MENU.strWidth = width;
                    }
                });
                if (DROPDOWN_MENU.strWidth > DROPDOWN_MENU.width) {
                    DROPDOWN_MENU.targetDropdown.find('.copy-activity-dropdown-inner').css('width', +DROPDOWN_MENU.strWidth + "px");
                } else {
                    DROPDOWN_MENU.targetDropdown.find('.copy-activity-dropdown-inner').css('width', +DROPDOWN_MENU.width + "px");
                }
            }

            function checkSectionWidth(sections) {
                DROPDOWN_MENU.strWidth = 1;
                var topAreaBtns = DROPDOWN_MENU.topArea.find('button');
                var btnsWidth = 0;
                topAreaBtns.each(function() {
                    btnsWidth += Math.floor($(this).outerWidth());
                });
                var topAreaWidth = Math.floor(DROPDOWN_MENU.topArea.find('span').outerWidth()) + btnsWidth;
                sections.each(function () {
                    if ($(this).attr('style') != "display: none;") {
                        var width = $(this).find('.section-item-name').outerWidth();
                        if (width > DROPDOWN_MENU.strWidth) {
                            DROPDOWN_MENU.strWidth = width + 60; // 60px it is paddings and icon width
                        }
                    }
                });
                if (+DROPDOWN_MENU.strWidth <= topAreaWidth) {
                    DROPDOWN_MENU.targetDropdown.find('.copy-activity-dropdown-inner').css('width', "100%");
                } else if (+DROPDOWN_MENU.strWidth > +DROPDOWN_MENU.width) {
                    DROPDOWN_MENU.targetDropdown.find('.copy-activity-dropdown-inner').css('width', +DROPDOWN_MENU.strWidth + "px");
                } else {
                    DROPDOWN_MENU.targetDropdown.find('.copy-activity-dropdown-inner').css('width', +DROPDOWN_MENU.width + "px");
                }
            }

            function dropdownActions(target) {

                target.courses.on('click', function () {
                    const courseid = $(this).data('id');
                    const course_name = $(this).text();
                    DROPDOWN_MENU.topArea.find('.drodpdown-menu-top-text').text(course_name);
                    if (courseid) {
                        target.courses.hide();
                        target.sections.each(function () {
                            if (+$(this).data('courseid') === +courseid) {
                                $(this).show();
                            }
                        });
                    }
                    target.backBtn.show();
                    checkSectionWidth(DROPDOWN_MENU.sections);

                });

                target.sections.on('click', function () {
                    if (!$(this).hasClass('active')) {
                        target.sections.removeClass('active');
                        $(this).addClass('active');
                        target.submitBtn.show();
                    }
                    target.submitBtn.parent().show();
                });

                target.backBtn.on('click', function (e) {
                    var back = $(e.target);
                    target.sections.removeClass('active');
                    DROPDOWN_MENU.submitBtn.hide();
                    DROPDOWN_MENU.topArea.find('.drodpdown-menu-top-text').text(DROPDOWN_MENU.topArea.data('text'));
                    target.sections.hide();
                    target.courses.show();
                    back.hide();
                    checkCourseWidth(DROPDOWN_MENU.courses);
                });

                target.closeBtn.on('click', function (e) {
                    target.sections.removeClass('active');
                    DROPDOWN_MENU.targetDropdown.trigger('click');
                });

                target.submitBtn.on('click', function () {

                    let button = $(this).parent().parent().parent().find('.copy-activity-btn');
                    let cmid = $(button).data('cmid');
                    let referer = $(button).data('referer');

                    let sectionid = $('.dropdown-item.section-item.active').data('sectionid');

                    Ajax.call([{
                        methodname: 'community_oer_copy_activity_to_section',
                        args: {
                            sectionid: sectionid,
                            cmid: cmid,
                            referer: referer
                        },
                        done: function (response) {
                            DROPDOWN_MENU.targetDropdown.dropdown('hide');

                            $(button).removeClass('btn-primary');
                            $(button).addClass('btn--white');
                            $(button).find('span').text('הועתק לסביבה');

                            Str.get_strings([
                                { key: 'activitycopy', component: 'community_oer' },
                                { key: 'successcopybody', component: 'community_oer' },
                                { key: 'approve', component: 'community_oer' },
                            ]).done(function (strings) {
                                ModalFactory.create({
                                    type: ModalFactory.types.CANCEL,
                                    title: strings[0],
                                    body: strings[1]
                                }).done(function (modal) {
                                    modal.setButtonText('cancel', strings[2]);
                                    modal.show();
                                });
                            });
                        },
                        fail: Notification.exception
                    }]);
                });

            }

        },

        singlePage: function (cmid) {
            Ajax.call([{
                methodname: 'community_oer_activity_get_single_page',
                args: {
                    cmid: cmid
                },
                done: function (response) {
                    let data = JSON.parse(response);

                    if(Object.keys(data).length) {
                        Templates.render('community_oer/activity/block', data)
                            .done(function (html, js) {
                                Templates.prependNodeContents('#page-content', html, js);

                                $('#page-header div').html('');
                                $('.header.backto').hide();
                                $('#topofscroll').addClass('wider');
                            })
                            .fail(Notification.exception);
                    }
                },
                fail: Notification.exception
            }]);
        }
    };
});
