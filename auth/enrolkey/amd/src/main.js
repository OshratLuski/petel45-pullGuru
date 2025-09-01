define([
    'jquery',
    'core/str',
    'core/ajax',
    'core/notification',

], function($, Str, Ajax, Notification) {

    return {
        validate_token: function() {

            // Validate token.
            $('#id_signup_token').on('focusout', function(e) {
                let token = $("#id_signup_token").val();

                if (token.trim().length > 0) {
                    Ajax.call([{
                        methodname: 'auth_enrolkey_validate_token',
                        args: {
                            'token': token
                        },
                        done: function (response) {
                            let data = JSON.parse(response);

                            if (data.error) {
                                $('#id_error_signup_token').text(data.message).show();
                                $(e.target).addClass('is-invalid');
                            } else {
                                $('#id_error_signup_token').text(data.message).hide();
                                $(e.target).removeClass('is-invalid');
                            }
                        },
                        fail: Notification.exception
                    }]);
                }

            });
        }
    };
});
