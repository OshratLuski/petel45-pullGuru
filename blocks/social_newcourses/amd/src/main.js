/**
 * Javascript to initialise the myoverview block.
 *
 * @package    block_social_newcourses
 * @copyright  2018 Bas Brands <bas@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define([
        'jquery',
        'core/ajax',
        'core/notification',
        'local_petel/inview'
    ],
    function ($, Ajax, Notification, inView) {
        `use strict`;

        var selector = {
            wrapper: '#block-social_newcourses-selectSort',
            mainBlock: '.block-social_newcourses-content-courses',
            selectItems: '#block-social_newcourses-selectSort .dropdown-item',
            spinner: '#block-social_newcourses-selectSort .spinner-border',
            button: '.social_newcourses-btn',
            page: '#block-social_newcourses-page',
        };

        const toggleSpinner = (status) => {
            var spinner = $(selector.spinner);
            var borderColor = status ? '' : 'transparent';
            spinner.css('border-color', borderColor);
        };

        const renderBlock = (perpage) => {

            $(selector.page).val(perpage);
            toggleSpinner(true);

            Ajax.call([{
                methodname: 'block_social_newcourses_render_courses_block',
                args: {
                    perpage: perpage
                },
                done: function (response) {
                    toggleSpinner(false);
                    if (response.status) {
                        $(selector.mainBlock).html(response.content);
                        $(selector.button).on('click', btnClassClick);
                    }
                },
                fail: Notification.exception
            }]);
        };

        const btnClassClick = (event) => {

            // handle public course and send single course
            if (event.target.dataset.handler === `send_followed_single_course`) {
                var ids = [];
                let courseid = event.target.dataset.courseid;
                ids.push(courseid);

                var page_userid = event.target.dataset.page_userid;
                var current_userid = event.target.dataset.current_userid;

                toggleSpinner(true);
                send_followed_courses(page_userid, current_userid, ids, function () {
                    var page = $(selector.page).val();
                    renderBlock(page);
                    toggleSpinner(false);
                });

                return;
            }
        };

        const send_followed_courses = (page_userid, current_userid, ids, callback) => {
            Ajax.call([{
                methodname: 'community_social_send_followed_courses',
                args: {
                    page_userid: Number(page_userid),
                    current_userid: Number(current_userid),
                    ids: JSON.stringify(ids)
                },
                done: function () {
                    callback();
                },
                fail: Notification.exception
            }]);
        };

        return {

            init: function () {

                var root = $(selector.wrapper);

                // First run.
                inView('.block-social-newcourses-inview')
                    .on('enter', function (e){
                        if(!$(e).hasClass('inview-done')){
                            $(e).addClass('inview-done');

                            var perpage = $('#block-social_newcourses-selectSort .dropdown-menu .active').data('value');
                            renderBlock(perpage);
                        }
                    })
                    .on('exit', el => {
                    });

                root.on('click', function (e) {
                    var target = $(e.target);
                    while ($.contains(root[0], target[0])) {
                        if (target.data('handler') === 'setPerpage') {

                            $(selector.selectItems).removeClass('active');
                            $(target).addClass('active');

                            renderBlock(target.data('value'));
                            return;
                        }

                        target = target.parent();
                    }
                });

                $(selector.button).on('click', btnClassClick);
            }
        };
    });
