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
 * @module     community_oer/init
 * @package    community_oer
 * @copyright  2019 Devlionco <info@devlion.co>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @since      3.6
 */

define([
  'jquery',
  'community_oer/review-events',
  'community_oer/review-modal',
], function($, events, modal) {

    var root = document.querySelector('body');

    return {
        init: function(popupdata) {

            modal.insertWrapper().done(function() {

                // Old version.
                if (popupdata.showrequest) {
                    events.askForReview(popupdata);
                }

                // New version v2.
                if (popupdata.showrequestv2) {
                    events.askForReviewV2(popupdata);
                }

                $(document).on('click', '[data-dismiss="modal"]', function(){
                     $('#modalReview').modal('hide');
                });

                $(document).on('click', '#activityremindtext a', function(){
                    window.location.href = $(this).attr('href');
                });

                root.addEventListener('click', function(e) {
                    var target = e.target;

                    // Activity remind event for activity menu.
                    if (target.role === 'menuitem') {
                        let key = $(target).parent().data('key');

                        if (key !== undefined && key.includes('activityRemind_')) {
                            target.dataset.cmid = key.replace('activityRemind_', "");
                            events.activityRemind(target);
                        }
                    }

                    while (root.contains(target)) {

                        switch(target.dataset.handler) {

                            // New version of review.
                            case 'sendShortReview':
                                events.sendReview(target);
                                break;
                            case 'confirmRejectReview':
                                events.confirmRejectReview(target);
                                break;
                            case 'reportBug':
                                events.reportBug(target);
                                break;

                            // Old version of review.
                            case 'sendReviewLater':
                                events.sendReviewLater(target);
                                break;
                            case 'openSendReview':
                                events.openSendReview(target);
                                break;
                            case 'sendReview':
                                events.sendReview(target);
                                break;


                            // Course view
                            case 'activityRemind':
                                events.activityRemind(target);
                                break;
                            case 'sendRemind':
                                events.sendRemind(target);
                                break;
                            case 'saveRemind':
                                events.saveRemind(target);
                                break;

                            case 'deleteReview':
                                events.deleteReview(target);
                                break;
                            case 'rejectReview':
                                events.rejectReview(target);
                                break;
                            case 'showReview':
                                events.showReview(target);
                                break;
                            case 'sendComment':
                                events.sendComment(target);
                                break;
                            case 'updateComment':
                                events.updateComment(target);
                                break;
                            case 'editComment':
                                events.editComment(target);
                                break;
                            case 'deleteComment':
                                events.deleteComment(target);
                                break;
                            case 'cancelComment':
                                events.cancelComment(target);
                                break;
                            case 'askForReviewOnCourse':
                                events.askForReviewOnCourse(target);
                                break;
                        }
                        target = target.parentNode;
                    }
                });
            });
        },

        add_icon: function(icon, callback) {
            $('.page-header-headings').find('h1').append(' '+ icon).ready(function () {
                callback()
            });
        },

        add_icon_to_page: function(icon) {
            this.add_icon(icon, function(){
            })
        },

        add_and_open_icon_on_page: function(icon) {
            this.add_icon(icon, function(){
                setTimeout(function(){
                    $('.page-header-headings').parent().find('.comment-icon')[0].click();
                }, 1000);
            })
        },
    };
});
