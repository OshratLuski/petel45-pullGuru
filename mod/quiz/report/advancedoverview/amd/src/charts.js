import * as Str from 'core/str';
import $ from 'jquery';
import d3 from 'report/advancedoverview/js/d3.v7.min.js';


const chartParams = {
    gradeColorScheme: ['#b2b2b2', '#003F5B', '#003F5B', '#003F5B', '#003F5B', '#003F5B', '#003F5B'],
    stateColorScheme: ["#003F5B", "#FFBC00", "#5175A8"],

};

export const createStateChart = function(parent) {

    let parentCard = $(parent).closest('.card');
    parentCard.find('.donut-сhart-legend-title').html('');
    parentCard.find('.donut-сhart-legend-inner').html('');
    parentCard.find('.donut-сhart').html('');

    let radius = 128 /* Math.round(parentCard.outerWidth() * 0.275) */;
    let innerRadius = radius * 0.30;

    const state_svg = d3.select(parent)
        .append("svg")
        .attr("width", radius)
        .attr("height", radius)
        .append("g")
        .attr("transform", `translate(${radius / 2} ,${radius / 2})`);
    const color = d3.scaleOrdinal()
        .range(chartParams.stateColorScheme);
    const pie = d3.pie()
        .value(d => d[1].value);

    const state_data = pie(Object.entries(chartParams.state));
    state_svg
        .selectAll('whatever')
        .data(state_data)
        .join('path')
        .attr('d', d3.arc()
            .innerRadius(innerRadius)
            .outerRadius(radius / 2.1)
        )
        .attr('data-type', 'state')
        .attr('data-index', d => d.data[0])
        .attr('data-label', d => d.data[1].label)
        .attr('data-value', d => d.data[1].value)
        .attr('fill', d => color(d.data[0]))
        .attr("stroke", "white")
        .style("stroke-width", "1px");

    const createLegendRow = (index, label, value) => {
        let template = `<div class="donut-сhart-legend-row" data-row-type="${label}">
                        <div class="square" style="background-color: ${chartParams.stateColorScheme[index]}"></div>
                        <div class="card-text">${value} ${label}</div>
                </div>`;
        return $(template);
    };

    let usersSumm = 0;
    chartParams.state.forEach((element, index) => {
        createLegendRow(index, element.label, element.value).appendTo(".donut-сhart-legend-inner");
        usersSumm += +element.value;
    });
    var donutChartLegendTitle = Str.get_string('studentsintotal', 'quiz_advancedoverview', usersSumm);
    // As soon as the string is retrieved, i.e. the promise has been fulfilled,
    // edit the text of a UI element so that it then is the localized string
    // Note: $.when can be used with an arbitrary number of promised things
    $.when(donutChartLegendTitle).done(function(str) {
        $(".donut-сhart-legend-title").text(str);
    });
    this.initTooltip(parent, state_svg);
};
export const createGradeChart = function(parent) {
    // GRADE CHART
    // ////////////
    let parentCard = $(parent).closest('.card');
    parentCard.find('.bar-сhart').html('');

    let width = /* Math.round(parentCard.outerWidth() * 0.90) */ 380;
    let height = /* Math.round(parentCard.outerHeight() * 0.66) */96;

    // Append the svg object to the body of the page
    const grade_svg = d3.select(parent)
        .append("svg")
        .style("overflow", "visible")
        .attr("width", /* Grade_width + grade_margin.left + grade_margin.right */ width)
        .attr("height", /* Grade_height + grade_margin.top + grade_margin.bottom */ height + 30)
        .append("g")
        // .attr("transform", `translate(${grade_margin.left}, ${grade_margin.top})`);
        .attr("transform", "translate(0, 30)");


    let grade_data = chartParams.grade;

    const color = d3.scaleOrdinal()
        .range(chartParams.gradeColorScheme);
    // X axis
    const x = d3.scaleBand()
        .range([0, width])
        .domain(grade_data.map(d => d.label))
        .padding(0.2);
    grade_svg.append("g")
        .attr("transform", `translate(0, ${height - 24})`)
        .call(d3.axisBottom(x).ticks(8, "$.0f"))
        .call(d3.axisBottom(x).tickSize(0))
        .call(g => g.select(".domain").remove())
        .selectAll("text")
        .style("font-size", "0.875rem")
        .style("text-anchor", "center")
        .attr("transform", (d, i) => {
            if (i > 0) {return "translate(14, 0)";}  // Move labels for all but the first bar
            return "translate(0, 0)";
        });

    // Add Y axis
    const max = d3.max(grade_data, function(d) {
        return d.value;
    });

    // If all data == 0.
    if (max === 0) {
        height = 0;
    }

    const y = d3.scaleLinear()
        .domain([0, max])
        .range([height, 0]);

    // Bars
    grade_svg.selectAll("mybar")
        .data(grade_data)
        .enter()
        .append("rect")
        .attr("x", (d, i) => {
            let xPosition = x(d.label);
            if (i > 0) {xPosition += 14;}  // Add 20 pixels of space after the first bar
            return xPosition + 11;
        })
        .attr("y", d => y(d.value))
        .attr("width", x.bandwidth() - 22)
        .attr("height", function(d) {
            let value = height - y(d.value) - 30;
            return value > 0 ? value : 0;
        }
        )
        .attr('fill', d => color(d))
        .attr("stroke", "#707070")
        .style("stroke-width", "1px")
        .attr('data-type', 'grade')
        .attr('data-index', (d, i) => i)
        .attr('data-label', d => d.label)
        .attr('data-value', d => d.value);

    this.initTooltip(parent, grade_svg);
};
export const initTooltip = function(target, state_svg) {
    const tooltipTemplate = `
                <div class="chart-tooltip-header d-flex align-items-center justify-content-between">
                    <button type="button" class="close-tooltip-btn mr-2">
                        <i class="fa-regular fa-circle-xmark"></i>
                    </button>
                    <div class="chart-tooltip-title"></div>
                </div>
                <div class="inner">
                    <div class="chart-tooltip-body p-2"></div>
                </div>
            `;
    const userItem = (name, link, disabled) => {
        if(disabled) {
            return `<span target=”_blank” class="tooltip-link d-block text-decoration-none ">${name}</span>`;
        } else{
            return `<a href="${link}" target=”_blank” class="tooltip-link d-block">${name}</a>`;
        }
    };
    // Define tooltip
    var tooltip = d3.select(target).append('div').attr('class', 'chart-tooltip').html(tooltipTemplate);

    state_svg.on('mouseover', function(d) {
        if (d.target.localName === 'path' || d.target.localName === 'rect') {
            let title = `${d.target.dataset.value} ${d.target.dataset.label}`;
            let type = d.target.dataset.type || '';
            let index = d.target.dataset.index || '';
            let users = chartParams[type][index].users || '';
            let counter = 0;
            let usersList = '';

            if (users) {
                users.forEach(el => {
                    usersList += userItem(el.firstname + ' ' + el.lastname, el.link, el.disabled);
                    counter += 1;
                });
            }

            tooltip.select('.chart-tooltip-title').html(title);
            tooltip.select('.chart-tooltip-body').html(usersList)
                .attr('class', counter > 10 ? 'chart-tooltip-body hascolumns' : 'chart-tooltip-body');

            tooltip.style('display', 'block');
            let tooltipHeight = tooltip.node().offsetHeight;
            tooltip.style('top', (d.layerY - 8 - (tooltipHeight / 2)) + 'px')
                .style('left', (d.layerX + 8) + 'px');
        }

    });

    state_svg.on('mouseout', function() {
        tooltip.style('display', 'none');
    });
    tooltip.on('mouseover', function() {
        tooltip.style('display', 'block');
    });
    tooltip.on('mouseout', function() {
        tooltip.style('display', 'none');
    });
    tooltip.select('.close-tooltip-btn').on('click', function() {
        tooltip.style('display', 'none');
    });
};

export const initcharts = function(charts) {
    charts = JSON.parse(charts);
    let lang = $('html')[0].lang;
    chartParams.textDirection = 'ltr';
    if (lang === "he") {
        chartParams.textDirection = 'rtl';
    }

    chartParams.average = charts.average;
    chartParams.state = charts.state;
    chartParams.grade = charts.grade[0].values;

    this.createStateChart("#charts_state .donut-сhart");
    this.createGradeChart("#charts_grade .bar-сhart");
};
