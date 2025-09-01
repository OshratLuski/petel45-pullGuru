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
 * Commands helper for the Moodle tiny_bdi plugin.
 *
 * @module      tiny_bdi/commands
 * @copyright   2025 Devlion <devlion@devlion.co>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import {getButtonImage} from 'editor_tiny/utils';
import {get_string as getString} from 'core/str';
import {
    component,
    icon,
    buttonName
} from './common';


/**
 * Handle the action for your plugin.
 * @param {TinyMCE.editor} editor The tinyMCE editor instance.
 */
const handleAction = (editor) => {
    const selection = editor.selection;

    // If there's no selection or it's collapsed (i.e., just a caret)
    if (!selection || selection.isCollapsed()) {
        return;
    }

    // Get the currently selected content
    const selectedContent = selection.getContent({ format: 'html' });

    const wrapper = document.createElement('div');
    wrapper.innerHTML = selectedContent.trim();
    const firstChild = wrapper.firstElementChild;

    const isAlreadyBDI =
      firstChild &&
      (firstChild.tagName === 'SPAN' && firstChild.className === 'bdi');

    if (isAlreadyBDI) {
        // Remove the BDI wrapper
        const container = document.createElement('div');
        container.innerHTML = selectedContent;

        const bdiSpans = container.querySelectorAll('span.bdi[dir="auto"]');

        bdiSpans.forEach(span => {
            const textNode = document.createTextNode(span.textContent);
            span.replaceWith(textNode);
        });
        selection.setContent(container.innerHTML);
    } else {
        // Add BDI wrapper
        const wrapped = `<span dir="auto" class="bdi">${selectedContent}</span>`;
        selection.setContent(wrapped);
    }
};

/**
 * Get the setup function for the buttons.
 *
 * This is performed in an async function which ultimately returns the registration function as the
 * Tiny.AddOnManager.Add() function does not support async functions.
 *
 * @returns {function} The registration function to call within the Plugin.add function.
 */
export const getSetup = async() => {
    const [
        buttonImage,
        buttonTitle
    ] = await Promise.all([
        getButtonImage('icon', component),
        getString('pluginname', component)
    ]);

    return (editor) => {
        // Register the Moodle SVG as an icon suitable for use as a TinyMCE toolbar button.
        editor.ui.registry.addIcon(icon, buttonImage.html);
        editor.ui.registry.addButton(buttonName, {
            icon: icon, tooltip: buttonTitle, onAction: () => handleAction(editor),
        });
    };
};
