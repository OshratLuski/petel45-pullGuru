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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Apply or remove styles from selected text in the TinyMCE editor.
 *
 * @module     tiny_styles/styleactions
 * @copyright  2025 Oshrat Luski <oshrat.luski@weizmann.ac.il>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Applies or toggles a list of classes on a div wrapping the selected text.
 *
 * @param {TinyMCE.Editor} editor - The TinyMCE editor instance
 * @param {string[]} classList - Array of CSS classes to apply
 */
export const applyStyle = (editor, classList) => {
    const selection = editor.selection;

    if (!selection || selection.isCollapsed()) {
        return;
    }

    // Normalize classList: handle both array and string
    const classes = Array.isArray(classList) ? classList : classList.split(' ');
    const styleName = 'customstyle_' + classes.join('_');

    // Register the style formatter if not already registered
    editor.formatter.register(styleName, {
        block: 'div',
        classes: classes,
        remove_similar: true,
    });

    // Check if the style is already applied
    const selectedNode = selection.getNode();
    const existingDiv = editor.dom.getParent(selectedNode, (node) =>
        node.nodeName === 'DIV' &&
        node.classList &&
        classes.every(cls => node.classList.contains(cls))
    );

    if (existingDiv) {
        editor.formatter.remove(styleName);
    } else {
        editor.formatter.apply(styleName);
    }
};

