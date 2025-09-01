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
 * @module     tiny_styles/plugin
 * @copyright   2025 Oshrat Luski <oshrat.luski@weizmann.ac.il>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import {getTinyMCE} from 'editor_tiny/loader';
import {pluginName, component} from './common';
import * as Configuration from './configuration';
import {getPluginMetadata} from 'editor_tiny/utils';
import {getSetupCommands} from './commands';
import {register as registerOptions} from './options';

// Setup the tiny_accessibilityfontcolor plugin.
// eslint-disable-next-line no-async-promise-executor
export default new Promise(async(resolve) => {
    // Note: The PluginManager.add function does not support asynchronous configuration.
    // Perform any asynchronous configuration here, and then call the PluginManager.add function.
    const [
        tinyMCE,
        setupCommands,
        pluginMetadata,
    ] = await Promise.all([
        getTinyMCE(),
        getSetupCommands(),
        getPluginMetadata(component, pluginName),
    ]);

    // Reminder: Any asynchronous code must be run before this point.
    tinyMCE.PluginManager.add(pluginName, (editor) => {

        // Load the custom CSS file into the TinyMCE iframe.
        const styles = editor.contentCSS.push(M.cfg.wwwroot + '/lib/editor/tiny/plugins/styles/styles.css');

        // Register options first
        registerOptions(editor);

        const pluginConfig = {
            styles,
        };

        // Register options.
        editor.plugins[component] = pluginConfig;

        // Setup commands
        setupCommands(editor);

        // Return pluginMetadata + config so getParam('styles') works
        return {
            ...pluginMetadata,
            ...pluginConfig
        };
    });

    resolve([pluginName, Configuration]);
});
