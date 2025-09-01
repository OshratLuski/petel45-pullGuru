define([], function() {
    'use strict';

    function KeySelect() {

        let currentItem = -1;

        let items = '';

        const forms = Array.from(document.querySelectorAll(`form`));

        this.init = function(container) {
            items = Array.from(container.childNodes);

            forms.forEach((form) => {
                if (form.contains(container)) {
                    form.onkeydown = (event) => {
                        if (event.keyCode === 13) {
                            event.preventDefault();
                        }
                    };
                }
            });

            container.onmouseover = (e) => {
                items.forEach((item, index) => {
                    item.classList.remove(`aut-hover`);
                    if (e.target === item) {
                        currentItem = index;
                    }
                });

                e.target.classList.add(`aut-hover`);
            };

        };

        const makeHover = () => {
            items.forEach((item) => {
                item.classList.remove(`aut-hover`);
            });
            items[currentItem].classList.add(`aut-hover`);
        };

        const goUp = () => {
            if (currentItem <= 0) {
                return;
            }
            currentItem--;
            makeHover();
        };

        const goDown = () => {
            if (currentItem >= items.length - 1) {
                return;
            }
            currentItem++;
            makeHover();
        };

        const selectItem = () => {
            let event = new Event('click', {bubbles: true});
            if (items[currentItem]) {
                items[currentItem].dispatchEvent(event);
                currentItem = -1;
            }
            currentItem = -1;
        };

        const hideAll = () => {
            container.innerHTML = '';
            container.style.display = 'none';
            currentItem = -1;
        };

        this.checkKeyCode = function(event) {
            switch (event.keyCode) {
                case 38:
                    goUp();
                    break;
                case 40:
                    goDown();
                    break;
                case 13:
                    selectItem();
                    break;
                case 27:
                    hideAll();
                    break;
            }
        };

    }

    const addStyleToPage = () => {

        const style = document.createElement(`style`);
        style.classList.add('autocomplete_style');
        style.innerHTML = `
            .compl__outer {
              top: 34px;
              left: 0;
              width: 60%;
              background-color: #fff;
              box-sizing: content-box;
              border-radius: 5px;
              overflow: hidden;
              display: none;
              border: 1px solid #ddd;
              padding: 0;
              position: absolute;
              list-style-type: none;
              z-index: 1000;
            }
            .compl__outer > li {
              padding: 10px;
              cursor: pointer;
              border-bottom: 1px solid #ddd;
            }
            .compl__outer > li.aut-hover {
        
              background-color: #bbb;
            }
            .compl__outer-placeholder {
              color: #9e9e9e;
            }
            .formulaspart .compl__outer {
                right: 0;
                width: 20%;
            }
        `;
        if (!document.querySelector('.autocomplete_style')) {
            document.querySelector('body').appendChild(style);
        }
    };

    const makeOutputBlock = (inputSelector) => {

        const outputBlock = document.createElement(`ul`);
        outputBlock.classList.add('compl__outer');
        inputSelector.parentNode.style.position = 'relative';
        inputSelector.parentNode.appendChild(outputBlock);

        return outputBlock;
    };

    const setStyleError = (input) => {
        input.style.borderColor = `red`;
        input.style.outlineColor = `red`;
        input.style.color = `red`;
    };
    const setStyleInherit = (input) => {
        input.style.borderColor = ``;
        input.style.outlineColor = ``;
        input.style.color = ``;
    };

    const autocomplete = {

        init: (inputSelectorsJson, searchWordsJson) => {

            let targetArray = JSON.parse(searchWordsJson);
            let inputSelectorsName = JSON.parse(inputSelectorsJson);
            if (!Array.isArray(targetArray) || !Array.isArray(inputSelectorsName)) {
             return;
            }

            const keySelect = new KeySelect();
            document.addEventListener('keydown', keySelect.checkKeyCode);

            let allSelectors = inputSelectorsName.join(', ');
            const inputSelectors = Array.from(document.querySelectorAll(allSelectors));

            inputSelectors.forEach(function(inputSelector) {
                if (!inputSelector) {
                    return;
                }

                inputSelector.autocomplete = `off`;
                let outputBlockPrevStep;

                let outputBlock = makeOutputBlock(inputSelector);
                const outerItem = document.createElement(`li`);
                outerItem.classList.add('compl__outer-item');

                inputSelector.addEventListener(`input`, function(e) {
                    setStyleInherit(inputSelector);
                    outputBlock.innerHTML = '';
                    outputBlock.style.display = 'none';

                    // Get value and unit.
                    let string = this.value;
                    string = string.replaceAll(' ', '');
                    string = string.replaceAll('E+', 'E');
                    string = string.replaceAll('e+', 'e');

                    string = string.replaceAll('\\', '');

                    let flagerrordigit = false;
                    let flag = true;
                    let digitInputValue = '';
                    let notDigitInputValue = '';

                    const degree = ['E', 'e', '^'];
                    const specific = ['.', '-', '+'];
                    const operand = ['-', '+'];
                    const numbers = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];

                    let numberchars = [];
                    numberchars = numberchars.concat(numbers, degree, specific);

                    for (let i = 0; i <= string.length - 1; i++) {
                        let char = string.charAt(i);

                        if (numberchars.includes(char)) {

                            // Check degree.
                            if (flag && degree.includes(char)) {
                                if (!numbers.includes(string.charAt(i + 1)) && !operand.includes(string.charAt(i + 1))) {
                                    flag = false;
                                }
                            }

                            if (flag) {
                                digitInputValue += char;
                            } else {
                                notDigitInputValue += char;
                            }
                        } else {
                            flag = false;
                            notDigitInputValue += char;
                        }
                    }

                    // Check errors specific chars.
                    let ch, count;
                    let counts = {};

                    for (let i = 0, len = digitInputValue.length; i < len; ++i) {

                        // Disable first "-".
                        if (i === 0 && digitInputValue.charAt(0) === '-') {
                            continue;
                        }

                        ch = digitInputValue.charAt(i);
                        count = counts[ch];
                        counts[ch] = count ? count + 1 : 1;
                    }

                    specific.forEach(function(char, index) {
                        if (counts[char] > 1) {
                            flagerrordigit = true;
                        }
                    });

                    let inputValue = this.value.replaceAll('\\', '');

                    if (digitInputValue.length && notDigitInputValue.length && !flagerrordigit) {

                        // Const digitInputValue = inputValue.match(/\d|[.]|[+-]|[\s]|[E|/\^|*]/g);
                        // const notDigitInputValue = inputValue.replace(/\d|[.]|[+-]|[\s]|[E|/\^|*]/g, '');
                        // const withoutnumber = inputValue.replace(digitInputValue, '').replace(/\s+/, "");
                        // const value = new RegExp(`${withoutnumber}`, `i`);

                        // Change '*' for find operation;
                        notDigitInputValue = notDigitInputValue.replaceAll('*', '\\*');

                        // Change '^' for find operation;
                        notDigitInputValue = notDigitInputValue.replaceAll('^', '\\^');

                        const unit = new RegExp(`${notDigitInputValue}`, `i`);
                        const unitcelcius = new RegExp('°' + `${notDigitInputValue}`, `i`);

                        outputBlock.style.display = 'block';

                        targetArray.sort().forEach(function(item) {
                            let itemParts = item.toString().split(`(`);

                            if (itemParts[0].search(unit) === 0 || itemParts[0].search(unitcelcius) === 0) {
                                let el = outerItem.cloneNode();
                                el.innerHTML = `<span class = "compl__outer-data">${itemParts[0]}</span>
                                    <span class = "compl__outer-placeholder">${itemParts[1] ? '(' + itemParts[1] : ''}</span>`;
                                outputBlock.appendChild(el);
                            }
                        });

                        if (outputBlock.innerHTML) {
                            outputBlockPrevStep = outputBlock.innerHTML;
                            outputBlock.style.display = 'block';
                            keySelect.init(outputBlock);
                        } else {
                            setStyleError(inputSelector);
                            outputBlock.innerHTML = '';
                            outputBlock.style.display = 'none';
                        }

                        outputBlock.addEventListener('click', function(e) {
                            let targetEl = e.target;
                            while (targetEl.tagName !== `UL`) {
                                if (targetEl.tagName === `LI`) {

                                    // Let result = inputSelector.value.search(/[a-z]/gimu);

                                    // $.each(digitInputValue, function(index, value) {
                                    //     if (value === '/') {
                                    //         delete digitInputValue[index];
                                    //     }
                                    // });

                                    inputSelector.value = (digitInputValue + targetEl.firstChild.innerHTML).trim();
                                    outputBlock.style.display = 'none';
                                    return;
                                }
                                targetEl = targetEl.parentNode;
                            }

                        });

                        // Remove wrong digit input.
                    } else if (inputValue.search(/[+-\d]+/) !== 0 || flagerrordigit) {
                        setTimeout(function() {
                            inputSelector.value = inputSelector.value.substr(0, inputSelector.value.length - 1);
                        }, 300);
                    }

                });

                inputSelector.addEventListener('blur', function(e) {
                    setTimeout(function(e) {
                        outputBlock.style.display = 'none';
                        outputBlock.innerHTML = '';
                    }, 500);
                });

            });

            addStyleToPage();
        }
    };

    return autocomplete;
});
