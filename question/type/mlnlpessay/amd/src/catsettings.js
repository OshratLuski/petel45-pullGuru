import Ajax from "core/ajax";
import Notification, {saveCancelPromise} from "core/notification";
import Tabulator from "qtype_mlnlpessay/tabulatorlib";
import Modal from "core/modal";
import ModalEvents from "core/modal_events";
import {get_string as getString} from "core/str";
import Templates from "core/templates";
import Fragment from "core/fragment";
import $ from "jquery";

const SELECTORS = {
    CATCONTAINER: '.mlnlpessay-categories-wrapper',
    LANGCONTAINER: '.mlnlpessay-lang-wrapper',
    ADDEDITTRIGGER: 'qtype-mlnlpessay-settingaddedit',
    TOPICCONTAINER: '.mlnlpessay-topics-wrapper',
    SUBTOPICCONTAINER: '.mlnlpessay-subtopics-wrapper',
    MODALCONTAINER: '.mlnlpessay-modal',
    DELETETRIGGER: 'qtype-mlnlpessay-settingdelete',
    VISIBILITYTRIGGER: 'qtype-mlnlpessay-settingvisibilitytoggle',
    CSVUPLOADTRIGGER: 'qtype-mlnlpessay-csvupload',
    CSVVERIFYTRIGGER: 'qtype-mlnlpessay-csverify',
    CSVCANCELTRIGGER: 'qtype-mlnlpessay-csvcancel',
};

const getData = async(item) => {
    let data = [];
    let response = await Ajax.call([{
        methodname: "qtype_mlnlpessay_get_" + item, args: {}
    }])[0];

    if (response.status) {
        data = response.response;
    }

    return data;
};

// Global state for CSV modal
window.qtypeMlnlpessayCsvModalState = {
    step: 'upload', // 'upload', 'precheck', 'result'
    precheckData: null,
    formData: null,
};

export const init = async(contextid) => {
    const tables = {
        categoriestable: new Tabulator(SELECTORS.CATCONTAINER, {
            data: await getData('categories'), // Load row data from array
            layout: "fitColumns", // Fit columns to width of table
            responsiveLayout: "hide", // Hide columns that don't fit on the table
            addRowPos: "top", // When adding a new row, add it to the top of the table
            history: true, // Allow undo and redo actions on the table
            pagination: "local", // Paginate the data
            paginationSize: 7, // Allow 7 rows per page of data
            paginationSizeSelector: true,
            paginationCounter: "rows", // Display count of paginated rows in footer
            movableColumns: true, // Allow column order to be changed
            initialSort: [ // Set the initial sort order of the data
                {column: "id", dir: "asc"},
            ],
            columnDefaults: {
                tooltip: true, // Show tool tips on cells
            },
            columns: [ // Define the table columns
                {title: M.util.get_string('catid', 'qtype_mlnlpessay'), field: "id", headerSort: true, sorter: "number"},
                {
                    title: M.util.get_string('categoryname', 'qtype_mlnlpessay'), field: "name",
                    headerFilter: "input",
                    headerFilterLiveFilter: true,
                },
                {
                    title: M.util.get_string('modelid', 'qtype_mlnlpessay'), field: "modelid",
                    headerFilter: "input",
                    headerFilterLiveFilter: true,
                },
                {
                    title: M.util.get_string('modelname', 'qtype_mlnlpessay'), field: "model",
                    headerFilter: "input",
                    headerFilterLiveFilter: true,
                },
                {
                    title: M.util.get_string('categorytag', 'qtype_mlnlpessay'), field: "tag",
                    headerFilter: "input",
                    headerFilterLiveFilter: true,
                },
                {
                    title: M.util.get_string('descriptioncategory', 'qtype_mlnlpessay'), field: "description",
                    headerFilter: "input",
                    headerFilterLiveFilter: true,
                },
                {
                    title: M.util.get_string('catlang', 'qtype_mlnlpessay'), field: "lang",
                    headerFilter: "input",
                    headerFilterLiveFilter: true,
                },
                {
                    title: M.util.get_string('cattopic', 'qtype_mlnlpessay'), field: "topic",
                    headerFilter: "input",
                    headerFilterLiveFilter: true,
                },
                {
                    title: M.util.get_string('catsubtopic', 'qtype_mlnlpessay'), field: "subtopic",
                    headerFilter: "input",
                    headerFilterLiveFilter: true,
                },
                {title: M.util.get_string('catstatus', 'qtype_mlnlpessay'), field: "active", formatter: "html"},
                {title: M.util.get_string('catactions', 'qtype_mlnlpessay'), field: "catactions", formatter: "html"},
            ],
        }),
        langstable: new Tabulator(SELECTORS.LANGCONTAINER, {
            data: await getData('langs'), // Load row data from array
            layout: "fitColumns", // Fit columns to width of table
            responsiveLayout: "hide", // Hide columns that don't fit on the table
            addRowPos: "top", // When adding a new row, add it to the top of the table
            history: true, // Allow undo and redo actions on the table
            pagination: "local", // Paginate the data
            paginationSize: 7, // Allow 7 rows per page of data
            paginationSizeSelector: true,
            paginationCounter: "rows", // Display count of paginated rows in footer
            movableColumns: true, // Allow column order to be changed
            initialSort: [ // Set the initial sort order of the data
                {column: "id", dir: "asc"},
            ],
            columnDefaults: {
                tooltip: true, // Show tool tips on cells
            },
            columns: [ // Define the table columns
                {title: M.util.get_string('langid', 'qtype_mlnlpessay'), field: "id"},
                {title: M.util.get_string('langcode', 'qtype_mlnlpessay'), field: "code"},
                {title: M.util.get_string('langname', 'qtype_mlnlpessay'), field: "name"},
                {title: M.util.get_string('langactive', 'qtype_mlnlpessay'), field: "active", formatter: "html"},
                {title: M.util.get_string('langactions', 'qtype_mlnlpessay'), field: "langactions", formatter: "html"},
            ],
        }),
        topicstable: new Tabulator(SELECTORS.TOPICCONTAINER, {
            data: await getData('topics'), // Load row data from array
            layout: "fitColumns", // Fit columns to width of table
            responsiveLayout: "hide", // Hide columns that don't fit on the table
            addRowPos: "top", // When adding a new row, add it to the top of the table
            history: true, // Allow undo and redo actions on the table
            pagination: "local", // Paginate the data
            paginationSize: 7, // Allow 7 rows per page of data
            paginationSizeSelector: true,
            paginationCounter: "rows", // Display count of paginated rows in footer
            movableColumns: true, // Allow column order to be changed
            initialSort: [ // Set the initial sort order of the data
                {column: "id", dir: "asc"},
            ],
            columnDefaults: {
                tooltip: true, // Show tool tips on cells
            },
            columns: [ // Define the table columns
                {title: M.util.get_string('topicid', 'qtype_mlnlpessay'), field: "id"},
                {title: M.util.get_string('topicname', 'qtype_mlnlpessay'), field: "name"},
                {title: M.util.get_string('topicactive', 'qtype_mlnlpessay'), field: "active", formatter: "html"},
                {title: M.util.get_string('topicactions', 'qtype_mlnlpessay'), field: "topicactions", formatter: "html"},
            ],
        }),
        subtopicstable: new Tabulator(SELECTORS.SUBTOPICCONTAINER, {
            data: await getData('subtopics'), // Load row data from array
            layout: "fitColumns", // Fit columns to width of table
            responsiveLayout: "hide", // Hide columns that don't fit on the table
            addRowPos: "top", // When adding a new row, add it to the top of the table
            history: true, // Allow undo and redo actions on the table
            pagination: "local", // Paginate the data
            paginationSize: 7, // Allow 7 rows per page of data
            paginationSizeSelector: true,
            paginationCounter: "rows", // Display count of paginated rows in footer
            movableColumns: true, // Allow column order to be changed
            initialSort: [ // Set the initial sort order of the data
                {column: "id", dir: "asc"},
            ],
            columnDefaults: {
                tooltip: true, // Show tool tips on cells
            },
            columns: [ // Define the table columns
                {title: M.util.get_string('subtopicid', 'qtype_mlnlpessay'), field: "id"},
                {title: M.util.get_string('subtopicname', 'qtype_mlnlpessay'), field: "name"},
                {title: M.util.get_string('subtopicactive', 'qtype_mlnlpessay'), field: "active", formatter: "html"},
                {title: M.util.get_string('subtopicactions', 'qtype_mlnlpessay'), field: "subtopicactions", formatter: "html"},
            ],
        })
    };

    document.addEventListener('click', async function(e) {
        if (e.target.classList.contains(SELECTORS.ADDEDITTRIGGER)) {
            e.preventDefault();
            let action = e.target.getAttribute('data-action');
            const modal = await Modal.create({
                title: await getString('modal' + action + 'title', 'qtype_mlnlpessay'),
                body: await Templates.render('qtype_mlnlpessay/modal', {"action": action}),
            });
            modal.show();
            modal.getRoot().on(ModalEvents.hidden, function() {
                modal.destroy();
            });
            let fragmentargs = {action: action};
            if (e.target.getAttribute('data-id')) {
                fragmentargs.id = e.target.getAttribute('data-id');
            }
            Fragment.loadFragment('qtype_mlnlpessay', 'settingsform', contextid,
                fragmentargs
            ).done(async function(html, js) {
                    Templates.replaceNodeContents($(SELECTORS.MODALCONTAINER), html, js);
                    $(SELECTORS.MODALCONTAINER).find('form').on('submit', async function(e) {
                        e.preventDefault();
                        e.stopPropagation();
                        const formData = $(this).serializeArray();
                        const newCategoryName = formData.find(item => item.name === 'name')?.value;
                        const existingCategories = tables.categoriestable.getData();
                        const categoryExists = existingCategories.some(category =>
                            category.name.toLowerCase() === newCategoryName.toLowerCase() &&
                            (!fragmentargs.id || category.id.toString() !== fragmentargs.id)
                        );

                        if (categoryExists) {
                            modal.hide();
                            const errorModal = await Modal.create({
                                title: await getString('failedaddcategory', 'qtype_mlnlpessay'),
                                body: await getString('categoryexists', 'qtype_mlnlpessay'),
                                type: Modal.ALERT
                            });
                            errorModal.show();
                            errorModal.getRoot().on(ModalEvents.hidden, function() {
                                errorModal.destroy();
                            });
                            return;
                        }

                        Notification.saveCancelPromise(
                            M.util.get_string('saveconfirm', 'qtype_mlnlpessay'),
                            M.util.get_string('savewarning', 'qtype_mlnlpessay')
                        ).then(() => {
                            let formdata = $(this).serialize();
                            Ajax.call([{
                                methodname: "qtype_mlnlpessay_save_settings", args: {action: action, formdata: formdata}
                            }])[0].done(async function(response) {
                                if (response.status) {
                                    Notification.addNotification({
                                        message: M.util.get_string('savesuccess', 'qtype_mlnlpessay'),
                                        type: 'info'
                                    });
                                    modal.hide();
                                    let actiontable = action + 'table';
                                    tables[actiontable].replaceData(await getData(action));
                                } else {
                                    Notification.addNotification({
                                        message: M.util.get_string('saveerror', 'qtype_mlnlpessay'),
                                        type: 'error'
                                    });
                                }
                            }).fail(Notification.exception);
                        });
                    });
                });
        }
        if (e.target.classList.contains(SELECTORS.DELETETRIGGER)) {
            e.preventDefault();
            let action = e.target.getAttribute('data-action');
            let id = e.target.getAttribute('data-id');
            Notification.deleteCancelPromise(
                M.util.get_string('deleteconfirm', 'qtype_mlnlpessay'),
                M.util.get_string('deletewarning', 'qtype_mlnlpessay')
            ).then(() => {
                Ajax.call([{
                    methodname: "qtype_mlnlpessay_delete_setting", args: {action: action, id: id}
                }])[0].done(async function(response) {
                    if (response.status) {
                        Notification.addNotification({
                            message: M.util.get_string('deletesuccess', 'qtype_mlnlpessay'),
                            type: 'info'
                        });
                        let actiontable = action + 'table';
                        tables[actiontable].replaceData(await getData(action));
                    } else {
                        Notification.addNotification({
                            message: M.util.get_string('saveerror', 'qtype_mlnlpessay'),
                            type: 'error'
                        });
                    }
                }).fail(Notification.exception);
            }).catch(() => {
                return;
            });
        }

        if (e.target.classList.contains(SELECTORS.VISIBILITYTRIGGER)) {
            e.preventDefault();
            let action = e.target.getAttribute('data-action');
            let id = e.target.getAttribute('data-id');
            Ajax.call([{
                methodname: "qtype_mlnlpessay_toggle_visible", args: {action: action, id: id}
            }])[0].done(async function(response) {
                if (response.status) {
                    let actiontable = action + 'table';
                    tables[actiontable].replaceData(await getData(action));
                } else {
                    Notification.addNotification({
                        message: M.util.get_string('saveerror', 'qtype_mlnlpessay'),
                        type: 'error'
                    });
                }
            }).fail(Notification.exception);
        }

        if (e.target.classList.contains(SELECTORS.CSVUPLOADTRIGGER)) {
            e.preventDefault();
            window.qtypeMlnlpessayCsvModalState = {step: 'upload', precheckData: null, formData: null};
            const modal = await Modal.create({
                title: await getString('modalcsvuploadtitle', 'qtype_mlnlpessay'),
                body: await Templates.render('qtype_mlnlpessay/modal', {}),
            });
            modal.show();
            modal.getRoot().on(ModalEvents.hidden, async function() {
                modal.destroy();
                if (tables && tables.categoriestable) {
                    const newData = await getData('categories');
                    tables.categoriestable.replaceData(newData);
                }
                // Clear state on close
                window.qtypeMlnlpessayCsvModalState = {step: 'upload', precheckData: null, formData: null};
            });
            Fragment.loadFragment('qtype_mlnlpessay', 'csvuploadform', contextid, [])
                .done(async function(html, js) {
                    Templates.replaceNodeContents($(SELECTORS.MODALCONTAINER), html, js);
                    // Restore file selection if going back
                    if (window.qtypeMlnlpessayCsvModalState.formData) {
                        // Try to restore file input (not always possible for security reasons)
                        // Optionally, show a message to reselect file if needed
                    }
                    $(SELECTORS.MODALCONTAINER).find('form').on('submit', async function(e) {
                        e.preventDefault();
                        Notification.saveCancelPromise(
                            M.util.get_string('csvconfirm', 'qtype_mlnlpessay'),
                            M.util.get_string('csvwarning', 'qtype_mlnlpessay'),
                            M.util.get_string('csvproceed', 'qtype_mlnlpessay'),
                        ).then(async () => {
                            let formdata = $(this).serialize();
                            // Show loading indicator
                            Templates.replaceNodeContents($(SELECTORS.MODALCONTAINER), '<div class="text-center p-3"><span class="spinner-border" role="status"></span></div>', '');
                            let response = await Ajax.call([{
                                methodname: "qtype_mlnlpessay_csv_upload", args: {formdata: formdata}
                            }])[0];
                            if (!response.status || !response.response || !response.response.rows || response.response.rows.length === 0) {
                                Notification.addNotification({
                                    message: M.util.get_string('csvuploaderror', 'qtype_mlnlpessay'),
                                    type: 'error'
                                });
                                // Go back to upload form
                                Fragment.loadFragment('qtype_mlnlpessay', 'csvuploadform', contextid, [])
                                    .done(function(html, js) {
                                        Templates.replaceNodeContents($(SELECTORS.MODALCONTAINER), html, js);
                                    });
                                return;
                            }
                            // Store precheck data for back button
                            window.qtypeMlnlpessayCsvModalState = {step: 'precheck', precheckData: response.response, formData: formdata};
                            let html = await Templates.render('qtype_mlnlpessay/csvuploadprecheck', response.response);
                            Templates.replaceNodeContents($(SELECTORS.MODALCONTAINER), html, '');
                            // Disable perform upload if no rows
                            if (!response.response.rows || response.response.rows.length === 0) {
                                $(SELECTORS.MODALCONTAINER).find('.qtype-mlnlpessay-csvprocess').prop('disabled', true);
                            }
                        });
                    });
                });
        }

        // Handle Back button in CSV modal (precheck/result)
        if (e.target.classList.contains('qtype-mlnlpessay-csvback')) {
            e.preventDefault();
            if (window.qtypeMlnlpessayCsvModalState.step === 'result') {
                // Go back to precheck step
                window.qtypeMlnlpessayCsvModalState.step = 'precheck';
                if (window.qtypeMlnlpessayCsvModalState.precheckData) {
                    Templates.render('qtype_mlnlpessay/csvuploadprecheck', window.qtypeMlnlpessayCsvModalState.precheckData)
                        .then(function(html) {
                            Templates.replaceNodeContents($(SELECTORS.MODALCONTAINER), html, '');
                        });
                }
            } else if (window.qtypeMlnlpessayCsvModalState.step === 'precheck') {
                // Go back to upload form
                window.qtypeMlnlpessayCsvModalState.step = 'upload';
                Fragment.loadFragment('qtype_mlnlpessay', 'csvuploadform', contextid, [])
                    .done(function(html, js) {
                        Templates.replaceNodeContents($(SELECTORS.MODALCONTAINER), html, js);
                        // Optionally restore form data
                    });
            }
        }

        // Handle Perform Upload (CSV Import) button in precheck modal
        if (e.target.classList.contains('qtype-mlnlpessay-csvprocess')) {
            e.preventDefault();
            let form = $(SELECTORS.MODALCONTAINER).find('form');
            let formdata = window.qtypeMlnlpessayCsvModalState.formData || (form.length ? form.serialize() : '');
            if (!formdata) {
                Notification.addNotification({
                    message: 'Could not find CSV file data to upload.',
                    type: 'error'
                });
                return;
            }
            // Show loading indicator
            Templates.replaceNodeContents($(SELECTORS.MODALCONTAINER), '<div class="text-center p-3"><span class="spinner-border" role="status"></span></div>', '');
            Ajax.call([{
                methodname: "qtype_mlnlpessay_csv_upload_perform", args: {formdata: formdata}
            }])[0].done(async function(response) {
                if (response.status) {
                    window.qtypeMlnlpessayCsvModalState.step = 'result';
                    let html = await Templates.render('qtype_mlnlpessay/csvuploadresult', response.response);
                    Templates.replaceNodeContents($(SELECTORS.MODALCONTAINER), html, '');
                } else {
                    Notification.addNotification({
                        message: response.message || 'CSV import failed.',
                        type: 'error'
                    });
                }
            }).fail(Notification.exception);
        }

        // Handle Undo button in CSV result step
        if (e.target.classList.contains('qtype-mlnlpessay-csvundo')) {
            e.preventDefault();
            let importid = e.target.getAttribute('data-importid');
            if (!importid) {
                return;
            }
            // Disable button during AJAX
            e.target.disabled = true;
            Ajax.call([{
                methodname: "qtype_mlnlpessay_csv_upload_undo", args: {importid: importid}
            }])[0].done(function(response) {
                if (response.status) {
                    Notification.addNotification({
                        message: M.util.get_string('csvundodone', 'qtype_mlnlpessay'),
                        type: 'info'
                    });
                    // Optionally close modal or refresh categories table
                    $(SELECTORS.MODALCONTAINER).closest('.modal').modal('hide');
                } else {
                    Notification.addNotification({
                        message: response.message || 'Undo failed.',
                        type: 'error'
                    });
                }
            }).fail(Notification.exception).always(function() {
                e.target.disabled = false;
            });
        }
    });

    // Custom search for categories table
    $(document).on('keydown', '.mlnlpessay-categories-search', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            e.stopPropagation();
            const query = $(this).val().toLowerCase();
            if (!query) {
                tables.categoriestable.clearFilter();
                return;
            }
            tables.categoriestable.setFilter(function(data) {
                return Object.values(data).some(val =>
                    (val !== null && val !== undefined && (val + '').includes(query))
                );
            });
        }
    });
    $(document).on('keydown', '.tabulator-col input', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            e.stopPropagation();
            return false;
        }
    });
};