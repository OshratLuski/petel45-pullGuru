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
    };

    let current_aside_question = {};
    let prev_search_question = {};

    return {
        init: function (callback) {

            // Get instance question.
            Ajax.call([{
                methodname: 'community_oer_get_question_instance',
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

            current_aside_question = current_aside;
            prev_search_question = prev_search;

            this.mergeInstanceWithPreset(instance_data, preset_data, function (data) {

                // Render aside.
                Templates.render('community_oer/question/popup/aside', data)
                    .done(function (html, js) {

                        if(current_aside_question.render_aside) {
                            Templates.replaceNodeContents(SELECTORS.mainAside, html, js);
                        }

                        // Render dashboard.
                        Templates.render('community_oer/question/popup/dashboard', data)
                            .done(function (html, js) {
                                Templates.replaceNodeContents(SELECTORS.mainDashboard, html, js);

                                if(prev_search_question.length > 0) {
                                    $.each(prev_search_question, function (index, value) {
                                        let param = value;
                                        self.createPillSearch(param, value, function () {
                                            self.defaultRenderBlocks();
                                            callback();
                                        });
                                    });
                                }else{
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
            // Default render blocks.
            let elements = $("*").filter(function () {
                return ($(this).data("plugin") === 'question' && $(this).data("area") === 'sidemenu');
            });

            $(elements).each(function () {

                // Click on category, course, section.
                if ($(this).data("action") === current_aside_question.type &&
                    $(this).data("value") === current_aside_question.value &&
                    $(this).data("secondaction") === undefined &&
                    current_aside_question.url_params.childcategory === undefined
                ) {

                    $(this).click();
                }

                // Click on section childcategory.
                if ($(this).data("action") === current_aside_question.type &&
                    $(this).data("value") === current_aside_question.value &&
                    current_aside_question.url_params.childcategory !== undefined &&
                    $(this).data("childcategory") === parseInt(current_aside_question.url_params.childcategory)
                ) {
                    $(this).click();
                }
            });
        },

        actionOnClick: function (object) {

            // Button view selected questions.
            if($('.btn-view-selected-questions').hasClass('active')){
                $('.btn-view-selected-questions').removeClass('active');
                $('.btn-view-selected-questions').removeClass('btn-success');
                $('.btn-view-selected-questions').addClass('btn-outline-primary');
            }

            let data = $(object).data();
            let self = this;

            if (data.area === 'sidemenu') {
                let elements = $("*").filter(function () {
                    return ($(this).data("plugin") === 'question' && $(this).data("area") === 'sidemenu');
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
                    return ($(this).data("plugin") === 'question' && $(this).data("area") === 'paging');
                });

                $(elements).each(function () {
                    $(this).data("selected", 0);
                });

                $(object).data("selected", "1");
            }

            if (data.area !== 'paging') {
                let elements = $("*").filter(function () {
                    return ($(this).data("plugin") === 'question' && $(this).data("area") === 'paging');
                });

                $(elements).each(function () {
                    $(this).data("selected", 0);
                });
            }

            // Hidden questions.
            if (data.area === 'hidden') {
                if ($(object).data("selected") !== undefined) {
                    $(object).removeData("selected");
                } else {
                    $(object).data("selected", "1");
                }
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
            if(data.area === 'removeallpills'){
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
                    return ($(this).data("plugin") === 'question' && $(this).data("area") === 'sorting');
                });

                $(elements).each(function () {
                    $(this).data("selected", '0');
                });
                
                if (flag) {
                    $(object).data("selected", '1');
                }
            }

            if (data.area === 'sort-column') {
                $(object).data("selected", '1');
            }

            if (data.area === 'mainsearch') {
                let param = data.value;
                this.createPillSearch(param, data.value, function(){
                    self.renderBlocks();
                });
            }else{
                this.renderBlocks();
            }

            // Change title breadcrumbs.
            this.changeTitleBreadcrumbs(object);
        },

        returnPreset: function () {

            // Get selected.
            let selected = $("*").filter(function () {
                return ($(this).data("plugin") === 'question' && ($(this).data("selected") === '1' || $(this).data("selected") === 1));
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
            $(SELECTORS.blockContent).find('.question-item-wrapper').remove();
            $(SELECTORS.blockContent).find('.pagination').remove();

            // Render loading icon.
            Templates.render('community_oer/loading', {})
                .done(function (html, js) {
                    Templates.replaceNodeContents(SELECTORS.blockContent, html, js);
                })
                .fail(Notification.exception);

            Ajax.call([{
                methodname: 'community_oer_get_question_blocks',
                args: {
                    presets: JSON.stringify(returnpreset)
                },
                done: function (response) {
                    let result = JSON.parse(response);

                    Str.get_strings([
                        {key: 'resultsearch', component: 'community_oer'},
                        {key: 'items', component: 'community_oer'},
                        {key: 'itemshidden', component: 'community_oer'},
                    ]).done(function (strings) {

                        // Update total questions.
                        let str = '';
                        if(result.if_hidden_items){
                            str = strings[0] + ': ' + result.total_blocks_hidden + ' ' + strings[2];
                        }else{
                            str = strings[0] + ': ' + result.total_blocks + ' ' + strings[1];
                        }

                        $(SELECTORS.totalItems).html(str);

                        // Update total menu block.
                        $(SELECTORS.mainTotalElementsActivity).html('(' + result.activity_total_all_blocks + ')');
                        $(SELECTORS.mainTotalElementsQuestion).html('(' + result.question_total_all_blocks + ')');
                        $(SELECTORS.mainTotalElementsSequence).html('(' + result.sequence_total_all_blocks + ')');

                        // Render blocks.
                        Templates.render('community_oer/question/popup/block', result)
                            .done(function (html, js) {
                                Templates.replaceNodeContents(SELECTORS.blockContent, html, js);
                                callback();
                            })
                            .fail(Notification.exception);
                    });
                },
                fail: Notification.exception
            }]);
        },

        renderSelectedBlocks: function (qids) {

            let returnpreset = this.returnPreset();

            // Remove block items.
            $(SELECTORS.blockContent).find('.question-item-wrapper').remove();
            $(SELECTORS.blockContent).find('.pagination').remove();

            // Render loading icon.
            Templates.render('community_oer/loading', {})
                .done(function (html, js) {
                    Templates.replaceNodeContents(SELECTORS.blockContent, html, js);
                })
                .fail(Notification.exception);

            Ajax.call([{
                methodname: 'community_oer_get_selected_question_blocks',
                args: {
                    qids: JSON.stringify(qids)
                },
                done: function (response) {
                    let result = JSON.parse(response);

                    Str.get_strings([
                        {key: 'resultselected', component: 'community_oer'},
                        {key: 'qitems', component: 'community_oer'},
                        {key: 'itemshidden', component: 'community_oer'},
                    ]).done(function (strings) {

                        // Update total questions.
                        let str = strings[0] + ': ' + result.total_blocks + ' ' + strings[1];

                        $(SELECTORS.totalItems).html(str);

                        // Update total menu block.
                        // $(SELECTORS.mainTotalElementsActivity).html('(' + result.activity_total_all_blocks + ')');
                        // $(SELECTORS.mainTotalElementsQuestion).html('(' + result.question_total_all_blocks + ')');
                        // $(SELECTORS.mainTotalElementsSequence).html('(' + result.sequence_total_all_blocks + ')');

                        // Render blocks.
                        Templates.render('community_oer/question/popup/block', result)
                            .done(function (html, js) {
                                Templates.replaceNodeContents(SELECTORS.blockContent, html, js);
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
                {key: 'removeallpills', component: 'community_oer'},
            ]).done(function (strings) {

                let pillTarget = $(SELECTORS.filterPills);

                // Add remove all.
                if(!pillTarget.find('button').length) {
                    let pillremoveall = $(`
                    <button type="button" data-plugin="question" data-area="removeallpills" class="removeallpills btn btn-secondary  mr-3 py-1 px-3  mb-2">
                        ${strings[0]}                       
                    </button>`);

                    pillTarget.append(pillremoveall);
                }

                let pill = $(`
                <div class="checked-option-pill rounded-pill bg-light text-primary border border-primary mr-3 py-1 px-3 mb-2" data-id="${id}">
                    <span class="checked-option-pill-name">${text}</span>
                    <button type="button" class="close ml-3" aria-label="Close">
                        <span aria-hidden="true" data-plugin="question" data-area="pill" data-value="${id}">&times;</span>
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
                {key: 'removeallpills', component: 'community_oer'},
            ]).done(function (strings) {

                let pillTarget = $(SELECTORS.filterPills);

                // Add remove all.
                if(!pillTarget.find('button').length) {
                    let pillremoveall = $(`
                    <button type="button" data-plugin="question" data-area="removeallpills" class="removeallpills btn btn-secondary mr-3 py-1 px-3 mb-2">
                        ${strings[0]}                       
                    </button>`);

                    pillTarget.append(pillremoveall);
                }

                let pill = $(`
                <div class="checked-option-pill search-pill rounded-pill bg-light text-primary border border-primary mr-3 py-1 px-3 mb-2" data-id="${query}">
                    <span class="checked-option-pill-name">${text}</span>
                    <button type="button" class="close ml-3" aria-label="Close">
                        <span aria-hidden="true" data-plugin="question" data-area="pillsearch" data-value="${query}" data-selected="1">&times;</span>
                    </button>
                </div>`);

                pillTarget.append(pill);

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

        singlePage: function (data) {
            Templates.render('community_oer/question/popup/block', data)
                .done(function (html, js) {
                    Templates.prependNodeContents('#page-content', html, js);
                })
                .fail(Notification.exception);
        },

        iframeProperties: function (id) {
            window.addEventListener("load", function(e){
                let height = document.getElementById("region-main").offsetHeight;
                let data = [height, id];
                top.postMessage(data);
                document.getElementById("page-question-preview").style.overflow="scroll";
                new SimpleBar(document.getElementById("page-question-preview"), { autoHide: false });              
             });
        }
    };
});
