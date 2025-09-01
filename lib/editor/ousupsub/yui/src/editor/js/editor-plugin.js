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
 * ousupsub editor plugin.
 *
 * @module moodle-editor_ousupsub-editor
 * @submodule plugin-base
 * @package    editor_ousupsub
 * @copyright  2014 Andrew Nicols
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * A Plugin for the ousupsub Editor used in Moodle.
 *
 * This class should not be directly instantiated, and all Editor plugins
 * should extend this class.
 *
 * @namespace M.editor_ousupsub
 * @class EditorPlugin
 * @main
 * @constructor
 * @uses M.editor_ousupsub.EditorPluginButtons
 */

function EditorPlugin() {
    EditorPlugin.superclass.constructor.apply(this, arguments);
}

var DISABLED = 'disabled',
    HIGHLIGHT = 'highlight',
    GROUPSELECTOR = '.ousupsub_group.',
    GROUP = '_group';

Y.extend(EditorPlugin, Y.Base, {
    /**
     * The name of the current plugin.
     *
     * @property name
     * @type string
     */
    name: null,

    /**
     * The name of the command to execute when the button is clicked.
     *
     * @property exec
     * @type string
     */
    exec: null,

    /**
     * A Node reference to the editor.
     *
     * @property editor
     * @type Node
     */
    editor: null,

    /**
     * A Node reference to the editor toolbar.
     *
     * @property toolbar
     * @type Node
     */
    toolbar: null,

    /**
     * Event Handles to clear on plugin destruction.
     *
     * @property _eventHandles
     * @private
     */
    _eventHandles: null,

    /**
     * All of the buttons that belong to this plugin instance.
     *
     * Buttons are stored by button name.
     *
     * @property buttons
     * @type object
     */
    buttons: null,

    /**
     * A list of each of the button names.
     *
     * @property buttonNames
     * @type array
     */
    buttonNames: null,

    /**
     * A read-only view of the current state for each button. Mappings are stored by name.
     *
     * Possible states are:
     * <ul>
     * <li>{{#crossLink "M.editor_ousupsub.EditorPluginButtons/ENABLED:property"}}{{/crossLink}}; and</li>
     * <li>{{#crossLink "M.editor_ousupsub.EditorPluginButtons/DISABLED:property"}}{{/crossLink}}.</li>
     * </ul>
     *
     * @property buttonStates
     * @type object
     */
    buttonStates: null,

    /**
     * The state for a disabled button.
     *
     * @property DISABLED
     * @type Number
     * @static
     * @value 0
     */
    DISABLED: 0,

    /**
     * The state for an enabled button.
     *
     * @property ENABLED
     * @type Number
     * @static
     * @value 1
     */
    ENABLED: 1,

    /**
     * The list of Event Handlers for buttons.
     *
     * @property _buttonHandlers
     * @protected
     * @type array
     */
    _buttonHandlers: null,

    /**
     * A textual description of the primary keyboard shortcut for this
     * plugin.
     *
     * This will be null if no keyboard shortcut has been registered.
     *
     * @property _primaryKeyboardShortcut
     * @protected
     * @type String
     * @default null
     */
    _primaryKeyboardShortcut: null,

    /**
     * An list of handles returned by setTimeout().
     *
     * The keys will be the buttonName of the button, and the value the handles.
     *
     * @property _highlightQueue
     * @protected
     * @type Object
     * @default null
     */
    _highlightQueue: null,

    initializer: function(config) {
        // Set the references to configuration parameters.
        this.name = config.name;
        this.exec = config.exec;
        this.toolbar = config.toolbar;
        this.editor = config.editor;

        // Set up the prototypal properties.
        // These must be set up here because prototypal arrays and objects are copied across instances.
        this.buttons = {};
        this.buttonNames = [];
        this.buttonStates = {};
        this._primaryKeyboardShortcut = [];
        this._buttonHandlers = [];
        this._menuHideHandlers = [];
        this._highlightQueue = {};
        this._eventHandles = [];
        this.addButton(config);
    },

    destructor: function() {
        // Detach all EventHandles.
        new Y.EventHandle(this._eventHandles).detach();
    },

    /**
     * Mark the content ediable content as having been changed.
     *
     * This is a convenience function and passes through to
     * {{#crossLink "M.editor_ousupsub.EditorTextArea/updateOriginal"}}updateOriginal{{/crossLink}}.
     *
     * @method markUpdated
     */
    markUpdated: function() {
        // Save selection after changes to the DOM. If you don't do this here,
        // subsequent calls to restoreSelection() will fail expecting the
        // previous DOM state.
        this.get('host').saveSelection();

        return this.get('host').updateOriginal();
    },

    /**
     * Register an event handle for disposal in the destructor.
     *
     * @method registerEventHandle
     * @param {EventHandle} The Event Handle as returned by Y.on, and Y.delegate.
     */
    registerEventHandle: function(handle) {
        this._eventHandles.push(handle);
    },

    /**
     * Add a button for this plugin to the toolbar.
     *
     * @method addButton
     * @param {object} config The configuration for this button
     * @param {string} [config.iconurl] The URL for the icon. If not specified, then the icon and component will be used instead.
     * @param {string} [config.icon] The icon identifier.
     * @param {string} [config.iconComponent='core'] The icon component.
     * @param {string} [config.keys] The shortcut key that can call this plugin from the keyboard.
     * @param {string} [config.keyDescription] An optional description for the keyboard shortcuts.
     * If not specified, this is automatically generated based on config.keys.
     * If multiple key bindings are supplied to config.keys, then only the first is used.
     * If set to false, then no description is added to the title.
     * @param {string} [config.tags] The tags that trigger this button to be highlighted.
     * @param {boolean} [config.tagMatchRequiresAll=true] Working in combination with the tags parameter, when true
     * every tag of the selection has to match. When false, only one match is needed. Only set this to false when
     * necessary as it is much less efficient.
     * See {{#crossLink "M.editor_ousupsub.EditorSelection/selectionFilterMatches:method"}}{{/crossLink}} for more information.
     * @param {string} [config.title=this.name] The string identifier in the plugin's language file.
     * @param {string} [config.buttonName=this.name] The name of the button. This is used in the buttons object, and if
     * specified, in the class for the button.
     * @param {function} config.callback A callback function to call when the button is clicked.
     * @param {object} [config.callbackArgs] Any arguments to pass to the callback.
     * @return {Node} The Node representing the newly created button.
     */
    addButton: function(config) {
        var group = this.get('group'),
            pluginname = this.name,
            buttonClass = 'ousupsub_' + pluginname + '_button',
            button,
            host = this.get('host');

        if (config.exec) {
            buttonClass = buttonClass + '_' + config.exec;
        }

        if (!config.buttonName) {
            // Set a default button name - this is used as an identifier in the button object.
            config.buttonName = config.exec || pluginname;
        } else {
            buttonClass = buttonClass + '_' + config.buttonName;
        }
        config.buttonClass = buttonClass;

        // Normalize icon configuration.
        config = this._normalizeIcon(config);

        if (!config.title) {
            config.title = 'pluginname';
        }
        var title = M.util.get_string(pluginname, 'editor_ousupsub');

        // Create the actual button.
        var icon = '';
        if (config.iconurl) {
            icon = '<img class="icon" aria-hidden="true" role="presentation" width="16" height="16" src="' +
            config.iconurl + '" />';
        }
        button = Y.Node.create('<button type="button" class="' + buttonClass + '" tabindex="-1">' +
                    icon + '</button>');
        button.setAttribute('title', title);

        // Append it to the group.
        group.append(button);

        var currentfocus = this.toolbar.getAttribute('aria-activedescendant');
        if (!currentfocus) {
            // Initially set the first button in the toolbar to be the default on keyboard focus.
         // Initially set the first button in the toolbar to be the default on keyboard focus.
            button.setAttribute('tabindex', '0');
            this.toolbar.setAttribute('aria-activedescendant', button.generateID());
            this.get('host')._tabFocus = button;
        }
        // Normalize the callback parameters.
        if (!config.callback) {
            config.callback = this._applyTextCommand;
        }
        config.callback = Y.rbind(this._callbackWrapper, this, config.callback);

        // Add the standard click handler to the button.
        this._buttonHandlers.push(
            this.toolbar.delegate('click', config.callback, '.' + buttonClass, this)
        );

        // Handle button click via shortcut key.
        if (config.keys) {
            if (typeof config.keyDescription !== 'undefined') {
                // A keyboard shortcut description was specified - use it.
                this._primaryKeyboardShortcut[buttonClass] = config.keyDescription;
            }
            this._addKeyboardListener(config.callback, config.keys, buttonClass);

            if (this._primaryKeyboardShortcut[buttonClass]) {
                // If we have a valid keyboard shortcut description, then set it with the title.
                button.setAttribute('title', M.util.get_string('plugin_title_shortcut', 'editor_ousupsub', {
                        title: title,
                        shortcut: this._primaryKeyboardShortcut[buttonClass]
                    }));
            }
        }

        // Handle highlighting of the button.
        if (config.tags) {
            var tagMatchRequiresAll = true;
            if (typeof config.tagMatchRequiresAll === 'boolean') {
                tagMatchRequiresAll = config.tagMatchRequiresAll;
            }
            this._buttonHandlers.push(
                host.on(['ousupsub:selectionchanged', 'change'], function(e) {
                    if (typeof this._highlightQueue[config.buttonName] !== 'undefined') {
                        clearTimeout(this._highlightQueue[config.buttonName]);
                    }
                    // Async the highlighting.
                    this._highlightQueue[config.buttonName] = setTimeout(Y.bind(function(e) {
                        if (host.selectionFilterMatches(config.tags, e.selectedNodes, tagMatchRequiresAll)) {
                            this.highlightButtons(config.buttonName);
                        } else {
                            this.unHighlightButtons(config.buttonName);
                        }
                    }, this, e), 0);
                }, this)
            );
        }

        // Add the button reference to the buttons array for later reference.
        this.buttonNames.push(config.buttonName);
        this.buttons[config.buttonName] = button;
        this.buttonStates[config.buttonName] = this.ENABLED;
        return button;
    },

    /**
     * Normalize and sanitize the configuration variables relating to callbacks.
     *
     * @method _normalizeCallback
     * @param {object} config
     * @param {function} config.callback A callback function to call when the button is clicked.
     * @param {object} [config.callbackArgs] Any arguments to pass to the callback.
     * @param {object} [inheritFrom] A parent configuration that this configuration may inherit from.
     * @return {object} The normalized configuration
     * @private
     */
    _normalizeCallback: function(config, inheritFrom) {
        if (config._callbackNormalized) {
            // Return early if the callback has already been normalized.
            return config;
        }

        if (!inheritFrom) {
            // Create an empty inheritFrom to make life easier below.
            inheritFrom = {};
        }

        // We wrap the callback in function to prevent the default action, check whether the editor is
        // active and focus it, and then mark the field as updated.
        config._callback = config.callback || inheritFrom.callback;
        config.callback = Y.rbind(this._callbackWrapper, this, this._applyTextCommand, config.callbackArgs);

        config._callbackNormalized = true;

        return config;
    },

    /**
     * Normalize and sanitize the configuration variables relating to icons.
     *
     * @method _normalizeIcon
     * @param {object} config
     * @param {string} [config.iconurl] The URL for the icon. If not specified, then the icon and component will be used instead.
     * @param {string} [config.icon] The icon identifier.
     * @param {string} [config.iconComponent='core'] The icon component.
     * @return {object} The normalized configuration
     * @private
     */
    _normalizeIcon: function(config) {
        if (config.icon && !config.iconurl) {
            // The default icon component.
            if (!config.iconComponent) {
                config.iconComponent = 'core';
            }
            config.iconurl = M.util.image_url(config.icon, config.iconComponent);
        }

        return config;
    },

    /**
     * A wrapper in which to run the callbacks.
     *
     * This handles common functionality such as:
     * <ul>
     *  <li>preventing the default action; and</li>
     *  <li>focusing the editor if relevant.</li>
     * </ul>
     *
     * @method _callbackWrapper
     * @param {EventFacade} e
     * @param {Function} callback The function to call which makes the relevant changes.
     * @param {Array} [callbackArgs] The arguments passed to this callback.
     * @return {Mixed} The value returned by the callback.
     * @private
     */
    _callbackWrapper: function(e, callback, callbackArgs) {
        e.preventDefault();

        if (!this.isEnabled()) {
            // Exit early if the plugin is disabled.
            return;
        }

        var creatorButton = e.currentTarget.ancestor('button', true);

        if (creatorButton && creatorButton.hasAttribute(DISABLED)) {
            // Exit early if the clicked button was disabled.
            return;
        }

        if (!(YUI.Env.UA.android || this.get('host').isActive())) {
            // We must not focus for Android here, even if the editor is not active because the keyboard auto-completion
            // changes the cursor position.
            // If we save that change, then when we restore the change later we get put in the wrong place.
            // Android is fine to save the selection without the editor being in focus.
            this.get('host').focus();
        }

        // Save the selection.
        this.get('host').saveSelection();

        // Build the arguments list, but remove the callback we're calling.
        var args = [e, callbackArgs];

        // Restore selection before making changes.
        this.get('host').restoreSelection();

        // Actually call the callback now.
        return callback.apply(this, args);
    },

    /**
     * Add a keyboard listener to call the callback.
     *
     * The keyConfig will take either an array of keyConfigurations, in
     * which case _addKeyboardListener is called multiple times; an object
     * containing an optional eventtype, optional container, and a set of
     * keyCodes, or just a string containing the keyCodes. When keyConfig is
     * not an object, it is wrapped around a function that ensures that
     * only the expected key modifiers were used. For instance, it checks
     * that space+ctrl is not triggered when the user presses ctrl+shift+space.
     * When using an object, the developer should check that manually.
     *
     * @method _addKeyboardListener
     * @param {function} callback
     * @param {array|object|string} keyConfig
     * @param {string} [keyConfig.eventtype=key] The type of event
     * @param {string} [keyConfig.container=.editor_ousupsub_content] The containing element.
     * @param {string} keyConfig.keyCodes The keycodes to user for the event.
     * @private
     *
     */
    _addKeyboardListener: function(callback, keyConfig, buttonName) {
        var eventtype = 'key',
            container = CSS.EDITORWRAPPER,
            keys,
            handler,
            modifier;

        if (Y.Lang.isArray(keyConfig)) {
            // If an Array was specified, call the add function for each element.
            Y.Array.each(keyConfig, function(config) {
                this._addKeyboardListener(callback, config, buttonName);
            }, this);

            return this;

        } else if (typeof keyConfig === "object") {
            if (keyConfig.eventtype) {
                eventtype = keyConfig.eventtype;
            }

            if (keyConfig.container) {
                container = keyConfig.container;
            }

            // Must be specified.
            keys = keyConfig.keyCodes;
            handler = callback;

        } else {
            modifier = '';
            keys = keyConfig;
            if (typeof this._primaryKeyboardShortcut[buttonName] === 'undefined') {
                this._primaryKeyboardShortcut[buttonName] = this._getDefaultMetaKeyDescription(keyConfig);
            }
            // Wrap the callback into a handler to check if it uses the specified modifiers, not more.
            handler = Y.bind(function(modifiers, e) {
                    callback.apply(this, [e]);
            }, this, [modifier]);
        }

        this._buttonHandlers.push(
            this.editor.delegate(
                eventtype,
                handler,
                keys,
                container,
                this
            )
        );

    },

    /**
     * Checks if a key event was strictly defined for the modifiers passed.
     *
     * @method _eventUsesExactKeyModifiers
     * @param  {Array} modifiers List of key modifiers to check for (alt, ctrl, meta or shift).
     * @param  {EventFacade} e The event facade.
     * @return {Boolean} True if the event was stricly using the modifiers specified.
     */
    _eventUsesExactKeyModifiers: function(modifiers, e) {
        var exactMatch = true,
            hasKey;

        if (e.type !== 'key') {
            return false;
        }

        hasKey = Y.Array.indexOf(modifiers, 'alt') > -1;
        exactMatch = exactMatch && ((e.altKey && hasKey) || (!e.altKey && !hasKey));
        hasKey = Y.Array.indexOf(modifiers, 'ctrl') > -1;
        exactMatch = exactMatch && ((e.ctrlKey && hasKey) || (!e.ctrlKey && !hasKey));
        hasKey = Y.Array.indexOf(modifiers, 'meta') > -1;
        exactMatch = exactMatch && ((e.metaKey && hasKey) || (!e.metaKey && !hasKey));
        hasKey = Y.Array.indexOf(modifiers, 'shift') > -1;
        exactMatch = exactMatch && ((e.shiftKey && hasKey) || (!e.shiftKey && !hasKey));

        return exactMatch;
    },

    /**
     * Determine if this plugin is enabled, based upon the state of it's buttons.
     *
     * @method isEnabled
     * @return {boolean}
     */
    isEnabled: function() {
        // The first instance of an undisabled button will make this return true.
        var found = Y.Object.some(this.buttonStates, function(button) {
            return (button === this.ENABLED);
        }, this);

        return found;
    },

    /**
     * Enable one button, or all buttons relating to this Plugin.
     *
     * If no button is specified, all buttons are disabled.
     *
     * @method disableButtons
     * @param {String} [button] The name of a specific plugin to enable.
     * @chainable
     */
    disableButtons: function(button) {
        return this._setButtonState(false, button);
    },

    /**
     * Enable one button, or all buttons relating to this Plugin.
     *
     * If no button is specified, all buttons are enabled.
     *
     * @method enableButtons
     * @param {String} [button] The name of a specific plugin to enable.
     * @chainable
     */
    enableButtons: function(button) {
        return this._setButtonState(true, button);
    },

    /**
     * Set the button state for one button, or all buttons associated with this plugin.
     *
     * @method _setButtonState
     * @param {Boolean} enable Whether to enable this button.
     * @param {String} [button] The name of a specific plugin to set state for.
     * @chainable
     * @private
     */
    _setButtonState: function(enable, button) {
        var attributeChange = 'setAttribute';
        if (enable) {
            attributeChange = 'removeAttribute';
        }
        if (button) {
            if (this.buttons[button]) {
                this.buttons[button][attributeChange](DISABLED, DISABLED);
                this.buttonStates[button] = enable ? this.ENABLED : this.DISABLED;
            }
        } else {
            Y.Array.each(this.buttonNames, function(button) {
                this.buttons[button][attributeChange](DISABLED, DISABLED);
                this.buttonStates[button] = enable ? this.ENABLED : this.DISABLED;
            }, this);
        }

        this.get('host').checkTabFocus();
        return this;
    },

    /**
     * Highlight a button, or buttons in the toolbar.
     *
     * If no button is specified, all buttons are highlighted.
     *
     * @method highlightButtons
     * @param {string} [button] If a plugin has multiple buttons, the specific button to highlight.
     * @chainable
     */
    highlightButtons: function(button) {
        return this._changeButtonHighlight(true, button);
    },

    /**
     * Un-highlight a button, or buttons in the toolbar.
     *
     * If no button is specified, all buttons are un-highlighted.
     *
     * @method unHighlightButtons
     * @param {string} [button] If a plugin has multiple buttons, the specific button to highlight.
     * @chainable
     */
    unHighlightButtons: function(button) {
        return this._changeButtonHighlight(false, button);
    },

    /**
     * Highlight a button, or buttons in the toolbar.
     *
     * @method _changeButtonHighlight
     * @param {boolean} highlight true
     * @param {string} [button] If a plugin has multiple buttons, the specific button to highlight.
     * @protected
     * @chainable
     */
    _changeButtonHighlight: function(highlight, button) {
        var method = 'addClass';

        if (!highlight) {
            method = 'removeClass';
        }
        if (button) {
            if (this.buttons[button]) {
                this.buttons[button][method](HIGHLIGHT);
            }
        } else {
            Y.Object.each(this.buttons, function(button) {
                button[method](HIGHLIGHT);
            }, this);
        }

        return this;
    },

    /**
     * Get the default meta key to use with keyboard events.
     *
     * On a Mac, this will be the 'meta' key for Command; otherwise it will
     * be the Control key.
     *
     * @method _getDefaultMetaKey
     * @return {string}
     * @private
     */
    _getDefaultMetaKey: function() {
        if (Y.UA.os === 'macintosh') {
            return 'meta';
        } else {
            return 'ctrl';
        }
    },

    /**
     * Get the user-visible description of the meta key to use with keyboard events.
     *
     * On a Mac, this will be 'Command' ; otherwise it will be 'Control'.
     *
     * @method _getDefaultMetaKeyDescription
     * @return {string}
     * @private
     */
    _getDefaultMetaKeyDescription: function(keyCode) {
        if (Y.UA.os === 'macintosh') {
            return M.util.get_string('editor_command_keycode', 'editor_ousupsub', String.fromCharCode(keyCode).toLowerCase());
        } else {
            return M.util.get_string('editor_control_keycode', 'editor_ousupsub', String.fromCharCode(keyCode).toLowerCase());
        }
    },

    /**
     * Get the standard key event to use for keyboard events.
     *
     * @method _getKeyEvent
     * @return {string}
     * @private
     */
    _getKeyEvent: function() {
        return 'down:';
    },

     /**
      * Apply the given document.execCommand and tidy up the editor dom afterwards.
      *
      * @method _applyTextCommand
      * @private
      * @return void
      */
     _applyTextCommand: function(e) {
         var mode = 0;

         if(e && e.type === 'key') {
             // handled by this._getEditor().textareaKeyboardNavigation(e);
             return;
         }

         this._getEditor()._applyTextCommand(this.exec, mode);
     },

    /**
     * Get the editor object.
     *
     * @method _getEditor
     * @private
     * @return node.
     */
    _getEditor: function(host) {
        if (!host) {
            host = this.get('host');
        }

        return host;
    },

    /**
     * Get the node containing the editor html to be updated.
     *
     * @method _getEditorNode
     * @private
     * @return node.
     */
    _getEditorNode: function(host) {
        return this._getEditor(host).editor._node;
    }

}, {
    NAME: 'editorPlugin',
    ATTRS: {
        /**
         * The editor instance that this plugin was instantiated by.
         *
         * @attribute host
         * @type M.editor_ousupsub.Editor
         * @writeOnce
         */
        host: {
            writeOnce: true
        },

        /**
         * The toolbar group that this button belongs to.
         *
         * When setting, the name of the group should be specified.
         *
         * When retrieving, the Node for the toolbar group is returned. If
         * the group doesn't exist yet, then it is created first.
         *
         * @attribute group
         * @type Node
         * @writeOnce
         */
        group: {
            writeOnce: true,
            getter: function(groupName) {
                var group = this.toolbar.one(GROUPSELECTOR + groupName + GROUP);
                if (!group) {
                    group = Y.Node.create('<div class="ousupsub_group ' +
                            groupName + GROUP + '"></div>');
                    this.toolbar.append(group);
                }

                return group;
            }
        }
    }
});

Y.namespace('M.editor_ousupsub').EditorPlugin = EditorPlugin;
