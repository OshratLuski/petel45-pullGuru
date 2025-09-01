/* eslint-disable no-unused-vars */
define([
    'jquery',
    'core/str',
    'core/ajax',
    'core/notification',
    'community_social/inview',
], function ($, Str, Ajax, Notification, inView) {

    const lazyLoadIconStart = () => {

        Str.get_strings([
            { key: 'waitforloading', component: 'community_social' },
        ]).done(function (strings) {
            const alertWidth = $('#teachers_card_block').outerWidth() + 18;
            const alertText = strings[0];
            const loadingAlert = `<div id="loadingalert" class="alert alert-secondarymedium text-primary text-center p-3 w-100 mb-0"
                                    role="alert"
                                    style="max-width: ${alertWidth}px">
                                    <i class="fas fa-sync fa-spin mr-2"></i>
                                    ${alertText}
                                 </div>`;
            $('.main').append(loadingAlert);
        });
    };

    const lazyLoadIconStop = () => {
        $('#loadingalert').remove();
    };

    const lazyLoad = (object, callback) => {
        setTimeout(() => {
            inView(".lazyload").on("enter", function(e) {

                if (!$(e).hasClass('done')) {

                    lazyLoadIconStart();

                    Ajax.call([{
                        methodname: 'community_social_lazy_load',
                        args: {
                            teacher_tab: $('#social_teacher_tabid').val(),
                            search: $('#search_teachers').val(),
                            loaded_cards: $('#social_teacher_loaded_users_onpage').val()
                        },
                        done: function (response) {

                            if (response.content.length !== 0) {
                                $('.lazyload ').remove();

                                $('#social_teacher_loaded_users_onpage').val(response.loaded_cards);
                                $('.teacher-cards').append(response.content).append(`<div class="lazyload"></div>`);

                                lazyLoad();
                            }

                            lazyLoadIconStop();
                        },
                        fail: function (response) {
                            lazyLoadIconStop();
                            Notification.exception(response);
                        },
                    }]);

                    $(e).addClass('done');
                }
            });
        }, 500);
    };

    return {
        init: function (maxuserspage) {
            $('#social_teacher_loaded_users_onpage').val(maxuserspage);
            lazyLoad();
        },

        load: function () {
            lazyLoad();
        },
    };
});
