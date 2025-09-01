/* eslint-disable no-undef */
/* eslint-disable no-implicit-globals */
/* eslint-disable no-unused-vars */
/* eslint-disable max-len */
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Javascript main event handler.
 *
 * @module     community_comments/init
 * @package    community_comments
 * @copyright  2019 Devlionco <info@devlion.co>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @since      3.6
 */

define([
    'jquery',
    'core/str',
    'core/ajax',
    'core/notification'
], function ($, str, Ajax, Notification) {

    str.get_strings([
        {key: 'opencomment', component: 'community_comments'},
        {key: 'closecomment', component: 'community_comments'},
    ]).done(function () {
    });

    return {
        init: function (activityid) {

            let pathname = window.location.pathname;

            if (pathname.toLowerCase().indexOf("quiz/attempt.php") == -1 &&
                pathname.toLowerCase().indexOf("questionnaire/preview.php") == -1 ){

                return;
            }

            this.render(activityid, 0);
        },

        scripts: function () {
            var self = this;

            // Save comment.
            $('.comment__send').bind('click', function () {
                var comment = $('#community_textarea_comment').val();
                var activityid = $('#community_activityid_comment').val();
                var sort = $('#community_sort_comment').val();

                self.save_comment(activityid, comment, function(){
                    self.render(activityid, sort);
                });
            });

            // Change sort.
            $( '#community_sort_comment' ).change(function() {
                var activityid = $('#community_activityid_comment').val();
                var sort = $('#community_sort_comment').val();

                self.render(activityid, sort);
            });

            // Open/close comment.
            $('.comment__close').on('click', function(){

                $(this).toggleClass('comment__open');
                if($(this).hasClass('comment__open')) {
                    $(this).parent().prev().find('.comment__short').addClass('display__none');
                    $(this).parent().prev().find('.comment__long').removeClass('display__none');
                    $(this).parent().prev().removeClass('comment__body--short');
                    $(this).text(M.util.get_string('closecomment', 'community_comments'));
                    $(this).addClass('comment__icon-open');
                }else {
                    $(this).parent().prev().find('.comment__short').removeClass('display__none');
                    $(this).parent().prev().find('.comment__long').addClass('display__none');
                    $(this).parent().prev().addClass('comment__body--short');
                    $(this).text(M.util.get_string('opencomment', 'community_comments'));
                    $(this).removeClass('comment__icon-open');
                }

            });
        },

        render: function (activityid, sort) {
            var self = this;

            var data = {
                activityid: Number(activityid),
                sort: Number(sort),
            };

            Ajax.call([{
                methodname: 'community_comments_render_block',
                args: data,
                done: function (response) {

                    // Remove old block.
                    if( $('#insert_community_comments').length ){
                        $('#insert_community_comments').remove();
                    }

                    $.when( $('.activity-navigation').first().before(response.content) ).then(function() {
                        self.scripts();
                    });

                },
                fail: Notification.exception
            }]);
        },

        save_comment: function (activityid, comment, callback) {
            var data = {
                activityid: Number(activityid),
                comment: comment,
            };

            Ajax.call([{
                methodname: 'community_comments_save_comment',
                args: data,
                done: function () {
                    callback();
                },
                fail: Notification.exception
            }]);
        },

    };
});