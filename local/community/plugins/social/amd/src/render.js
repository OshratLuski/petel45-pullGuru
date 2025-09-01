define([
    'core/yui',
    'community_social/loadingSpinner',
    'core/ajax',
    'core/templates',
    'core/notification'
], function (Y, loading, Ajax, Templates, Notification) {
    `use strict`;

    const social = document.querySelector(`#region-main .social`);

    let render = {

        // Block Aside User Data.
        userData: function (userid) {
            loading.show();

            const userData = social.querySelector(`.user`);

            Ajax.call([{
                methodname: 'community_social_render_block_user_data',
                args: {
                    userid: userid
                },
                done: function (response) {

                    let data = JSON.parse(response.data);
                    Templates.render('community_social/profile/aside-user-data', data)
                        .done(function (html, js) {
                            Templates.replaceNodeContents(userData, html, js);
                        });

                    loading.remove();
                },
                fail: function (response) {
                    loading.remove();
                    Notification.exception(response);
                },
            }]);

        },

        // Block Aside Courses Pombim.
        asideCoursesPombim: function (userid) {
            loading.show();

            const coursesPombim = social.querySelector(`.public-course`);

            Ajax.call([{
                methodname: 'community_social_render_block_aside_courses_pombim',
                args: {
                    userid: userid
                },
                done: function (response) {

                    let data = JSON.parse(response.data);
                    Templates.render('community_social/profile/aside-public-course', data)
                        .done(function (html, js) {
                            Templates.replaceNodeContents(coursesPombim, html, js);
                        });

                    loading.remove();
                },
                fail: function (response) {
                    loading.remove();
                    Notification.exception(response);
                },
            }]);

        },

        // Block Courses Pombim and Oer.
        coursesBlock: function (userid, search, callback) {
            loading.show();
            const coursesBloack = social.querySelector(`.courses__wrapper`);

            Ajax.call([{
                methodname: 'community_social_render_profile_blocks',
                args: {
                    userid: userid,
                    search: search
                },
                done: function (response) {

                    let data = JSON.parse(response.data);
                    Templates.render('community_social/profile/courses', data)
                        .done(function (html, js) {
                            Templates.replaceNodeContents(coursesBloack, html, js);
                        });

                    loading.remove();
                    callback();
                },
                fail: function (response) {
                    loading.remove();
                    Notification.exception(response);
                },
            }]);
        },

        // Blocks on search.
        searchInProfile: function (userid, search) {
            loading.show();

            Ajax.call([{
                methodname: 'community_social_render_profile_blocks',
                args: {
                    userid: userid,
                    search: JSON.stringify(search)
                },
                done: function (response) {

                    let data = JSON.parse(response.data);

                    // Courses pombim and oer.
                    const coursesBloack = social.querySelector(`.courses__wrapper`);
                    Templates.render('community_social/profile/courses', data)
                        .done(function (html, js) {
                            Templates.replaceNodeContents(coursesBloack, html, js);
                        });

                    // Activities oercatalog.
                    const activitiesOer = social.querySelector(`.activities_oer__wrapper`);
                    Templates.render('community_social/profile/activities-oer', data)
                        .done(function (html, js) {
                            Templates.replaceNodeContents(activitiesOer, html, js);
                        });

                    loading.remove();
                },
                fail: function (response) {
                    loading.remove();
                    Notification.exception(response);
                },
            }]);

        }
    };

    return render;
});
