/* eslint-disable no-tabs */
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
 * Commands helper for the Moodle tiny_hotwords plugin.
 *
 * @module      tiny_hotwords/commands
 * @copyright   2025 Devlion <devlion@devlion.co>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import {getButtonImage} from 'editor_tiny/utils';
import {get_string as getString} from 'core/str';
import {
	component, hotwordsButtonName, hotwordsDeleteName
} from './common';
import Modal from 'core/modal';
import ModalEvents from 'core/modal_events';
import Templates from 'core/templates';
import Ajax from 'core/ajax';
import Notification from 'core/notification';
import Fragment from 'core/fragment';
import * as Options from './options';
import $ from 'jquery';

/**
 * Handle the action for your plugin.
 * @param {TinyMCE.editor} editor The tinyMCE editor instance.
 */
const handleAction = async(editor) => {

	const currentSelection = editor.selection.getContent({format: 'text'});

	let selectionnode = editor.selection.getNode();
	selectionnode = editor.dom.getParent(selectionnode, 'a[data-toggle="hotword"]');
	let prevcontent = selectionnode ? selectionnode.dataset.text : '';
	const isExpanded = !editor.selection.isCollapsed();

	if (isExpanded && !selectionnode) {
		const match = editor.selection.getContent().match(/data-text="([^"]*)"/);
		let result = match ? match[1] : '';
		const textarea = document.createElement("textarea");
		textarea.innerHTML = result;
		prevcontent = textarea.value;
	}

	const modal = await Modal.create({
		title: await getString('modaltitle', 'tiny_hotwords'),
		body: await Templates.render('tiny_hotwords/modal'),
	});
	await modal.getRoot().find('.modal-dialog').css({
		'width': '60vw',
		'max-width': '60vw'
	});
	modal.show();
	modal.getRoot().on(ModalEvents.hidden, function() {
		modal.destroy();
	});

	Fragment.loadFragment('tiny_hotwords', 'form', Options.getContextId(editor),
		{
			contextId: Options.getContextId(editor),
			existingcode: prevcontent,
			urltext: currentSelection || selectionnode?.innerText || ''
		}
	).done(async function(html, js) {
		Templates.replaceNodeContents($('.tiny_hotwords-wrap'), html, js);
		// eslint-disable-next-line no-console
		console.log(document.querySelector('#embedhotwordsform #id_submitbutton'));
		document.querySelector('#embedhotwordsform #id_submitbutton').addEventListener('click', (e) => {
			// eslint-disable-next-line no-console
			console.log('e');
			// eslint-disable-next-line no-console
			console.log(e);
			e.preventDefault();
			const linktext = document.querySelector('input#id_urltext').value;
			Ajax.call([{
				methodname: 'filter_hotwords_format_hotword', args: {
					content: document.querySelector('textarea#id_content').value,
				}
			}])[0].done(function(response) {
				if (!response.messages) {
					setHotwords(editor, linktext, response.content);
					modal.destroy();
					document.querySelector('#embedhotwordsform')?.remove();
				}
			}).fail(Notification.exception);
		});
	}).fail(Notification.exception);
};

const setHotwords = async(editor, urlText, value) => {
	let selectednode, isExpanded, textToDisplay;

	editor.focus();
	isExpanded = !editor.selection.isCollapsed();

	textToDisplay = urlText.replace(/(<([^>]+)>)/gi, "").trim();
	if (textToDisplay === '') {
		textToDisplay = urlText;
	}

	const timecreated = new Date().getTime();
	const isUpdatingOnlyLink = editor.selection.getNode().dataset.toggle === 'hotword';

	if (isExpanded && !isUpdatingOnlyLink) {
		editor.execCommand('mceInsertLink', false, {
			'href': '#',
			'data-timecreated': timecreated,
			'data-toggle': "hotword",
			'data-text': value,
			'title': textToDisplay,
		});
		const creatednode = selectednode = Array.from(editor.dom.select('a[data-timecreated]'))
			.find(node => node.getAttribute('data-timecreated') === timecreated.toString());
		creatednode.innerText = textToDisplay;
	}

	if (textToDisplay && isUpdatingOnlyLink) {
		selectednode = Array.from(editor.dom.select('a[data-timecreated]'))
			.find(node => node.getAttribute('data-timecreated') === timecreated.toString());
		selectednode = selectednode ? selectednode : editor.selection.getNode();
		if (!selectednode) {
			return;
		}
		selectednode.setAttribute('data-toggle', "hotword");
		selectednode.setAttribute('data-text', value);
		selectednode.setAttribute('title', textToDisplay);
		selectednode.innerText = textToDisplay;
	}
	if (!isExpanded && !isUpdatingOnlyLink) {
		const hotwords = document.createElement('a');
		hotwords.innerText = textToDisplay;
		hotwords.setAttribute('href', '#');
		hotwords.setAttribute('data-toggle', 'hotword');
		hotwords.setAttribute('data-text', value);
		hotwords.setAttribute('title', textToDisplay);
		editor.selection.setNode(hotwords);
		editor.selection.select(hotwords);
	}
};


const unlink = (editor) => {
	editor.execCommand('unlink');

	// Mark the editor as updated.
	editor.nodeChanged();
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
	const [buttonImage, buttonDeleteImage, buttonTitle, deleteButtonTitle,] = await Promise.all([
		getButtonImage('icon', component),
		getButtonImage('icon2', component),
		await getString('title', 'tiny_hotwords'),
		await getString('deletetitle', 'tiny_hotwords')
	]);
	return (editor) => {
		// Register the Moodle SVG as an icon suitable for use as a TinyMCE toolbar button.
		editor.ui.registry.addIcon('icon', buttonImage.html);
		editor.ui.registry.addButton(hotwordsButtonName, {
			icon: 'icon', tooltip: buttonTitle, onAction: () => handleAction(editor),
		});

		editor.ui.registry.addIcon('icon2', buttonDeleteImage.html);
		editor.ui.registry.addButton(hotwordsDeleteName, {
			icon: 'icon2', tooltip: deleteButtonTitle, onAction: () => unlink(editor),
		});
	};
};
