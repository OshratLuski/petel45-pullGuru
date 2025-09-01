/* eslint-disable promise/catch-or-return */
/* eslint-disable camelcase */
/* eslint-disable jsdoc/require-jsdoc */
/* eslint-disable require-jsdoc */
/* eslint-disable promise/always-return */
/* eslint-disable no-unused-vars */
/* eslint-disable babel/object-curly-spacing */
/* eslint-disable space-before-function-paren */
/* eslint-disable capitalized-comments */
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
    "https://unpkg.com/tabulator-tables/dist/js/tabulator.min.js"
], function ($, Tabulator) {
    return {
        init: function (data) {
            var searchInput = $("#searchInput");
            var searchQuestionNumber = $("#searchQuestionNumber");
            var table;
            var baseColumns = [
                { title: "Attempt ID", field: "qattid", sorter: "number" },
                { title: "Question ID", field: "qid", formatter: "html" },
                { title: "Question Number", field: "qnumber" },
                { title: "Question Name", field: "questionname", formatter: "html" },
                { title: "Quiz Name", field: "quizname", formatter: "html" },
                { title: "User ID", field: "quizattuserid", sorter: "number" },
                { title: "Attempt Number", field: "quizattattempt", sorter: "number" },
                { title: "Response Summary", field: "qattresponsesummary" },
                { title: "Attempt Time", field: "attempttime" }, 
                { title: "Course Name", field: "coursename", formatter: "html" } 
            ];

            try {
                table = new Tabulator("#questionattempts", {
                    layout: "fitColumns",
                    pagination: true,
                    paginationMode: "remote",
                    paginationSize: 20,
                    paginationSizeSelector: [20, 40, 100, 1000],
                    ajaxURL: M.cfg.wwwroot + "/question/type/mlnlpessay/allreportajax.php",
                    ajaxContentType: "json",
                    columns: baseColumns,
                    ajaxResponse: function (url, params, response) {
                        var dynamicColumns = [...baseColumns];
                        
                        for (var i = 10; i < response.coltitles.length; i++) { 
                            dynamicColumns.push({ 
                                title: response.coltitles[i], 
                                field: "cat" + (i - 10),
                                sorter: "string" 
                            });
                        }
                    
                        table.setColumns(dynamicColumns);
                    
                        return {
                            data: response.attempts.map(function (row) {
                                var rowData = {
                                    qattid: row[0],
                                    qid: row[1],
                                    qnumber: row[2],
                                    questionname: row[3],
                                    quizname: row[4],
                                    quizattuserid: row[5],
                                    quizattattempt: row[6],
                                    qattresponsesummary: row[7],
                                    attempttime: row[8], 
                                    coursename: row[9] 
                                };
                                for (var i = 10; i < row.length; i++) { 
                                    rowData["cat" + (i - 10)] = row[i]; 
                                }
                                return rowData;
                            }),
                            last_page: response.last_page
                        };
                    },
                    ajaxURLGenerator: function (url, config, params) {
                        url = M.cfg.wwwroot + "/question/type/mlnlpessay/allreportajax.php";
                        url += "?limit=" + params.size + "&offset=" + ((params.page - 1) * params.size);
                        if (params.sort && params.sort.length > 0) {
                            url += "&sort=" + params.sort[0].dir + "&col=" + params.sort[0].field;
                        }
                        if (searchInput.val()) {
                            url += "&search=" + encodeURIComponent(searchInput.val());
                        }
                        if (searchQuestionNumber.val()) {
                            url += "&questionnumber=" + encodeURIComponent(searchQuestionNumber.val());
                        }
                        return url;
                    },
                    initialSort: [{ column: "qattid", dir: "asc" }]
                });

                searchInput.on("keyup", function () {
                    table.setData();
                });

                searchQuestionNumber.on("keyup", function () {
                    var selectedValue = $(this).val();
                    if (selectedValue) {
                        var newUrl = new URL(window.location);
                        newUrl.searchParams.set('qidnumber', selectedValue);
                        window.history.pushState({}, '', newUrl);
                    } else {
                        var newUrl = new URL(window.location);
                        newUrl.searchParams.delete('qidnumber');
                        window.history.pushState({}, '', newUrl);
                    }
                    table.setData();
                });

                var urlParams = new URLSearchParams(window.location.search);
                if (urlParams.has('qidnumber')) {
                    var initialQid = urlParams.get('qidnumber');
                    searchQuestionNumber.val(initialQid);
                }

                $("#downloadFullDataButton").click(function () {
                    fetch(M.cfg.wwwroot + "/question/type/mlnlpessay/allreportajax.php?limit=0")
                        .then(response => response.json())
                        .then(data => {
                            exportDataAsCsv(data);
                        })
                        .catch(error => console.error('Download error:', error));
                });

            } catch (error) {
                console.error('Table initialization error:', error);
            }

            function exportDataAsCsv(data) {
                const colTitles = data.coltitles;
                const attempts = data.attempts;
                let csv = colTitles.map(title => `"${title}"`).join(",") + "\n";
                attempts.forEach(attempt => {
                    csv += attempt.map(field => {
                        if (field === null || field === undefined) return '""';
                        return `"${field.toString().replace(/"/g, '""')}"`;
                    }).join(",") + "\n";
                });

                const blob = new Blob([csv], { type: "text/csv;charset=utf-8" });
                const link = document.createElement("a");
                link.href = window.URL.createObjectURL(blob);
                link.download = "all_questions_data.csv";
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
            }
        }
    };
});