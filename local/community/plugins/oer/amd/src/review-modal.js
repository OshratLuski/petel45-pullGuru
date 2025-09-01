define([
  'jquery',
  'core/templates',
  'core/notification',
], function($, Templates, Notification) {

  var SELECTORS = {
        modalWrapper: '#modalReview',
        modalContent: '#modalContentReview',
        triggerBtn: '#triggerModalReview'
      };

  return {

    TEMPLATE: {
      main: {
        src: 'community_oer/review/review_modal',
        id: 'askToReview'
      },
      main_v2: {
        src: 'community_oer/review/review_modal_v2',
        id: 'askToReview'
      },
      form: {
        src: 'community_oer/review/review_modal_form',
        id: 'reviewForm'
      },
      approve: {
        src: 'community_oer/review/review_modal_approve',
        id: 'reviewApprove'
      },
      approve2: {
        src: 'community_oer/review/review_modal_approve2',
        id: 'reviewApprove2'
      },
      approve3: {
        src: 'community_oer/review/review_modal_approve3',
        id: 'reviewApprove3'
      },
      response: {
        src: 'community_oer/review/review_modal_response',
        id: 'review_response'
      },
      confirmReject: {
        src: 'community_oer/review/review_modal_confirm',
        id: 'confirmReject'
      },
      activity_remind: {
        src: 'community_oer/review/review_activity_remind',
        id: 'activityEdit'
      }
    },

    modalInit: false,
    triggerBtn: '',
    modalContent: '',
    modalWrapper: '',

    /**
     * Insert modal markup on the page.
     *
     * @method render
     * @param {string} template The template name.
     * @param {object} context The context for template.
     * @return {Promise}
     */
    render: function (template, context) {
      var self = this;
      context.wwwroot = M.cfg.wwwroot;
      self.modalContent.innerHTML = '';
      return Templates.render(template, context)
          .done(function (html, js) {
            $('#modalReview').modal('show');
            Templates.replaceNodeContents(self.modalContent, html, js);
          })
          .fail(Notification.exception);
    },

    /**
     * Insert modal markup on the page.
     *
     * @method insertTemplates
     * @return {Promise|boolean}
     */
    insertWrapper: function () {
      var context = {},
          self = this;

      return Templates.render('community_oer/review/review_wrapper', context)
          .done(function (html, js) {
            if (!self.modalInit) {
              Templates.appendNodeContents('body', html, js);
              self.modalInit = true;
              self.modalWrapper = document.querySelector(SELECTORS.modalWrapper);
              self.modalContent = document.querySelector(SELECTORS.modalContent);
              self.triggerBtn = document.querySelector(SELECTORS.triggerBtn);
            }
          })
          .fail(Notification.exception);
    },

  };
});
