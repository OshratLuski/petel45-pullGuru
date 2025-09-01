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
 * Handles events related to the diagnostic ADV question type answers.
 *
 * @module     qtype_multichoice/answers
 * @copyright  Devlion.ltd <info@devlion.co>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Selectors for this module.
 *
 * @type {{ANSWER_LABEL: string}}
 */
const SELECTORS = {
    ANSWER_LABEL: '[data-region=answer-label]',
    ANSWER_INPUT: 'input[data-answertype="answer"]',
    CUSTOM_INPUT: 'input[data-answertype="custom"]',
    CUSTOM_FIELD: 'input[data-answertype="customtext"]',
    SECURITYWRAPPER: 'div.securitywrapper',
    SECURITYTEXTAREA: 'div.securitywrapper textarea',
    SECURITYSUREYES: 'input#securitysureyes',
    SECURITYSURENO: 'input#securitysureno',
};

/**
 * Init method.
 *
 * @param {string} rootId The ID of the question container.
 */
const init = (rootId) => {
    const root = document.getElementById(rootId);

    // Add click event handlers for the divs containing the answer since these cannot be enclosed in a label element.
    const answerLabels = root.querySelectorAll(SELECTORS.ANSWER_LABEL);
    answerLabels.forEach((answerLabel) => {
        answerLabel.addEventListener('click', (e) => {
            const labelId = e.currentTarget.id;
            // Fetch the answer this label is assigned to.
            const linkedOption = root.querySelector(`[aria-labelledby="${labelId}"]`);
            // Trigger the click event.
            linkedOption.click();
        });
    });

    const answers = root.querySelectorAll(SELECTORS.ANSWER_INPUT);
    const textfield = root.querySelector(SELECTORS.CUSTOM_FIELD);
    const securitywrapper = root.querySelector(SELECTORS.SECURITYWRAPPER);
    const securitytextarea = root.querySelector(SELECTORS.SECURITYWRAPPER);

    if (securitywrapper) {
        securitywrapper.style.display = 'none';
        securitytextarea.disabled = true;
    }

    answers.forEach((answer) => {
        answer.addEventListener('click', (e) => {
                textfield.disabled = true;
        });
    });

    const custom = root.querySelector(SELECTORS.CUSTOM_INPUT);
    if (custom) {
        custom.addEventListener('click', (e) => {
            textfield.disabled = false;
        });
    }

    const securitysureyes = root.querySelector(SELECTORS.SECURITYSUREYES);
    if (securitysureyes) {
        securitysureyes.addEventListener('click', (e) => {
            if(e.currentTarget.checked) {
                securitywrapper.style.display = 'none';
                securitytextarea.disabled = true;
            }
        });
    }

    const securitysureno = root.querySelector(SELECTORS.SECURITYSURENO);
    if (securitysureno) {
        if (securitysureno.checked) {
            securitywrapper.style.display = 'block';
            securitytextarea.disabled = false;
        }
    
        securitysureno.addEventListener('click', (e) => {
            if (e.currentTarget.checked) {
                securitywrapper.style.display = 'block';
                securitytextarea.disabled = false;
            }
        });
    }
};

export default {
    init: init
};
