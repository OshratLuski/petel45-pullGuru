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
 * JavaScript to add popup sql hints in the editor
 *
 * @package block_configurable_reports
 * @copyright 2021 Marcus Green
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
import CodeMirror from 'block_configurable_reports/codemirror/lib/codemirror';
import showhint from 'block_configurable_reports/codemirror/addon/hint/show-hint';
import hint from 'block_configurable_reports/codemirror/addon/hint/sql-hint';
import mode from 'block_configurable_reports/codemirror/mode/sql/sql';

export const init = () => {
    var tablejson = document.getElementById('tablejson');
    var AUTOCOMPLETE_TABLES = JSON.parse(tablejson.value);
    var editor = CodeMirror.fromTextArea(document.getElementById('id_querysql'), {
        mode: 'text/x-mysql',
        styleActiveLine: true,
        lineNumbers: true,
        extraKeys: { "Ctrl-Space": "autocomplete" }
    });
    editor.setSize('100%', 400);

    var editor_remote = CodeMirror.fromTextArea(document.getElementById('id_remotequerysql'), {
        mode: 'text/x-mysql',
        styleActiveLine: true,
        lineNumbers: true,
        extraKeys: { "Ctrl-Space": "autocomplete" }
    });
    editor_remote.setSize('100%', 400);

    CodeMirror.commands.autocomplete = function (cm) {
        CodeMirror.showHint(cm, CodeMirror.hint.sql, {
            tables: AUTOCOMPLETE_TABLES,
            disableKeywords: true
        });
    }
};
