// This file is part of VPL for Moodle - http://vpl.dis.ulpgc.es/
//
// VPL for Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// VPL for Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with VPL for Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Button submission
 *
 * @copyright 2023 Devlion.ltd
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define([], function() {
    return {
        init: function(btnid) {

            let btn = document.getElementById(btnid);
            if (btn) {
                btn.addEventListener('click', function () {
                    // Enable saveandeval
                    let acebtn = document.getElementById('vpl_ide_' + btnid);
                    if (acebtn) {
                        acebtn.click();
                    }
                });
            }

        }
    };
});
