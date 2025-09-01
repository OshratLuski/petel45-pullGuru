/* eslint-disable no-unused-vars */
/* eslint-disable no-debugger */
/* eslint-disable capitalized-comments */
/* eslint-disable no-undef */
/* eslint-disable no-console */
/* eslint-disable no-multiple-empty-lines */
/* eslint-disable no-trailing-spaces */
/* eslint-disable keyword-spacing */
/* eslint-disable spaced-comment */
/* eslint-disable key-spacing */
/* eslint-disable comma-spacing */
/* eslint-disable-next-line no-unused-vars */
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
 * @module      mod_codescore/codeeditor
 * @copyright   2024 Devlion <info@devlion.co>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import CodeMirror from "mod_codescore/codemirrorlib";
import javascript from "mod_codescore/langs/js";

export const init = (cmid) => {
  let isReadOnly = false;
  const textarea = document.getElementById("codeEditor");
  if (textarea.className.includes("disabled")) {
    isReadOnly = true;
  }
  let cm = CodeMirror.fromTextArea(document.getElementById("codeEditor"), {
    lineNumbers: true,
    mode: "javascript", //TODO add lang from backend
    theme: "darcula",
    readOnly: isReadOnly,
  });

  // Event Listeners/
  document
    .getElementById("submitattemptbutton")
    .addEventListener("click", (e) => {
      e.preventDefault();

      let code = cm.getValue();
      let notes = document.getElementById("codeNotes").value;

      document.getElementById("code").value = code;
      document.getElementById("notes").value = notes;
      document.getElementById("sesskey").value = M.cfg.sesskey;
    //   document.getElementById("cmid").value = cmid;


      document.getElementById("saveAndExitForm").submit();
    });
};