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

/*
 * The module resizes the iframe containing the embedded question to be
 * just the right size for the question.
 *
 * @module    filter_hotwords
 * @package   filter_hotwords
 * @copyright 2024 Devlion.co
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
import * as ModalFactory from 'core/modal_factory';

export const init = () => {
    document.addEventListener("click", function (e) {
        let _target = e.target;
        if (_target.getAttribute("data-toggle") == "hotword" && !_target.closest("div.editor_atto")) {
            ModalFactory.create({
                type: ModalFactory.types.ALERT,
                title: _target.innerHTML,
                body: hotwordDecodeHtml(_target.getAttribute("data-text")),
            }).then(modal => {
                modal.show();
            });
        }
    });
};

function hotwordDecodeHtml(html) {
    let txt = document.createElement("textarea");
    txt.innerHTML = html;
    return txt.value;
}