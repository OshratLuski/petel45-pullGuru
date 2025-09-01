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
 * JS actions.
 *
 * @module      mod_codescore/report
 * @copyright   2024 Devlion <info@devlion.co>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import {MergeView} from 'mod_codescore/codemirrormerge';
// eslint-disable-next-line no-unused-vars
import javascript from "mod_codescore/langs/js";

export const init = () => {
    const studentCode = document.getElementById('codeValue').value;
    const correctedCode = document.getElementById('correctedCodeValue').value;
    // eslint-disable-next-line no-unused-vars
    const m = new MergeView(
        document.getElementById('mergeViewWrapper'),
        {
            origLeft: studentCode,
            origRight: correctedCode,
            value:correctedCode,
            revertButtons: false,
            mode: "javascript", //TODO add lang from backend
            theme: "darcula",
        }
    );
};