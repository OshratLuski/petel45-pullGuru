/* eslint-disable promise/catch-or-return */
/* eslint-disable camelcase */
/* eslint-disable jsdoc/require-jsdoc */
/* eslint-disable require-jsdoc */
/* eslint-disable promise/always-return */
/* eslint-disable no-unused-vars */
/* eslint-disable babel/object-curly-spacing */
/* eslint-disable space-before-function-paren */
/* eslint-disable capitalized-comments */
define([
  "jquery",
  "https://unpkg.com/tabulator-tables/dist/js/tabulator.min.js",
], function ($, Tabulator) {
  return {
    init: function (questionid, data) {
      var lang = $("html")[0].lang;
      var searchInput = $("#searchInput");
      var currenturl = "";
      var sortcolumnsids;
      var colTitles;

      var table = new Tabulator("#questionattempts", {
        last_page: data.last_page,
        pagination: true,
        paginationMode: "remote",
        paginationSize: 20,
        paginationSizeSelector: [20, 40, 100, 1000],
        ajaxContentType: "json",

        autoColumns: true,
        autoColumnsDefinitions: function (definitions) {
          definitions.forEach((column, index) => {
            column.headerSort = index < 4 ? true : false;
          });

          return definitions;
        },

        ajaxResponse: function (url, params, response) {
          sortcolumnsids = response.sortcolumnsids;
          colTitles = response.coltitles;
          var tableData2 = response.attempts.map(function (row) {
            var rowData = {};
            colTitles.forEach(function (title, index) {
              rowData[title] = row[index];
            });

            return rowData;
          });
          return {
            data: tableData2,
            last_page: response.last_page,
          };
        },
        filterMode: "remote",
        sortMode: "remote",
        ajaxLoader: true,
        ajaxLoaderLoading: "Fetching data from server...",
        ajaxURLGenerator: function (url, config, params) {
          url =
            M.cfg.wwwroot +
            "/question/type/essayrubric/questionreportajax.php";
          url += "?id=" + questionid;

          if (params.sort.length > 0) {
            url += "&sort=" + params.sort[0].dir;
            url += "&col=" + sortcolumnsids[params.sort[0].field];
          }
          if (searchInput.val()) {
            url += "&search=" + searchInput.val();
          }
          currenturl = url + "&limit=0";
          url += "&limit=" + params.size;
          url += "&offset=" + (params.page - 1) * params.size;
          return url;
        },
        ajaxURL:
          M.cfg.wwwroot +
          "/question/type/essayrubric/questionreportajax.php" +
          "?id=" +
          questionid,
      });
      searchInput.on("keyup", function () {
        table.setData(
          M.cfg.wwwroot +
            "/question/type/essayrubric/questionreportajax.php" +
            "?id=" +
            questionid
        );
      });
      function downloadFullData() {
        fetch(currenturl)
          .then((response) => response.json())
          .then((data) => {
            exportDataAsCsv(data);
          });
      }
      $("#downloadFullDataButton").click(function () {
        downloadFullData();
      });
      function exportDataAsCsv(data) {
        const colTitles = data.coltitles;
        const attempts = data.attempts;
        let csv = colTitles.map((title) => `"${title}"`).join(",") + "\n";
        attempts.forEach((attempt) => {
          attempt.forEach((field, index) => {
            if (field === null || field === undefined) {
              csv += `""`;
            } else {
              field = field.toString().replace(/"/g, '""');
              csv += `"${field}"`;
            }
            if (index < attempt.length - 1) {
              csv += ",";
            }
          });
          csv += "\n";
        });
        const blob = new Blob([csv], { type: "text/csv;charset=utf-8" });
        const link = document.createElement("a");
        link.href = window.URL.createObjectURL(blob);
        link.setAttribute("download", "data.csv");
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
      }
    },
  };
});
