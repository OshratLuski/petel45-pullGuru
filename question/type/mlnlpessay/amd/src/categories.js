
define(['jquery', 'https://unpkg.com/tabulator-tables/dist/js/tabulator.min.js'], function ($, Tabulator) {

    return {

        init: function (input, params, hascapedit) {

            tabledata = [];
            tabledata = Object.keys(input).map(function (key) {
                return input[key];
            });
            tabledata.sort((a, b) => a.sortorder - b.sortorder);

            var lang = $('html')[0].lang;
            if (lang === "he") {
                var table = new Tabulator("#rubiccategoryfulltable-table", {
                    movableRows: hascapedit,
                    height: 540,
                    data: tabledata,
                    textDirection: "rtl",
                    layout: "fitColumns",
                    columns: [
                        {
                            rowHandle: hascapedit,
                            formatter: "handle",
                            headerSort: false,
                            // frozen: true,
                            width: 30,
                            minWidth: 30
                        },
                        {
                            field: "iscategoryselected",
                            hozAlign: "center",
                            headerHozAlign: "center",
                            headerSort: false,
                            formatter: (cell) => {
                                catid = cell.getRow()._row.data.id;
                                const value = cell.getValue();
                                return `
                                    <input class="n_catch n_catch2" ${hascapedit ? "" : 'disabled=disabled'} type="checkbox" ${value ? 'checked="checked"' : ''} name="n_categories_${catid}[]" id="n_categories_${catid}"/>
                                    `;
                            },
                            title: hascapedit ? `
                        <span>Select all</span>
                        <br>
                        <input class="n_catch_all" type="checkbox" name="n_catch_all" id="n_catch_all"/>
                        ` : `<span>Select all</span>`,
                        },
                        {
                            title: "Name",
                            field: "name",
                            width: '40%',
                            // hozAlign: "right",
                            headerFilter: "input",
                            headerFilterLiveFilter: true,
                            headerSort: false
                        },
                        {
                            title: "Type",
                            field: "type",
                            editor: hascapedit ? "list" : false,
                            editorParams: {
                                values: params.types,
                                allowEmpty: true,
                                listOnEmpty: true,
                                clearable: true,
                                itemFormatter: (label,
                                    value,
                                    item,
                                    element) => {
                                    return `
                                <strong>${label}</strong><br/>
                                `;
                                },
                            },
                            formatter: function (cell, formatterParams, onRendered) {
                                return params.types[cell.getValue()] == '—' ? '' : params.types[cell.getValue()];
                            },
                            headerSort: false,
                            cssClass: hascapedit ? "editablecol" : false,
                        },
                        {
                            title: "Weight",
                            field: "weight",
                            // hozAlign: "left",
                            editor: hascapedit ? true : false,
                            cssClass: hascapedit ? "editablecol" : false,
                            validator: ["min:0",
                                "max:100",
                                "integer"],
                            headerSort: false,
                        },
                        {
                            title: "Tag",
                            field: "tag",
                            // hozAlign: "left",
                            headerFilter: "input",
                            headerFilterLiveFilter: true,
                            headerSort: false
                        },
                        {
                            title: "Topics",
                            field: "topics",
                            // hozAlign: "left",
                            headerFilter: "input",
                            headerFilterLiveFilter: true,
                            headerSort: false
                        },
                        {
                            title: "Subtopics",
                            field: "subtopics",
                            // hozAlign: "left",
                            headerFilter: "input",
                            headerFilterLiveFilter: true,
                            headerSort: false
                        },
                        {
                            title: "Lang",
                            field: "lang",
                            // hozAlign: "left",
                            headerFilter: "input",
                            headerFilterLiveFilter: true,
                            headerSort: false
                        },
                    ],
                });
            }
            else {
                var table = new Tabulator("#rubiccategoryfulltable-table", {
                    movableRows: hascapedit,
                    height: 540,
                    data: tabledata,
                    textDirection: "ltr",
                    layout: "fitColumns",
                    columns: [
                        {
                            rowHandle: hascapedit,
                            formatter: "handle",
                            headerSort: false,
                            frozen: true,
                            width: 30,
                            minWidth: 30
                        },
                        {
                            field: "iscategoryselected",
                            hozAlign: "center",
                            headerHozAlign: "center",
                            headerSort: false,
                            formatter: (cell) => {
                                catid = cell.getRow()._row.data.id;
                                const value = cell.getValue();
                                return `
                                    <input class="n_catch n_catch2" ${hascapedit ? "" : 'disabled=disabled'} type="checkbox" ${value ? 'checked="checked"' : ''} name="n_categories_${catid}[]" id="n_categories_${catid}"/>
                                    `;
                            },
                            title: hascapedit ? `
                        <span>Select all</span>
                        <br>
                        <input class="n_catch_all" type="checkbox" name="n_catch_all" id="n_catch_all"/>
                        ` : `<span>Select all</span>`,
                        },
                        {
                            title: "Name",
                            field: "name",
                            width: '40%',
                            // hozAlign: "right",
                            headerFilter: "input",
                            headerFilterLiveFilter: true,
                            headerSort: false
                        },
                        {
                            title: "Type",
                            field: "type",
                            editor: hascapedit ? "list" : false,
                            editorParams: {
                                values: params.types,
                                allowEmpty: true,
                                listOnEmpty: true,
                                clearable: true,
                                itemFormatter: (label,
                                    value,
                                    item,
                                    element) => {
                                    return `
                                <strong>${label}</strong><br/>
                                `;
                                },
                            },
                            formatter: function (cell, formatterParams, onRendered) {
                                return params.types[cell.getValue()] == '—' ? '' : params.types[cell.getValue()];
                            },
                            headerSort: false,
                            cssClass: hascapedit ? "editablecol" : false,
                        },
                        {
                            title: "Weight",
                            field: "weight",
                            // hozAlign: "left",
                            editor: hascapedit ? true : false,
                            cssClass: hascapedit ? "editablecol" : false,
                            validator: ["min:0",
                                "max:100",
                                "integer"],
                            headerSort: false,
                        },
                        {
                            title: "Tag",
                            field: "tag",
                            // hozAlign: "left",
                            headerFilter: "input",
                            headerFilterLiveFilter: true,
                            headerSort: false
                        },
                        {
                            title: "Topics",
                            field: "topics",
                            // hozAlign: "left",
                            headerFilter: "input",
                            headerFilterLiveFilter: true,
                            headerSort: false
                        },
                        {
                            title: "Subtopics",
                            field: "subtopics",
                            // hozAlign: "left",
                            headerFilter: "input",
                            headerFilterLiveFilter: true,
                            headerSort: false
                        },
                        {
                            title: "Lang",
                            field: "lang",
                            // hozAlign: "left",
                            headerFilter: "input",
                            headerFilterLiveFilter: true,
                            headerSort: false
                        },
                    ],
                });
            }

            // Listeners.
            if (hascapedit) {

                table.on("tableBuilt", function () {
                    $("#n_catch_all").change(function () {
                        cells = table.getColumn('iscategoryselected').getCells();
                        cells.forEach(element => {
                            element.setValue(this.checked);
                        });
                    });
                    $("form").submit((e) => {
                        $('#rubiccategoryfulltable').attr('value', JSON.stringify(table.getData()));
                        return true;
                    });
                });

                table.on("cellClick", function (e, cell) {
                    if ($(e.target).hasClass('n_catch')) {
                        cell.setValue($(e.target).is(":checked"));
                    }
                });
            }

        },

    };
});
