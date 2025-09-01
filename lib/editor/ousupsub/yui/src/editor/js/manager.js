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
 * The manager for the OUSupSub Editor.
 *
 * @module     moodle-editor_ousupsub-editor
 * @submodule  manager
 * @package    editor_ousupsub
 * @copyright  2014 Andrew Nicols <andrew@nicols.co.uk>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @main       moodle-editor_ousupsub-editor
 */

/**
 * @module moodle-editor_ousupsub-editor
 */

/**
 * The manager for the OUSupSub editor.
 *
 * @class editor_ousupsub
 */

Y.M.editor_ousupsub = Y.M.editor_ousupsub || {};

/**
 * List of editor_ousupsub instances. Intentionally placed on window.M, not
 * something in the namespace, so we can be sure it is really global.
 */
M = M || {};
M.editor_ousupsub = M.editor_ousupsub || {};
M.editor_ousupsub._instances = M.editor_ousupsub._instances || {};

/**
 * Add a reference to an editor.
 * Note: This is an internal method which should only be called by the editor itself.
 *
 * @method addEditorReference
 * @param {String} name The name of the editor instance to add
 * @private
 */
Y.M.editor_ousupsub.addEditorReference = function(name, reference) {
    Y.log("Registering a new ousupsub editor: " + name, 'debug', LOGNAME);
    if (typeof M.editor_ousupsub._instances[name] === 'undefined') {
        M.editor_ousupsub._instances[name] = reference;
    } else {
        Y.log("An ousupsub editor with the name '" + name + "' already exists. Unable to add.", 'warn', LOGNAME);
    }

    return Y.M.editor_ousupsub;
};

/**
 * Create a new editor using simple options.
 *
 * @method createEditor
 * @param {String} id of the textarea to turn into an editor.
 * @param {String} type 'superscript', 'subscript' or 'both'.
 * @return {M.editor_ousupsub.Editor} The newly created editor instance
 */
Y.M.editor_ousupsub.createEditorSimple = function(id, type) {
    var plugins = [];
    if (type === 'both' || type === 'superscript') {
        plugins.push({"name": "superscript", "params": []});
    }
    if (type === 'both' || type === 'subscript') {
        plugins.push({"name": "subscript", "params": []});
    }

    Y.M.editor_ousupsub.createEditor(
            {"elementid" : id, "content_css" : "", "contextid" : 0, "language" : "en",
             "directionality" : "ltr", "plugins" : [{"group" : "style1", "plugins" : plugins}],"pageHash" : ""});
};

/**
 * Create a new editor using the specified configuration.
 *
 * @method createEditor
 * @param {Object} config See the attributes for {{#crossLink
 * "M.editor_ousupsub.Editor"}}{{/crossLink}} for configuration options. The
 * elementid provided will be used as the name of this editor within
 * the editor Manager.
 * @return {M.editor_ousupsub.Editor} The newly created editor instance
 */
Y.M.editor_ousupsub.createEditor = function(config) {

    var instance = new Y.M.editor_ousupsub.Editor(config);
    Y.M.editor_ousupsub.fire('editor_ousupsub:created', {
        id: instance.get('elementid'),
        instance: instance
    });
    return instance;
};

/**
 * Get the requested Editor instance.
 *
 * @method getEditor
 * @param {String} name The name of the editor instance to retrieve
 * @return {M.editor_ousupsub.Editor} The requested editor instance
 */
Y.M.editor_ousupsub.getEditor = function(name) {
    return M.editor_ousupsub._instances[name];
};

/**
 * Remove the reference for an editor.
 *
 * @method removeEditorReference
 * @param {String} name The name of the editor instance to remove
 */
Y.M.editor_ousupsub.removeEditor = function(name) {
    var instance = Y.M.editor_ousupsub.getEditor(name);
    if (instance) {
        instance.destroy();
        this.fire('editor_ousupsub:removed', {
            id: name
        });
    }
    return Y.M.editor_ousupsub;
};

/**
 * Remove the reference for an editor.
 * Note: This is an internal method which should only be called by the editor itself.
 *
 * @method removeEditorReference
 * @param {String} name The name of the editor instance to remove
 * @private
 */
Y.M.editor_ousupsub.removeEditorReference = function(name) {
    if (Y.M.editor_ousupsub.getEditor(name)) {
        delete M.editor_ousupsub._instances[name];
    }
};

/**
 * Add the supplied function to the manager using the specified name.
 *
 * @method addMethod
 * @param {String} name The name to store the method on within the editor manager.
 * @param {Function} fn The function to be added.
 * @param {Object} [context] The context to apply the function with. If not specified, the Editor itself is used.
 */
Y.M.editor_ousupsub.addMethod = function(name, fn) {
    if (typeof this[name] !== 'undefined') {
        Y.log('Overwriting existing method: ' + name, 'warn', LOGNAME);
    }

    Y.M.editor_ousupsub[name] = function() {
        var ret = [],
            args = arguments;

        Y.Object.each(M.editor_ousupsub._instances, function(editor) {
            var result = fn.apply(editor, args);

            if (result !== undefined && result !== editor) {
                ret[ret.length] = result;
            }
        });

        // If we received a set of results, return them, otherwise make this method chainable.
        return ret.length ? ret : this;
    };
};

Y.augment(Y.M.editor_ousupsub, Y.EventTarget);

Y.Array.each(['saveSelection', 'updateFromTextArea', 'updateOriginal', 'cleanEditorHTML', 'destroy'], function(name) {
    Y.M.editor_ousupsub.addMethod(name, Y.M.editor_ousupsub.Editor.prototype[name]);
});
