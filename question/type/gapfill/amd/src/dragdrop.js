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
 * JavaScript code for the gapfill question type.
 *
 * @copyright  2020 Marcus Green
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

export const init = (singleuse) => {
  Window.singleuse = singleuse;
  var draggables = document.querySelectorAll('span[class*="draggable"]');
  draggables.forEach(function(e) {
    e.addEventListener('dragstart', dragStart);
  });

  document.querySelectorAll('span.droptarget').forEach(function(el) {
    el.addEventListener("dblclick", function() {
      if (Window.singleuse) {
        dragShow(this);
      }
      const dropTarget = this.classList.contains('droptarget') ? this : this.closest(".droptarget");
      const parent = dropTarget.closest(".droptarget-wrapper");
      const dropTargetInput = parent.querySelector("input");
      dropTargetInput.setAttribute('value', "");
      dropTarget.dataset.contentValue = '';
      dropTarget.innerHTML = '';
    });

    el.addEventListener("drop", drop);
    if (el.innerHTML.length > 0) {
      const width = parseInt(el.offsetWidth);
      const parentWidth = parseInt(el.closest('.droptarget-wrapper').offsetWidth);
      if (width > parentWidth) {
        el.closest('.droptarget-wrapper').style.width = width + 'px';
      }
    }
  });

  /**
   *
   * @param {*} that
   */
  function dragShow(that) {
    const dropTarget = that.classList.contains('droptarget') ? that : that.closest(".droptarget");
    const dropTargetInput = dropTarget.nextElementSibling;
    var targetVal = dropTargetInput.value;

    draggables.forEach(function(e) {

      if (e.innerText.trim() === targetVal.trim()) {
        e.classList.remove("hide");
      }
    });
  }

  /**
   * Stops strange things happening on ios drop event
   * @param {*} e
   */
  document.addEventListener('dragover', function(e) {
    e.preventDefault();
  });


  /**
   *
   * @param {*} e
   */
  function drop(e) {
    dragShow(this);
    e.stopPropagation();
    e.preventDefault();
    const dropTarget = e.currentTarget.classList.contains('droptarget') ? e.currentTarget : e.currentTarget.closest(".droptarget");
    const parent = dropTarget.closest(".droptarget-wrapper");
    const dropTargetInput = parent.querySelector("input");

    dropTargetInput.setAttribute('value', e.dataTransfer.getData('text/plain'));
    dropTarget.dataset.contentValue = e.dataTransfer.getData('content');
    dropTarget.innerHTML = e.dataTransfer.getData('content');

    var sourceId = e.dataTransfer.getData("sourceId");
    var sourceEl = document.getElementById(sourceId);

    if (sourceEl.offsetWidth > parent.offsetWidth) {
      parent.style.width = sourceEl.offsetWidth + 10 + 'px';
    }

    if (Window.singleuse) {
      sourceEl.classList.add('hide');
      e.preventDefault();
    }
    e.preventDefault();
  }
  /**
   *
   * @param {*} e
   */
  function dragStart(e) {
    e.dataTransfer.setData('text/plain', e.target.innerText);
    e.dataTransfer.setData('content', e.target.innerHTML);
    e.dataTransfer.setData('sourceId', this.id);
    e.dataTransfer.effectAllowed = "move";
    e.dataTransfer.dropEffect = "move";
  }

  /**
   * Check is mobile.
   * @returns {any}
   */
  function isMobile() {
    const regex = /Mobi|Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i;
    return regex.test(navigator.userAgent);
  }

  let temp = {};

  if (isMobile()) {
    const dragableAnswers = document.querySelectorAll('.draggable.answers');
    const droptargets = document.querySelectorAll('.droptarget-wrapper');
    dragableAnswers.forEach(function(el) {
      el.addEventListener('click', (e) => copyValue(e.currentTarget));
    });
    droptargets.forEach(function(el) {
      el.addEventListener('click', (e) => setValue(e.currentTarget));
    });
  }

  /**
   * Copy target text value into temp object.
   * @param {any} target
   * @returns {any}
   */
  function copyValue(target) {
    temp.innerText = target.innerText;
    temp.content = target.innerHTML;
    temp.sourceId = target.id;
    temp.elWidth = target.offsetWidth;
    target.classList.add('bg-info');
  }

  /**
   * Set temp object values into input.
   * @param {any} target
   * @returns {any}
   */
  function setValue(target) {
    if (Object.keys(temp).length > 0) {

      const dropTargetInput = target.querySelector('input');
      const droptarget = target.querySelector('.droptarget');
      const sourceEl = document.getElementById(temp.sourceId);

      dropTargetInput.value = temp.innerText;
      dropTargetInput.dataset.contentValue = temp.content;
      droptarget.innerHTML = temp.content;
      sourceEl.classList.remove('bg-info');

      if (temp.elWidth > dropTargetInput.offsetWidth) {
        dropTargetInput.style.width = temp.elWidth + 10 + 'px';
      }

      if (Window.singleuse) {
        sourceEl.classList.add('hide');
      }
    }

    temp = {};
  }
};

