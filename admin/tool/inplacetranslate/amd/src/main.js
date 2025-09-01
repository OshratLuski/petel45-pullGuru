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
 * Javascript main event handler.
 *
 * @module     tool/inplacetranslate
 * @copyright  2024 Devlionco <info@devlion.co>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @since      4.1
 */

import {call as fetchMany} from 'core/ajax';
import {exception as displayException} from 'core/notification';
import Templates from 'core/templates';
import {debounce} from 'core/utils';
import ModalFactory from 'core/modal_factory';
import ModalEvents from 'core/modal_events';
import {get_string as getString} from 'core/str';

const DEBOUNCE_TIMER = 500;

let oldTranslate;

const Selectors = {
    targets: {
        menu: 'inplacetranslate_menu',
        menuToggler: 'inplacetranslate_menu_toggler',
        search: 'inplacetranslate_search',
        clearSearchBtn: 'inplacetranslate_clear_search_btn',
        items: 'inplacetranslate-item',
        translatesForm: 'translates_form',
        pageFooter: 'page-footer'
    },
    elements: {}
};

/**
 * Initialize selectors.
 */
const initSelectors = () => {
    Selectors.elements.menu = document.getElementById(Selectors.targets.menu);
    Selectors.elements.menuToggler = document.getElementById(Selectors.targets.menuToggler);
    Selectors.elements.search = document.getElementById(Selectors.targets.search);
    Selectors.elements.clearSearchBtn = document.getElementById(Selectors.targets.clearSearchBtn);
    Selectors.elements.items = document.querySelectorAll('.' + Selectors.targets.items);
};

/**
 * Get translate.
 * @param {string} query Search query.
 * @returns {any} strings data.
 */
const getTranslate = (query) => fetchMany([{
    methodname: 'tool_inplacetranslate_get_translated_string',
    args: {
        query: query
    }
}])[0];

/**
 * Save translate strings.
 * @param {string} identifier Translated string.
 * @param {string} translateJson JSON example {string: 'some text', lang: 'en'}.
 * @returns {any} strings data.
 */

const setTranslate = (identifier, translateJson) => fetchMany([{
    methodname: 'tool_inplacetranslate_set_translated_string',
    args: {
        identifier: identifier,
        translateJson: translateJson
    }
}])[0];

const clearSearch = () => {
    Selectors.elements.search.value = '';
    Selectors.elements.items.forEach((element) => {
        element.closest('li').classList.add('d-flex');
        element.closest('li').classList.remove('d-none');
    });
};

const showClearSearchBtn = () => {
    Selectors.elements.clearSearchBtn.classList.remove('d-none');
};

const hideClearSearchBtn = () => {
    Selectors.elements.clearSearchBtn.classList.add('d-none');
};

const showSearchResult = (searchQuery) => {
    const htmlRegex = /(<([^>]+)>)/gi;
    Selectors.elements.items.forEach((element) => {
        const text = element.innerHTML.replace(htmlRegex, ' ').trim().toLowerCase();
        const dataset = element.dataset.translate.replace(htmlRegex, ' ').trim().toLowerCase();
        if (text.includes(searchQuery) || dataset.includes(searchQuery)) {
            element.closest('li').classList.add('d-flex');
            element.closest('li').classList.remove('d-none');
        } else {
            element.closest('li').classList.remove('d-flex');
            element.closest('li').classList.add('d-none');
        }
    });
};

const addToggleMenuBtn = () => {
    return Templates.renderForPromise('tool_inplacetranslate/togglemenulink', {})
        .then(({html, js}) => {
            const target = document.querySelector('.footer-link') ? '#page-footer .footer-links' : Selectors.elements.pageFooter;
            Templates.prependNodeContents(target, html, js);
            initSelectors();

            if (Selectors.elements.clearSearchBtn !== null) {
                Selectors.elements.clearSearchBtn.onclick = () => {
                    clearSearch();
                    hideClearSearchBtn();
                };
            }

            if (Selectors.elements.menuToggler !== null) {
                Selectors.elements.menuToggler.onclick = () => {
                    if (Selectors.elements.menuToggler.classList.contains('shown')) {
                        Selectors.elements.menuToggler.classList.remove('shown');
                        document.body.classList.add('inplacetranslate-menu-shown');
                    } else {
                        Selectors.elements.menuToggler.classList.add('shown');
                        document.body.classList.remove('inplacetranslate-menu-shown');
                    }
                };
            }

            if (Selectors.elements.search !== null) {
                Selectors.elements.search.addEventListener('keyup', debounce((event) => {
                    event.preventDefault();
                    const searchQuery = event.target.value.toString().toLowerCase().trim();
                    showSearchResult(searchQuery);
                    showClearSearchBtn();
                }, DEBOUNCE_TIMER));
            }
        });
};

export const init = () => {
    addToggleMenuBtn();
    document.onclick = (event) => {
        if (event.target.closest('.inplacetranslate-item')) {
            const element = event.target.closest('.inplacetranslate-item');
            const identifier = element.dataset.id.toString();
            oldTranslate = event.target.closest('.inplacetranslate-item').dataset.translate;
            // ActiveString = element.querySelector('.translate').innerText;
            getTranslate(identifier).then(result => showModal(result)).catch((error) => displayException(error));
        }
    };
};

const showAlert = () => {
    getString('stringwasupdated', 'tool_inplacetranslate', oldTranslate)
        .then((str) => {
            const alertElement = document.createElement('div');
            alertElement.classList.add('position-fixed', 'alert', 'alert-success', 'alert-dismissible', 'p-3', 'fade', 'show');
            alertElement.setAttribute('role', 'alert');
            alertElement.setAttribute('style', 'top: 15vh; left: 32vw;position: fixed; z-index: 100;');


            // Create the close button element
            const closeButton = document.createElement('button');
            closeButton.classList.add('close');
            closeButton.setAttribute('type', 'button');
            closeButton.setAttribute('data-dismiss', 'alert');

            // Create the span element inside the close button
            const closeSpan = document.createElement('span');
            closeSpan.setAttribute('aria-hidden', 'true');
            closeSpan.textContent = '×';

            // Append the elements
            closeButton.appendChild(closeSpan);
            alertElement.appendChild(document.createTextNode(str));
            alertElement.appendChild(closeButton);

            // Append the alert element to the body
            return document.body.appendChild(alertElement);
        }).catch((error) => displayException(error));

};

/* Const findElementsWithText = (result) => {
    const lowercaseQuery = oldTranslate.toLowerCase();

    const treeWalker = document.createTreeWalker(
        document.body,
        NodeFilter.SHOW_TEXT,
    );


    let currentNode = treeWalker.nextNode();
    let matchingElement;

    // Iterate through all text nodes
    while (currentNode) {
        const parentElement = currentNode.parentElement;
        const nodeText = currentNode.textContent.toLowerCase().trim();

        // Check if the text content of the node is equal to the query
        if (nodeText === lowercaseQuery) {
            // If the parent element is not already in the list, add it
            matchingElement = parentElement;
            let text = matchingElement.innerText;
            matchingElement.style.boxShadow = "inset 0 0 30px yellow";
            matchingElement.innerText = text.replace(oldTranslate, newTranslate);
            if (!parentElement.closest('#' + Selectors.targets.menu)) {
                matchingElement.focus();
                matchingElement.scrollIntoView();
            }
        }
        currentNode = treeWalker.nextNode();
    }
    setTimeout(() => {
        matchingElement.style.boxShadow = 'none';
    }, 2000);
}; */

const saveTranslate = (form) => {
    const identifier = form.dataset.id;
    const data = [];
    const text = form.querySelectorAll('textarea');
    // Const currentLang = form.dataset.current;
    text.forEach((el) => {
        data.push({string: el.value.trim(), lang: el.dataset.lang.toLowerCase()});
        /*        If (el.dataset.lang.toLowerCase() === currentLang) {
                   newTranslate = el.value.trim();
               } */
    });
    setTranslate(identifier, JSON.stringify(data)).then(() => showAlert())
        .catch((error) => displayException(error));
};

const showModal = async(data) => {
    const templateContext = JSON.parse(data);
    const modal = await ModalFactory.create({
        type: ModalFactory.types.SAVE_CANCEL,
        large: true,
        title: getString('setnewtranslate', 'tool_inplacetranslate'),
        body: Templates.render('tool_inplacetranslate/stringmodal', templateContext),
    });
    modal.show();
    modal.getRoot().on(ModalEvents.save, () => {
        const form = modal.getBody()[0].querySelector('#' + Selectors.targets.translatesForm);
        saveTranslate(form);
        modal.destroy();
    });
};