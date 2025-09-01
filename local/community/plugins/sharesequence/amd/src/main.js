define([
    'jquery',
    'core/yui',
    'core/str',
    'core/modal_factory',
    'core/modal_events',
    'core/ajax',
    'core/templates',
    'core/notification',
    'core/fragment',
    'community_sharesequence/select2'

], function ($, Y, Str, ModalFactory, ModalEvents, Ajax, Templates, Notification, Fragment) {
    `use strict`;

    const flag_background_enable = false;

    function build_select2(modal, sclass){
        var select2Target =  modal.body.find(sclass);
        var dropdownParent = select2Target.closest('.select-wrapper');
        select2Target.select2({
            dropdownAutoWidth: true,
            dropdownParent: dropdownParent
        });
    }

    function open_upload_to_catalog_page_1(default_data) {

        const getBody = function() {

            // Get the content of the modal.
            return Fragment.loadFragment('community_sharesequence', 'upload_sequence_catalog_page_1', default_data.coursecontext, default_data);
        };

        Str.get_strings([
            {key: 'share_national_shared', component: 'community_sharesequence'},
        ]).done(function (strings) {

            var modalPromise = ModalFactory.create({
                type: ModalFactory.types.DEFAULT,
                title: strings[0],
                body: getBody()
            });

            $.when(modalPromise).then(function(fmodal) {

                /* fmodal.setLarge(); */

                var root = fmodal.getRoot();
                root.find('.modal-dialog').addClass('modal-xlg');
                root.addClass('sharesequence-modal');

                root.on(ModalEvents.bodyRendered, function () {
                    root.find('.modal-body').animate({
                        scrollTop: 0
                    }, 0);

                    setTimeout(function(){
                        root.find('input:not([type=hidden])').first().focus();
                    }, 300);
                });

                return fmodal;
            }).done(function(modal) {
                modal.show();
            }).fail(Notification.exception);
        });

    }

    function open_upload_to_catalog_page_2(default_data) {

        const getBody = function() {

            // Get the content of the modal.
            return Fragment.loadFragment('community_sharesequence', 'upload_sequence_catalog_page_2', default_data.coursecontext, default_data);
        };

        Str.get_strings([
            {key: 'share_national_shared', component: 'community_sharesequence'},
        ]).done(function (strings) {
            var modalPromise = ModalFactory.create({
                type: ModalFactory.types.DEFAULT,
                title: strings[0],
                body: getBody()
            });

            $.when(modalPromise).then(function(fmodal) {

                fmodal.setLarge();

                var root = fmodal.getRoot();
                root.find('.modal-dialog').addClass('modal-xlg');
                root.addClass('sharesequence-modal');
                root.on(ModalEvents.bodyRendered, function () {
                    root.find('.modal-body').animate({
                        scrollTop: 0
                    }, 0);

                    setTimeout(function(){
                        root.find('input:not([type=hidden])').first().focus();
                    }, 300);
                });

                return fmodal;
            }).done(function(modal) {
                modal.show();
            }).fail(Notification.exception);
        });

    }

    return {
        init: function (default_data) {
            let self = this;

            // Event on button share.
            $('.btn-share-sequence').on( "click", function(e) {
                default_data = self.prepare_default_data(default_data);
                open_upload_to_catalog_page_1(default_data);
            })
        },

        init_page_1: function (default_data) {
            default_data = this.prepare_default_data(default_data);
            open_upload_to_catalog_page_1(default_data);
        },

        init_page_2: function (default_data) {
            default_data = this.prepare_default_data(default_data);
            open_upload_to_catalog_page_2(default_data);
        },

        prepare_default_data: function (default_data) {

            let result = {};
            result.coursecontext = default_data.coursecontext;
            result.courseid = default_data.courseid;
            result.default_data = JSON.stringify(default_data);

            return result;
        },
    }
});
