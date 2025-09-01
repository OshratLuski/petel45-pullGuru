<?php
// This file is part of Moodle - http://moodle.org/
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
 * ousupsub custom steps definitions.
 *
 * @package   editor_ousupsub
 * @category  test
 * @copyright 2015 The Open University
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// NOTE: no MOODLE_INTERNAL test here, this file may be required by behat before including /config.php.
use Behat\Mink\Exception\ExpectationException as ExpectationException;

/**
 * Steps definitions to deal with the ousupsub text editor
 *
 * @copyright 2015 The Open University
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class behat_editor_ousupsub extends behat_base {

    /**
     * Opens an ousupsubtest page.
     *
     * @Given /^I am on the integrated "(sup|sub|both)" editor test page$/
     */
    public function i_am_on_integrated_test_page($type) {
        $this->getSession()->visit($this->locate_path(
                '/lib/editor/ousupsub/tests/fixtures/editortestpage.php?type=' . $type));
    }

    /**
     * Opens the stand-alone test page.
     *
     * @Given /^I am on the stand-alone supsub editor test page$/
     */
    public function i_am_on_standalone_test_page() {
        $this->getSession()->visit($this->locate_path('/lib/editor/ousupsub/standalone/index.html'));
    }

    /**
     * Select the text in an ousupsub field.
     *
     * @Given /^I select the text in the "([^"]*)" ousupsub editor$/
     * @throws ElementNotFoundException Thrown by behat_base::find
     * @param string $field
     * @return void
     */
    public function select_the_text_in_the_ousupsub_editor($fieldlocator) {
        if (!$this->running_javascript()) {
            throw new coding_exception('Selecting text requires javascript.');
        }
        // We delegate to behat_form_field class, it will
        // guess the type properly.
        $field = behat_field_manager::get_form_field_from_label($fieldlocator, $this);

        if (!method_exists($field, 'select_text')) {
            throw new coding_exception('Field does not support the select_text function.');
        }
        $field->select_text();
    }

    /**
     * Check the text in an ousupsub field.
     *
     * @Given /^I should see "([^"]*)" in the "([^"]*)" ousupsub editor$/
     * @throws ElementNotFoundException Thrown by behat_base::find
     * @param string $text
     * @param string $field
     * @return void
     */
    public function should_see_in_the_ousupsub_editor($text, $fieldlocator) {
        if (!$this->running_javascript()) {
            throw new coding_exception('Selecting text requires javascript.');
        }
        // We delegate to behat_form_field class, it will
        // guess the type properly.
        $field = behat_field_manager::get_form_field_from_label($fieldlocator, $this);

        if (!method_exists($field, 'get_value')) {
            throw new coding_exception('Field does not support the get_value function.');
        }

        if (!$field->matches($text)) {
            throw new ExpectationException("The field '" . $fieldlocator .
                    "' does not contain the text '" . $text . "'. It contains '" . $field->get_value() . "'.", $this->getSession());
        }
    }

    /**
     * Set the contents of a stand-alone supsub field.
     *
     * @Given /^I set the "([^"]*)" stand-alone ousupsub editor to "([^"]*)"$/
     * @throws ElementNotFoundException Thrown by behat_base::find
     * @param string $label the field label.
     * @param string $text the text to insert into the field.
     */
    public function i_set_the_standalone_ousupsub_editor_to($label, $text) {
        if (!$this->running_javascript()) {
            throw new coding_exception('Setting text requires javascript.');
        }

        // We delegate to behat_form_field class, which thinks this is an (Atto) editor.
        $field = $this->find_field($label);

        // Unfortunately, Atto uses Y to set the field value, which we don't have with
        // our nicely encapsulated JavaScript, so do it manually.
        $id = $field->getAttribute('id');
        $js = 'editor_ousupsub.getEditor("' . $id . '").editor.setHTML("' . $text . '");';
        $this->getSession()->executeScript($js);
    }

    /**
     * Set the given range in a stand-alone ousupsub field.
     *
     * @Given /^I select the range "([^"]*)" in the "([^"]*)" ousupsub editor$/
     * @throws ElementNotFoundException Thrown by behat_base::find
     * @param string $text
     * @param string $field
     */
    public function select_range_in_the_ousupsub_editor($range, $fieldlocator) {
        // NodeElement.keyPress simply doesn't work.
        if (!$this->running_javascript()) {
            throw new coding_exception('Selecting text requires javascript.');
        }
        // We delegate to behat_form_field class, it will
        // guess the type properly.
        $field = behat_field_manager::get_form_field_from_label($fieldlocator, $this);

        if (!method_exists($field, 'get_value')) {
            throw new coding_exception('Field does not support the get_value function.');
        }

        $editorid = $this->find_field($fieldlocator)->getAttribute('id');

        // Get query values for the range.
        list($startquery, $startoffset, $endquery, $endoffset) = explode(",", $range);
        $js = '
    function getNode(editor, query, node) {
        if (query !== "" && !isNaN(query)) {
            node = editor.childNodes[query];
        } else {
            node = query ? editor.querySelector(query) : editor;
            node = node.firstChild;
        }
        return node;
    }
    function RangySelectTextBehat() {
        var id = "'.$editorid.'", startquery = '.$startquery.', startoffset = '.$startoffset.',
            endquery  = '.$endquery.', endoffset = '.$endoffset.';
        var e = document.getElementById(id + "editable"),
            r = rangy.createRange();

        e.focus();
        if(startquery || startoffset || endquery || endoffset) {
            // Set defaults for testing.
            startoffset = startoffset ? startoffset : 0;
            endoffset = endoffset ? endoffset : 0;

            // Find the text nodes from the Start/end queries or default to the editor node.
            var startnode, endnode;
            startnode = getNode(e, startquery, startoffset);
            endnode = getNode(e, endquery, endoffset);
            r.setStart(startnode, startoffset);
            r.setEnd(endnode, endoffset);
        } else {
            r.selectNodeContents(e.firstChild);
        }
        var s = rangy.getSelection();
        s.setSingleRange(r);
        if (typeof editor_ousupsub !== "undefined") {
            // For testing standalone.
            editor_ousupsub.getEditor(id)._selections = [r];
        } else {
            // For testing in Moodle.
            YUI().use("moodle-editor_ousupsub-editor", function(Y) {
                Y.M.editor_ousupsub.getEditor(id)._selections = [r];
            });
        }
    }
    RangySelectTextBehat();';
        $this->getSession()->executeScript($js);
    }

    /**
     * Press key(s) in an ousupsub field.
     *
     * @Given /^I press the key "([^"]*)" in the "([^"]*)" ousupsub editor$/
     * @throws ElementNotFoundException Thrown by behat_base::find
     * @param string $keys
     * @param string $field
     */
    public function press_key_in_the_ousupsub_editor($keys, $fieldlocator) {
        // NodeElement.keyPress simply doesn't work.
        if (!$this->running_javascript()) {
            throw new coding_exception('Pressing keys requires javascript.');
        }
        // We delegate to behat_form_field class, it will
        // guess the type properly.
        $field = behat_field_manager::get_form_field_from_label($fieldlocator, $this);

        if (!method_exists($field, 'get_value')) {
            throw new coding_exception('Field does not support the get_value function.');
        }

        $editorid = $this->find_field($fieldlocator)->getAttribute('id');

        // Trigger the key press through javascript.
        $js = '
    function TriggerKeyPressBehat(id, keys) {
    // http://www.wfimc.org/public/js/yui/3.4.1/docs/event/simulate.html
    YUI().use("node-event-simulate", function(Y) {
        var node = Y.one("#" + id + "editable");

        node.focus();
        var keyEvent = "keypress";
        if (Y.UA.webkit || Y.UA.ie) {
            keyEvent = "keydown";
        }
        var event = {};

        // Handle modifiers like shift, ctrl and alt.
        var trimmedKeys = [];
        for(var i=0; i<keys.length;i++) {
            // Look for key (press|down|up) event switch
            if(keys[i].indexOf && keys[i].indexOf("key") > -1) {
                keyEvent = keys[i];
                continue;
            }
            if(!keys[i].indexOf || !keys[i].indexOf("Key")) {
                trimmedKeys.push(keys[i]);
                continue;
            }
            event[keys[i]] = true;
        }
        for(var i=0; i<trimmedKeys.length;i++) {
            event.charCode = trimmedKeys[i];
            node.simulate(keyEvent, event);
        }
    });

    // Update the textarea text from the contenteditable div we just changed.
    UpdateTextArea(id);
}
    TriggerKeyPressBehat("'.$editorid.'", ['.$keys.']);';
        $js = $this->get_js_update_textarea() . $js;
        $this->getSession()->executeScript($js);
    }

    /**
     * Enter text in a stand-alone ousupsub field.
     *
     * @Given /^I enter the text "([^"]*)" in the "([^"]*)" ousupsub editor$/
     * @throws ElementNotFoundException Thrown by behat_base::find
     * @param string $text
     * @param string $field
     */
    public function enter_text_in_the_ousupsub_editor($text, $fieldlocator) {
        // NodeElement.keyPress simply doesn't work.
        if (!$this->running_javascript()) {
            throw new coding_exception('Entering text requires javascript.');
        }
        // We delegate to behat_form_field class, it will
        // guess the type properly.
        $field = behat_field_manager::get_form_field_from_label($fieldlocator, $this);

        $editorid = $this->find_field($fieldlocator)->getAttribute('id');

        // Trigger the key press through javascript.
        $js = '
    function EnterTextBehat (id, text) {
    // Only works in chrome.
    var target = document.getElementById(id + "editable");
    // https://stackoverflow.com/questions/39947875/as-of-chrome-53-how-to-add-text-as-if-a-trusted-textinput-event-was-dispatched
    target.focus();
    document.execCommand("insertText", false, "' . $text . '");
    // Update the textarea text from the contenteditable div we just changed.
    UpdateTextArea(id);
}
    EnterTextBehat("'.$editorid.'", "'.$text.'");';
        $js = $this->get_js_update_textarea() . $js;
        $this->getSession()->executeScript($js);

    }

    /**
     * Paste text in a stand-alone ousupsub field.
     *
     * @Given /^I paste the text "([^"]*)" in the "([^"]*)" ousupsub editor$/
     * @throws ElementNotFoundException Thrown by behat_base::find
     * @param string $text
     * @param string $field
     */
    public function paste_text_in_the_ousupsub_editor($text, $fieldlocator) {
        // NodeElement.keyPress simply doesn't work.
        if (!$this->running_javascript()) {
            throw new coding_exception('Pasting text requires javascript.');
        }
        // We delegate to behat_form_field class, it will
        // guess the type properly.
        $field = behat_field_manager::get_form_field_from_label($fieldlocator, $this);

        $editorid = $this->find_field($fieldlocator)->getAttribute('id');

        // Trigger the key press through javascript.
        // The clibpoardData object is not created correctly in chrome. Pass our own.
        $js = '
    function ClipboardData() {}
ClipboardData.prototype = {
    data: null,
    types: [],

    getData: function() {
        return this.data;
    },

    setData: function(mimeType, data) {
        this.types.push(mimeType);
        this.data = data;
    }
}

function PasteTextBehat (id, text) {
    // Would use ClipboardEvent but in chrome it instantiates with a null clipboardData object
    // that you cannot override.
    var target = document.getElementById(id + "editable");
    var evt = document.createEvent("TextEvent");
    evt.initEvent ("paste", true, true, window, text, 0, "en-US");
    evt.clipboardData = new ClipboardData();
    evt.clipboardData.setData("text/html", text);
    target.focus();
    target.dispatchEvent(evt);
    // Update the textarea text from the contenteditable div we just changed.
    UpdateTextArea(id);
}
    PasteTextBehat("'.$editorid.'", "'.$text.'");';
        $js = $this->get_js_update_textarea() . $js;
        $this->getSession()->executeScript($js);

    }

    /**
     * Select the first button in a stand-alone ousupsub field.
     *
     * @Given /^I select and click the first button in the "([^"]*)" ousupsub editor$/
     * @param string $text
     * @param string $field
     */
    public function select_and_click_first_button_in_the_ousupsub_editor($fieldlocator) {
        // NodeElement.keyPress simply doesn't work.
        if (!$this->running_javascript()) {
            throw new coding_exception('Pasting text requires javascript.');
        }
        // We delegate to behat_form_field class, it will
        // guess the type properly.
        $field = behat_field_manager::get_form_field_from_label($fieldlocator, $this);

        $editorid = $this->find_field($fieldlocator)->getAttribute('id');

        // Trigger the key press through javascript.
        // The clibpoardData object is not created correctly in chrome. Pass our own.
        $js = '
function SelectAndClickFirstButtonBehat (id) {
    var editor = GetEditor(id);
    var button = editor.toolbar.all(\'button[tabindex="0"]\').item(0)
    button.focus();
    editor._tabFocus = button;
    document.activeElement.click();
}
    SelectAndClickFirstButtonBehat("'.$editorid.'");';
        $js = $this->get_js_get_editor() . $js;
        $this->getSession()->executeScript($js);

    }

    /**
     * Press the superscript key in an ousupsub field.
     *
     * @Given /^I press the superscript key in the "([^"]*)" ousupsub editor$/
     */
    public function i_press_superscript_key_in_the_ousupsub_edito($fieldlocator) {
        $this->execute('behat_editor_ousupsub::press_key_in_the_ousupsub_editor',
                array('\'keypress\', 94', 'Input'));
    }

    /**
     * Press the subscript key in an ousupsub field.
     *
     * @Given /^I press the subscript key in the "([^"]*)" ousupsub editor$/
     */
    public function i_press_subscript_key_in_the_ousupsub_edito($fieldlocator) {
        $this->execute('behat_editor_ousupsub::press_key_in_the_ousupsub_editor',
                array('\'keypress\', 95', 'Input'));
    }

    /**
     * Press the up arrow key in an ousupsub field.
     *
     * @Given /^I press the up arrow key in the "([^"]*)" ousupsub editor$/
     */
    public function i_press_up_arrow_key_in_the_ousupsub_edito($fieldlocator) {
        $this->execute('behat_editor_ousupsub::press_key_in_the_ousupsub_editor',
                array('38', 'Input'));
    }

    /**
     * Press the down arrow key in a stand-alone ousupsub field.
     *
     * @Given /^I press the down arrow key in the "([^"]*)" ousupsub editor$/
     */
    public function i_press_down_arrow_key_in_the_ousupsub_edito($fieldlocator) {
        $this->execute('behat_editor_ousupsub::press_key_in_the_ousupsub_editor',
                array('40', 'Input'));
    }

    /**
     * Press the undo key in an ousupsub field.
     *
     * @Given /^I press the undo key in the "([^"]*)" ousupsub editor$/
     */
    public function i_press_undo_key_in_the_ousupsub_edito($fieldlocator) {
        $this->execute('behat_editor_ousupsub::press_key_in_the_ousupsub_editor',
                array('\'ctrlKey\', 90', 'Input'));
    }

    /**
     * Press the redo key in an ousupsub field.
     *
     * @Given /^I press the redo key in the "([^"]*)" ousupsub editor$/
     */
    public function i_press_redo_key_in_the_ousupsub_edito($fieldlocator) {
        $this->execute('behat_editor_ousupsub::press_key_in_the_ousupsub_editor',
                array('\'ctrlKey\', 89', 'Input'));
    }

    /**
     * Returns a javascript helper method to update the textarea text from the contenteditable div
     * and trigger required key and html events for the editor.
     *
     * @method UpdateTextArea
     * @param {String} id
     */
    protected function get_js_update_textarea() {
        $js = $this->get_js_get_editor();
        $js .= '
function UpdateTextArea (id) {
    var editor = GetEditor(id);
    editor.updateOriginal();
    editor.fire("ousupsub:selectionchanged");
    if ("createEvent" in document) {
        var evt = document.createEvent("HTMLEvents");
        evt.initEvent("change", false, true);
        editor._getEditorNode().dispatchEvent(evt);
    }
    else {
        editor._getEditorNode().fireEvent("onchange");
    }
}';
        return $js;
    }

    /**
     * Returns a javascript helper method to update the textarea text from the contenteditable div
     * and trigger required key and html events for the editor.
     *
     * @method UpdateTextArea
     * @param {String} id
     */
    protected function get_js_get_editor() {
        $js = '
function GetEditor (id) {
    var editor;
    if (typeof editor_ousupsub !== "undefined") {
        // For testing standalone.
        editor = editor_ousupsub.getEditor(id);
    } else {
        // For testing in Moodle.
        YUI().use("moodle-editor_ousupsub-editor", function(Y) {
            editor = Y.M.editor_ousupsub.getEditor(id);
        });
    }
    return editor;
}';
        return $js;
    }
}
