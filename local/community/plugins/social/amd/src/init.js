define([
    'jquery',
    'core/str',
    'community_social/ajax',
    'community_social/render',
    'community_social/loadingSpinner',
    'community_social/inview',
    'community_social/modal',
    'community_social/lazyLoad',
], function ($, str, ajax, render, loading, inView, SocialModal, lazyLoad) {
    `use strict`;

    str.get_strings([
        {key: 'disablingsocialarea', component: 'community_social'},
        {key: 'choosingpubliccourses', component: 'community_social'},
        {key: 'editingtheschool', component: 'community_social'},
        {key: 'close', component: 'community_social'},
        {key: 'errormessage', component: 'community_social'},
        {key: 'cancel', component: 'community_social'},
        {key: 'excludeteacher', component: 'community_social'},
        {key: 'warning', component: 'community_social'},
        {key: 'removepeerteacher', component: 'community_social'},
        {key: 'ok', component: 'community_social'}
    ]).done(function () {
    });

    const mainBlock = document.querySelector(`#region-main .social`);

    const follow_teacher = (page_userid, current_userid, follow_enable, callback) => {
        loading.show();

        ajax.data = {
            metod: 'community_social_follow_teacher',
            page_userid: page_userid,
            current_userid: current_userid,
            follow_enable: follow_enable
        };

        ajax.run(function () {
            callback();
        });
    };

    const follow_teacher_by_userid = (custom_userid, page_userid, current_userid, callback) => {
        ajax.data = {
            metod: 'community_social_change_follow_teacher_by_user',
            page_userid: page_userid,
            current_userid: current_userid,
            custom_userid: custom_userid
        };

        ajax.run(function () {
            callback();
        });
    };

    const teacher_tab = (teacher_tab, search) => {
        let target_block = `#teachers_card_block`;

        ajax.data = {
            metod: 'community_social_render_teacher_block',
            teacher_tab: teacher_tab,
            target_block: target_block,
            search: search
        };

        ajax.setHTML(SocialModal.initHandler);
    };

    const createPill = (query, callback) => {

        query = query.trim();

        if (query.length === 0) {
            return;
        }

        str.get_strings([
            { key: 'removeallpills', component: 'community_oer' },
        ]).done(function (strings) {

            $('#profile_search').val('');

            let pillTarget = $('.checked-option-pills');

            // Add remove all.
            if (!pillTarget.find('button').length) {
                let pillremoveall = $(`
                    <button type="button" data-handler="removeallpills" class="removeallpills btn btn-secondary mr-3 py-1 px-3 mb-2">
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
                <div class="checked-option-pill rounded-pill bg-light text-primary border border-primary mr-3 py-1 px-3 mb-2" data-id="${query}">
                    <span class="checked-option-pill-name">${query}</span>
                    <button type="button" class="close ml-3" aria-label="Close"  data-handler="removepill">
                        <span aria-hidden="true" data-area="pillsearch" data-value="${query}">&times;</span>
                    </button>
                </div>`);

                pillTarget.append(pill);
            } else {
                query.split(' ').forEach(function(value) {
                    if (value.length > 0) {
                        let pill = $(`
                                <div class="checked-option-pill rounded-pill bg-light text-primary border border-primary mr-3 py-1 px-3 mb-2" data-id="${value}">
                                    <span class="checked-option-pill-name">${value}</span>
                                    <button type="button" class="close ml-3" aria-label="Close"  data-handler="removepill">
                                        <span aria-hidden="true" data-area="pillsearch" data-value="${value}">&times;</span>
                                    </button>
                                </div>`);

                        pillTarget.append(pill);
                    }
                });
            }

            callback();
        });
    }

    const removePillSearch = (object, callback) =>  {
        $(object).parent().remove();

        // Remove button remove all pills.
        let buttons = $('.checked-option-pills').find('button');
        if (buttons.length === 1) {
            buttons.remove();
        }

        callback();
    }

    const removeAllPillSearch = (object, callback) =>  {
        let elementssearch = $("*").filter(function () {
            return ($(this).data("area") === 'pillsearch');
        });

        $(elementssearch).each(function () {
            removePillSearch($(this).parent(), function (){

            });
        });

        callback();
    }

    return {
        init: function (page_userid, current_userid) {

            // Add tooltip to page.
            mainBlock.addEventListener(`click`, function (event) {
                let target = event.target;
                while (target !== mainBlock) {

                    // School settings popup.
                    if (target.dataset.handler === `school-settings`) {
                        SocialModal.school_settings(page_userid);
                        return;
                    }

                    // Show courses pombim popup.
                    if (target.dataset.handler === `courses_pombim`) {
                        SocialModal.courses_pombim(page_userid);
                        return;
                    }

                    // Migrate courses pombim popup.
                    if (target.dataset.handler === `migrate_courses_pombim`) {
                        SocialModal.migrate_courses_pombim(page_userid);
                        return;
                    }

                    // Disable social area popup.
                    if (target.dataset.handler === `social_disable`) {
                        SocialModal.social_disable();
                        return;
                    }

                    // View followers list popup.
                    if (target.dataset.handler === `user-collegues-list`) {
                        SocialModal.user_collegues_list(page_userid, current_userid);
                        return;
                    }

                    // View followers list popup.
                    if (target.dataset.handler === `user-follower-list`) {
                        SocialModal.user_follower_list(page_userid, current_userid);
                        return;
                    }

                    // Request to followed courses popup. Not used.
                    if (target.dataset.handler === `request-followed-courses`) {
                        SocialModal.request_followed_courses(page_userid, current_userid);
                        return;
                    }

                    // Request to removing teacher from course popup.  Not used.
                    if (target.dataset.handler === `remove-teacher-request`) {
                        let courseid = target.dataset.courseid;
                        let userid = target.dataset.userid;
                        SocialModal.remove_teacher_request(userid, courseid);
                        return;
                    }

                    // Follow another teacher.  Not used.
                    if (target.dataset.handler === `follow`) {
                        let follow_enable = target.dataset.follow_enable;
                        follow_teacher(page_userid, current_userid, follow_enable, function () {
                            render.userData(page_userid);
                        });
                        return;
                    }

                    // Follow another teacher by user id.  Not used.
                    if (target.dataset.handler === `follow-user`) {
                        let custom_userid = target.dataset.custom_userid;
                        follow_teacher_by_userid(custom_userid, page_userid, current_userid, function () {
                            render.userData(page_userid);
                            SocialModal.user_follower_list(page_userid, current_userid);
                        });
                        return;
                    }

                    // Teacher tab + seatch tab.
                    if (target.dataset.handler === `teacher_tab` || target.dataset.handler === `search_teacher`) {
                        let tab_id = target.dataset.tab_id;
                        let search = $('#search_teachers').val();
                        teacher_tab(tab_id, search);
                        return;
                    }

                    if (target.dataset.handler === `search_profile`) {
                        let search = $('#profile_search').val();

                        createPill(search, function (){

                            let arrsearch = [];
                            $(".checked-option-pill").each(function(index) {
                                arrsearch.push($(this).data('id'));
                            });

                            render.searchInProfile(page_userid, arrsearch);
                        });

                        return;
                    }

                    if (target.dataset.handler === `removepill`) {
                        removePillSearch(target, function (){

                            let arrsearch = [];
                            $(".checked-option-pill").each(function(index) {
                                arrsearch.push($(this).data('id'));
                            });

                            render.searchInProfile(page_userid, arrsearch);
                        });

                        return;
                    }

                    if (target.dataset.handler === `removeallpills`) {
                        removeAllPillSearch(target, function (){

                            let arrsearch = [];
                            render.searchInProfile(page_userid, arrsearch);
                        });

                        return;
                    }

                    // SlideIn slideOut teachers tag on the course card.
                    if (target.dataset.handler === `slideCourseCard`) {
                        $(target).toggleClass('tc-rotate');
                        let scrollHeight = $(target).hasClass('tc-rotate') ? $(target).parents('.course').prop('scrollHeight') : 195;
                        $(target).parents('.course').css({'height': scrollHeight + 'px'});
                        return;
                    }

                    // SlideIn slideOut courses on the teachers card.
                    if (target.dataset.handler === `slideTeacherCard`) {
                        $(target).toggleClass('tc-rotate');
                        let scrollHeight = $(target).hasClass('tc-rotate') ? $(target).parents('.tc').prop('scrollHeight') : 140;
                        $(target).parents('.tc').css({'height': scrollHeight + 'px'});
                        return;
                    }

                    target = target.parentNode;
                }

            });

            window.onresize = function (event) {
                SocialModal.initHandler();
            };

            mainBlock.addEventListener('keydown', function (event) {
                if (event.keyCode === 13 && event.target.id === `search_teachers`) {
                    let tab_id = event.target.nextElementSibling.dataset.tab_id;
                    let search = event.target.value;
                    teacher_tab(tab_id, search);
                }

                if (event.keyCode === 13 && event.target.id === `profile_search`) {
                    let search = event.target.value;

                    createPill(search, function (){

                        let arrsearch = [];
                        $(".checked-option-pill").each(function(index) {
                            arrsearch.push($(this).data('id'));
                        });

                        render.searchInProfile(page_userid, arrsearch);
                    });
                }
            });

        }
    };
});
