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
 * Options helper for Tiny Font Color plugin.
 *
 * @module      tiny_accessibilityfontcolor
 * @copyright   2023 Luca Bösch <luca.boesch@bfh.ch>
 * @copyright   2023 Stephan Robotta <stephan.robotta@bfh.ch>
 * @copyright   2024 Oshrat Luski <oshrat.luski@weizmann.ac.il> - Added full accessibility support and color contrast filtering
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import {getPluginOptionName} from 'editor_tiny/options';
import {pluginName} from './common';
import {isArrayOf, isString, mapColors} from "./polyfill";

const forecolorMap = getPluginOptionName(pluginName, 'textcolors');
const backcolorMap = getPluginOptionName(pluginName, 'backgroundcolors');
const forecolorPicker = getPluginOptionName(pluginName, 'textcolorpicker');
const backcolorPicker = getPluginOptionName(pluginName, 'backgroundcolorpicker');

import { getAccessibleBackgroundColors, getAccessibleTextColors, rgbToHex, addRemoveColorIfMissing } from './coloraccessibility';
import { initializeColorStorage } from './colorstorage';


/**
 * Register the options for the Tiny Equation plugin.
 *
 * @param {TinyMCE} editor
 */
export const register = (editor) => {
  editor.options.register(forecolorMap, {
    processor: value => {
        let processedColors = isArrayOf(value, isString) ? mapColors(value) : value;
        return {
            value: addRemoveColorIfMissing(processedColors),
            valid: true
        };
    },
    "default": [],
  });

  editor.options.register(backcolorMap, {
    processor: value => {
      let processedColors = isArrayOf(value, isString) ? mapColors(value) : value;
        return {
            value: addRemoveColorIfMissing(processedColors),
            valid: true
        };
      },
    "default": [],
  });

  setTimeout(() => {
    initializeColorStorage(editor);
  }, 500);

  /**
   * Register event listener in TinyMCE
   */
  editor.on('change', function() {
    let node = editor.selection.getNode();

    let selectedTextColor = rgbToHex(window.getComputedStyle(node).color);
    let selectedBgColor = rgbToHex(window.getComputedStyle(node).backgroundColor);

    if (selectedTextColor) {
        let filteredColors = getAccessibleBackgroundColors(selectedTextColor);
        editor.options.register(backcolorMap, {
          processor: () => {
              return {
                  value: filteredColors,
                  valid: true
              };
          },
          "default": []
      });

        editor.dispatch('TextColorChange', { name: 'backcolor', color: selectedBgColor });
    }

    if (selectedBgColor) {
        let filteredColors = getAccessibleTextColors(selectedBgColor);
        editor.options.register(forecolorMap, {
          processor: () => {
              return {
                  value: filteredColors,
                  valid: true
              };
          },
          "default": []
      });

        editor.dispatch('TextColorChange', { name: 'forecolor', color: selectedTextColor });
    }
  });


  editor.options.register(forecolorPicker, {
    processor: 'boolean',
    "default": false,
  });

  editor.options.register(backcolorPicker, {
    processor: 'boolean',
    "default": false,
  });

};

/**
 * Get the defined colors for the text color.
 *
 * @param {TinyMCE.Editor} editor
 * @returns {array}
 */
export const getForecolorMap = (editor) => editor.options.get(forecolorMap);
/**
 * Get the defined colors for the background color.
 *
 * @param {TinyMCE.Editor} editor
 * @returns {array}
 */
export const getBackcolorMap = (editor) => editor.options.get(backcolorMap);
/**
 * Get whether the color picker for the text color is enabled.
 *
 * @param {TinyMCE.Editor} editor
 * @returns {boolean}
 */
export const isForecolorPickerOn = (editor) => editor.options.get(forecolorPicker);
/**
 * Get whether the color picker for the background color is enabled.
 *
 * @param {TinyMCE.Editor} editor
 * @returns {boolean}
 */
export const isBackcolorPickerOn = (editor) => editor.options.get(backcolorPicker);
