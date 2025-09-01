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
 * Commands helper for the Moodle tiny_insertforum plugin.
 *
 * @module      tiny_insertforum/commands
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
import Modal from 'core/modal';
import ModalEvents from 'core/modal_events';
import Templates from 'core/templates';
import Ajax from 'core/ajax';

/**
 * Handle the action for your plugin.
 * @param {TinyMCE.editor} editor The tinyMCE editor instance.
 */
const handleAction = async (editor) => {

    const data = await Ajax.call([{
        methodname: 'tiny_insertforum_get_modal_data',
        args: {courseid: M.cfg.courseId},
    }])[0];

    const modal = await Modal.create({
        title: await getString('pluginname', 'tiny_insertforum'),
        body: await Templates.render('tiny_insertforum/modal', {
            forums: data.forums,
            groups: data.groups,
            groupings: data.groupings,
        }),
    });
    modal.show();
    modal.getRoot().on(ModalEvents.hidden, function() {
        document.querySelector('#insertforum-modal')?.remove();
    });
    document.querySelector('#insertforum_submit').addEventListener('click', () => {
        const forumid = document.querySelector('#insertforum_forum').value;
        const groupid = document.querySelector('#insertforum_group').value;
        const groupingid = document.querySelector('#insertforum_grouping').value;
        const postcount = document.querySelector('#insertforum_posts').value;
        editor.insertContent(`[[forum(${forumid},${groupid},${groupingid},${postcount})]]`);

        modal.destroy();
        document.querySelector('#insertforum-modal')?.remove();
    });
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
        buttonTitle,
    ] = await Promise.all([
        getButtonImage('icon', component),
        getString('pluginname', component),
    ]);

    return (editor) => {
        // Register the Moodle SVG as an icon suitable for use as a TinyMCE toolbar button.
        editor.ui.registry.addIcon(icon, buttonImage.html);
        editor.ui.registry.addButton(buttonName, {
            icon: icon, tooltip: buttonTitle, onAction: () => handleAction(editor),
        });
    };
};
