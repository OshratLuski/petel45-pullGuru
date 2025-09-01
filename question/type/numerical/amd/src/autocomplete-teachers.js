define([''], function () {
    'use strict';

    let btnToggle;

    // document.addEventListener('change',function(event){
    //   let target = event.target;
    //   let fieldset;
    //   while (!target.classList.contains('panel-body')) {
    //     if ((target.tagName == 'SELECT')) {
    //       if ((target.name.search(/answertype/i)) >= 0) {
    //         fieldset = target;
    //         while (fieldset.tagName != 'FIELDSET') {
    //           fieldset = fieldset.parentNode;
    //         }
    //
    //         btnToggle = fieldset.querySelectorAll(`input[name^="autocomplete"]`);
    //         if (target.value != 0) {
    //           btnToggle.forEach((item)=>{
    //             item.disabled = true;
    //           });
    //           btnToggle[1].checked = true;
    //         }else {
    //           btnToggle.forEach((item)=>{
    //             item.disabled = false;
    //           });
    //           btnToggle[0].checked = true;
    //         }
    //
    //         return
    //       }
    //     }
    //     target = target.parentNode;
    //   }
    // });

    const checkSwitch = () => {

        if (!btnToggle.length) {
            return false;
        }

        if (btnToggle[0].checked) {
            return false;
        } else {
            return true;
        }
    }

    function KeySelect() {

        let currentItem = -1;

        let items = '';

        const forms = Array.from(document.querySelectorAll(`form`));

        this.init = function (container) {
            items = Array.from(container.childNodes);

            forms.forEach((form) => {
                if (form.contains(container)) {
                    form.onkeydown = (event) => {
                        if (event.keyCode === 13) {
                            event.preventDefault();
                        }
                    }
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
            }

        }

        const makeHover = () => {
            items.forEach((item) => {
                item.classList.remove(`aut-hover`);
            });
            items[currentItem].classList.add(`aut-hover`);
        }

        const goUp = () => {
            if (currentItem <= 0) {
                return;
            }
            currentItem--;
            makeHover();
        }

        const goDown = () => {
            if (currentItem >= items.length - 1) {
                return;
            }
            currentItem++;
            makeHover();
        }

        const selectItem = () => {
            let event = new Event('click', {bubbles: true});
            if (items[currentItem]) {
                items[currentItem].dispatchEvent(event);
                currentItem = -1;
            }
            currentItem = -1;
        }

        const hideAll = () => {
            container.innerHTML = '';
            container.style.display = 'none';
            currentItem = -1;
        }

        this.checkKeyCode = function (event) {
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
        }

    }

    const addStyleToPage = () => {

        const style = document.createElement(`style`);
        style.innerHTML = `
          .compl__outer {
            min-width: 150px;
            background-color: #fff;
            box-sizing: content-box;
            border-radius: 5px;
            overflow: hidden;
            display: none;
            margin: 2px 0 0 0;
            border: 1px solid #ddd;
            padding: 0;
            position: absolute;
            left: 0;
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
        `;
        document.querySelector('body').appendChild(style);
    }

    const autocomplete = {

        init: (inputSelectorsJson, searchWordsJson) => {
            const keySelect = new KeySelect();
            document.addEventListener('keydown', keySelect.checkKeyCode);


            let searchWords = JSON.parse(searchWordsJson);
            let inputSelectorsName = JSON.parse(inputSelectorsJson);
            if (!Array.isArray(searchWords) || !Array.isArray(inputSelectorsName)) return;

            inputSelectorsName.forEach((item) => {

                let inputSelectors = Array.from(document.querySelectorAll(item));

                inputSelectors.forEach((inputSelector) => {
                    if (!inputSelectors) return;

                    const outerBlock = document.createElement(`ul`);
                    outerBlock.classList.add('compl__outer');
                    const outerItem = document.createElement(`li`);
                    outerItem.classList.add('compl__outer-item');
                    let outerBlockPrevStep;

                    inputSelector.autocomplete = `off`;
                    inputSelector.parentNode.appendChild(outerBlock);

                    inputSelector.addEventListener(`input`, function (e) {
                        let fieldset = inputSelector;
                        while (fieldset.tagName !== 'FIELDSET') {
                            fieldset = fieldset.parentNode;
                        }
                        btnToggle = fieldset.querySelectorAll(`input[name^="autocomplete"]`);

                        if (checkSwitch()) return;
                        outerBlock.style.top = inputSelector.offsetTop + inputSelector.offsetHeight + 10 + 'px';
                        outerBlock.style.left = inputSelector.offsetLeft + 'px';
                        // outerBlock.style.right = inputSelector.offsetRight  + 'px';
                        outerBlock.style.width = inputSelector.clientWidth + 'px';
                        outerBlock.innerHTML = '';

                        let tempval = this.value.replaceAll('\\', '');
                        const value = new RegExp(`${tempval}`, `i`);
                        const valuecelcius = new RegExp('°' + `${tempval}`, `i`);

                        searchWords.sort().forEach(function (item) {
                            if (tempval && (item.search(value) === 0 || item.search(valuecelcius) === 0)) {
                                let el = outerItem.cloneNode()
                                el.innerHTML = item;
                                outerBlock.appendChild(el);
                            }
                        });

                        if (outerBlock.innerHTML) {

                            outerBlockPrevStep = outerBlock.innerHTML;
                            outerBlock.style.display = 'block';
                            keySelect.init(outerBlock);

                            outerBlock.addEventListener('click', function (e) {
                                let targetEl = e.target;
                                while (targetEl.tagName !== `UL`) {
                                    if (targetEl.tagName === `LI`) {
                                        inputSelector.value = targetEl.innerHTML;
                                        outerBlock.style.display = 'none';
                                    }
                                    targetEl = targetEl.parentNode;
                                }
                            });

                        } else {
                            setTimeout(function () {
                                inputSelector.value = inputSelector.value.substr(0, inputSelector.value.length - 1)
                            }, 500);
                            if (outerBlockPrevStep && inputSelector.value) {
                                outerBlock.innerHTML = outerBlockPrevStep;
                                keySelect.init(outerBlock);
                            } else {
                                outerBlock.innerHTML = '';
                                outerBlock.style.display = 'none';
                            }
                        }

                    });

                    inputSelector.addEventListener('blur', function (e) {
                        setTimeout(() => {
                            outerBlock.style.display = 'none';
                            outerBlock.innerHTML = '';
                        }, 300);
                    });

                });

            });

            addStyleToPage();
        }
    }

    return autocomplete

});
