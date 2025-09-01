define(['core/ajax', 'jquery', 'core/str'], function(ajax, $, str) {
    return {
        init: function() {
            console.log('Correct Answer module loaded successfully');

            $('[id^="toggle-correct-answer-"]').on('click', function() {
                const button = $(this);
                const questionId = button.data('questionid');
                const isShowingAnswer = button.data('showingAnswer') || false;

                if (!isShowingAnswer) {
                    // Fetch the correct answer via AJAX
                    ajax.call([{
                        methodname: 'quiz_diagnosticstats_get_correctanswer',
                        args: { questionid: questionId },
                        done: function(data) {
                            const correctAnswerId = data.correctAnswerId;

                            // Find the correct radio button by its ID
                            const correctRadio = $(`#${correctAnswerId}`);

                            if (correctRadio.length) {
                                correctRadio.prop('checked', true);
                                correctRadio.closest('div').css('background-color', '#d4edda');
                                // Save the current answer ID for toggling
                                button.data('currentAnswerId', correctAnswerId);
                            } else {
                                console.warn('Could not locate the radio button for the correct answer.');
                            }

                            // Update button text to "Hide Correct Answer"
                            str.get_string('hidecorrectanswer', 'quiz_diagnosticstats').then(function(string) {
                                button.text(string);
                            });

                            button.data('showingAnswer', true);
                        },
                        fail: function(error) {
                            console.error('Error during AJAX call:', error);
                        }
                    }]);
                } else {
                    // Hide the correct answer
                    const currentAnswerId = button.data('currentAnswerId');
                    if (currentAnswerId) {
                        const correctRadio = $(`#${currentAnswerId}`);
                        correctRadio.prop('checked', false);
                        correctRadio.closest('div').css('background-color', '');
                    }

                    // Update button text to "Show Correct Answer"
                    str.get_string('showcorrectanswer', 'quiz_diagnosticstats').then(function(string) {
                        button.text(string);
                    });

                    button.data('showingAnswer', false);
                }
            });
        }
    };
});
