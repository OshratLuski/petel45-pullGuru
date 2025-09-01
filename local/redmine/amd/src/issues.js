define([
    'jquery',
    'core/str',
    'core/modal_factory',
    'core/modal_events',
    'core/ajax',
    'core/templates',
    'core/notification',
    'local_redmine/chat'

], function($, Str, ModalFactory, ModalEvents, Ajax, Templates, Notification, Chat) {

    let global_params = {
        default_search: '',
    };

    let history_params = {
        search: '',
        filter: 3,
        sort_col: '',
        sort_dir: '',
        page: 1,
    };

    let active_params = {
        search: '',
        filter: 1,
        sort_col: '',
        sort_dir: '',
        page: 1,
    };

    let SELECTORS = {
        ACTIVEISSUES: '.activeissues-wrapper',
        HISTORYISSUES: '.history-wrapper'
    };

    return {
        init: function(issueid) {
            // Hide header block.
            $('#page-header').addClass('d-none');

            if(issueid !== undefined){
                this.renderChatPage(issueid);
            }else{
                this.renderIssuesPage();
            }
        },

        renderIssuesPage: function() {
            let self = this;

            Templates.render('local_redmine/issues/view', global_params)
                .done(function(html, js) {
                    Templates.replaceNodeContents('#local-redmine-issues-page', html, js);

                    self.renderIssuesActiveBlock();
                    self.renderIssuesHistoryBlock();

                    $("#local-redmine-search").change(function(e) {
                        let value = $(e.target).val();

                        global_params.default_search = value;

                        history_params.search = value;
                        history_params.page = 1;
                        self.renderIssuesHistoryBlock();

                        active_params.search = value;
                        active_params.page = 1;
                        self.renderIssuesActiveBlock();
                    });
                });
        },

        renderIssuesActiveBlock: function() {
            let self = this;

            this.showLoading(SELECTORS.ACTIVEISSUES);
            Ajax.call([{
                methodname: 'local_redmine_get_active_issues',
                args: active_params,
                done: function(response) {

                    let data = JSON.parse(response);
                    Templates.render('local_redmine/issues/activeissues', data)
                        .done(function(html, js) {
                            Templates.replaceNodeContents('#local-redmine-issues-activeissues', html, js);

                            // Events on activeissues block.
                            $("#local-redmine-activeissues-block").on("click", function(e) {

                                // Paging.
                                if ($(e.target).data('area') === 'paging') {
                                    if ($(e.target).data('value') !== 'next' && $(e.target).data('value') !== 'previus') {
                                        active_params.page = $(e.target).data('value');
                                    } else {
                                        let active_page = $(e.target).parent().parent().find('.active a').data('value');

                                        if ($(e.target).data('value') === 'next') {
                                            active_params.page = active_page + 1;
                                        }

                                        if ($(e.target).data('value') === 'previus') {
                                            active_params.page = active_page - 1;
                                        }
                                    }

                                    self.renderIssuesActiveBlock();
                                }

                             
                                // Sorting.
                                if ($(e.target).data('area') === 'sorting') {
                                    active_params.sort_col = $(e.target).data('column');

                                    let direction = '';
                                    if ($(e.target).data('value').length === 0 || $(e.target).data('value') === 'asc') {
                                        direction = 'desc';
                                    }

                                    if ($(e.target).data('value') === 'desc') {
                                        direction = 'asc';
                                    }

                                    active_params.sort_col = $(e.target).data('column');
                                    active_params.sort_dir = direction;

                                    active_params.page = 1;
                                    self.renderIssuesActiveBlock();
                                }

                                // Get single issue page.
                                if ($(e.target).closest('.appeals-history-table-row').data('area') === 'edit_page') {
                                    let id = $(e.target).data('value') || $(e.target).closest('.appeals-history-table-row').data('value');

                                    self.showLoading(SELECTORS.ACTIVEISSUES);
                                    self.renderChatPage(id);
                                }
                            });
                            // Filter.
                            $("#local-redmine-activeissues-block select.custom-select").on('change', function (e) {
                                active_params.filter = $(this).find('option:selected').data('value');
                                active_params.page = 1;
                                self.renderIssuesActiveBlock();
                              })

                            self.hideLoading(SELECTORS.ACTIVEISSUES);
                        });
                },
                fail: Notification.exception
            }]);
        },

        renderIssuesHistoryBlock: function() {
            let self = this;
            this.showLoading(SELECTORS.HISTORYISSUES);
            console.debug(history_params)
            Ajax.call([{
                methodname: 'local_redmine_get_history_issues',
                args: history_params,
                done: function(response) {

                    let data = JSON.parse(response);
                    Templates.render('local_redmine/issues/history', data)
                        .done(function(html, js) {
                            Templates.replaceNodeContents('#local-redmine-issues-history', html, js);

                            // Events on history block.
                            $("#local-redmine-history-block").on("click", function(e) {

                                // Paging.
                                if ($(e.target).data('area') === 'paging') {
                                    if ($(e.target).data('value') !== 'next' && $(e.target).data('value') !== 'previus') {
                                        history_params.page = $(e.target).data('value');
                                    } else {
                                        let active_page = $(e.target).parent().parent().find('.active a').data('value');

                                        if ($(e.target).data('value') === 'next') {
                                            history_params.page = active_page + 1;
                                        }

                                        if ($(e.target).data('value') === 'previus') {
                                            history_params.page = active_page - 1;
                                        }
                                    }

                                    self.renderIssuesHistoryBlock();
                                }

                                // Sorting.
                                if ($(e.target).data('area') === 'sorting') {
                                    history_params.sort_col = $(e.target).data('column');

                                    let direction = '';
                                    if ($(e.target).data('value').length === 0 || $(e.target).data('value') === 'asc') {
                                        direction = 'desc';
                                    }

                                    if ($(e.target).data('value') === 'desc') {
                                        direction = 'asc';
                                    }

                                    history_params.sort_col = $(e.target).data('column');
                                    history_params.sort_dir = direction;

                                    history_params.page = 1;
                                    self.renderIssuesHistoryBlock();
                                }

                                // Get single issue page.
                                if ($(e.target).closest('.appeals-history-table-row').data('area') === 'edit_page') {
                                    let id = $(e.target).data('value') || $(e.target).closest('.appeals-history-table-row').data('value');

                                    self.showLoading(SELECTORS.ACTIVEISSUES);
                                    self.renderChatPage(id);
                                }
                            });
                            
                            // Filter.
                            $("#local-redmine-history-block select.custom-select").on('change', function (e) {
                                history_params.filter = $(this).find('option:selected').data('value');
                                history_params.page = 1;
                                self.renderIssuesHistoryBlock();
                              })
                            self.hideLoading(SELECTORS.HISTORYISSUES);
                        });
                },
                fail: Notification.exception
            }]);
        },

        renderChatPage: function(id) {
            let self = this;

            Ajax.call([{
                methodname: 'local_redmine_get_chat_page',
                args: {
                    id: id,
                },
                done: function(response) {
                    let data = JSON.parse(response);

                    self.buildHistoryUrl(id);

                    Templates.render('local_redmine/chat/view', data)
                        .done(function(html, js) {
                            Templates.replaceNodeContents('#local-redmine-issues-page', html, js);

                            // Event back page.
                            $("#local-redmine-messages-page").on("click", function(e) {
                                if ($(e.target).data('action') === 'back_button') {
                                    self.renderIssuesPage();
                                    self.buildHistoryUrl(0);
                                }
                            })

                            Chat.init(id);
                        });
                },
                fail: Notification.exception
            }]);
        },

        showLoading: function(parent){
            $('.messages-loading-wrapper').show();
        },

        hideLoading: function(parent){
            $('.messages-loading-wrapper').hide();
        },

        buildHistoryUrl: function (issueid) {

            // Build url.
            if(issueid === 0){
                window.history.replaceState({}, document.title, window.location.pathname);
            }

            if(issueid !== 0){
                let url = '?id=' + issueid;
                window.history.pushState("", "", url);
            }

            return true;
        },
    };
});
