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
 * @module      mod_codescore/adminsettings
 * @copyright   2024 Devlion <info@devlion.co>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import $ from 'jquery';
import {get_string as getString} from 'core/str';

export const init = async () => {
    const settingPage = document.getElementById('page-admin-setting-modsettingcodescore');
    if (!settingPage) { return; }
    const select = document.getElementById('id_s_mod_codescore_rubricscount');
    select.addEventListener('change', () => {
        document.querySelector('#adminsettings [type=submit]').click();
    });
    const warntext = await getString('gradewarn', 'mod_codescore');
    $('#page-content .btn-primary').after(`<span class="mt-2 text-danger d-none" id="grade-warn">${warntext}<span>`);

    let gradeElements = $('[id^="id_s_mod_codescore_maximumgrade"]');

    const validateGrades = () => {
        let totalmaxgrade = 0;
        Array.from(gradeElements).forEach((el) => {
            totalmaxgrade += Number(el.value);
        });
        const isRight = totalmaxgrade === 100;
        if (!isRight) {
            $('#page-content .btn-primary').addClass('save-disabled');
            $('[id^="id_s_mod_codescore_maximumgrade"]').addClass('border-danger');
            $('#grade-warn').removeClass('d-none');
        } else {
            $('#page-content .btn-primary').removeClass('save-disabled');
            $('[id^="id_s_mod_codescore_maximumgrade"]').removeClass('border-danger');
            $('#grade-warn').addClass('d-none');
        }
    };
    validateGrades();
    gradeElements.on("change", validateGrades);
};