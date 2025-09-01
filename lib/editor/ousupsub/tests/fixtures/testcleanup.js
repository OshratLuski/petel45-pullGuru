/**
 * Simple javascript unit test script for the OUsupsup editor plugin
 */

YUI().use("moodle-editor_ousupsub-editor", function(Y) {

    var testcases = [
                     {input: "<sup id=\"yui12345\" class=\"yui12345\" alt=\"\" style=\"\">1</sup>", expected: "<sup>1</sup>"},
                     {input: "<sub id=\"yui12345\" class=\"yui12345\" alt=\"\" style=\"\">1</sub>", expected: "<sub>1</sub>"},
                     {input: "&nbsp;", expected: ""},
                     {input: "1<sup> 2</sup>", expected: "1 <sup>2</sup>"}, // Space after start sup tag.
                     {input: "1<sub> 2</sub>", expected: "1 <sub>2</sub>"}, // Space after start sub tag.
                     {input: "1<sup>   2</sup>", expected: "1   <sup>2</sup>"}, // Spaces after start sup tag.
                     {input: "1<sub>   2</sub>", expected: "1   <sub>2</sub>"}, // Spaces after start sub tag.
                     {input: "<sup>1 </sup>2", expected: "<sup>1</sup> 2"}, // Space before end sup tag.
                     {input: "<sub>1 </sub>2", expected: "<sub>1</sub> 2"}, // Space before end sub tag.
                     {input: "<sup>1   </sup>2", expected: "<sup>1</sup>   2"}, // Spaces before end sup tag.
                     {input: "<sub>1   </sub>2", expected: "<sub>1</sub>   2"}, // Spaces before end sub tag.

                     {input: "<span><sup>12</sup></span>", expected: "12"},
                     {input: "<sup>12</sup><sup>34</sup>", expected: "<sup>1234</sup>"},
                     {input: "<sup>12</sup> <sup>34</sup>", expected: "<sup>12 34</sup>"}, // Space between matching tags.
                     {input: "<sub>12</sub> <sub>34</sub>", expected: "<sub>12 34</sub>"}, // Space between matching tags.
                     {input: "<sup>12</sup>    <sup>34</sup>", expected: "<sup>12    34</sup>"}, // Spaces between matching tags.
                     {input: "<sup>1<sup>2</sup>3</sup>", expected: "<sup>1</sup>2<sup>3</sup>"}, // Nested matching sup tag.
                     {input: "<sub>1<sub>2</sub>3</sub>", expected: "<sub>1</sub>2<sub>3</sub>"}, // Nested matching sub tag.
                     {input: "0<sup>12<sup>34</sup>56</sup>789", expected: "0<sup>12</sup>34<sup>56</sup>789"}, // Nested matching sup tag.
                     {input: "0<sub>12<sub>34</sub>56</sub>789", expected: "0<sub>12</sub>34<sub>56</sub>789"}, // Nested matching sub tag.
                     {input: "<sup><sup>12</sup></sup>", expected: "12"},
                     {input: "1<sup>2</sup>3<sup>4</sup>5", expected: "1<sup>2</sup>3<sup>4</sup>5"},
                     {input: "<sup><sub>12</sub></sup>", expected: "<sup>12</sup>"},
                     {input: "<p><sup><sub>12</sub></sup></p>", expected: "<sup>12</sup>"},

                     {input: "1<span><sup>2</sup></span>3", expected: "123"}, // Add sup.
                     {input: "1<sup><sup>2</sup></sup>3", expected: "123"}, // Remove sup.
                     {input: "1<sub><sup>2</sup>3</sub>4<br>", expected: "1<sub>23</sub>4"}, // Keep sub.
                     {input: "1<sup>2</sup><sub><sup>3</sup>4</sub>5<br>", expected: "1<sup>2</sup><sub>34</sub>5"}, // Keep sub.

                     // Test paste event.
                     {input: "<div><ul><li><span><a href=\"\">1<img /></a></span></li></ul></div>", expected: "1", event: "paste"},

                     // Spans with rangy and without.
                     {input: "1<span id=\"selectionBoundary_123\" class=\"rangySelectionBoundary\"></span>2<span>3<span></span>4</span>5", expected: "1<span id=\"selectionBoundary_123\" class=\"rangySelectionBoundary\"></span>2345"}, // Keep sub.

                     /* Check for disallowed characters */
                     {input: "<sup><p>12</p></sup>", expected: "<sup>12</sup>"},
                     {input: "<sup><b>12</b></sup>", expected: "<sup>12</sup>"},
                     {input: "<sup><i>12</i></sup>", expected: "<sup>12</sup>"},
                     {input: "<sup><u>12</u></sup>", expected: "<sup>12</sup>"},
                     {input: "1<br>", expected: "1"},
                     {input: "1<br />", expected: "1"},
                     {input: "1.2x10<sup id=\"yui_3_17_2_2_1434116789356_168\">3</sup> g<sup id=\"yui_3_17_2_2_1434116789356_165\"></sup>", expected: "1.2x10<sup>3</sup> g"}, // Empty trailing sup tag removed
                     {input: "1.2x10<sub id=\"yui_3_17_2_2_1434116789356_168\">3</sub> g<sub id=\"yui_3_17_2_2_1434116789356_165\"></sub>", expected: "1.2x10<sub>3</sub> g"}, // Empty trailing sub tag removed
                     {input: "<xmlns:texthelpns rwthpgen='1'>mm s</xmlns:texthelpns>", expected: "mm s"} // Empty special xmlns tag from browser plugin.
    ];

    // Elements to remove completely including contents.
    var disallowed_characters_and_text = ['style','script'];
    for (var x = 0; x < disallowed_characters_and_text.length; x++) {
        testcases[testcases.length] = {input: "<" + disallowed_characters_and_text[x] + ">1</" + disallowed_characters_and_text[x] + ">", expected: ""};
    }

    // Elements to remove while contents are left.
    var disallowed_characters = ['br','title','std','font','html','body','link',
                                 'a','ul','li','ol','b','i','u','ul','ol','li','img',
                                 'abbr','address','area','article','address','article',
                                 'aside','audio','base','bdi','bdo','blockquote','button',
                                 'canvas','caption','cite','code','col','colgroup','content',
                                 'data','datalist','dd','decorator','del','details','dialog',
                                 'dfn','div','dl','dt','element','em','embed','fieldset',
                                 'figcaption','figure','footer','form','h1','h2','h3','h4',
                                 'h5','h6','head','header','hgroup','hr','iframe','input',
                                 'ins','kbd','keygen','label','legend','main','map','mark',
                                 'menu','menuitem','meter','meta','nav','noscript',
                                 'object','optgroup','option','output','optgroup','options',
                                 'p','param','pre','progress','q','rp','rt','rtc','ruby',
                                 'samp','section','select','shadow','small','source','std',
                                 'strong','summary','span','table','tbody','td','template',
                                 'textarea','time','tfoot','th','thead','tr','track','var',
                                 'wbr','video',
                                 // Deprecated elements
                                 'acronym','applet','basefont','big','blink','center','dir',
                                 'frame','frameset','isindex','listing','noembed',
                                 'spacer','strike','tt','xmp',
                                 // Elements from common sites including google.com.
                                 'jsl','nobr'
                                 ];
    for (var x = 0; x < disallowed_characters.length; x++) {
        testcases[testcases.length] = {input: "<" + disallowed_characters[x] + ">1</" + disallowed_characters[x] + ">", expected: "1"};
    }


    function init_ousupsub(id, params) {
        M.str = {
                "moodle": {
                    "error": "Error",
                    "morehelp": "More help"
                },
                "editor_ousupsub": {
                    "editor_command_keycode":"Cmd + {$a}",
                    "editor_control_keycode":"Ctrl + {$a}",
                    "editor_shift_keycode":"Shift + {$a}",
                    "plugin_title_shortcut":"{$a->title} [{$a->shortcut}]",
                    "subscript":"Subscript",
                    "superscript":"Superscript"
                },
            }

        plugins = [];
        if (params.superscript) {
            plugins.push({"name": "superscript", "params": []});
        }
        if (params.subscript) {
            plugins.push({"name": "subscript", "params": []});
        }

        Y.M.editor_ousupsub.createEditor(
            {"elementid":id,"content_css":"","contextid":0,"language":"en",
                "directionality":"ltr","plugins":[{"group":"style1","plugins":plugins}],"pageHash":""});
    };

    // Initialise an editor to test with.
    init_ousupsub("id_description_editor", {"subscript":true, "superscript":true});

    function get_editor(id) {
        return Y.M.editor_ousupsub.getEditor(id);
    }

    function escape_html(str) {
        return str.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/ /g,'.');
    }

    function run_tests(Y) {
        var editor = get_editor("id_description_editor");
        for(var i = 0; i < testcases.length; i++) {
            run_test(editor, testcases[i]);
        }
    }


    function run_test(editor, test) {
        var input = test.input
        if(test.event && test.event == 'paste') {
            input = editor._cleanPasteHTML(input);
        }
        editor.editor.set('innerHTML', input);
        // Fake the subscript button.
        editor.plugins.subscript._applyTextCommand();
        // Fake submit
        editor.updateFromTextArea();
        test.actual = editor.editor.get('innerHTML');
        test.matched = test.expected == test.actual;
    }

    function update_display() {
        // Update table.
        var table = Y.one('#results');
        var showPasses = false;
        var numberPassed = 0, numberFailed = 0;
        var summary = '';
        var summaryNode = Y.one('#summary');
        for(var i = 0; i < testcases.length; i++) {
            test = testcases[i];
            test.matched ? ++numberPassed : ++numberFailed;

            if (!showPasses && test.matched) {
                continue;
            }

            var rowText = '<tr>';
            rowText += '<td>' + escape_html(test.input) + '</td>';
            rowText += '<td>' + escape_html(test.expected) + '</td>';
            rowText += '<td>' + escape_html(test.actual) + '</td>';
            rowText += '<td class="' + (test.matched ? 'matched' : 'notmatched') + '">' + test.matched + '</td>';
            rowText += '</tr>';
            var row = Y.Node.create(rowText);
            table.appendChild(row);
        }

        // Explain if there are no failures to show.
        if (!numberFailed) {
            var rowText = '<tr>';
            rowText += '<td class="matched" colspan = "4">There were no failures to show.</td>';
            rowText += '</tr>';
            var row = Y.Node.create(rowText);
            table.appendChild(row);
        }

        summary = 'Of ' + testcases.length + ' tests run there were ' + numberPassed + ' test passes and ';
        summary += numberFailed + ' failures.';
        summaryNode.set('innerHTML', summary);

        var statusNode = Y.one('#status');
        var status = numberFailed ? 'failure' : 'success';
        statusNode.set('innerHTML', 'Overall status = <span class="' + status + '">' + status + '</span>');
    }

    run_tests();
    update_display();
});
