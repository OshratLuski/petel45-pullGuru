define(['jquery', 'core/ajax'], function($, Ajax) {
    return {
        init: function() {
            const searchParams = new URLSearchParams(window.location.search);
            const attemptId = searchParams.get('attempt');
            const cmId = searchParams.get('cmid');
            const slot = $('#slot').val();
            var chatContainer = $('.chat-messages');

            function renderMathJax(element) {
                if (typeof MathJax !== 'undefined' && MathJax.typesetPromise) {
                    MathJax.typesetPromise([element]).catch(function(err) {
                        console.error('MathJax typesetting failed: ', err);
                    });
                } else if (typeof MathJax !== 'undefined' && MathJax.Hub) {
                    MathJax.Hub.Queue(["Typeset", MathJax.Hub, element]);
                } 
            }

            function loadMessages() {
                Ajax.call([{
                    methodname: 'qtype_diagnosticadvai_get_message',
                    args: { attemptid: attemptId },
                    done: function(response) {
                        chatContainer.empty();
                        response.messages.forEach(function(msg) {
                            appendMessage(msg.text, msg.sender, msg.timestamp);
                        });
                        if (response.messages.length === 0) {
                            $('.run-button').show();
                            $('#allbuttons').hide();
                        } else {
                            $('.run-button').hide();
                            $('#allbuttons').show();
                        }
                        renderMathJax(chatContainer[0]);
                    },
                    fail: function(error) {
                        console.log("Error loading messages:", error);
                    }
                }]);
            }

            function appendMessage(text, sender, timestamp) {
                var messageClass = sender === 'user' ? 'user-message right' : 'ai-message left';
                var messageHtml = '<div class="' + messageClass + ' message-container">' +
                    '<div class="message-bubble">' +
                    '<span class="message-text">' + text + '</span>' +
                    '<span class="message-timestamp">' + timestamp + '</span>' +
                    '</div>' +
                    '</div>';
                var newMessage = $(messageHtml);
                chatContainer.append(newMessage);
                chatContainer.scrollTop(chatContainer.prop("scrollHeight"));
                renderMathJax(newMessage[0]);
            }

            $(document).on('click', '.run-button', function(event) {
                event.preventDefault();
                $(".btn_aianalytics_spinner_run").show();
                $(this).prop("disabled", true);
                Ajax.call([{
                    methodname: 'qtype_diagnosticadvai_send_message',
                    args: { attemptid: attemptId, message: '', cmid: cmId, slot: slot },
                    done: function(response) {
                        $('.run-button').hide();
                        $('#allbuttons').show();
                        appendMessage(response.reply, 'ai', new Date().toLocaleTimeString());
                        $(".btn_aianalytics_spinner").hide();
                        $(".btn_aianalytics_spinner_run").hide();
                        $(".send-button").prop("disabled", false);
                    },
                    fail: function(error) {
                        console.log("AJAX Error:", error);
                        appendMessage('Error initiating conversation!', 'ai', new Date().toLocaleTimeString());
                        $(".btn_aianalytics_spinner_run").hide();
                        $(".btn_aianalytics_spinner").hide();
                        $('.run-button').prop("disabled", false);
                    }
                }]);
            });

            $(document).on('click', '.send-button', function(event) {
                event.preventDefault();
                var message = $('#question').val().trim();
                if (!message) {
                    return;
                }
                $(".btn_aianalytics_spinner").show();
                $(".send-button").prop("disabled", true);
                appendMessage(message, 'user', new Date().toLocaleTimeString());
                $('#question').val('');

                Ajax.call([{
                    methodname: 'qtype_diagnosticadvai_send_message',
                    args: { attemptid: attemptId, message: message, cmid: cmId, slot: slot },
                    done: function(response) {
                        appendMessage(response.reply, 'ai', new Date().toLocaleTimeString());
                        $(".btn_aianalytics_spinner").hide();
                        $(".send-button").prop("disabled", false);
                    },
                    fail: function(error) {
                        console.log("AJAX Error:", error);
                        appendMessage('Error sending message!', 'ai', new Date().toLocaleTimeString());
                        $(".btn_aianalytics_spinner").hide();
                        $(".send-button").prop("disabled", false);
                    }
                }]);
            });

            loadMessages();
        }
    };
});