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
 * Shows the header menu content in a popover.
 *
 * @module     theme_boost/footer-popover
 * @copyright  2023 Devlion.co

 */

import $ from 'jquery';
import Popover from 'theme_boost/popover';

const SELECTORS = {
    HEADERCONTAINER: 'nav.navbar .primary-navigation',
    HEADERCONTENT: '[data-region="header-content-popover"]',
    HEADERBUTTON: '.oer-popup-btn'
};

let footerIsShown = false;

export const init = () => {
    const container = document.querySelector(SELECTORS.HEADERCONTAINER);
    const HEADERBUTTON = document.querySelector(SELECTORS.HEADERBUTTON);

    // All jQuery in this code can be replaced when MDL-71979 is integrated.
    $(HEADERBUTTON).popover({
        content: getContent,
        container: container,
        html: true,
        placement: 'bottom',
        //customClass: 'footer',
        trigger: 'click'
    });

    document.addEventListener('click', e => {
            if (footerIsShown && !e.target.closest(SELECTORS.HEADERCONTAINER)) {
                $(HEADERBUTTON).popover('hide');
            }
        },
        true);

    document.addEventListener('keydown', e => {
        if (footerIsShown && e.key === 'Escape') {
            $(HEADERBUTTON).popover('hide');
            HEADERBUTTON.focus();
        }
    });

    document.addEventListener('focus', e => {
            if (footerIsShown && !e.target.closest(SELECTORS.HEADERCONTAINER)) {
                $(HEADERBUTTON).popover('hide');
            }
        },
        true);

    $(HEADERBUTTON).on('shown.bs.popover', () => {
        footerIsShown = true;
        let popoverBody = document.querySelector('.popover-body');
        let listTitleIcons = popoverBody.querySelectorAll('.oer-popup-list-title-icon');
        let listItemIcons = popoverBody.querySelectorAll('.oer-popup-list-item-icon');
        listTitleIcons.forEach((el) => {
            el.style.backgroundImage = `url(${el.title})`;
            el.removeAttribute('title');
        });
        listItemIcons.forEach((el) => {
            el.style.backgroundImage = `url(${el.title})`;
            el.removeAttribute('title');
        });
    });

    $(HEADERBUTTON).on('hide.bs.popover', () => {
        footerIsShown = false;
    });
};

/**
 * Get the footer content for popover.
 *
 * @returns {String} HTML string
 * @private
 */
const getContent = () => {
    return document.querySelector(SELECTORS.HEADERCONTENT).innerHTML;
};

export {
    Popover
};
