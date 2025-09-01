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
 * Accessibility color filtering and contrast checking for Tiny Accessibility Font Color plugin.
 *
 * This module ensures that selected colors maintain accessibility standards.
 * It provides functions to check contrast ratios, reset filtered colors, and ensure accessible
 * text-background color combinations.
 *
 * @module      tiny_accessibilityfontcolor
 * @copyright   2024 Oshrat Luski <oshrat.luski@weizmann.ac.il>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import { getOriginalBackgroundColors, getOriginalTextColors } from './colorstorage';
import {getPluginOptionName} from 'editor_tiny/options';
import {pluginName} from './common';

const forecolorMap = getPluginOptionName(pluginName, 'textcolors');
const backcolorMap = getPluginOptionName(pluginName, 'backgroundcolors');

/**
 * HEX to RGB color converter
 * @param {string} hex
 * @returns {object} {r, g, b}
 */
function hexToRgb(hex) {

    if (Array.isArray(hex)) {
        hex = hex[0]?.value || "#000000";
    }

    if (typeof hex === "object" && hex !== null) {
        hex = hex.value || "#000000";
    }

    if (typeof hex !== 'string') {
        return { r: 0, g: 0, b: 0 };
    }

    hex = hex.replace(/^#/, '');

    if (hex.length === 3) {
        hex = hex.split('').map(c => c + c).join('');
    }
    let num = parseInt(hex, 16);
    return {
        r: (num >> 16) & 255,
        g: (num >> 8) & 255,
        b: num & 255
    };
}

/**
 * Converts an RGB or RGBA color string to a stable HEX format.
 * Ensures a consistent HEX output regardless of input variations.
 * 
 * @param {string} rgb - The RGB or RGBA color string (e.g., "rgb(255, 0, 0)" or "rgba(255, 0, 0, 1)").
 * @returns {string} The corresponding HEX color code (e.g., "#FF0000").
 */
export function rgbToHex(rgb) {

    if (typeof rgb !== 'string') {
        return "#000000";
    }

    let result = rgb.match(/\d+/g); // שולף את המספרים מה-RGB
    if (!result || result.length < 3) {
        return "#000000";
    }

    if (result.length === 4 && result[3] === "0") {
        return "#FFFFFF";
    }

    return "#" + result.slice(0, 3).map(x => ('0' + parseInt(x).toString(16)).slice(-2)).join('');
}

/**
 * Computer color brightness (luminance)
 * @param {string} hex Color in HEX format
 * @returns {number} Brightness value (0 - dark, 1 - light)
 */
function getLuminance(hex) {
    let { r, g, b } = hexToRgb(hex);
    let [RsRGB, GsRGB, BsRGB] = [r, g, b].map(v => v / 255);
    let [R, G, B] = [RsRGB, GsRGB, BsRGB].map(v =>
        v <= 0.03928 ? v / 12.92 : Math.pow((v + 0.055) / 1.055, 2.4)
    );
    return 0.2126 * R + 0.7152 * G + 0.0722 * B;
}

/**
 * Calculates contrast between two colors
 * @param {string} color1 First color (HEX)
 * @param {string} color2 Second color (HEX)
 * @returns {number} Contrast ratio
 */
function getContrastRatio(color1, color2) {
    let lum1 = getLuminance(color1);
    let lum2 = getLuminance(color2);
    let [L1, L2] = lum1 > lum2 ? [lum1, lum2] : [lum2, lum1];
    return (L1 + 0.05) / (L2 + 0.05);
}

/**
 * Checks if a color combination is accessible according to the WCAG standard
 * @param {string} textColor Text color (HEX)
 * @param {string} bgColor Background color (HEX)
 * @param {string} level Accessibility level ('AA' or 'AAA')
 * @returns {boolean} Do the colors meet the standard?
 */
function isAccessible(textColor, bgColor, level = 'AA') {
    let contrast = getContrastRatio(textColor, bgColor);
    let requiredContrast = level === 'AAA' ? 7 : 4.5;
    return contrast >= requiredContrast;
}

/**
 * Ensures that "Remove Color" option exists in the color list.
 * @param {Array} colors - List of colors.
 * @returns {Array} Updated list with "Remove Color" option.
 */
export const addRemoveColorIfMissing = (colors) => {
    if (colors.some(color => color.value === 'remove' || color.text === 'Remove color')) {
        return colors;
    }

    colors = colors.filter(color => color.value !== 'remove');

    colors.push({
        text: 'Remove Color',
        icon: 'color-swatch-remove-color',
        value: 'remove',
        type: 'choiceitem'
    });

    return colors;
};

/**
 * Returns only background colors that are accessible with the selected text color.
 * @param {string} textColor The selected text color
 * @returns {array} List of accessible background colors
 */
export function getAccessibleBackgroundColors(textColor) {
    let filteredColors = getOriginalBackgroundColors().filter(color =>
        isAccessible(textColor, color.value, 'AA')
    );

    return addRemoveColorIfMissing(filteredColors);
}

/**
 * Returns only text colors that are accessible with the selected background color.
 * @param {string} backgroundColor The selected background color
 * @returns {array} List of accessible text colors
 */
export function getAccessibleTextColors(backgroundColor) {
    let filteredColors = backgroundColor === "#FFFFFF"
        ? getOriginalTextColors()
        : getOriginalTextColors().filter(color =>
            isAccessible(backgroundColor, color.value, 'AA')
        );

    return addRemoveColorIfMissing(filteredColors);
}

/**
 * Resets the color filtering, restoring the full color palette.
 * If forecolor is reset, it also resets backcolor if needed.
 * @param {object} editor - TinyMCE editor instance.
 * @param {string} format - Either 'forecolor' or 'backcolor'.
 */
export const resetFilteredColors = (editor, format) => {
    if (format === 'forecolor') {
        const originalBackgroundColors = getOriginalBackgroundColors();
        editor.options.register(backcolorMap, {
            processor: () => ({
                value: originalBackgroundColors,
                valid: true
            }),
            "default": []
        });

        const selectedBgColor = rgbToHex(window.getComputedStyle(editor.selection.getNode()).backgroundColor);
        if (!selectedBgColor || selectedBgColor === "#FFFFFF") {
            resetFilteredColors(editor, "backcolor");
        }

    } else if (format === 'backcolor') {
        const originalTextColors = getOriginalTextColors();
        editor.options.register(forecolorMap, {
            processor: () => ({
                value: originalTextColors,
                valid: true
            }),
            "default": []
        });

        const selectedTextColor = rgbToHex(window.getComputedStyle(editor.selection.getNode()).color);
        if (!selectedTextColor || selectedTextColor === "#000000") {
            resetFilteredColors(editor, "forecolor");
        }

    }
};

