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
 * Color storage management for Tiny Accessibility Font Color plugin.
 *
 * This module initializes and stores the original text and background colors
 * to allow proper resetting and filtering of colors based on accessibility criteria.
 *
 * @module      tiny_accessibilityfontcolor
 * @copyright   2024 Oshrat Luski <oshrat.luski@weizmann.ac.il>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import { getPluginOptionName } from 'editor_tiny/options';
import { pluginName } from './common';

const forecolorMap = getPluginOptionName(pluginName, 'textcolors');
const backcolorMap = getPluginOptionName(pluginName, 'backgroundcolors');

let originalBackgroundColors = null;
let originalTextColors = null;
let isInitialized = false;

export const initializeColorStorage = (editor) => {
    if (!isInitialized) {

        let backColors = editor.options.get(backcolorMap);
        let textColors = editor.options.get(forecolorMap);

        if (!backColors || !textColors) {
            setTimeout(() => initializeColorStorage(editor), 100);
            return;
        }

        originalBackgroundColors = JSON.parse(JSON.stringify(backColors));
        originalTextColors = JSON.parse(JSON.stringify(textColors));

        isInitialized = true;
    } else {
        console.warn("Color storage already initialized – skipping.");
    }
};

export const getOriginalBackgroundColors = () => JSON.parse(JSON.stringify(originalBackgroundColors));
export const getOriginalTextColors = () => JSON.parse(JSON.stringify(originalTextColors));
