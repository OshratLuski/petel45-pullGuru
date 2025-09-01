define([
    'jquery',
    'core/str',
    'core/notification',
    'core/chartjs-lazy'
], function($, Str, Notification, ChartJS) {
    `use strict`;

    return {
        init: function(id) {

            let refresh = $('#' + id + '_refreshChart');

            // Event input change title.
            $('#' + id + '_titleChart').keyup(function(e) {
                if (e.keyCode === 13) {
                    refresh.val('1');
                }
            });

            // Event select change chart type.
            var select = $('#' + id + '_selectChart');
            select.change(function() {
                $('#' + id).find('.class_' + $(this).val()).click();
            });

            // Set default select.
            var typeChart = $('#' + id + '_typeChart').val();
            select.val(typeChart);

            setInterval(function() {

                if (refresh.val() === '1') {

                    refresh.val(0);
                    $('#' + id + '_defaultImage').hide();
                    $('#' + id + '_externalChart').parent().show();

                    let data = JSON.parse($('#' + id + '_dataChart').val());

                    var barColors = ["red", "green", "blue", "orange", "brown", "#b91d47", "#00aba9", "#2b5797", "#e8c3b9", "#1e7145"];

                    let type = $('#' + id + '_typeChart').val();
                    let charttype = "bar";
                    let backgroundColor = barColors;
                    let borderColor = "rgba(0,0,0,0)";

                    // Check labels axis and data.
                    var xValues = [];
                    let labelXenable = false;
                    let labelX = '';
                    $(data.labels).each(function(index) {
                        if (index === 0 && (type === 'LINECHART' || type === 'SCATTERCHART' || type === 'BARCHART')) {
                            if (data.labels[index].length > 0) {
                                labelXenable = true;
                                labelX = data.labels[index];
                            }
                        } else {
                            xValues.push(data.labels[index]);
                        }
                    });

                    var yValues = [];
                    let labelYenable = false;
                    let labelY = '';
                    $(data.values).each(function(index) {
                        if (index === 0 && (type === 'LINECHART' || type === 'SCATTERCHART' || type === 'BARCHART')) {
                            if (data.values[index].length > 0) {
                                labelYenable = true;
                                labelY = data.values[index];
                            }
                        } else {
                            yValues.push(data.values[index]);
                        }
                    });

                    // Build options.
                    let options = {
                        legend: {display: false},
                    };

                    // Build title.
                    var title = $('#' + id + '_titleChart').val();
                    let titledisplay;

                    if (title.length > 0) {
                        titledisplay = true;
                    } else {
                        titledisplay = false;
                        title = '';
                    }

                    options.plugins = {
                            title: {
                                display: titledisplay,
                                text: title
                            }
                        };


                    // Build scales.
                    let scales = {
                        x: {
                            beginAtZero: true,
                            title: {
                                display: labelXenable,
                                text: labelX
                            }
                        },

                        y: {
                            beginAtZero: true,
                            title: {
                                display: labelYenable,
                                text: labelY
                            }
                        }
                    };

                    switch (type) {
                        case 'BARCHART':
                            charttype = "bar";
                            options.scales = scales;
                            break;
                        case 'PIECHART':
                            charttype = "pie";
                            break;
                        case 'DOUGHNUTCHART':
                            charttype = "doughnut";
                            break;
                        case 'LINECHART':
                            charttype = "line";
                            backgroundColor = "rgba(0,0,0,0)";
                            borderColor = "rgba(0,0,0,1)";
                            options.scales = scales;
                            break;
                        case 'SCATTERCHART':
                            charttype = "scatter";
                            backgroundColor = "rgba(0,0,0,1)";
                            borderColor = "rgba(0,0,0,1)";
                            options.scales = scales;
                            break;
                    }

                    let dataChart = {
                        labels: xValues,
                        datasets: [{
                            borderColor: borderColor,
                            backgroundColor: backgroundColor,
                            data: yValues,
                        }]
                    };

                    if (type === 'SCATTERCHART') {

                        var xyValues = [];

                        $(xValues).each(function(index) {
                            xyValues.push({'x': xValues[index], 'y': yValues[index]});
                        });

                        dataChart = {
                            datasets: [{
                                pointRadius: 4,
                                pointBackgroundColor: "rgba(0, 0, 0, 1)",
                                showLine: true,
                                backgroundColor: 'rgb(0,0,0,0)',
                                data: xyValues
                            }]
                        };
                    }

                    let classname = 'myChart' + id;

                    if (window[classname] !== undefined) {
                        window[classname].destroy();
                    }

                    window[classname] = new ChartJS(id + "_externalChart", {
                        type: charttype,
                        data: dataChart,
                        options: options
                    });
                }

            }, 500);

        },

    };
});
