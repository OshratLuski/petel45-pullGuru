define([
    'core/yui'
], function (Y) {
    `use strict`;

    return {
        'init': function (data) {

            data = JSON.parse(data);

            // Configuration for the Atto editor
            var config = {
                // Basic configuration
                elementid: 'id_' + data.id,  // ID of your textarea
                content_css: M.cfg.wwwroot + '/theme/styles.php?theme=' + data.theme,
                contextid: data.contextid, // Set your context ID

                // Plugin configurations
                plugins: {
                    // List of Atto plugins to load
                    list: [
                        'text',
                        'bold',
                        'italic',
                        'underline',
                        'strikethrough',
                        'subscript',
                        'superscript',
                        'link',
                        'unlink',
                        'image',
                        'media',
                        'managefiles',
                        'table',
                        'clear',
                        'undo',
                        'accessibility',
                        'htmlplus',
                        'charmap',
                        'emoticon',
                        'equation',
                        'align',
                        'indent'
                    ],

                    // Plugin-specific configurations
                    image: {
                        maxsizebytes: 2097152, // 2MB max file size
                        accepted_types: ['image/jpeg', 'image/png', 'image/gif']
                    },

                    media: {
                        enable: true
                    }
                },

                // Toolbar configuration
                toolbar: {
                    buttons: {
                        group1: ['bold', 'italic', 'underline', 'strikethrough'],
                        group2: ['subscript', 'superscript'],
                        group3: ['link', 'unlink'],
                        group4: ['image', 'media', 'managefiles'],
                        group5: ['table'],
                        group6: ['undo', 'clear'],
                        group7: ['accessibility', 'htmlplus'],
                        group8: ['charmap', 'emoticon', 'equation'],
                        group9: ['align', 'indent']
                    }
                }
            };

            // Function to initialize the editor
            function initEditor() {
                try {
                    // Create new instance of Atto editor
                    var editor = new Y.M.editor_atto.Editor(config);

                    // Add event listeners
                    editor.on('change', function() {
                        //console.debug('Editor content changed');
                    });

                    editor.on('load', function() {alert()
                        //console.debug('Editor loaded successfully');
                    });

                    // Return the editor instance
                    return editor;
                } catch (e) {
                    console.error('Error initializing Atto editor:', e);
                    return null;
                }
            }

            // Initialize.
            var attoeditor = initEditor();
            if (attoeditor && attoeditor.editor !== null) {

                // if (attoeditor.editor.getHTML() === '') {
                //     attoeditor.editor.setHTML(attoeditor._getEmptyContent());
                // }

                attoeditor.editor.setHTML(data.default);

                // Store attoeditor instance if needed
                window.attoEditor = attoeditor;
            }
        },
    };
});
