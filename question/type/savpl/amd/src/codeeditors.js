// This file is part of Moodle - https://moodle.org/
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
 * Provides utility methods to setup resizable ace editors into a page.
 * @copyright  Astor Bizard, 2019
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/* globals ace */
define(['jquery', 'core/url', 'core/config', 'core/ajax', 'core/notification', 'core/str'], function($, url, cfg, Ajax, Notification, Str) {

    // Global Ace editor theme and font size to use for all editors.
    var aceTheme;
    var fontSize;

    $(document).on('click', '[data-action=aisupport]', function() {
        updateChatWindow($(this).data('questionid'));
    });

    $('.aisupport-prompt-student').on('keypress', function(e) {
        var keycode = (e.keyCode ? e.keyCode : e.which);
        if(keycode == 13) {
            e.preventDefault();
            updateChatWindow($(this).data('questionid'));
        }
    });

    $(document).on('click', '.aisupport-resp-copy', function() {
        let _respid = $(this).data('respid');
        let _qaid = $(this).data('qaid');
        let _text = $('.aisupport-resp-' + _respid).text();
        Str.get_strings([
            {key: 'respcopyconfirmtitle', component: 'qtype_savpl'},
            {key: 'respcopyconfirmbody', component: 'qtype_savpl'},
            {key: 'yes', component: 'moodle'},
            {key: 'cancel', component: 'moodle'}
        ]).done(function(s) {
            Notification.confirm(s[0], s[1], s[2], s[3], function() {
                let _textarea = $('.aisupport-textarea-' + _qaid);
                if (_textarea) {
                    _textarea.html(_text);
                    let placeholder = _textarea.siblings('.ace-placeholder');
                    aceEditor = ace.edit(placeholder[0]);
                    aceEditor.setValue(_text);
                }
            });
        }).fail(Notification.exception);
    });


    function updateChatWindow(_questionid) {
        let _button = $('.aisupport-btn-' + _questionid);;
        let _textarea = $('.aisupport-prompt-' + _questionid);
        let _chatwindow = $('.aisupport-chat-window-' + _questionid);
        let _request = _textarea.val();
        _textarea.val('');
        let _qaid = _button.data('qaid');
        _button.attr("disabled", true);
        _chatwindow.html(_chatwindow.html() + '<div class="aisupport-chat-response aisupport-chat-response-student"> <div class="aisupport-chat-response-student-inner">' + _request + '</div></div>');
        let request = {
            methodname: 'qtype_savpl_get_aisupport',
            args: {
                'prompt': _request,
                'userid': _button.data('userid'),
                'qaid': _qaid,
                'questionid': _button.data('questionid'),
                'quizid': _button.data('quizid'),
            }
        };
        Ajax.call([request])[0].done(function(data) {
            if (data.result) {
                let langkey = data.isrestricted ? 'aisupportbtnwithleft' : 'aisupportbtn';
                Str.get_strings([
                    {key: 'respcopybtn', component: 'qtype_savpl'},
                    {key: langkey, component: 'qtype_savpl', param: data.airequestsleft}
                ])
                    .then(function(strings) {
                        _button.html(strings[1]);
                        let _r = (Math.random() + 1).toString(36).substring(7);
                        _chatwindow.html(_chatwindow.html() +
                            '<div class="aisupport-chat-response aisupport-chat-response-ai">' +
                            '<div class="aisupport-chat-response-ai-inner">' +
                            '<span class="aisupport-resp-' + _r + ' float-left">'
                            + data.response +
                            '</span>' +
                            '<a class="btn btn-primary aisupport-resp-copy float-left" data-respid="' + _r + '" data-qaid="' + _qaid + '">' + strings[0] + '</a>' +
                            '</div>' + '<img class="aisupport-ai-icon mr-2 ml-2" src="' + M.util.image_url('ai', 'qtype_savpl') + '"/>' + '</div>'
                        );
                        _chatwindow.scrollTop(_chatwindow[0].scrollHeight - _chatwindow[0].clientHeight);
                    });
                if (data.airequestsleft > 0) {
                    _button.attr("disabled", false);
                }
            } else {
                Notification.addNotification({
                    message: data.message,
                    type: 'error'
                });
                _button.attr("disabled", false);
            }
        }).fail(function() {
            _button.attr("disabled", false);
            Notification.exception;
        });
    }
    /**
     * Setup each specified textarea with Ace editor, with a vertical resize feature.
     * It inherits readonly attribute from textarea.
     * @param {jQuery} $textareas JQuery set of textareas from which to set up editors.
     * @param {String} aceSize Initial CSS size of editors.
     * @param {String} aceLang (optional) Lang (mode) to setup editors from.
     * @return {Editor} The last editor set up.
     */
    function setupAceEditors($textareas, aceSize, aceLang) {
        var aceEditor;

        // Vertical resizing.
        var prevY;
        var $placeholderBeingResized = null;

        if (aceLang === undefined) {
            aceLang = 'plain_text';
        }

        console.log('$textareas');
        console.log($textareas);

        $textareas.each(function() {
            var $textarea = $(this);
            var $editorPlaceholder = $('<div>', {
                width: '100%',
                height: aceSize,
                'id': 'ace_placeholder_' + $textarea.attr('name'),
                'class': 'ace-placeholder'
            }).insertAfter($textarea);
            $textarea.hide();

            $('<div>', {
                'id': 'ace_resize_' + $textarea.attr('name'),
                'class': 'ace-resize'
            }).insertAfter($editorPlaceholder)
            .mousedown(function(event) {
                prevY = event.clientY;
                $placeholderBeingResized = $editorPlaceholder;
                event.preventDefault();
            });
            console.log('ace');
            console.log(ace);
            // This is what creates the Ace editor within the placeholder div.
            aceEditor = ace.edit($editorPlaceholder[0]);
            console.log('aceEditor');
            console.log(aceEditor);
            aceEditor.setOptions({
                theme: 'ace/theme/' + aceTheme,
                mode: 'ace/mode/' + aceLang
            });
            aceEditor.setFontSize(fontSize);
            aceEditor.$blockScrolling = Infinity; // Disable ace warning.
            aceEditor.getSession().setValue($textarea.val());
            aceEditor.setReadOnly($textarea.is('[readonly]'));

            // On submit or run/check, propagate the changes to textarea.
            $('[type=submit], .qvpl-buttons button').click(function() {
                // Cannot use aceEditor here, as it will have another value later.
                $textarea.val(ace.edit('ace_placeholder_' + $textarea.attr('name')).getValue());
            });
        });

        $(window).mousemove(function(event) {
            if ($placeholderBeingResized) {
                $placeholderBeingResized.height(function(i, height) {
                    return height + event.clientY - prevY;
                });
                prevY = event.clientY;
                ace.edit($placeholderBeingResized[0]).resize();
                event.preventDefault();
            }
        }).mouseup(function() {
            $placeholderBeingResized = null;
        });

        return aceEditor;
    }

    /**
     * Loads Ace script from VPL plugin.
     * @return {Promise} A promise that resolves upon load.
     */
    function loadAce() {
        if (typeof ace !== 'undefined' && typeof aceTheme !== 'undefined') {
            return $.Deferred().resolve();
        }
        var ACESCRIPTLOCATION = url.relativeUrl("/mod/vpl/editor/ace9");
        return $.when(
            $.ajax({
                url: ACESCRIPTLOCATION + '/ace.js',
                dataType: 'script',
                cache: true,
                success: function() {
                    ace.config.set('basePath', ACESCRIPTLOCATION);
                }
            }),
            getEditorPreferences().then(function(prefs) {
                aceTheme = prefs.aceTheme;
                fontSize = prefs.fontSize;
            }),
        );
    }

    /**
     * Get current preferences for font size and editor theme.
     * @return {Promise} A promise that resolves upon load with an argument that is an object containing fontSize and aceTheme keys.
     */
    function getEditorPreferences() {
        return $.ajax({
            url: url.relativeUrl('/question/type/savpl/ajax/vplpreferences.json.php'),
            cache: true,
        }).promise().then(function(outcome) {
            return {
                aceTheme: outcome.success ? outcome.response.aceTheme : 'chrome',
                fontSize: outcome.success ? Number(outcome.response.fontSize) : 12,
            };
        });
    }

    /**
     * Save preferences for font size and editor theme.
     * @param {String} aceTheme The new theme.
     * @param {String|Number} fontSize The new font size.
     */
    function saveEditorPreferences(aceTheme, fontSize) {
        $.ajax({
            url: url.relativeUrl('/question/type/vplquestion/ajax/vplpreferences.json.php'),
            cache: false,
            method: 'POST',
            data: {
                set: {
                    aceTheme: aceTheme,
                    fontSize: Number(fontSize),
                },
                sesskey: cfg.sesskey,
            },
        });
    }

    return {
        // Setup editors in question edition form.
        setupFormEditors: function() {
            return loadAce().done(function() {
                console.log("$('.code-editor')");
                console.log($('.code-editor'));
                console.log("$('.code-editor textarea')");
                console.log($('.code-editor textarea'));
                setupAceEditors($('.code-editor textarea'), '170px');
            });
        },

        // Setup editor in answer form.
        setupQuestionEditor: function($textarea, $setTextButtons, lineOffset) {
            return loadAce().done(function() {
                console.log("setupQuestionEditor $textarea");
                console.log($textarea);
                // Setup question editor.
                var aceEditor = setupAceEditors($textarea, '200px', $textarea.data('templatelang'));
                // Set first line number to match compilation messages.
                aceEditor.setOption('firstLineNumber', lineOffset);
                // Setup reset and correction buttons (if present, ie. not review mode).
                $setTextButtons.each(function() {
                    var text = $(this).data('text');
                    $(this).removeAttr('data-text');
                    $(this).click(function(event) {
                        if (aceEditor.getValue() != text) {
                            aceEditor.setValue(text);
                        }
                        event.preventDefault();
                    });
                });
            });
        },

        getEditorPreferences: getEditorPreferences,

        saveEditorPreferences: saveEditorPreferences,

        changeFontSize: function(newFontSize) {
            $('.ace-placeholder').each(function() {
                ace.edit(this).setFontSize(Number(newFontSize));
            });
        },

        changeTheme: function(newTheme) {
            $('.ace-placeholder').each(function() {
                ace.edit(this).setOptions({
                    theme: 'ace/theme/' + newTheme
                });
            });
        },
    };
});
