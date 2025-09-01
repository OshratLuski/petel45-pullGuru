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
 * Javascript controller for the "Actions" panel at the bottom of the page.
 *
 * @module     community_oer/events
 * @package    community_oer
 * @copyright  2019 Devlionco <info@devlion.co>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @since      3.6
 */

 define([
   'jquery',
   'core/str',
   'core/ajax',
   'community_oer/review-modal',
   'core/notification',
   'local_redmine/support',
   'community_oer/main',
 ], function($, Str, Ajax, modal, Notification, support, main) {

     return /** @alias module:community_oer/events */ {

        ICON: {
          spinner: 'circle-loading',
          component: 'community_oer'
        },

        askForReview: function(popupdata) {
          var template = modal.TEMPLATE.main.src;
          var strings = {
            userName: popupdata.firstname || '',
            activityUrl: popupdata.url || '',
            activityName: popupdata.mod_name || ''
          };
          var context = {
              body: M.util.get_string('askforfeedback', 'community_oer', strings),
              activityId: popupdata.metadata_id,
              requestId: popupdata.requestid,
              rejectReview: popupdata.rejectReview,
              activityImg: popupdata.image,
              activityName: popupdata.mod_name,
              activityUrl: popupdata.url,
              allowed: popupdata.allowed,
              limiteduser: popupdata.limiteduser,
              reviewsnumber: popupdata.reviewsnumber,
              reviewsnumberleft: popupdata.reviewsnumberleft,
              reviewspc: popupdata.reviewspc,
              showprogress: popupdata.showprogress,
              progressitems: popupdata.progressitems
          };
          modal.render(template, context).done(modal.triggerBtn.click());
        },

         askForReviewV2: function(popupdata) {
             var template = modal.TEMPLATE.main_v2.src;
             var param1 = {
                 userName: popupdata.firstname || '',
                 activityUrl: popupdata.url || '',
                 activityName: popupdata.mod_name || ''
             };
             var param2 = {
                 userName: popupdata.username || ''
             };
             var param3 = {
                 activityName: popupdata.mod_name || '',
                 activityUrl: popupdata.url || ''
             };

             Str.get_strings([
                 { key: 'askforfeedback', component: 'community_oer', param: param1 },
                 { key: 'reviewsheadertext1', component: 'community_oer', param: param2 },
                 { key: 'reviewsheadertext2', component: 'community_oer', param: param3 },
             ]).done(function (strings) {

                 var context = {
                     body: strings[0],
                     activityId: popupdata.metadata_id,
                     requestId: popupdata.requestid,
                     rejectReview: popupdata.rejectReview,
                     activityImg: popupdata.image,
                     activityName: popupdata.mod_name,
                     activityUrl: popupdata.url,
                     allowed: popupdata.allowed,
                     limiteduser: popupdata.limiteduser,
                     reviewsnumber: popupdata.reviewsnumber,
                     reviewsnumberleft: popupdata.reviewsnumberleft,
                     reviewspc: popupdata.reviewspc,
                     showprogress: popupdata.showprogress,
                     progressitems: popupdata.progressitems,
                     reviewsheadertext1: strings[1],
                     reviewsheadertext2: strings[2],
                     reviewsquestiontext: popupdata.reviewsquestiontext,
                     reviewstextarea: popupdata.reviewstextarea,
                 };
                 modal.render(template, context).done(modal.triggerBtn.click());
             })
         },

         confirmRejectReview: function(target) {
             var template = modal.TEMPLATE.confirmReject.src;
             var context = {
                     requestId: target.dataset.requestid,
                     activityId: target.dataset.activityid,
                     activityName: target.dataset.activityname,
                 };
             modal.render(template, context);
         },

         reportBug: function(target) {
             support.supportPopup(4);
             return false;
         },

         askForReviewOnCourse: function(target) {
             var courseid = $(target).data('courseid'),
                 self = this;
             Ajax.call([{
                 methodname: 'community_oer_show_review_popup',
                 args: {
                     courseid: Number(courseid),
                 },
                 done: (function(response) {
                     var popupdata = JSON.parse(response);
                     self.askForReviewV2(popupdata);
                 }),
                 fail: Notification.exception
             }]);
         },

        sendReviewLater: function(target) {
          var requestid = target.dataset.requestid;
          var activityid = target.dataset.activityid;
          Ajax.call([{
                methodname: 'community_oer_send_review_later',
            args: {
                activityid: Number(activityid),
                requestid: Number(requestid)
            },
            done: {},
            fail: Notification.exception
          }]);
        },

        rejectReview: function(target) {
          var requestId = target.dataset.requestid,
              self = this;
          Ajax.call([{
            methodname: 'community_oer_reject_review',
            args: {
                requestid: Number(requestId),
                courseid: Number(this.getCurrentCourse())
            },
            done: (function(response){
                self.refreshReviewButton(response);
            }),
            fail: Notification.exception
          }]);
        },

        openSendReview: function(target) {
          var activityid = target.dataset.activityid,
              requestid = target.dataset.requestid,
              activityname = target.dataset.activityname,
              activityurl = target.dataset.activityurl,
              activityimg = target.dataset.activityimg,
              template = modal.TEMPLATE.form.src,
              context = {
                title: 'form',
                activityid: activityid,
                requestid: requestid,
                activityname: activityname,
                activityimg: activityimg,
                activityurl: activityurl
              };
          modal.render(template, context);
        },

        sendReview: function(target) {

            // Hide error.
            $('.review_description_error').hide();

            var required = 0,
                self = this,
                topelement = $(target).parents('.review-unit');

                topelement.find('.description_text').each(function() {
                    var requiredfield = $(this);
                    var requiredcontent = requiredfield.val();
                    if (!requiredcontent.trim().length) {
                        Str.get_string('requiredfield', 'community_oer')
                            .done(function(placeholder) {
                                requiredfield.addClass('required').attr("placeholder", placeholder);
                            });
                        required++;
                    }
                });

            // If error.
            if (required) {
                $('.review_description_error').show();
                return;
            }
            var reviewform = (target.dataset.handler === 'sendShortReview') ? 'short' : 'long',
                objid = target.dataset.objid,
                objtype = target.dataset.objtype,
                requestid = target.dataset.requestid,
                sesskey = M.cfg.sesskey;

            var activityby = $('#reviewForm input[name="activity_by"]:checked').val();
            var classroom = $('#reviewForm input[name="classroom"]:checked').val();
            var home = $('#reviewForm input[name="home"]:checked').val();
            var phone = $('#reviewForm input[name="phone"]:checked').val();
            var pc = $('#reviewForm input[name="pc"]:checked').val();
            var descr1 = $('#reviewForm #descr1').val();
            var descr2 = $('#reviewForm #descr2').val();
            var descr3 = $('#reviewForm #descr3').val();

            let errorreporting = '';
            let issuedescr = '';
            let customRange3 = '';
            if (reviewform === 'long') {
                errorreporting = $('#reviewForm input[name="error_reporting"]:checked').val();
                issuedescr = $('#reviewForm #issue_descr').val();
                customRange3 = $('#reviewForm #customRange3').val();
            } else {
                customRange3 = topelement.find('input[name="shortReviewFormRadios"]:checked').val();
                var question1 = '';
                if (customRange3 === undefined) {
                    customRange3 = 20;
                } else {
                    var rid = topelement.find('input[name="shortReviewFormRadios"]:checked').attr('id');
                    question1 = $("label[for='" + rid + "']").text();
                }
            }
            var descr = topelement.find('.short-review-form-description').val();

            var fd = new FormData();
            var f = 1;
            $('.screenshots').each(function() {
                var ff = $(this);
                fd.append('img' + f, ff.prop('files')[0]);
                f++;
            });
            fd.append('sesskey', sesskey);
            fd.append('objid', objid);
            fd.append('reviewtype', objtype);
            fd.append('courseid', this.getCurrentCourse());
            fd.append('requestid', requestid);
            fd.append('recommendation', customRange3);
            fd.append('reviewdata', JSON.stringify({
                        activityby: activityby,
                        classroom: classroom,
                        home: home,
                        phone: phone,
                        pc: pc,
                        descr1: descr1,
                        descr2: descr2,
                        descr3: descr3,
                        descr: descr,
                        question1: question1
                    }));
            fd.append('errorreporting', errorreporting);
            fd.append('issuedescr', issuedescr);

            $.ajax({
                url: M.cfg.wwwroot + '/local/community/plugins/oer/ajax/ajax-send-review.php',
                type: "post",
                data: fd,
                processData: false,
                contentType: false,
            })
            .done(function(response) {
                var data = JSON.parse(response);
                if (data.result) {
                    let template = modal.TEMPLATE.approve.src,
                        context = {
                        };

                    modal.render(template, context);
                    self.refreshReviewButton(response);

                    // Render main oer block.
                    var url = window.location.pathname;
                    if(url.includes('/local/community/plugins/oer/')){
                        main.reRerenderMain();
                    }

                    if(url.includes('/mod/quiz/edit.php')){
                        let question = $('*[data-type="question"][data-objid="'+objid+'"]');
                        question.find('span').text(data.objcountreviews);
                    }

                } else {
                  Notification.exception();
                }
            })
            .fail({
                fail: Notification.exception
            });
          },

         activityRemind: function(target) {
             var activityId = target.dataset.cmid;

             Ajax.call([{
                 methodname: 'community_oer_open_popup_remind',
                 args: {
                     activityid: Number(activityId),
                 },
                 done: function(response) {
                     let data = JSON.parse(response);
                     if(data.result){

                         let strings = {
                             name: data.activityName,
                             date: data.date,
                             url: data.url,
                         };

                         Str.get_string('remindtext', 'community_oer', strings)
                             .done(function(string) {
                                 var template = modal.TEMPLATE.activity_remind.src,
                                     context = {
                                         'remindText': string,
                                         'activityId': data.activityId,
                                         'activityName': data.activityName
                                     };
                                 modal.render(template, context);
                             });
                     }
                 },
                 fail: Notification.exception
             }]);
         },

         sendRemind: function(target) {
             var remindtext = $('#activityremindtext').html();
             var activityId = target.dataset.activityid;
             var required = 0;
             var template = modal.TEMPLATE.approve2.src;
             if (!remindtext.trim().replace(/<br>/g, "").replace(/<br \/>/g, "").length) {
                 Str.get_string('requiredfield', 'community_oer')
                     .done(function(placeholder) {
                         $('#activityremindtext').addClass('required').focus();
                         $('#activityremindtexterror').html(placeholder).removeClass('d-none');
                     });
                 required++;
             }
             // If error.
             if (required) {
                 return;
             }

             Ajax.call([{
                 methodname: 'community_oer_send_remind',
                 args: {
                     activityid: Number(activityId),
                     remindtext: remindtext.replace(/&/g, "&amp;").replace(/>/g, "&gt;").replace(/</g, "&lt;").replace(/"/g, "&quot;"),
                     ifsend: true
                 },
                 done: function(response) {
                     if (!response) {
                         return;
                     }

                     let data = JSON.parse(response);
                     var context = {'activityName': data.activityName};

                     modal.render(template, context);
                 },
                 fail: Notification.exception
             }]);
         },

         saveRemind: function(target) {
             var remindtext = $('#activityremindtext').html();
             var activityId = target.dataset.activityid;
             var required = 0;
             var template = modal.TEMPLATE.approve3.src;
             if (!remindtext.trim().replace(/<br>/g, "").replace(/<br \/>/g, "").length) {
                 Str.get_string('requiredfield', 'community_oer')
                     .done(function(placeholder) {
                         $('#activityremindtext').addClass('required').focus();
                         $('#activityremindtexterror').html(placeholder).removeClass('d-none');
                     });
                 required++;
             }
             // If error.
             if (required) {
                 return;
             }

             Ajax.call([{
                 methodname: 'community_oer_send_remind',
                 args: {
                     activityid: Number(activityId),
                     remindtext: remindtext.replace(/&/g, "&amp;").replace(/>/g, "&gt;").replace(/</g, "&lt;").replace(/"/g, "&quot;"),
                     ifsend: false
                 },
                 done: function(response) {
                     if (!response) {
                         return;
                     }

                     let data = JSON.parse(response);
                     var context = {'activityName': data.activityName};

                     modal.render(template, context);
                 },
                 fail: Notification.exception
             }]);
         },

         deleteReview: function(target) {
             var review = $(target).parents('.review_bigblock');
             var reviewid = review.data('reviewid');
             var objid = review.data('objid');
             Ajax.call([{
                 methodname: 'community_oer_delete_review',
                 args: {
                     reviewid: Number(reviewid)
                 },
                 done: function(response) {
                     if (response === '1') {

                         // Render main oer block.
                         var url = window.location.pathname;
                         if(url.includes('/local/community/plugins/oer/')){
                             main.reRerenderMain();
                         }

                         if(url.includes('/mod/quiz/edit.php')){
                             let question = $('*[data-type="question"][data-objid="'+objid+'"]');

                             let number = question.find('span').text();
                             question.find('span').text(number - 1);
                         }

                         review.slideUp(500, function() {
                             // Str.get_string('deleted', 'community_oer').done(function(s) {
                             //     review.html(s).slideDown(500);
                             // });
                         });
                     }
                 },
                 fail: Notification.exception
             }]);
         },

         /**
           * Open the teacher overview popup.
           *
           * @method showReview
           * @param {Node} target target element.
           */
         showReview: function(target) {
           var template = modal.TEMPLATE.response.src;

            Ajax.call([{
              methodname: 'community_oer_show_review',
              args: {
                  objid: target.dataset.objid,
                  type: target.dataset.type
              },
              done: function(response) {
                  var data = JSON.parse(response);

                  var context = {
                      header: data.header,
                      reviews: data.reviews,
                      objurl: data.objurl,
                      objlinkname: data.objlinkname,
                      objid: data.objid,
                      objtype: data.objtype,
                      objcreated: data.objcreated,
                      userfirstname: data.userfirstname,
                      userlastname: data.userlastname,
                      avatar: data.avatar,
                      author: data.author,
                      messageurl: data.messageurl,
                  };
                  modal.render(template, context).done(modal.triggerBtn.click());

              },
              fail: Notification.exception
            }]);
         },

        sendComment: function(target) {
             var self = this;
            var reviewid = target.dataset.reviewid;
            var comment = $('textarea[name="addcomment' + reviewid + '"]').val();

            var required = 0;
            if (!comment.trim().length) {
                Str.get_string('requiredfield', 'community_oer')
                    .done(function(placeholder) {
                        $('textarea[name="addcomment' + reviewid + '"]').addClass('required').attr("placeholder", placeholder).focus();
                    });
                required++;
            }
            // If error.
            if (required) {
                return;
            }

            Ajax.call([{
                methodname: 'community_oer_send_comment',
                args: {
                    reviewid: Number(reviewid),
                    comment: comment
                },
                done: function(response) {
                    if (!response) {
                        $(target).parents('[data-ref="addComment"]').prev().trigger('click');
                        return;
                    }
                    $('textarea[name="addcomment' + reviewid + '"]').val('');
                    var d = new Date(),
                        year = d.getFullYear(),
                        month = ("0" + (d.getMonth() + 1)).slice(-2),
                        day = ("0" + d.getDate()).slice(-2),
                        date = day + '.' + month + '.' + year;
                    var commentBlock = $('textarea[name="addcomment' + reviewid + '"]').parents('[data-ref="addComment"]');
                    var newComment = commentBlock.clone();
                    var controls = $('#controls').clone();
                    controls.removeAttr('id').removeClass('d-none');
                    newComment.attr('data-commentid', response);
                    newComment.attr('data-reviewid', reviewid);
                    newComment.find('.addcomment_block').removeClass('addcomment_block');
                    newComment.find('.user_info').append(' | <span>' + date + '</span>');
                    newComment.find('.user_info').append(controls);
                    newComment.find('.review_block-body').html('<p class ="review_block-body-text">' + comment + '</p>');

                    var lastCommentBlock = $('#block-comments-' + reviewid).find('.review_block-comment');

                    if(lastCommentBlock.length !== 0) {
                        while (lastCommentBlock.next().hasClass('review_block-comment')) {
                            lastCommentBlock = lastCommentBlock.next();
                        }
                        newComment.insertAfter(lastCommentBlock);
                        $(target).parents('[data-ref="addComment"]').prev().trigger('click');

                        self.updateCounterOfComments(reviewid);
                    }else{
                        var commentBlock = $('#block-comments-' + reviewid);
                        commentBlock.html(newComment);
                        $(target).parents('[data-ref="addComment"]').prev().trigger('click');

                        self.updateCounterOfComments(reviewid);
                    }
                },
                fail: Notification.exception
            }]);
          },

        editComment: function(target) {
          var commentBlock = $(target).parents('.review_block-comment');
          if (commentBlock.hasClass('editing')) {
            return;
          }

          commentBlock.addClass('editing');

          var commentText = commentBlock.find('.review_block-body-text').text();
          var newCommentBlock = $('.addcomment_block').first().clone().removeClass('addcomment_block');
          commentBlock.attr('data-comment', commentText);
          commentBlock.find('.review_block-body').replaceWith(newCommentBlock);
          commentBlock.find('textarea').val(commentText).focus();
          commentBlock.find('[data-handler="sendComment"]').attr('data-handler', 'updateComment');
          },

        cancelComment: function(target) {

          var commentBlock = $(target).parents('.review_block-comment'),
              oldcomment = commentBlock.data('comment');
          commentBlock.removeAttr('data-comment');
          commentBlock.removeClass('editing');
          if ($(target).parents('.review_block-body').hasClass('addcomment_block')) {
              $(target).parents('[data-ref="addComment"]').prev().trigger('click');
              return;
          }
          commentBlock.find('textarea').replaceWith('<p class="review_block-body-text">' + oldcomment + '</p>');
          commentBlock.find('.control').remove();
        },

        updateComment: function(target) {
            var commentBlock = $(target).parents('.review_block-comment');
            var commentid = commentBlock.data('commentid');
            var newcomment = commentBlock.find('textarea').val();
            Ajax.call([{
                methodname: 'community_oer_edit_comment',
                args: {
                    commentid: Number(commentid),
                    comment: newcomment
                },
                done: function(response) {
                    if (response === '1') {
                        commentBlock.find('textarea').replaceWith('<p class="review_block-body-text">' + newcomment + '</p>');
                        commentBlock.find('.control').remove();
                        commentBlock.removeClass('editing');
                    }
                },
                fail: Notification.exception
            }]);
          },

        deleteComment: function(target) {
             var self = this;
            var comment = $(target).parents('.review_block-comment');
            var commentid = comment.data('commentid');
            var reviewid = comment.data('reviewid');
            Ajax.call([{
                methodname: 'community_oer_delete_comment',
                args: {
                    commentid: Number(commentid)
                },
                done: function(response) {
                    if (response === '1') {
                        comment.slideUp(500, function() {

                            self.updateCounterOfComments(reviewid);

                            // Str.get_string('deleted', 'community_oer').done(function(s) {
                            //     comment.html(s).slideDown(500);
                            // });
                        });
                    }
                },
                fail: Notification.exception
            }]);
          },

         /**
          * Update review button.
          *
          * @method refreshReviewButton
          * @param {JSON} response review counter.
          */
        refreshReviewButton: function(response) {
            var data = JSON.parse(response),
                targetNode = $('#reviewOnCourse'),
                counter = $('#reviewOnCourseCounter');
            if (!data.countreview) {
                targetNode.remove();
            }else {
                counter.html(data.countreview);
            }
        },


         /**
          * Create spinner image.
          *
          * @method addSpinner
          * @param {Node} $node target element.
          * @returns {*|jQuery}.
          */
        addSpinner: function($node) {
          var spinner = $('<img/>').attr('src', M.util.image_url(this.ICON.spinner, this.ICON.component))
              .addClass('mx-auto spinner');
          $node.html('');
          $node.append(spinner);
          spinner.fadeIn().css('display', 'block');
          return spinner;
        },

         /**
          * Get current course on which the system is located.
          *
          * @method getCurrentCourse
          * @param {string} handler name of the handler.
          * @return {int} id number of the course.
          */
         getCurrentCourse: function() {
             var str = $('body').attr('class'),
                 result = str.match(/course-\d+/gi)[0].replace(/\D+/, '');
             return result;
         },

         updateCounterOfComments: function(reviewid) {
             var count = 0;
             $('#block-comments-'+ reviewid).find('.review_block-comment').each(function() {
                 if ($(this).css('display') !== 'none' && $(this).css("visibility") !== "hidden") {
                     count = count + 1;
                 }
             });

             $('#count-comments-' + reviewid).html(count);

             // Open/close btn-colapse-comments;
             if(count > 0) {
                 $('#btn-colapse-comments-' + reviewid).show();
             }else{
                 $('#btn-colapse-comments-' + reviewid).hide();
             }

         },
     }
 });
