/**
 * Javascript main event handler.
 *
 * @module     local_petel/init
 * @package    local_petel
 * @copyright  2022 Weizmann institute of science, Israel.
 * @author  2022 Devlion Ltd. <info@devlion.co>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @since      3.6
 */

define([
    'jquery',
    'core/str',
    'core/url',
    'core/notification',
    'core/ajax',
    'core/modal_events',
    'core/modal_factory',
    'core/templates',
], function ($, Str, Url, Notification, Ajax, ModalEvents, ModalFactory, Templates) {

    var unique_id;

    return {
        init: function () {
            let self = this;

            $(document).on('click', '.demo_popup', function () {
                self.openPopup($(this).data('key'), $(this).data('cmid'), $(this).data('lang'));
            });

            $(document).on('click', '.demo-popup-course', function () {
                let classes = $(this).attr('class');
                let keyClass = classes.match(/(^|\s)(key\-[^\s]*)/)[0];
                let langClass = classes.match(/(^|\s)(lang\-[^\s]*)/)[0];
                if (keyClass !== undefined) {
                    let key = keyClass.replace(/^key-/, '');
                    let lang = langClass.replace('lang-', '');
                    self.openPopup(key, null, lang.trim());
                }
            });

            $(document).on('click', '.demo_copytoclipboard', function () {
                let _this = $(this);
                Str.get_strings([
                    { key: 'demo_copied', component: 'local_petel' }
                ]).done(function (strings) {
                    var copytext = _this.data('copytext');
                    navigator.clipboard.writeText(copytext);
                    _this.next('.tooltip').tooltip('show').attr('title', strings[0] + ": " + copytext);
                });
            });
        },

        openPopup: function (password, cmid, lang) {
            var self = this;

            var keys = [
                {
                    key: 'demomodalhdr',
                    component: 'local_petel'
                },
            ];

            var params = {
                key: password,
            };

            if (cmid !== undefined && cmid!=null) {
                params.cmid = cmid;
            }

            if (lang !== undefined) {
                params.lang = lang;
            }

            var queryparams = $.param(params);
            let url = Url.relativeUrl('/local/petel/demo.php?' + queryparams);

            Str.get_strings(keys).then(function(langStrings) {
                var modalTitle = langStrings[0];

                var context = {
                    'url': url
                };
                return ModalFactory.create({
                    title: modalTitle,
                    body: Templates.render('local_petel/demo_popup', context),
                    type: ModalFactory.types.CANCEL,
                    large: false
                });
            }).then(function(modal) {

                // Show the modal!
                modal.show();

                // Handle hidden event.
                modal.getRoot().on(ModalEvents.hidden, function() {
                    // Destroy when hidden.
                    modal.destroy();
                });

                return;
            }).catch(Notification.exception);
        },
    };
});