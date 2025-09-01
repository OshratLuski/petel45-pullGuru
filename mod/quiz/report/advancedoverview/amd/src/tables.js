/* eslint-disable no-console */
/* eslint-disable require-jsdoc */
/* eslint-disable max-len */
/* eslint-disable complexity */
/* eslint-disable no-unused-vars */
/* eslint-disable no-undef */
/* eslint-disable jsdoc/require-jsdoc */
import Notification from "core/notification";
import * as Str from "core/str";
import $ from "jquery";
import Tabulator from "report/advancedoverview/js/tabulator.min.js";
import * as Main from "quiz_advancedoverview/main";
import * as studentsTableActions from "quiz_advancedoverview/studentsTableActions";

const locale = getLocale();

export let QLENGTH = 0;
const SELECTORS = {};
export const TEMPCONFIG = {};

let outerQuestions;

export const TABLES = {};
export const translatedStrings = {};
export const hideLoadingIcon = () => {
    document.body.classList.remove('petel-loading');
};

// function to initialize table of diagnostic questions
export const initdiagnosticquestionstable = function (data, questions) {
    let textDirection = $("html").attr("dir");

    outerQuestions = questions;
    let tabledata = JSON.parse(data);
    QLENGTH = tabledata.length;

    var wrongTitle = locale === 'he-IL' ? "שגו" : "Wrong";

    let content = {
        headerSortElement: function (column, dir) {
            switch (dir) {
                case "asc":
                    return "<i class='fas fa-sort-up'>";
                case "desc":
                    return "<i class='fas fa-sort-down'>";
                default:
                    return "<i class='fas fa-sort'>";
            }
        },
        locale: locale,
        data: tabledata,
        autoColumns: true,
        movableRows: false,
        maxHeight: 280,
        textDirection: textDirection,
        layout: "fitColumns",
        autoColumnsDefinitions: function (definitions) {
            definitions.forEach((column, i) => {
                switch (i) {
                    case 0:
                        column.maxWidth = "50px";
                        break;
                    case 1:
                        column.headerSort = false;
                        column.formatter = "html";
                        column.widthGrow = 5;
                        column.hozAlign = "start";
                        column.vertAlign = "middle";
                        break;
                    case 2:
                        column.maxWidth = "80px";
                        break;
                    case 3:
                        column.maxWidth = "90px";
                        break;
                    case 4:
                        column.maxWidth = "120px";
                        break;
                    case 5:
                        column.maxWidth = "160px";
                        break;
                    case 6:
                        column.maxWidth = "140px";
                        break;
                    default:
                        column.hozAlign = "center";
                        column.vertAlign = "middle";
                        column.widthGrow = 0;
                        break;
                }
            });
            return definitions;
        },
        rowFormatter: function (row) {
            row.getElement().style.height = "48px";
        },
        initialSort: [
            { column: wrongTitle, dir: "desc" },
        ],
    };

    TABLES.diagnosticQuestionsTable = new Tabulator("#diagnostic-table", content);
};

export const initquestionstable = function (data, questions) {
    SELECTORS.studentstableNavFilter = document.getElementById(
        "studentstableNavFilter"
    );
    SELECTORS.studentsActionsCollapse = document.getElementById(
        "studentsActionsCollapse"
    );

    let textDirection = $("html").attr("dir");

    outerQuestions = questions;
    let tabledata = JSON.parse(data);
    QLENGTH = tabledata.length;

    var wrongTitle = locale === 'he-IL' ? "שגו" : "Wrong";

    let content = {
        headerSortElement: function (column, dir) {
            switch (dir) {
                case "asc":
                    return "<i class='fas fa-sort-up'>";
                case "desc":
                    return "<i class='fas fa-sort-down'>";
                default:
                    return "<i class='fas fa-sort'>";
            }
        },
        locale: locale,
        data: tabledata,
        autoColumns: true,
        movableRows: false,
        maxHeight: 280,
        textDirection: textDirection,
        layout: "fitColumns",
        autoColumnsDefinitions: function (definitions) {
            definitions.forEach((column, i) => {
                switch (i) {
                    case 0:
                        column.maxWidth = "50px";
                        break;
                    case 1:
                        column.headerSort = false;
                        column.formatter = "html";
                        column.widthGrow = 5;
                        column.hozAlign = "start";
                        column.vertAlign = "middle";
                        // Custom sorter function commented out
                        // column.sorter = function(a, b, aRow, bRow, column, dir, sorterParams) {
                        //   let aDesc = $(a).find('.description').text();
                        //   let bDesc = $(b).find('.description').text();
                        //   return aDesc.localeCompare(bDesc);
                        // };
                        break;
                    case 2:
                        column.sorter = function(a, b, aRow, bRow, column, dir, sorterParams) {
                            return question_table_number_sort(a, b);
                        };

                        column.maxWidth = "80px";
                        break;
                    case 3:
                        column.sorter = function(a, b, aRow, bRow, column, dir, sorterParams) {
                            return question_table_number_sort(a, b);
                        };

                        column.maxWidth = "90px";
                        break;
                    case 4:
                        column.sorter = function(a, b, aRow, bRow, column, dir, sorterParams) {
                            return question_table_number_sort(a, b);
                        };

                        column.maxWidth = "120px";
                        break;
                    case 5:
                        column.sorter = function(a, b, aRow, bRow, column, dir, sorterParams) {
                            return question_table_number_sort(a, b);
                        };

                        column.maxWidth = "160px";
                        break;
                    case 6:
                        column.sorter = function(a, b, aRow, bRow, column, dir, sorterParams) {
                            return question_table_number_sort(a, b);
                        };

                        column.maxWidth = "140px";
                        break;
                    default:
                        column.hozAlign = "center";
                        column.vertAlign = "middle";
                        column.widthGrow = 0;
                        break;
                }

                column.formatter = function (cell) {
                    return cell.getValue();
                };
            });
            return definitions;
        },
        rowFormatter: function (row) {
            row.getElement().style.height = "48px";
        },
        initialSort: [
            { column: wrongTitle, dir: "desc" },
        ],
    };


    TABLES.questionsTable = new Tabulator("#questions-table", content);
};

/**
 * Retrieves the current 'lang' attribute from the HTML tag and returns
 * the corresponding locale based on the provided mappings.
 *
 * Mappings:
 * 'he' => 'he-IL'
 * 'en' => 'en-GB'
 *
 * If no mapping is found, it returns the original 'lang' value.
 *
 * @returns {string} - The locale string corresponding to the 'lang' attribute of the HTML tag.
 *                     If no specific mapping is found, returns the 'lang' value directly.
 */
function getLocale() {
    let lang = document.documentElement.getAttribute("lang");
    let locale;
    switch (lang) {
        case "he":
            locale = "he-IL";
            break;
        case "he-kids":
            locale = "he-IL";
            break;
        case "en":
            locale = "en-GB";
            break;
        default:
            locale = lang;
    }

    return locale;
}

function question_table_number_sort (a, b) {
    let aNum = $(a).find('.numerical_value').text();
    let bNum = $(b).find('.numerical_value').text();

    return aNum - bNum;
}

/**
 * Compares and sorts two rows based on the attempt number.
 *
 * @param {object} aRow - The first row object to compare.
 * @param {object} bRow - The second row object to compare.
 * @param {string} dir - The sorting direction ('asc' for ascending, 'desc' for descending).
 * @returns {number} - A negative value if aRow should come before bRow, a positive value
 * if aRow should come after bRow, or 0 if they are equal.
 */
function attemptNumberSorter(aRow, bRow, dir) {
    const isChildRow = aRow._row.data.child || bRow._row.data.child;
    let a = "" + aRow._row.data.attempt_number;
    let b = "" + bRow._row.data.attempt_number;

    if (isChildRow) {
        return dir === "asc" ? b.localeCompare(a, locale) : a.localeCompare(b, locale);
    } else {
        return a.localeCompare(b, locale);
    }
}

export const initstudentstable = function (data, summary = '', anon = 0) {
    const rowsonpage = 20;
    const self = this;
    const strings = [
        {key: "viewingprofile", component: "quiz_advancedoverview"},
        {key: "passwordreset", component: "quiz_advancedoverview"},
        {key: "loginasthisstudent", component: "quiz_advancedoverview"},
        {key: "allcoursereport", component: "quiz_advancedoverview"},
        {key: "courseobservationreport", component: "quiz_advancedoverview"},
        {key: "sendingmessage", component: "quiz_advancedoverview"},
        {key: "recalculategrades", component: "quiz_advancedoverview"},
        {key: "fullname", component: "quiz_advancedoverview"},
        {key: "team", component: "quiz_advancedoverview"},
        {key: "state", component: "quiz_advancedoverview"},
        {key: "attempt_number", component: "quiz_advancedoverview"},
        {key: "grade", component: "quiz_advancedoverview"},
        {key: "starttime", component: "quiz_advancedoverview"},
        {key: "endtime", component: "quiz_advancedoverview"},
        {key: "duration", component: "quiz_advancedoverview"},
        {key: "empty", component: "quiz_advancedoverview"},
        {key: "pagetitle", component: "quiz_advancedoverview"},
        {key: "firsttitle", component: "quiz_advancedoverview"},
        {key: "lasttitle", component: "quiz_advancedoverview"},
        {key: "prevtitle", component: "quiz_advancedoverview"},
        {key: "nexttitle", component: "quiz_advancedoverview"},
        {key: "all", component: "quiz_advancedoverview"},
        {key: "firstname", component: "quiz_advancedoverview"},
        {key: "lastname", component: "quiz_advancedoverview"},
    ];

    Str.get_strings(strings)
        .done(function (str) {
            self.translatedStrings.viewingprofile = str[0];
            self.translatedStrings.passwordreset = str[1];
            self.translatedStrings.loginasthisstudent = str[2];
            self.translatedStrings.allcoursereport = str[3];
            self.translatedStrings.courseobservationreport = str[4];
            self.translatedStrings.sendingmessage = str[5];
            self.translatedStrings.recalculategrades = str[6];
            self.translatedStrings.fullname = str[7];
            self.translatedStrings.team = str[8];
            self.translatedStrings.state = str[9];
            self.translatedStrings.attemptNumber = str[10];
            self.translatedStrings.grade = str[11];
            self.translatedStrings.starttime = str[12];
            self.translatedStrings.endtime = str[13];
            self.translatedStrings.duration = str[14];
            self.translatedStrings.emptyTable = str[15];
            self.translatedStrings.pageTitle = str[16];
            self.translatedStrings.firstTitle = str[17];
            self.translatedStrings.lastTitle = str[18];
            self.translatedStrings.prevTitle = str[19];
            self.translatedStrings.nextTitle = str[20];
            self.translatedStrings.all = str[21];
            self.translatedStrings.firstname = str[22];
            self.translatedStrings.lastname = str[23];
            const clickMenu = [
                {
                    disabled: function (component) {
                        return component.getData().userprofilelink ? false : true;
                    },
                    label: `<span>${self.translatedStrings.viewingprofile}</span>`,
                    action: function (e, row) {
                        var link = row.getData().userprofilelink;
                        window.open(link, "_blank");
                    },
                },
                {
                    disabled: function (component) {
                        return component.getData().resetpasswordlink ? false : true;
                    },
                    label: `<span>${self.translatedStrings.passwordreset}</span>`,
                    action: function (e, row) {
                        var link = row.getData().resetpasswordlink;
                        window.open(link, "_blank");
                    },
                },
                {
                    disabled: function (component) {
                        return component.getData().loginaslink ? false : true;
                    },
                    label: `<span>${self.translatedStrings.loginasthisstudent}</span>`,
                    action: function (e, row) {
                        var link = row.getData().loginaslink;
                        window.open(link, "_blank");
                    },
                },
                {
                    disabled: function (component) {
                        return component.getData().completereportlink ? false : true;
                    },
                    label: `<span>${self.translatedStrings.allcoursereport}</span>`,
                    action: function (e, row) {
                        var link = row.getData().completereportlink;
                        window.open(link, "_blank");
                    },
                },
                {
                    disabled: function (component) {
                        return component.getData().outlinereportlink ? false : true;
                    },
                    label: `<span>${self.translatedStrings.courseobservationreport}</span>`,
                    action: function (e, row) {
                        var link = row.getData().outlinereportlink;
                        window.open(link, "_blank");
                    },
                },
                {
                    separator: true,
                },
                {
                    label: `<span>${self.translatedStrings.sendingmessage}</span>`,
                    action: function (e, row) {
                        var userid = row.getData().userid;
                        Main.showMessagePopup([userid]);
                    },
                },
                {
                    disabled: function (component) {
                        return component.getData().attemptid ? false : true;
                    },
                    label: `<span>${self.translatedStrings.recalculategrades}</span>`,
                    action: function (e, row) {
                        var rowData = row.getData();
                        Main.regradaAttemtps(
                            +Main.TEMPDATA.cmid,
                            +Main.TEMPDATA.courseid,
                            +Main.TEMPDATA.quizid,
                            rowData.attemptid
                        );
                    },
                },
            ];

            let textDirection = $("html").attr("dir");

            let tabledata = JSON.parse(data);

            const linkRegex = /<a[^>]*>([^<]+)<\/a>/;
            tabledata.map((el) => {
                const newGrade = linkRegex.exec(el.grade);
                if (newGrade) {
                    el.grade = newGrade[1];
                }

                return el;
            });

            let content = {
                headerSortElement: function (column, dir) {
                    switch (dir) {
                        case "asc":
                            return "<i class='fas fa-sort-up'>";
                        case "desc":
                            return "<i class='fas fa-sort-down'>";
                        default:
                            return "<i class='fas fa-sort'>";
                    }
                },

                placeholder: self.translatedStrings.emptyTable,
                locale: locale,

                langs:{
                    "he-IL":{
                        "pagination":{
                            "page_title": self.translatedStrings.pageTitle,
                            "first": self.translatedStrings.firstTitle,
                            "first_title": self.translatedStrings.firstTitle,
                            "last": self.translatedStrings.lastTitle,
                            "last_title": self.translatedStrings.lastTitle,
                            "prev": self.translatedStrings.prevTitle,
                            "prev_title": self.translatedStrings.prevTitle,
                            "next": self.translatedStrings.nextTitle,
                            "next_title": self.translatedStrings.nextTitle,
                            "all": self.translatedStrings.all,
                        },
                    },
                },

                data: tabledata,
                autoColumns: true,
                movableRows: false,
                textDirection: textDirection,
                frozenRows: 0,
                pagination: false,
                paginationSize: rowsonpage,
                rowHeight: 48,
                dataTree: true,
                dataTreeStartExpanded: true,
                layout: "fitDataFill",
                autoColumnsDefinitions: function (definitions) {
                    definitions.forEach((column, i) => {
                        const fieldName = column.field;
                        column.formatter = "html";
                        column.topCalc = customTopCalc;
                        column.topCalcParams = {field: column.field};
                        switch (fieldName) {
                            case "checkbox":
                                column.formatter = "rowSelection";
                                column.titleFormatter = "rowSelection";
                                column.titleFormatterParams = {
                                    rowRange: "active",
                                };
                                column.headerSort = false;
                                column.hozAlign = "center";
                                column.download = false;
                                break;

                            case "fullname":
                                column.title = self.translatedStrings.fullname;
                                column.headerSort = false;
                                column.cellStyle = "border-right: none;";
                                break;

                            case "team":
                                column.title = self.translatedStrings.team;
                                break;

                            case "state":
                                column.title = self.translatedStrings.state;
                                break;

                            case "attempt_number":
                                column.title = self.translatedStrings.attemptNumber;
                                column.sorter = "number";
                                column.hozAlign = "center";
                                column.cssClass = 'justify-content-center';
                                break;

                            case "grade":
                                column.title = self.translatedStrings.grade;
                                column.sorter = "number";
                                column.hozAlign = "center";
                                column.cssClass = 'justify-content-center';
                                break;

                            case "starttime":
                                column.title = self.translatedStrings.starttime;
                                column.formatter = function (cell) {
                                    var cellValue = cell.getValue();
                                    if (cellValue === '—') {
                                        let wrappedValue = document.createElement("span");
                                        wrappedValue.className = "wrapped-value";
                                        wrappedValue.innerHTML = cellValue;
                                        return wrappedValue;
                                    } else {
                                        return cellValue;
                                    }
                                };
                                break;

                            case "endtime":
                                column.title = self.translatedStrings.endtime;
                                column.formatter = function (cell) {
                                    var cellValue = cell.getValue();
                                    if (cellValue === '—') {
                                        let wrappedValue = document.createElement("span");
                                        wrappedValue.className = "wrapped-value";
                                        wrappedValue.innerHTML = cellValue;
                                        return wrappedValue;
                                    } else {
                                        return cellValue;
                                    }
                                };
                                break;

                            case "duration":
                                column.title = self.translatedStrings.duration;
                                column.sorter = function (
                                    a,
                                    b,
                                    aRow,
                                    bRow,
                                    column,
                                    dir,
                                    sorterParams
                                ) {
                                    // eslint-disable-next-line require-jsdoc
                                    function sortTime(el) {
                                        let split = el.split('|');
                                        return Number(split[1]);
                                    }

                                    return sortTime(a) - sortTime(b);
                                };
                                column.formatter = function (cell) {
                                    let split = cell.getValue().split('|');

                                    let cellValue = split[0];
                                    if (cellValue === '—') {
                                        let wrappedValue = document.createElement("span");
                                        wrappedValue.className = "wrapped-value";
                                        wrappedValue.innerHTML = cellValue;
                                        return wrappedValue;
                                    } else {
                                        return cellValue;
                                    }
                                };
                                break;

                            case "usermenubtn":
                                column.headerSort = false;
                                column.title = "";
                                column.formatter = function (cell) {
                                    if (cell.getValue() === false) {
                                        return ""; // Return empty string if usermenubtn is false
                                    } else {
                                        let dropdownBtn = document.createElement("button");
                                        dropdownBtn.className = "row-menu-btn btn";
                                        dropdownBtn.type = "button";
                                        dropdownBtn.innerHTML = '<i class="fa fa-ellipsis-v"></i>';
                                        return dropdownBtn;
                                    }
                                };
                                column.clickMenu = clickMenu;
                                column.download = false;
                                break;

                            case "attemptid":
                            case "user_attempt_code":
                            case "userid":
                            case "resetpasswordlink":
                            case "userprofilelink":
                            case "loginaslink":
                            case "completereportlink":
                            case "outlinereportlink":
                            case "lastname":
                            case "firstname":
                            case "_children":
                                column.visible = false;
                                break;
                            default:
                                // eslint-disable-next-line
                                let resultText;
                                // eslint-disable-next-line
                                if (outerQuestions && outerQuestions.texts) {
                                    let rawText = outerQuestions.texts[i - 20].replace(/\&nbsp;/g, '');
                                    if (rawText.length > 50) {
                                        resultText = '...' + rawText.slice(0, 49);
                                    } else {
                                        resultText = rawText;
                                    }
                                }
                                column.headerTooltip = resultText;
                                column.sorter = function (
                                    a,
                                    b,
                                    aRow,
                                    bRow,
                                    column,
                                    dir,
                                    sorterParams
                                ) {
                                    function sortQuestion(el) {
                                        let result = -3;
                                        if (el[0] === "—") {
                                            result = -2;
                                            return result;
                                        }
                                        if (el[0] === "<" && el.includes("=numerical_value")) {
                                            const pattern = /<div class=numerical_value>([\d.]+)<\/div>/;
                                            const match = el.match(pattern);
                                            if (el.includes("=complete")) {
                                                result = 100;
                                                return result;
                                            }
                                            if (el.includes("=incorrect")) {
                                                result = -1;
                                                return result;
                                            }
                                            result = match[1];
                                        }
                                        return result;
                                    }
                                    return sortQuestion(b) - sortQuestion(a);
                                };
                                column.formatter = function (cell) {
                                    var cellValue = cell.getValue();
                                    if (cellValue === '—') {
                                        let wrappedValue = document.createElement("span");
                                        wrappedValue.className = "wrapped-value";
                                        wrappedValue.innerHTML = cellValue;
                                        return wrappedValue;
                                    } else {
                                        return cellValue;
                                    }
                                };
                                break;
                        }
                        if (i <= 11) {
                            column.frozen = true;
                        }
                    });
                    return definitions;
                },
            };

            function customTopCalc(values, data, calcParams) {
                const parsedSummary = JSON.parse(summary);
                const fieldname = calcParams.field;

                if (parsedSummary[fieldname] !== undefined && parsedSummary[fieldname] !== false) {
                    return parsedSummary[fieldname];
                }
                return '';
            }

            // Enable pagination.
            if (tabledata.length > rowsonpage) {
                content.pagination = true;
            }
            self.TABLES.studentsTable = new Tabulator("#students-table", content);

            self.TABLES.studentsTable.on(
                "rowSelectionChanged",
                function (data, rows) {
                    Main.TEMPDATA.rowData = data;
                    let state = data.length > 0 ? false : true;
                    Main.changeStudentActionState(state);

                    let lentable = self.TABLES.studentsTable.getData().length;

                    let len;
                    if (rows.length === lentable) {
                        len = lentable - 1;
                    } else {
                        len = rows.length;
                    }

                    Main.setSelectedStudentsStr(len);
                }
            );

            $("#downloadXlsTable").off("click");
            $("#downloadXlsTable").on("click", function () {
                var tableData = self.TABLES.studentsTable.getData();

                var td2 = [];

                // Attaching children.
                tableData.forEach(function (row) {
                    if (row.userid === "summary") {
                        return;
                    }

                    if (row.hasOwnProperty("_children")) {
                        td2.push(row);
                        row._children.forEach(function (ch) {
                            td2.push(ch);
                        });
                        delete row._children;
                    } else {
                        td2.push(row);
                    }
                });

                // Modifications data for output.
                td2.forEach(function (row) {
                    delete row.checkbox;
                    delete row.usermenubtn;
                    delete row.attemptid;
                    delete row.user_attempt_code;
                    delete row.userid;
                    delete row.resetpasswordlink;
                    delete row.userprofilelink;
                    delete row.loginaslink;
                    delete row.completereportlink;
                    delete row.outlinereportlink;

                    delete row.fullname;

                    function stripHtml(dirtyString) {
                        const doc = new DOMParser().parseFromString(dirtyString, 'text/html');
                        return doc.body.textContent || '';
                    }

                    // Numeric value from HTML.
                    for (var prop1 in row) {
                        if (row.hasOwnProperty(prop1)) {
                            if (/^Q \d+ \/ \d+$/.test(prop1)) {
                                var value11 = row[prop1];
                                var match11 = value11.match(/<div.*>(\d+(?:\.\d+)?)<\/div>/);
                                if (match11) {
                                    var newValue11 = match11[1];
                                    row[prop1] = newValue11;
                                }

                                row[prop1] = stripHtml(row[prop1]);
                            }
                        }

                        if (row.hasOwnProperty(prop1)) {
                            if (prop1 === "fullname" || prop1 === "grade") {
                                var value12 = row[prop1].toString();
                                var match12 = value12.match(/<a[^>]*>([^<]*)<\/a>/i);
                                if (match12) {
                                    var newValue12 = match12[1];
                                    row[prop1] = newValue12;
                                }
                            }
                        }
                    }

                    // Changing titles.
                    for (var prop2 in row) {
                        if (row.hasOwnProperty(prop2)) {
                            switch (prop2) {
                                case "firstname":
                                    row[self.translatedStrings.firstname] = row[prop2];
                                    delete row[prop2];
                                    break;
                                case "lastname":
                                    row[self.translatedStrings.lastname] = row[prop2];
                                    delete row[prop2];
                                    break;
                                case "fullname":
                                    row[self.translatedStrings.fullname] = row[prop2];
                                    delete row[prop2];
                                    break;
                                case "team":
                                    row[self.translatedStrings.team] = row[prop2];
                                    delete row[prop2];
                                    break;
                                case "state":
                                    row[self.translatedStrings.state] = row[prop2];
                                    delete row[prop2];
                                    break;
                                case "attempt_number":
                                    row[self.translatedStrings.attemptNumber] = row[prop2];
                                    delete row[prop2];
                                    break;
                                case "grade":
                                    row[self.translatedStrings.grade] = row[prop2];
                                    delete row[prop2];
                                    break;
                                case "starttime":
                                    row[self.translatedStrings.starttime] = row[prop2];
                                    delete row[prop2];
                                    break;
                                case "endtime":
                                    row[self.translatedStrings.endtime] = row[prop2];
                                    delete row[prop2];
                                    break;
                                case "duration":
                                    row[self.translatedStrings.duration] = row[prop2];
                                    delete row[prop2];
                                    break;
                                default:
                                    var value = row[prop2];
                                    delete row[prop2];
                                    row[prop2] = value;
                                    break;
                            }
                        }
                    }
                });

                // Download file.
                var workbook = XLSX.utils.book_new();
                var worksheet = XLSX.utils.json_to_sheet(td2);
                XLSX.utils.book_append_sheet(workbook, worksheet, "Sheet1");
                XLSX.writeFile(workbook, "table-data.xlsx");
            });

            self.TABLES.studentsTable.on("tableBuilt", function () {
                let tableData = self.TABLES.studentsTable.getSelectedData();
                Main.TEMPDATA.rowData = tableData;
                let state = tableData.length > 0 ? false : true;
                Main.changeStudentActionState(state);
                Main.setSelectedStudentsStr(tableData.length);

                /**
                 * Sets the active class on the clicked target element and removes it from other sibling elements.
                 *
                 * @param {HTMLElement} target - The target element that should be set as active.
                 */
                function setActive(target) {
                    let parent = $(target).closest(".tabulator-col-title");
                    parent.find("span").removeClass("active");
                    $(target).addClass("active");
                }

                $("#students-table .tabulator-sortable.tabulator-col-sorter-element").on("click", function (e) {
                    let target = $(e.target);
                    if (!(target.hasClass("fname") || target.hasClass("sname"))) {
                        $('[tabulator-field="fullname"]')
                            .find("span")
                            .removeClass("active");
                    }
                });

                $('[tabulator-field="fullname"]').on("click", ".fname", function (e) {
                    let sort = e.target.dataset.sort;
                    let resultSort = sort === "desc" ? "asc" : "desc";
                    e.target.setAttribute("data-sort", resultSort);
                    setActive(e.target);
                    self.TABLES.studentsTable.setSort("firstname", resultSort);
                });
                $('[tabulator-field="fullname"]').on("click", ".lname", function (e) {
                    let sort = e.target.dataset.sort;
                    let resultSort = sort === "desc" ? "asc" : "desc";
                    e.target.setAttribute("data-sort", resultSort);
                    setActive(e.target);
                    self.TABLES.studentsTable.setSort("lastname", resultSort);
                });
                hideLoadingIcon();
            });

            // studentsTableActions.setStatesFromConfig(anon);

            // const page = document.getElementById('page');
            // let storedScrollPosition = +sessionStorage.getItem("scrollPosition") || 0;

            // $(document).ready(() => {
            //     $(page).scrollTop(storedScrollPosition);
            // });

            // window.addEventListener('beforeunload', function () {
            //    sessionStorage.setItem("scrollPosition", page.scrollTop);
            // });

        })
        .fail(Notification.exception);
};