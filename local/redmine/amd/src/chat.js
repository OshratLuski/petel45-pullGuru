define([
    'jquery',
    'core/str',
    'core/modal_factory',
    'core/modal_events',
    'core/ajax',
    'core/templates',
    'core/notification',
    'local_redmine/support',

], function($, Str, ModalFactory, ModalEvents, Ajax, Templates, Notification, Support) {

    let issueid;

    return {
        init: function(id) {
            // Hide header block.
            $('#page-header').addClass('d-none');

            issueid = id;

            $('.messages-loading-wrapper').show();

            this.renderMessageBlock(function(){
                $('.messages-loading-wrapper').hide();
            });
        },

        renderMessageBlock: function(callback) {
            let self = this;

            Ajax.call([{
                methodname: 'local_redmine_get_chat_messages',
                args: {
                    id: issueid,
                },
                done: function(response) {
                    let data = JSON.parse(response);

                    Templates.render('local_redmine/chat/messages', data)
                        .done(function(html, js) {
                            Templates.replaceNodeContents('#local_redmine_messages_content', html, js);

                            // Events.
                            $('.messaging-form').submit(false);
                            $('#local_redmine_message_submit').click(function(e) {
                                self.sendMessage();
                            });

                            callback();
                        });
                },
                fail: Notification.exception
            }]);
        },

        sendMessage: function() {
            const self = this;
            const file = $("#local_redmine_outcomming_file").prop("files")[0];
            let message = $('#local_redmine_outcomming_message').val().trim();

            $('#error-message').hide();
            $('#error-imgformat-message').hide();

            if ($('#local_redmine_outcomming_file').data('added-file').toString() === "true" && message.length === 0){
                $('#error-message').show();
                return false;

            } else if (message.length === 0) {
                return false;
            }
            
            // Loading.
            $('.messages-loading-wrapper').show();

            
            if (file !== undefined) {
                let filename = file.name;
                let filetype = file.type;

                // Create FileReader object
                var reader = new FileReader();
                reader.readAsDataURL(file);

                // Get base64 image from FileReader object
                reader.onload = function() {
                    let filecontent = reader.result;

                    Ajax.call([{
                        methodname: 'local_redmine_send_chat_message',
                        args: {
                            id: issueid,
                            message: message,
                            filename: filename,
                            filetype: filetype,
                            filecontent: filecontent
                        },
                        done: function(response) {
                            self.renderMessageBlock(function(){
                                $('.messages-loading-wrapper').hide();
                            });
                            Support.renderIssueCounter();
                        },
                        fail: Notification.exception
                    }]);
                };
            } else {
                Ajax.call([{
                    methodname: 'local_redmine_send_chat_message',
                    args: {
                        id: issueid,
                        message: message,
                        filename: '',
                        filetype: '',
                        filecontent: ''
                    },
                    done: function(response) {
                        self.renderMessageBlock(function(){
                            $('.messages-loading-wrapper').hide();
                        });
                        Support.renderIssueCounter();
                    },
                    fail: Notification.exception
                }]);
            }
        },
        chatPageActions: function() {

            $('.messaging-form .fa-paperclip').on('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                // If file not added - trigger click on hidden input type="file"
                if ($('#local_redmine_outcomming_file').data('added-file').toString() === "false") {
                    $('#local_redmine_outcomming_file').trigger('click');
                }
            });

            $('#local_redmine_outcomming_file').on('change', function(e) {

                // Check input type="file" loaded file format
                let valueArr = $(this).val().split('.');
                const format = valueArr[(valueArr.length - 1)];

                // If format is correct
                if (format.toLowerCase() === 'jpg' || format.toLowerCase() === 'png' || format.toLowerCase() === 'jpeg') {
                    // Change input type="file" data attribute to avoid second click;
                    $(this).data('added-file', 'true');

                    // Change btn color
                    $('.messaging-form .fa-paperclip').hide();
                    $('.messaging-form .fa-paperclip.text-success').show();

                    // Hide error message block.
                    $('#error-imgformat-message').hide();

                    // Add filename into added-file-block message and show block.
                    const filenameText = $(this)[0].files[0].name;
                    $('#loaded-file-name').text(filenameText);
                    $('#added-file-block').show();
                    $('#local_redmine_outcomming_file').data('added-file', true);
                } else {
                    // If format incorrect.
                    // Change input type="file" data attr.
                    $(this).data('added-file', "false");

                    // Hide added-file-block message.
                    $('#added-file-block').hide();

                    // Show error message.
                    $('#error-imgformat-message').show();
                }
            });

            // Delete loaded file from input type="file" and file name string (under message text input)
            $(document).on('click', '#delete-file-btn', function() {
                $('#local_redmine_outcomming_file').data('added-file', "false");
                $('#local_redmine_outcomming_file').val('');
                $('.messaging-form .fa-paperclip').show();
                $('.messaging-form .fa-paperclip.text-success').hide();
                $('#added-file-block').hide();
                $('#error-message').hide();
            });

            // Remove eror message when start typing text
            $('#local_redmine_outcomming_message').on('input', function() {
                $('#error-message').hide();
            });

            // Scroll page to lasst message
            $([document.documentElement, document.body]).animate({
                scrollTop:   $('h1').last().offset().top
            }, 500);
            if ($('.message-wrapper').length > 1) {
                $('.messaging-block-inner').animate({
                    scrollTop:   $('.message-wrapper').last().offset().top
                }, 10);
            }

        }
    };
});
