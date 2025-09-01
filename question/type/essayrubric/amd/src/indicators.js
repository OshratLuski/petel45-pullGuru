/* eslint-disable space-infix-ops */
/* eslint-disable promise/catch-or-return */
/* eslint-disable promise/always-return */
/* eslint-disable curly */
/* eslint-disable max-len */
/* eslint-disable no-trailing-spaces */
/* eslint-disable space-before-function-paren */
/* eslint-disable block-scoped-var */
/* eslint-disable no-empty */
/* eslint-disable no-undef */
/* eslint-disable no-console */
/* eslint-disable no-dupe-keys */
/* eslint-disable no-unused-vars */
/* eslint-disable key-spacing */
/* eslint-disable spaced-comment */
/* eslint-disable capitalized-comments */
/* eslint-disable babel/semi */

// import $ from "jquery";
import Ajax from "core/ajax";
import * as Str from "core/str";
import Tabulator from "https://unpkg.com/tabulator-tables/dist/js/tabulator.min.js";

export default {
  init: function (params, hascapedit, usedindicators) {
    Ajax.call([
      {
        methodname: "qtype_essayrubric_get_indicators",
        args: {
        },
        done: function (result) {
          result = JSON.parse(result.result);
          renderIndicators(result);
        },
        fail: {},
      },
    ]);

    const renderIndicators = (input) => {
      var strings = [
        {
          key: "selectall",
          component: "qtype_essayrubric",
        },
        {
          key: "indicatorid",
          component: "qtype_essayrubric",
        },
        {
          key: "name",
          component: "qtype_essayrubric",
        },
        {
          key: "category",
          component: "qtype_essayrubric",
        },
        {
          key: "model",
          component: "qtype_essayrubric",
        },
        {
          key: "research",
          component: "qtype_essayrubric",
        },
        {
          key: "visible",
          component: "qtype_essayrubric",
        },
        {
          key: "search",
          component: "qtype_essayrubric",
        },
      ];
      Str.get_strings(strings).then(function (results) {
        let tabledata = [];
        tabledata = Object.keys(input).map(function (key) {
          return input[key];
        });
        tabledata.forEach((el, ind) => {
          tabledata[ind].visible = Number(el.visible);
          tabledata[ind].research = Number(el.research);
        });

        const direction = $("html").attr("dir");

        const toggleDisabled = () => {
          const outerElements = Array.from(
            document.querySelectorAll(".n_catch2")
          );
          outerElements.forEach((el) => {
            el.addEventListener("change", () => {
              if (
                $(".n_catch2:checked")[0] &&
                $("#ind_delete").hasClass("disabled")
              ) {
                $("#ind_delete").removeClass("disabled");
              }
              if (
                !$(".n_catch2:checked")[0] &&
                !$("#ind_delete").hasClass("disabled")
              ) {
                $("#ind_delete").addClass("disabled");
              }
            });
          });
        };

        const isEditable = (cell) => {
          let rowData = cell.getRow().getData();
          let isEditable = !usedindicators.includes(rowData.indicatorid);
          // eslint-disable-next-line curly
          if (!isEditable) cell.getElement().className += " cell-disabled";

          return isEditable;
        };

        var table = new Tabulator("#indicators-table", {
          //movableRows: hascapedit,
          height: 540,
          data: tabledata,
          textDirection: direction,
          layout: "fitColumns",
          reactiveData: true,
          columns: [
            // {
            //   rowHandle: hascapedit,
            //   formatter: "handle",
            //   headerSort: false,
            //   frozen: true,
            //   width: 30,
            //   minWidth: 30,
            // },
            {
              field: "isindicatorselected",
              hozAlign: "center",
              headerHozAlign: "center",
              headerSort: false,
              editable: isEditable,
              formatter: (cell) => {
                catid = cell.getRow()._row.data.id;
                const value = cell.getValue();
                let rowData = cell.getRow().getData();
                let isCheckEditable = !usedindicators.includes(
                  rowData.indicatorid
                );
                return `
                                    <input class="n_catch n_catch2" ${
                                      hascapedit && isCheckEditable
                                        ? ""
                                        : "disabled=disabled"
                                    } type="checkbox" ${
                  value ? 'checked="checked"' : ""
                } name="n_indicators_${catid}[]" id="n_indicators_${catid}"/>
                                    `;
              },
              title: hascapedit
                ? `
                        <span>` +
                  results[0] +
                  `</span>
                        <br>
                        <input class="n_catch_all" type="checkbox" name="n_catch_all" id="n_catch_all"/>
                        `
                : `<span>` + results[0] + `</span>`,
            },
            {
              // title: "Indicatorid",
              title: results[1],
              // title: Str.get_string('indicatorid', 'qtype_essayrubrics'),
              field: "indicatorid",
              validator: "unique",
              editable: isEditable,
              width: "15%",
              editor: hascapedit ? "list" : false,
              headerFilter: "input",
              headerFilterPlaceholder: results[7],
              headerFilterLiveFilter: true,
              headerSort: false,
              editorParams: {
                valuesLookup: "active",
                autocomplete: true,
                allowEmpty: false,
                listOnEmpty: true,
                freetext: true,
                placeholderEmpty: false,
              },
            },
            {
              title: results[2],
              field: "name",
              width: "30%",
              editable: isEditable,
              editor: hascapedit ? "list" : false,
              headerFilter: "input",
              headerFilterPlaceholder: results[7],
              headerFilterLiveFilter: true,
              tooltip: true,
              validator: [
                "unique",
                (cell, value) => value !== "" && value !== " ",
              ],
              editorParams: {
                valuesLookup: "active",
                autocomplete: true,
                allowEmpty: false,
                listOnEmpty: true,
                freetext: true,
                placeholderEmpty: false,
              },
              headerSort: false,
            },
            {
              title: results[3],
              field: "category",
              editable: isEditable,
              editor: hascapedit ? "list" : false,
              headerFilter: "input",
              headerFilterPlaceholder: results[7],
              headerFilterLiveFilter: true,
              validator: [
                "unique",
                (cell, value) => value !== "" && value !== " ",
              ],
              editorParams: {
                valuesLookup: "active",
                autocomplete: true,
                allowEmpty: false,
                listOnEmpty: true,
                freetext: true,
                placeholderEmpty: false,
                // itemFormatter: (label, value, item, element) => {
                //   return `<strong>${label}</strong><br/>`;
                // },
              },
              formatter: function (cell, formatterParams, onRendered) {
                return cell.getValue() === "—" ? "" : cell.getValue();
              },
              headerSort: false,
              // cssClass: hascapedit ? "editablecol" : false,
            },
            {
              title: results[4],
              field: "model",
              editable: isEditable,
              editor: hascapedit ? "list" : false,
              headerFilter: "input",
              headerFilterPlaceholder: results[7],
              tooltip: true,
              headerFilterLiveFilter: true,
              validator: [
                "unique",
                (cell, value) => value !== "" && value !== " ",
              ],
              editorParams: {
                //values: params.models,
                valuesLookup: "active",
                autocomplete: true,
                allowEmpty: false,
                listOnEmpty: true,
                freetext: true,
                placeholderEmpty: false,
                // itemFormatter: (label, value, item, element) => {
                //   return `<strong>${label}</strong><br/>`;
                // },
              },
              formatter: function (cell, formatterParams, onRendered) {
                return cell.getValue() === "—" ? "" : cell.getValue();
              },
              headerSort: false,
              // cssClass: hascapedit ? "editablecol" : false,
            },
            {
              title: results[5],
              field: "research",
              hozAlign: "center",
              width: "10%",
              headerHozAlign: "center",
              headerSort: false,
              formatter: "tickCross",
              formatterParams: {
                allowEmpty: true,
                allowTruthy: true,
              },
              cellClick: function (e, cell) {
                cell.setValue(!cell.getValue());
              },
            },
            {
              title: results[6],
              field: "visible",
              hozAlign: "center",
              editable: isEditable,
              width: "10%",
              headerHozAlign: "center",
              headerSort: false,
              formatter: "tickCross",
              //editor: "tickCross",
              formatterParams: {
                allowEmpty: true,
                allowTruthy: true,
                tickElement:
                  "<i class='fa fa-eye eyeComponent' style='color: black;'></i>",
                crossElement:
                  "<i class='fa fa-eye-slash eyeComponent' style='color: black;'></i>",
              },
              cellClick: function (e, cell) {
                if (isEditable(cell)) {
                  cell.setValue(!cell.getValue());
                }
              },
            },
          ],
        });

        // Listeners.
        table.on("dataProcessed", () => {
          toggleDisabled();
        });

        if (hascapedit) {
          table.on("tableBuilt", function () {
            $("#n_catch_all").change(function () {
              cells = table.getColumn("isindicatorselected").getCells();
              cells.forEach((element) => {
                if ($(element.getElement()).hasClass("cell-disabled")) return;
                element.setValue(this.checked);
              });
              if (
                $(".n_catch2:checked")[0] &&
                $("#ind_delete").hasClass("disabled")
              ) {
                $("#ind_delete").removeClass("disabled");
              }
              if (
                !$(".n_catch2:checked")[0] &&
                !$("#ind_delete").hasClass("disabled")
              ) {
                $("#ind_delete").addClass("disabled");
              }
              toggleDisabled();
            });

            document
              .getElementById("ind_submit")
              .addEventListener("click", () => {
                let updatedTableData = table.getData();

                const empty = updatedTableData.find(
                  (element) => element.name == undefined
                );

                if (empty) {
                  require(["core/toast"], (Toast) => {
                    Toast.add("Empty indicator name!", {
                      type: "danger",
                    });
                  });
                  return false;
                }

                let indicatorsdata = JSON.stringify(table.getData());
                document.getElementById("indicatorsdata").value =
                  indicatorsdata;
                document.getElementById("indicatorsdata").form.submit();

                return true;
              });
          });
          table.on("renderComplete", () => {
            let cells = [
              ...table.getColumn("visible").getCells(),
              ...table.getColumn("research").getCells(),
              ...table.getColumn("isindicatorselected").getCells(),
            ];
            cells.forEach((cell) => {
              isEditable(cell);
            });
          });
          document
            .getElementById("ind_addnew")
            .addEventListener("click", function () {
              let lastId = 0;
              let newData = table.getData();
              newData.forEach((el) => {
                if (el.indicatorid) {
                  lastId = Number(el.indicatorid);
                }
              });
              newData.push({
                indicatorid: lastId + 1,
                research: 1,
                visible: 1,
              });
              table.replaceData(newData);
            });
          document
            .getElementById("ind_delete")
            .addEventListener("click", () => {
              const elements = Array.from($(".n_catch2"));
              const oldData = table.getData();
              const newData = [];
              elements.forEach((el, ind) => {
                if (!el.checked) {
                  newData.push(oldData[ind]);
                }
              });
              table.replaceData(newData);
              $(".n_catch_all")[0].checked = false;
              $("#ind_delete").addClass("disabled");
            });
        }
      });
    };
  },
};
