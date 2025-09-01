/* eslint-disable space-infix-ops */
/* eslint-disable curly */
/* eslint-disable promise/catch-or-return */
/* eslint-disable promise/always-return */
/* eslint-disable no-unreachable */
/* eslint-disable babel/object-curly-spacing */
/* eslint-disable no-empty-function */
/* eslint-disable no-multiple-empty-lines */
/* eslint-disable babel/semi */
/* eslint-disable brace-style */
/* eslint-disable block-spacing */
/* eslint-disable max-statements-per-line */
/* eslint-disable semi-spacing */
/* eslint-disable no-debugger */
/* eslint-disable space-before-blocks */
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

import $ from "jquery";
import * as Str from "core/str";
import ModalFactory from 'core/modal_factory';
import ModalEvents from 'core/modal_events';
import Notification from 'core/notification';
import Tabulator from "https://unpkg.com/tabulator-tables/dist/js/tabulator.min.js";

export default {
  init: function (input, params, hascapedit, researchquestion) {
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
        key: "type",
        component: "qtype_essayrubric",
      },
      {
        key: "weight",
        component: "qtype_essayrubric",
      },
      {
        key: "totalweight",
        component: "qtype_essayrubric",
      },
      {
        key: "search",
        component: "qtype_essayrubric",
      },
    ];
    Str.get_strings(strings).then(function (results) {
      let table;
      const reinitTable = () => {
        if (table !== undefined) {
          // table.destroy();
          table.replaceData(table.getData());
        }
        // table = new Tabulator("#questionindicatorfulltable-table", tableoptions);
      };

      let availindicators = [];

      const updateAutocomplete = (cell = null) => {
        let allvalues = [];
        if (cell) {
          allvalues = cell.getColumn()._column.cells.map((col, i) => {
            return col.value;
          });
        } else {
          allvalues = tabledata.map((col, i) => {
            return col.name;
          });
        }
        availindicators = [
          ...new Set(params.availindicators.concat(allvalues)),
        ];
        availindicators = availindicators.filter((el) => {
          return el !== undefined;
        });
      };

      const updateIndicatorsId = (cell = null) => {
        let row = cell.getRow();

        params.availindicatorsfull.forEach((element) => {
          const matchingElement = params.availindicatorsfull.find(
            (element) => element.name === cell.getData().name
          );
          if (matchingElement) {
            row.update({ indicatorid: matchingElement.indicatorid });
          } else {
            row.update({ indicatorid: null });
          }
        });

        params.availindicatorsfull = params.availindicatorsfull.filter((el) => {
          return el !== undefined && el !== "undefined";
        });
      };

      let tabledata = Object.keys(input).map((key) => input[key]);

      const direction = $("html").attr("dir");
      //TODO fetch readonly from backend
      const readOnly = researchquestion;

      let tableoptions = {
        movableRows: hascapedit && readOnly === "0",
        footerElement:
          "<div class='totalWeight'><span>" +
          results[9] +
          "</span> <span id='weightNum'>0</span></div>",
        height: 540,
        data: tabledata,
        textDirection: direction,
        layout: "fitColumns",
        reactiveData: true,
        columns: [
          {
            rowHandle: hascapedit,
            formatter: "handle",
            headerSort: false,
            frozen: true,
            width: 30,
            minWidth: 30,
            visible: readOnly === "0",
          },
          {
            field: "isindicatorselected",
            hozAlign: "center",
            headerHozAlign: "center",
            headerSort: false,
            editable: readOnly === "0",
            formatter: (cell) => {
              const catid = cell.getRow()._row.data.id;
              const value = cell.getValue();
              return `
          <input ${
            readOnly === "1" ? "disabled" : ""
          } class="n_catch n_catch2" ${hascapedit ? "" : "disabled=disabled"}
          type="checkbox" ${
            value ? 'checked="checked"' : ""
          } name="n_indicators_${catid}[]"
          id="n_indicators_${catid}"/>
        `;
            },
            title: hascapedit
              ? `
          <span>` +
                results[0] +
                `</span>
          <br>
          <input ${
            readOnly === "1" ? "disabled" : ""
          } class="n_catch_all" type="checkbox" name="n_catch_all" id="n_catch_all"/>
          `
              : `<span>` + results[0] + `</span>`,
          },
          {
            title: results[2],
            field: "name",
            width: "50%",
            editable: readOnly === "0",
            editor: hascapedit ? "list" : false,
            headerFilter: "input",
            headerFilterPlaceholder: results[10],
            headerFilterLiveFilter: true,
            validator: [
              "unique",
              (cell, value) => value !== "" && value !== " ",
            ],
            editorParams: {
              values: availindicators,
              autocomplete: true,
              allowEmpty: false,
              listOnEmpty: true,
              freetext: true,
              placeholderEmpty: false,
            },
            headerSort: false,
            cellEdited: function (cell) {
              updateIndicatorsId(cell);

              updateAutocomplete(cell);

              for (const column of tableoptions.columns) {
                if (column.field === "name") {
                  column.editorParams.values = availindicators;
                }
              }

              reinitTable();

              // TODO: check main indicator
            },
          },
          {
            title: results[7],
            field: "type",
            width: "20%",
            editable: readOnly === "0",
            editor: hascapedit ? "list" : false,
            editorParams: {
              values: params.types,
              allowEmpty: true,
              listOnEmpty: true,
              clearable: true,
              // itemFormatter: (label, value, item, element) => {
              //   return `
              //     <strong>${label}</strong><br/>
              //   `;
              // },
            },
            formatter: (cell, formatterParams, onRendered) => {
              return params.types[cell.getValue()] === "—"
                ? ""
                : params.types[cell.getValue()];
            },
            headerSort: false,
            // cssClass: hascapedit ? "editablecol" : false,
          },
          {
            title: results[8],
            field: "weight",
            hozAlign: "left",
            editable: readOnly === "0",
            maxWidth: 100,
            headerHozAlign: "center",
            cellEdited: (cell) => {
              cell.setValue(Number(cell.getValue()));
            },
            editor: hascapedit ? true : false,
          },
        ],
      };

      // table = new Tabulator(
      //   "#questionindicatorfulltable-table",
      //   tableoptions
      // );

      updateAutocomplete();

      // updateAutocomplete(cell);

      for (const column of tableoptions.columns) {
        if (column.field === "name") {
          column.editorParams.values = availindicators;
        }
      }
      // reinitTable();

      // reinitTable();
      table = new Tabulator("#questionindicatorfulltable-table", tableoptions);

      const rerenderTotalWeight = () => {
        let totalWeightSum = 0;

        document
          .querySelectorAll(".tabulator-row .tabulator-cell:nth-last-child(2)")
          .forEach((el) => {
            let currentNum = Number(el.innerText);
            if (isNaN(currentNum)) {
              return;
            }
            totalWeightSum += currentNum;
          });

        $("#weightNum")[0].innerText = totalWeightSum;
      };

      table.on("cellEdited", function (cell) {
        rerenderTotalWeight();
      });

      table.on("dataProcessed", () => {
        rerenderTotalWeight();
        checkAddEvent();
      });

      // Listeners.

      const checkAddEvent = () => {
        const outerElements = Array.from(
          document.querySelectorAll(".n_catch2")
        );
        outerElements.forEach((el) => {
          el.addEventListener("click", () => {
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

      table.on("renderComplete", checkAddEvent);
      table.on("renderComplete", () => {
        $(".tabulator-row-handle-box").attr(
          "class",
          "icon fa fa-arrows fa-fw  iconsmall"
        );
      });
      if (hascapedit) {
        table.on("tableBuilt", function () {
          if (readOnly === "1") $("#ind_addnew")[0].disabled = true;
          $("#n_catch_all").change(function () {
            const cells = table.getColumn("isindicatorselected").getCells();
            cells.forEach((element) => {
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
            checkAddEvent();
          });
          $("form").submit((e) => {
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

            let datajson = JSON.stringify(updatedTableData);

            $("#questionindicatorfulltable").attr("value", datajson);
            return true;
          });
        });

        // table.on("cellClick", function (e, cell) {
        //     if ($(e.target).hasClass("n_catch")) {
        //         cell.setValue($(e.target).is(":checked"));
        //     }
        // });

        document
          .getElementById("ind_addnew")
          .addEventListener("click", function () {
            if (readOnly === "1") return;
            let newData = table.getData();
            newData.push({ weight: 0 });

            table.replaceData(newData);
            checkAddEvent();
          });
        document.getElementById("ind_delete").addEventListener("click", () => {

          const elements = Array.from($(".n_catch2"));
          const oldData = table.getData();
          const newData = [];
          elements.forEach((el, ind) => {
            if (!el.checked) {
              newData.push(oldData[ind]);
            }
          });

          if (oldData.length !== newData.length) {

            Str.get_strings([
              { key: 'labeldeleteindicators', component: 'qtype_essayrubric' },
              { key: 'areyoushure', component: 'qtype_essayrubric' },
              { key: 'success', component: 'qtype_essayrubric' },
            ]).done(function (strings) {
              var modalPromise = ModalFactory.create({
                type: ModalFactory.types.SAVE_CANCEL,
                title: strings[0],
                body: strings[1]
              });

              $.when(modalPromise).then(function (fmodal) {

                fmodal.setSaveButtonText(strings[2]);

                // Handle save event.
                fmodal.getRoot().on(ModalEvents.save, function () {
                  table.replaceData(newData);
                  $(".n_catch_all")[0].checked = false;
                  $("#ind_delete").addClass("disabled");
                });

                return fmodal;
              }).done(function (modal) {
                modal.show();
              }).fail(Notification.exception);
            })
          }
        });
      }
    });
  },
};
