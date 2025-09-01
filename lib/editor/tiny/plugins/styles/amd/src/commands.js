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
 * Plugin version and other meta-data are defined here.
 *
 * @module     tiny_styles/commands
 * @copyright   2025 Oshrat Luski <oshrat.luski@weizmann.ac.il>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import {component, buttonName, buttonIcon} from './common';
import {getButtonImage} from 'editor_tiny/utils';
import {getString} from 'core/str';
import {applyStyle} from './styleactions';
import {getStyles} from './options';

export const getSetupCommands = async () => {

    const [buttonText, buttonImage, clearstyle] = await Promise.all([
        getString('buttontitle', component),
        getButtonImage('icon', component),
        getString('clearstyle', component),
    ]);

    // Helper function to clear attostylesbox styles
    const clearAttostylesboxStyles = (editor) => {
        const currentNode = editor.selection.getNode();
        const targetDiv = editor.dom.getParent(currentNode, (node) =>
            node.nodeName === 'DIV' &&
            node.classList &&
            [...node.classList].some(c => c.startsWith('attostylesbox'))
        );

        if (targetDiv) {
            const remainingClasses = [...targetDiv.classList].filter(
                c => !c.startsWith('attostylesbox')
            );
            targetDiv.removeAttribute('class');
            if (remainingClasses.length) {
                targetDiv.className = remainingClasses.join(' ');
            }
        }
    };

    const buildMenuItems = (editor, styles, clearstyle) => {
        const items = styles.map(style => ({
            type: 'menuitem',
            text: style.title,
            onAction: () => applyStyle(editor, style.classes.split(' '))
        }));

        return items;
    };

    return (editor) => {
        const styles = getStyles(editor) || [];

        editor.ui.registry.addIcon(buttonIcon, buttonImage.html);

        editor.ui.registry.addButton(buttonName, {
            icon: buttonIcon,
            tooltip: buttonText,
            onAction: () => {
                // No action needed for now; just shows icon in toolbar
            }
        });

        editor.ui.registry.addMenuButton(buttonName, {
            icon: buttonIcon,
            tooltip: buttonText,
            fetch: (callback) => {
                callback(buildMenuItems(editor, styles, clearstyle));
            }
        });

        editor.ui.registry.addNestedMenuItem(buttonName, {
            icon: buttonIcon,
            text: buttonText,
            getSubmenuItems: () => buildMenuItems(editor, styles, clearstyle)
        });

        // Add keyboard shortcuts
        editor.addShortcut('alt+shift+c', 'Clear style', () => {
            clearAttostylesboxStyles(editor);
        });

        // Override Enter key behavior to clear styles on double Enter
        let lastEnterTime = 0;
        editor.on('keydown', (e) => {
            if (e.keyCode === 13) { // Enter key
                const currentTime = Date.now();
                if (currentTime - lastEnterTime < 300) { // Double Enter within 300ms
                    clearAttostylesboxStyles(editor);
                }
                lastEnterTime = currentTime;
            }
        });

        const globalStyles = [
            `
            .tiny-styles-menu.tox-collection.tox-collection--list {
                min-width: 300px !important;
            }

            .tiny-styles-menu .tox-collection__item-label {
                white-space: normal !important;
                word-break: break-word;
                display: flex !important;
                align-items: center !important;
                gap: 8px !important;
            }
            `,
            `
            .tiny-style-icon {
                display: inline-block;
                flex-shrink: 0 !important;
            }
            `,
            `
            .attostylesbox {
                display: inline-block;
                padding: 6px 10px !important;
            }
            `,
            `
            .tox-collection__item--active {
                background-color: #f5f5f5 !important;
                color: black !important;
                border-radius: 4px;
            }
            `,
            `
            /* Make the styles plugin icon smaller to match other toolbar icons */
            .tox-toolbar .tox-tbtn[aria-label*="Style"] svg,
            .tox-toolbar .tox-tbtn[aria-label*="עיצוב"] svg {
                width: 16px !important;
                height: 16px !important;
            }
            
            .tox-toolbar .tox-tbtn[aria-label*="Style"] image,
            .tox-toolbar .tox-tbtn[aria-label*="עיצוב"] image {
                width: 16px !important;
                height: 16px !important;
            }
            `
        ];

        globalStyles.forEach(css => {
            const style = document.createElement('style');
            style.innerHTML = css;
            document.head.appendChild(style);
        });

        const isTinyStylesMenu = (node, styles) => {
            if (!node.classList.contains('tox-menu')) {
                return false;
            }

            const labels = node.querySelectorAll('.tox-collection__item-label');
            return [...labels].some(label =>
                styles.some(style => label.textContent === style.title)
            );
        };

        const observer = new MutationObserver((mutations) => {
            mutations.forEach(mutation => {
                mutation.addedNodes.forEach(node => {
                    if (node.nodeType === 1 && isTinyStylesMenu(node, styles)) {
                        node.classList.add('tiny-styles-menu');
                        const items = node.querySelectorAll('.tox-collection__item-label');

                        items.forEach((label, index) => {
                            const text = label.textContent;
                            label.innerHTML = '';

                            const iconWrapper = document.createElement('div');
                            iconWrapper.classList.add('tiny-style-icon');
                            iconWrapper.innerHTML = buttonImage.html;

                            const image = iconWrapper.querySelector('image');
                            if (image) {
                                image.setAttribute('style', 'width: 15px; height: 15px;');
                            }

                            const div = document.createElement('div');
                            const style = styles[index - 1];
                            if (style?.classes) {
                                div.classList.add(...style.classes.split(' '));
                            }

                            const textNode = document.createTextNode(text);
                            div.appendChild(textNode);

                            label.appendChild(iconWrapper);
                            label.appendChild(div);

                        });
                    }
                });
            });
        });

        observer.observe(document.body, {
            childList: true,
            subtree: true
        });
    };
};
