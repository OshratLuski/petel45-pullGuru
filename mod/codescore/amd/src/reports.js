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
 * @module      mod_codescore/reports
 * @copyright   2024 Devlion <info@devlion.co>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import Tabulator from 'mod_codescore/tabulatorlib';
import {get_string as getString} from 'core/str';
import Ajax from 'core/ajax';

export const init = async(data) => {
    const rawParams = window.location.search;
    const urlParams = new URLSearchParams(rawParams);
    const cmid = urlParams.get('id') ? urlParams.get('id') : urlParams.get('cmid');
    const link = (cell) => {
        const name = cell.getValue();
        const index = Number(cell.getRow().getIndex());
        const attempt = cell.getRow().getData().attempt;
        let userid;
        if (document.getElementById('page-mod-codescore-view')) {
            userid = data.attempts[0].userid;
        }
        else {
            userid = cell.getRow().getData().userid;
        }
        cell.getRow().getElement().addEventListener('click', () => {
            window.location.href = `report.php?cmid=${cmid}&userid=${userid}&attempt=${attempt}&index=${index}`;
        });

        return `<a href='report.php?cmid=${cmid}&userid=${userid}&attempt=${attempt}&index=${index}'>` + name + "</a>";
    };

    const dateFormatter = (cell) => {
        const value = cell.getValue();
        if (value === '0') {return '';}
        const toDate = new Date(Number(value) * 1000).toString();
        return toDate.split('GMT')[0];
    };

    const gradeFormatter = (cell) => {
        if (cell.getRow().getData().timegraded === '0') { return ''; }
        const value = cell.getValue();
        return Number(value);
    };

    const deleteFormatter = (cell) => {
        const index = cell.getRow().getIndex();
        return `<input type="checkbox" data-id="${index}" class="tabulatorSelect"/>`;
    };

    const aigradeFormatter = (cell) => {
        if (cell.getValue() === null) { return ''; }
        return Number(cell.getValue());
    };

    let inprogress = await getString('submittedstatus', 'mod_codescore');
    let finished = await getString('finishedstatus', 'mod_codescore');
    let pending = await getString('pendingstatus', 'mod_codescore');

    const statusFormatter = (cell) => {
        let result;
        switch (cell.getValue()) {
            case 'inprogress':
                result = inprogress;
                break;
            case 'finished':
                result = finished;
                break;
            case 'pending':
                result = pending;
                break;
        }
        return result;
    };

    if (!data.attempts[0]) {
        document.getElementsByClassName('reportTitle')[0].className += ' hidden';
        document.getElementById('tableWrapper').className += ' hidden';
        let noReports = await getString('noreports', 'mod_codescore');
        if (document.getElementById('page-mod-codescore-reports')) {
            document.querySelector("[role=main]").innerHTML = `<h3>${noReports}</h3>`;
        }
    }
    let tabledata = data.attempts;
    let table = new Tabulator("#tableWrapper", {
        data: tabledata, //assign data to table
        layout: "fitColumns", //fit columns to width of table (optional)
        textDirection: "ltr",
        columns: [ //Define Table Columns
            {
                title: "", field: "deletebtn", widthGrow: 0.1, formatter: deleteFormatter,
                hozAlign: "center", vertAlign: 'center', headerSort: false, resizable: false
            },
            {
                title: await getString('tablename', 'mod_codescore'), field: "username",
                formatter: link, resizable: false, widthGrow: 1.5},
            {
                title: await getString('startedat', 'mod_codescore'),
                field: "timestart", formatter: dateFormatter, resizable: false,
                widthGrow: 1.5
            },
            {
                title: await getString('timefinish', 'mod_codescore'), field: "timefinish",
                formatter: dateFormatter, resizable: false,
                widthGrow: 1.5},
            {
                title: await getString('timegraded', 'mod_codescore'),
                field: "timegraded", formatter: dateFormatter, resizable: false,
                widthGrow: 1.5},
            {
                title: await getString('tableteachergrade', 'mod_codescore'),
                field: "grade", formatter: gradeFormatter, resizable: false},
            {
                title: await getString('tableaigrade', 'mod_codescore'),
                field: "aigrade", formatter: aigradeFormatter, resizable: false},
            {
                title: await getString('statusattempt', 'mod_codescore'),
                field: "state", formatter: statusFormatter, resizable: false},
        ],
    });
    // Event listeners

    table.on("renderComplete", () => {
        document.querySelectorAll('.tabulatorSelect').forEach((el) => {
            el.addEventListener('click', (e) => {
                e.stopPropagation();
                const id = e.target.dataset.id;
                if (e.target.checked) {
                    table.selectRow(Number(id));
                } else {
                    table.deselectRow(Number(id));
                }
                const deleteBtn = document.getElementById('deleteAttempts');
                const regradeBtn = document.getElementById('regradeAttempts');
                if (!table.getSelectedData()[0]) {
                    deleteBtn.className = 'disabled btn btn-secondary mb-2';
                    regradeBtn.className = 'disabled btn btn-secondary mb-2';
                }
                else {
                    deleteBtn.className = 'btn btn-secondary mb-2';
                    regradeBtn.className = 'btn btn-secondary mb-2';
                }
            });
        });
    });
    document.getElementById('regradeAttempts').addEventListener('click', async () => {
        // eslint-disable-next-line curly
        if (!table.getSelectedData()) return;
        const selected = table.getSelectedData().map((el) => {
            return el.id;
        });
        await Ajax.call([{
            methodname: 'mod_codescore_regrade_attempts',
            args: {
                ids: selected.join(","),
            },
        }]);
        location.reload();
    });
    document.getElementById('deleteAttemptsConfirm').addEventListener('click', async() => {
        // eslint-disable-next-line curly
        if (!table.getSelectedData()) return;
        const selected = table.getSelectedData().map((el) => {
            return el.id;
        });
        await Ajax.call([{
            methodname: 'mod_codescore_delete_attempts',
            args: {
                ids: selected.join(","),
                cmid: cmid
            },
        }]);
        location.reload();
    });
    if (!data.candelete) {
        table.on("tableBuilt", function () {
            table.getColumns()[0].delete();
        });
    }
};