define(
    [
        'jquery',
        'block_configurable_reports/jquery.tablesorter',
        //'block_configurable_reports/jquery.dataTables',
        'https://cdn.datatables.net/1.10.25/js/jquery.dataTables.min.js',
        'block_configurable_reports/codemirror',
        'block_configurable_reports/sql'
    ], function($, tablesorter, dataTable, CodeMirror) {
        return {
            js_order: function(params) {
                $(params.selector).tablesorter();
                $(params.selector + ' th.header').css({
                    'background-image': 'url(' + params.background + ')',
                    'background-position': 'right center',
                    'background-repeat': 'no-repeat',
                    'cursor': 'pointer'
                });

                $(params.selector + ' th.headerSortUp').css({
                    'background-image': 'url(' + params.backgroundasc + ')'
                });

                $(params.selector + ' th.headerSortDown').css({
                    'background-image': 'url(' + params.backgrounddesc + ')'
                });
            },
            add_jsdatatables: function(params) {
                $(params.selector).dataTable({
                    initComplete: function () {
                        if (params.columnfilter === 1) {
                            this.api().columns().every( function () {
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
                                        select.append( '<option value="'+d+'">'+d+'</option>' )
                                    }
                                } );
                            } );
                        }
                    },
                    'bAutoWidth': false,
                    'sPaginationType': 'full_numbers',
                    'fixedHeader': true,
                    'aaSorting': [],
                    'lengthMenu': [
                        [10, 25, 50, 100, -1],
                        [10, 25, 50, 100, "All"]
                    ],
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
                });
            },
            cmirror: function() {
                // Documentation can be found @ http://codemirror.net/
                CodeMirror.fromTextArea(document.getElementById('id_querysql'), {
                    mode: "text/x-mysql",
                    rtlMoveVisually: true,
                    indentWithTabs: true,
                    smartIndent: true,
                    lineNumbers: true,
                    autofocus: true,
                });

                CodeMirror.fromTextArea(document.getElementById('id_remotequerysql'), {
                    mode: "text/x-mysql",
                    rtlMoveVisually: true,
                    indentWithTabs: true,
                    smartIndent: true,
                    lineNumbers: true,
                    //    autofocus: true
                });
            }
        };
    });