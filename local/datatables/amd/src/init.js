define(
    [
        "jquery",
        "local_datatables/jquery.dataTables",
        //"core/log",
        "local_datatables/dataTables.bootstrap",
        "local_datatables/dataTables.scroller",
        "local_datatables/dataTables.select",
        "local_datatables/dataTables.fixedHeader",
        "local_datatables/dataTables.buttons",
        "local_datatables/dataTables.fixedColumns",
        //"local_datatables/buttons.bootstrap"
    ],
    function ($, datatables) {
        return {
            test: function () {
                // window.console.log("$.fn is:");
                // window.console.log($.fn);
                // window.console.log("datatables is:");
                // window.console.log(datatables);
            },

            init: function (selector, params) {
                // Configure element matched by selector as a DataTable,
                // adding params to the default options.
                if (params.debug) {
                    window.console.log(
                        "local_datatables:init.js/init(): ",
                        selector,
                        params
                    );
                }
                var options = {
                    autoWidth: false,
                    paginate: false,
                    order: [] // disable initial sort
                };
                $.extend(true, options, params); // deep-merge params into options
                if (params.debug) {
                    window.console.log(
                        "local_datatables init.js/init(): options = ",
                        options
                    );
                }
                $(selector).DataTable(options);
            },

            initScroller: function (selector, params) {

                var options = {
                    autoWidth: true,
                    paginate: true,
                    fixedHeader: {
                        header: true,
                        footer: true
                    },
                    scrollY: 600,
                    scrollCollapse: true,
                    scroller: true,
                    info: false,
                    //dom: 'ftipr',
                    sDom: "lfrti",
                    searching: false,
                    lengthChange: false,
                    order: [] // disable initial sort
                };
                $.extend(true, options, params); // deep-merge params into options
                if (params.debug) {
                }
                $(selector).DataTable(options);
            },
            initFilters: function (params) {
                var table = $(params.selector).dataTable({
                    initComplete: function () {
                        if (params.columnfilter === 1) {
                            this.api().columns(params.onlycolumns).every( function () {
                                var column = this;
                                var select = $('<select><option value=""></option></select>')
                                    //.appendTo( $(column.footer()).empty() )
                                    .appendTo( $(column.header()) )
                                    .on( 'change', function () {
                                        var val = $.fn.dataTable.util.escapeRegex(
                                            $(this).val()
                                        );

                                        column
                                            .search( val ? '^'+val+'$' : '', true, false )
                                            .draw();
                                    } );

                                //column.data().unique().sort().each( function ( d, j ) {
                                column.cells('', column[0]).render('display').sort().unique().each( function ( d, j ) {
                                    if(column.search() === '^'+d+'$'){
                                        select.append( '<option value="'+d+'" selected="selected">'+d+'</option>' )
                                    } else {
                                        //debugger;
                                        //var role = /data-value-name=\"(.*)\" /g.exec(d);
                                        var role = /data-value-name=\"([a-zA-Z]*)\"/g.exec(d);
                                        if (role != null) {
                                            select.append( '<option value="'+role[1]+'">'+role[1]+'</option>' )
                                        }

                                    }
                                } );
                            } );
                        }
                    },
                    "aaSorting": [],
                    "iDisplayLength": params.iDisplayLength,
                    'aLengthMenu': [[ 25, 100, 200, -1], [ 25, 100, 200, 'All']],
                    "bScrollInfinite": true,
                    //'sPaginationType': 'full_numbers',
                    autoWidth: true,
                    paginate: params.paginate,
                    fixedHeader: {
                        header: true,
                        footer: true
                    },
                    //scrollX: '100%',
                    fixedColumns: true,
                    //{
                    //    left: 3
                    //},
                    scrollY: params.scrolly,
                    //scrollCollapse: true,
                    scroller: params.scroller,
                    info: false,
                    select: params.select,
                    dom: params.dom,
                    buttons: params.buttons,
                    'oLanguage': {
                        'sSearch': 'סינון תצוגה לפי: ',
                    }
                    /*
                    'oLanguage': {
                        'oAria': {
                            'sSortAscending': M.str.block_configurable_reports.datatables_sortascending,
                            'sSortDescending': M.str.block_configurable_reports.datatables_sortdescending,
                        },
                        'oPaginate': {
                            'sFirst': M.str.block_configurable_reports.datatables_first,
                            'sLast': M.str.block_configurable_reports.datatables_last,
                            'sNext': M.str.block_configurable_reports.datatables_next,
                            'sPrevious': M.str.block_configurable_reports.datatables_previous
                        },
                        'sEmptyTable': M.str.block_configurable_reports.datatables_emptytable,
                        'sInfo': M.str.block_configurable_reports.datatables_info,
                        'sInfoEmpty': M.str.block_configurable_reports.datatables_infoempty,
                        'sInfoFiltered': M.str.block_configurable_reports.datatables_infofiltered,
                        'sInfoThousands': M.str.langconfig.thousandssep,
                        'sLengthMenu': M.str.block_configurable_reports.datatables_lengthmenu,
                        'sLoadingRecords': M.str.block_configurable_reports.datatables_loadingrecords,
                        'sProcessing': M.str.block_configurable_reports.datatables_processing,
                        'sSearch': M.str.block_configurable_reports.datatables_search,
                        'sZeroRecords': M.str.block_configurable_reports.datatables_zerorecords
                    }
                    */
                });
            }
        };
    }
);
