/*
 * Copyright (C) 2012-2016 Project Delphinium - All Rights Reserved
 *
 * This file is subject to the terms and conditions defined in
 * file 'https://github.com/ProjectDelphinium/delphinium/blob/master/EULA',
 * which is part of this source code package.
 *
 * NOTICE:  All information contained herein is, and remains the property of Project Delphinium. The intellectual and technical concepts contained
 * herein are proprietary to Project Delphinium and may be covered by U.S. and Foreign Patents, patents in process, and are protected by trade secret or copyright law.
 * Dissemination of this information or reproduction of this material is strictly forbidden unless prior written permission is obtained
 * from Project Delphinium.
 *
 * THE RECEIPT OR POSSESSION OF THIS SOURCE CODE AND/OR RELATED INFORMATION DOES NOT CONVEY OR IMPLY ANY RIGHTS
 * TO REPRODUCE, DISCLOSE OR DISTRIBUTE ITS CONTENTS, OR TO MANUFACTURE, USE, OR SELL ANYTHING THAT IT  MAY DESCRIBE, IN WHOLE OR IN PART.
 *
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Non-commercial use only, you may not charge money for the software
 * You can modify personal copy of source-code but cannot distribute modifications
 * You may not distribute any version of this software, modified or otherwise
 */

var submissions = [];
var bottomExperienceScores=[];
document.tabdata = '';

//GET DATA FOR THE TOP CHART
var promise = $.get("gradebook/getAllStudentSubmissions");
promise.then(function (data1, textStatus, jqXHR) {
        submissions = data1;
        var inputs = document.getElementsByClassName('checkboxMultiselect');
        for(var i = 0; i < inputs.length; i++) {
            inputs[i].disabled = false;
        }
        d3.selectAll(".nameLabel").style("color","black");
        d3.select(".spinnerDiv").style("display","none");
        d3.select("#chart").style("opacity","1");
        d3.select("#topRight").style("opacity","1");
    })
    .fail(function (data2) {
        console.log("Unable to retrieve student submissions");
    });
callStudentsMilestoneInfo(students);

function getCHartDragPoints() {
    var min_max = [],
        rangeSliderContainer = $('.range-slider'); 
    min_max[0] = parseInt(rangeSliderContainer.find('.ui-slider-handle').eq(0).find('.ui-slider-tip').text());
    min_max[1] = parseInt(rangeSliderContainer.find('.ui-slider-handle').eq(1).find('.ui-slider-tip').text());

    return min_max;
}

function getChartDate() {
    var index = $('.ui-slider-pip-selected').find('.ui-slider-label').attr('data-value');
    return Date.parse(dateRange[index]);
}

div = d3.select("body").append("div")
    .attr("class", "tooltip")
    .style("opacity", 0);

var selectedStudents = [];
var monthNames = [
    "Jan", "Feb", "Mar",
    "Apr", "May", "Jun", "Jul",
    "Aug", "Sep", "Oct",
    "Nov", "Dec"
];
////////////////////////////////////////////////////////
var margin = {top: 30, right: 20, bottom: 30, left: 50},
    width = 800 - margin.left - margin.right,
    height = 400 - margin.top - margin.bottom;
var allSelected = false;
// Parse the date / time
var parseDate = d3.time.format("%d-%b-%y").parse;

// Set the ranges
var x = d3.time.scale().range([0, width]);
var y = d3.scale.linear().range([height, 0]);


// Define the axes
var xAxis = d3.svg.axis().scale(x)
    .orient("bottom").ticks(6);

var yAxis = d3.svg.axis().scale(y)
    .orient("left").ticks(10);

// Adds the svg canvas
var svg = d3.select("#chart")
    .append("svg")
    .attr("id", "svg")
    .attr("width", width + margin.left + margin.right)
    .attr("height", height + margin.top + margin.bottom);
var g = svg.append("g")
    .attr("transform",
        "translate(" + margin.left + "," + margin.top + ")")
    .attr("id", "gChart");

// Get the data
data = parseDates(chartData);
// Scale the range of the data
x.domain(d3.extent(data, function (d) {
    return d.date;
}));
y.domain([0, d3.max(data, function (d) {
    return d.points;
})]);

// Add the X Axis
g.append("g")
    .attr("class", "x axis")
    .attr("transform", "translate(0," + height + ")")
    .call(xAxis);

// Add the Y Axis
g.append("g")
    .attr("class", "y axis")
    .call(yAxis);

// add the red line chart
addLine(data, "red", "red");
// add circles for each milestone
addRedLineDots();

//add a vertical rect denoting today
var todayDate = new Date();
todayDate = (Date.parse(endDate) > Date.parse(todayDate)) ? todayDate : new Date(endDate);
var parsed = Date.parse(todayDate);
g.append("svg:rect")
    .attr("x", function (d) {
        return x(parsed);
    })
    .attr("y", function (d) {
        return 0;
    })
    .attr("height", function (d) {
        return height;
    })
    .attr("width", function (d) {
        return 0.5;
    })
    .attr("stroke-width", 1.5)
    .attr("class", "todayLine")
    .on("mouseover", function (d) {
        addTooltipProfessorGradebook(todayDate.toDateString());
    })
    .on("mouseout", function (d) {
        removeTooltipProfessorGradebook();
    });
///////////////////////////////////////

$(window).resize(function(){
    var range = $('.nouislider:visible');
    var content = $('.checkbox_filter_conternt:visible');
    
    if(range.length > 0){
        var td = range.closest('td');
        range.css({
            'left': $(td).offset().left-(148-(($(td).outerWidth())/2))+'px'
        });
    }
    
    if(content.length > 0){
        resizefix(content);
    }
});

function resizefix(content){
    var td = $(content).closest('td');
    var offset = td.offset(),
        width_td = td.outerWidth(),
        width_content = content.outerWidth(true);                
    var left = offset.left - (width_content - width_td);
    $(content).css({left:left});
}

function addLine(data, strokeColor, id)
{
    if($("#path" + id).length > 0){
        return false;
    }

    // Define the line
    var valueline = d3.svg.line()
        .x(function (d) {
            var dddate = new Date(d.date);
            return x(d.date);
        })
        .y(function (d) {
            return y(d.points);
        });

    var filteredData = students.filter(function (d) {
        var match = parseInt(d.user_id) === parseInt(id)
        return match;
    });
    var text = "User Id: " + id;
    if (filteredData.length > 0)
    {
        text = filteredData[0].name;
    }
// Add the valueline path.
    g.append("path")
        .attr("id", "path" + id)
        .attr("class", function (d)
        {
            if (id != "red")
            {
                return "bluePath line";
            }
            else
            {
                return "line";
            }
        })
        .attr("d", valueline(data))
        .style("stroke", strokeColor)
        .on("mouseover", function (d) {
            if (id != "red")
            {
                addTooltipProfessorGradebook(text);
            }
        })
        .on("mouseout", function (d) {
            if (id != "red")
            {
                removeTooltipProfessorGradebook();
            }
        });
    g.selectAll("dot")
        .data(data.filter(function (d, i) {
            if (i === 0) {
                return d;
            }
            if((id != "red")&&(data[i].date != data[i - 1].date))
            {
                return d;
            }
        }))
        .enter().append("circle")
        .attr("r", 2)
        .attr("class", function (d)
        {
            return "cir cir" + id;
        })
        .attr("fill", "steelblue")
        .attr("cx", function (d) {
            return x(d.date);
        })
        .attr("cy", function (d) {
            return y(d.points);
        })
        .on("mouseover", function (d) {
            if (id != "red")
            {
                var date = new Date(d.date);
                var day = date.getDate();
                var monthIndex = date.getMonth();
                var time = formatAMPM(date);
                addTooltipProfessorGradebook(text +" -- "+roundToTwo(d.points) + " pts earned on " + monthNames[monthIndex] + " " + day + " @ " + time);
            }
        })
        .on("mouseout", function (d) {
            if (id != "red")
            {
                removeTooltipProfessorGradebook();
            }
        });

    if (id != "red")
    {
        var paragraphs = g.selectAll(".cir" + id);
        paragraphs[0].forEach(function (d, i) {
            if (i === paragraphs[0].length - 1)
            {
                d.setAttribute("fill", "orange");
                d.setAttribute("r", 4);
                d.style.opacity = "0.9";
                d.style.filter  = 'alpha(opacity=90)';//IE
            }
        })
    }

}

d3.select(".deselectAll").on("change", function () {
    allSelected = !allSelected;
    var checkboxes = d3.selectAll(".single");
    checkboxes[0].forEach(function (d, i)
    {
        d.checked = allSelected;
        var num = parseInt(d.value);
        if (allSelected)
        {
            checkedBox(num);
            selectedStudents.push(num);
        }
        else
        {
            uncheckedBox(num);
            var index = selectedStudents.indexOf(num);
            selectedStudents.splice(index, 1);
        }
    });
    if (allSelected)
    {

        d3.select("#pathred").remove();
        d3.select(".cirred").remove();
        addLine(data, "red", "red");
        addRedLineDots();
    }

})
var selectedStudents = [];
d3.selectAll(".single").on("change", function () {
    var selected = this.value;
    var checked = this.checked;
    var num = parseInt(selected);
    selectedStudents.push(num);
    var masterArr = [];
    var newSelectedStudents = [];

    //remove current selected line
    if (!checked)
    {
        uncheckedBox(num);
    }
    else
    {//add line
        if (!isNaN(num))
        {       
            checkedBox(num);
        }
        else
        {
            alert("The selection is invalid or the selected user has no submissions");
        }


        if (selectedStudents.length >= 5)
        {//if we have more than 5 students we need to redraw the line cause it will start getting hidden behind
            //other lines.
            d3.select("#pathred").remove();
            d3.select(".cirred").remove();
            addLine(data, "red", "red");
            addRedLineDots();
        }
    }
});

d3.select(".multiselect").on("keydown", function() {
    var change = 0;
    var key = d3.event.keyCode;
    if(key==39||key==40)
    {//down
        d3.event.preventDefault();
        change = 1;
    }
    else if ((key == 37)||(key==38))
    {//up
        d3.event.preventDefault();
        change = -1;
    }
    else
    {
        return;
    }
    var success = false;
    var oldIndex, newIndex;
    var boxes = d3.selectAll(".checkboxMultiselect");
    boxes[0].forEach(function (d, i) {
        if(d.checked)
        {
            oldIndex = i;
            var id = parseInt(d.value);
            var index;
            var studentArr = students.filter(function (d,k) {
                if(parseInt(d.user_id) == id)
                {
                    index = k;
                }
                return parseInt(d.user_id) === id;
            });
            if(studentArr.length>0)
            {
                if(students.length-1>=(index+change)&&(index+change)>0)
                {
                    newIndex = index+change;
                    var newStudent = students[newIndex];
                    uncheckedBox(parseInt(students[index].user_id));
                    checkedBox(parseInt(newStudent.user_id));
                    success = true;
                }
                else
                {
                    if(change==1)
                    {
                        newIndex = 0;
                        var newStudent = students[0];
                        uncheckedBox(parseInt(students[index].user_id));
                        checkedBox(parseInt(newStudent.user_id));
                        success = true;
                    } else if(change==-1)
                    {
                        newIndex = students.length-1;
                        var newStudent = students[newIndex];
                        uncheckedBox(parseInt(students[index].user_id));
                        checkedBox(parseInt(newStudent.user_id));
                        success = true;
                    }
                }
                // || (index+change)<0
            }

        }
    });
    if(success)
    {//uncheck this item and check the one that was selected
        boxes[0][oldIndex].checked = false;
        boxes[0][newIndex].checked = true;
    }
});

function uncheckedBox(id)
{
    d3.select("#path" + id).remove();
    var selector = (".cir" + id).toString();
    d3.selectAll(selector).remove();
}

function checkedBox(id)
{
    var masterArr = submissions.filter(function (d) {
        return d.id === id;
    }),
        min_max = getCHartDragPoints(),
        dragDate = getChartDate();
    if (masterArr.length > 0)
    {
        var masterItems = masterArr[0].items,
            masterItemsDrag = [];
        $.each(masterItems, function(k,v){
            if(v.points >= min_max[0] && v.points <= min_max[1]){
                if(Date.parse(v.date) <= parseInt(dragDate) || v.date <= parseInt(dragDate)){
                    masterItemsDrag.push(masterItems[k]);
                }
            }
        });
        masterItems = masterItemsDrag;
        var parsedData = parseDates(masterItems);
        addLine(parsedData, "steelblue", masterArr[0].id);
    }
}

function parseDates(data)
{
    data.forEach(function (d) {

        var newDate = Date.parse(d.date);
        if (isNaN(newDate))//if the user has been selected before the dates have already been parsed. Trying to parse them again will throw an error
        {
            return;
        }
        d.date = Date.parse(d.date);

        d.points = +d.points;
    });
    return data;
}

$(document).on('mouseover','.line',function(event) {
    div.transition()
        .duration(200)
        .style("opacity", .9);
    div.html('Expected Performance')
        .style("left", (event.pageX) + "px")
        .style("top", (event.pageY - 28) + "px");
}).mouseout(function() {
    removeTooltipProfessorGradebook();
});

function addTooltipProfessorGradebook(text)
{
    div.transition()
        .duration(200)
        .style("opacity", .9);
    div.html(text)
        .style("left", (d3.event.pageX) + "px")
        .style("top", (d3.event.pageY - 28) + "px");
}

function removeTooltipProfessorGradebook()
{
    div.transition()
        .duration(500)
        .style("opacity", 0);
}

function parseTimestamp(UNIX_timestamp)
{
    var date = new Date(UNIX_timestamp);
    var monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
    var day = date.getDate();
    var monthIndex = date.getMonth();
    var year = date.getFullYear();

    var time = formatAMPM(date);
    return monthNames[monthIndex] + " " + day + " @ " + time;
}

function parseDayMonth(UNIX_timestamp) {
    var date = new Date(UNIX_timestamp);
    var monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
    var day = date.getDate();
    var monthIndex = date.getMonth();

    return monthNames[monthIndex] + " " + day;
}

function formatAMPM(date) {
    var hours = date.getHours();
    var minutes = date.getMinutes();
    var ampm = hours >= 12 ? 'pm' : 'am';
    hours = hours % 12;
    hours = hours ? hours : 12; // the hour '0' should be '12'
    minutes = minutes < 10 ? '0' + minutes : minutes;
    var strTime = hours + ':' + minutes + ' ' + ampm;
    return strTime;
}


function addRedLineDots()
{
    g.selectAll("dot")
        .data(data.filter(function (d, i) {
            if (i === 0) {
                return d;
            }
            if (data[i].points != data[i - 1].points)
            {
                return d;
            }
        }))
        .enter().append("circle")

        .attr("r", 2)
        .attr("cx", function (d) {
            return x(d.date);
        })
        .attr("cy", function (d) {
            return y(d.points);
        })
        .on("mouseover", function (d) {
            addTooltipProfessorGradebook(roundToTwo(d.points) + " points due " + parseTimestamp(d.date));
        })
        .on("mouseout", function (d) {
            removeTooltipProfessorGradebook();
        });
}

function checkboxFunctionality()
{
    var checkboxes = d3.selectAll(".checkboxMultiselect");
    checkboxes.each(function () {
        var checkbox = $(this);
        // Highlight pre-selected checkboxes
        if (checkbox.prop("checked"))
            checkbox.parent().addClass("multiselect-on");

        // Highlight checkboxes that the user selects
        checkbox.click(function () {
            if (checkbox.prop("checked"))
                checkbox.parent().addClass("multiselect-on");
            else
                checkbox.parent().removeClass("multiselect-on");
        });
    });
}

function filterrange(args, item, check,index_td) {
    
    if (check) {
        var td = $('#gridContainer .jsgrid-filter-row td:eq('+index_td+')');
        td.append('<div class="nouislider ' + check + '"><div class="rangearr"></div><div id="' + check + '"></div><span class="left-val" id="' + check + '-lover-value"></span><span id="lower-offset"></span><span class="right-val" id="' + check + '-upper-value"></span><span id="upper-offset"></span></div>');

        var slider_div = $('#' + check + '');
        noUiSlider.create(slider_div[0], {
            connect: true,
            behaviour: 'tap',
            orientation: 'horizontal',
            start: [ args['start_min'], args['start_max'] ],
            range: {
                'min': [ args['min'] ],
                'max': [ args['max'] ]
            }
        });
        slider_div[0].noUiSlider.on('end', function(values){
            if(parseFloat(args['start_min']).toFixed(2) == parseFloat(values[0]) && parseFloat(args['start_max']).toFixed(2) == parseFloat(values[1])){
                td.find('.range').removeClass('text_blue');
            }else{
                td.find('.range').addClass('text_blue');
            }

            $('.jsgrid-search-button').trigger('click');
        });
        s_div = $('.' + check + '');

        s_div.css(
        {
            'display' : 'block',
            'left': $(item).offset().left-(148-(($(item).outerWidth())/2))+'px'
        });
        function leftValue ( handle ) {
            return handle.parentElement.style.left;
        }

        var lowerValue = document.getElementById(''+check+'-lover-value'),
            upperValue = document.getElementById(''+check+'-upper-value'),
            handles = slider_div.find('.noUi-handle');

        slider_div[0].noUiSlider.on('update', function (values, handle ) {
            if ( !handle ) {

                lowerValue.innerHTML = d3.round(values[handle], 2);
            } else {
                upperValue.innerHTML = d3.round(values[handle], 2);
            }
        });

        if(args['min'] == args['max']){
            slider_div[0].setAttribute('disabled', true);
        }
    }

}

function buildTable(data) {

    $('body').mousedown(function(event) {
        if($(event.target).closest('div.nouislider').length == 0 && $(event.target).closest('.range-field').length == 0) {
            $('.nouislider').hide();
        }

        if ($(event.target).closest('.checkbox_filter_conternt').length == 0 && $(event.target).closest('td.checkbox-field').length == 0) {
            $('.checkbox_filter_conternt').hide();
            $('.my-arrow').hide();
        }
    });

    var table_range = {
        4:{
            column: 'expt',
            start_min: '',
            start_max: '',
            min: '',
            max: ''
        },
        5:{
            column: 'bonus',
            start_min: '',
            start_max: '',
            min: '',
            max: ''
        },
        6:{
            column: 'penalty',
            start_min: '',
            start_max: '',
            min: '',
            max: ''
        },
        7:{
            column: 'possBonus',
            start_min: '',
            start_max: '',
            min: '',
            max: ''
        },
        8:{
            column: 'probPenalty',
            start_min: '',
            start_max: '',
            min: '',
            max: ''
        },
        9:{
            column: 'total',
            start_min: '',
            start_max: '',
            min: '',
            max: ''
        },
        10:{
            column: 'currnent',
            start_min: '',
            start_max: '',
            min: '',
            max: ''
        }
    }

    var field_keys = {
        no: "<span class='col_no'>#</span>",
        first_name: "First Name",
        last_name: "Last Name",
        sections: "Sections",
        score: "Exp<span class='one_point'></span> Pts<span class='one_point'></span>",
        bonuses: "Bonus",
        penalties: "Penalties",
        probable_penalty: "Prob<span class='one_point'></span> Penalty",
        possible_bonus: "Poss<span class='one_point'></span> Bonus",
        totalBP: "Total B/P",
        total: "Current Score",
        grade: "Grade",
        details: "Details"
    };

    function get_checkboxes(col_name) {
        
        var checkboxes_arr = {},
            checkboxes = '';
            b = 0;

        $.each(data, function(key,val){
            $.each(data[key][col_name].split('<br>'),function(k,v){
                checkboxes_arr[v] = true;
            });
        });

        $.each(checkboxes_arr,function(key,val){
            checkboxes += '<div class="checkbox custom-checkbox"><input type="checkbox" id="' + b + col_name + '" checked="checked" value="'+ key +'" /><label for="'+b + col_name +'">'+ key +'</label></div>';
            b++;
        });

        return checkboxes;
    }

    function get_column_info(col_name) {
        var ret_d = {},
            ret_data = {};

        $.each(data, function(key,val){
            $.each(data[key][col_name].split('<br>'),function(k,v){
                ret_data[v] = true;
            });
        });

        return ret_data;
    }

    function createData(range, field) {

        if(typeof(field) == "undifined"){
            range = null;
            field = null;
        }

        var i = 0;
        var loadData = [];
        var columns_data = {
          expt: [],
          bonus: [],
          penalty: [],
          possBonus: [],
          probPenalty: [],
          total: [],
          currnent: []
        };

        $.each(data, function(key, row) {
            columns_data.expt.push(row.score);
            columns_data.bonus.push(row.bonuses);
            columns_data.penalty.push(row.penalties);
            columns_data.possBonus.push(row.possible_bonus);
            columns_data.probPenalty.push(row.probable_penalty);
            columns_data.total.push(row.totalBP);
            columns_data.currnent.push(row.total);
        ////////////////////////////////////////////
            var data_row = {};
            var value = null;
            $.each(field_keys, function(k, v) {
                switch(k){
                    case 'no':
                      value = ++i;  
                    break;
                    case 'first_name':
                        var name = row['name'].split(' ');
                        value = '<a href="' + row.profile_url + '">' + name[0].replace(',','') + '</a>';
                        break;
                    case 'last_name':
                        var name = row['name'].split(' ');
                        nm =  name.length > 1 ? name[1] : '';
                        value = '<a href="' + row.profile_url + '">' + nm + '</a>';

                        break
                    case 'grade':
                        value = '<div>' + row[k] + '<span style="display:none">' + key + '</span></div>';
                        break;
                    case 'sections':
                        value = row[k];
                        break;
                    case 'details':
                        value = $("<a/>")
                                    .attr("href","#")
                                    .attr("data-toggle","modal")
                                    .attr("data-target","#modalStudentGradebook")
                                    .html('<i class="fa fa-list-alt details_icon"></i>')
                                    .on('click', function(e){
                                        showStudentDetails(row);
                                    });
                        break;
                    default:
                        value = d3.round(row[k], 2);
                }
                data_row[v] = value;
            });
            loadData.push(data_row);
        });

        $.each(table_range, function(a,b) {
            var min_val = Math.min.apply(null, columns_data[b.column]);
            var max_val = Math.max.apply(null, columns_data[b.column]);
            table_range[a]['start_min'] = min_val;
            table_range[a]['start_max'] = max_val;
            table_range[a]['min'] = min_val;
            table_range[a]['max'] = max_val;
        });

        $(document).on('click', '.jsgrid-grid-header td.checkbox-field', function(){
            var self = $(this);
            var content = self.find('.checkbox_filter_conternt');
            $('.checkbox_filter_conternt').not(content).hide();
            $('.my-arrow').not(self.find('.my-arrow')).hide();
            if (content.is(':visible')){
                content.hide();
                self.find('.my-arrow').hide();
            } else {
                resizefix(content);
                content.show();
                self.find('.my-arrow').show();

            }
        });
        
        $(document).on('click','#gridContainer .jsgrid-filter-row .range-field', function() {
            var fnd = $(this).index();
            var b = $('.jsgrid-table').find('tr').eq(0).children('th').eq(fnd);
            var itex = b.text().slice(0, 3);
            col_num = $(this).closest('td').index();
            var check = $('body').find('#'+itex+'');
            var index_td = $(this).index();
            $('.nouislider').hide();
            if (!check[0]) {
                filterrange(table_range[col_num], this, itex, index_td);
            } else {
                check.closest('.nouislider').css({
                    'left': $(this).offset().left-(148-(($(this).outerWidth())/2))+'px'
                });
                if(check.is(':visible')){
                    check.closest('.nouislider').hide();
                }else{
                    check.closest('.nouislider').show();
                }
            }

        });

        $(document).on('keyup', '.first_name input, .last_name input, .sections input, .grade input', function(){
            $('.jsgrid-search-button').trigger('click');
        });

        $(document).on('change', '.checkbox_filter_conternt input[type=checkbox]', function(){
           $('.jsgrid-search-button').trigger('click'); 
        });

        return loadData;
    }
    var loadData = createData();
    var data_controller = {
       loadData: function(filter) {
            return $.grep(this.clients, function(client) {
                var sections_res = -1;
                $.each(filter[field_keys.sections], function(f_v){
                    $.each(client[field_keys.sections].split('<br>'), function(key,c_v){
                        if(f_v == c_v){
                            sections_res = 1;
                            return;
                        }
                    });
                });
                
                var grade_res = -1;
                var reg = /<div>.+<span/.exec(client[field_keys.grade]);
                reg = reg[0].replace(/<div>|<span/gi, function myFunction(x){return '';}).trim();
                $.each(filter[field_keys.grade], function(v){

                   if(v == reg){
                        grade_res = 1;
                        return;
                   } 
                });

                return (!filter[field_keys.no] || client[field_keys.no] === filter[field_keys.no])
                    && (!filter[field_keys.first_name].toLowerCase() || $(client[field_keys.first_name]).text().toLowerCase().indexOf(filter[field_keys.first_name].toLowerCase()) > -1)

                    && (!filter[field_keys.last_name].toLowerCase() || $(client[field_keys.last_name]).text().toLowerCase().indexOf(filter[field_keys.last_name].toLowerCase()) > -1)

                    && (!filter[field_keys.sections] || sections_res > -1)

                    && (!filter[field_keys.score] || (client[field_keys.score] >= JSON.parse(filter[field_keys.score])['min'] && client[field_keys.score] <= JSON.parse(filter[field_keys.score])['max']))

                    && (!filter[field_keys.bonuses] || (client[field_keys.bonuses] >= JSON.parse(filter[field_keys.bonuses])['min'] && client[field_keys.bonuses] <= JSON.parse(filter[field_keys.bonuses])['max']))

                    && (!filter[field_keys.penalties] || (client[field_keys.penalties] >= JSON.parse(filter[field_keys.penalties])['min'] && client[field_keys.penalties] <= JSON.parse(filter[field_keys.penalties])['max']))

                    && (!filter[field_keys.probable_penalty] || (client[field_keys.probable_penalty] >= JSON.parse(filter[field_keys.probable_penalty])['min'] && client[field_keys.probable_penalty] <= JSON.parse(filter[field_keys.probable_penalty])['max']))

                    && (!filter[field_keys.possible_bonus] || (client[field_keys.possible_bonus] >= JSON.parse(filter[field_keys.possible_bonus])['min'] && client[field_keys.possible_bonus] <= JSON.parse(filter[field_keys.possible_bonus])['max']))

                    && (!filter[field_keys.totalBP] || (client[field_keys.totalBP] >= JSON.parse(filter[field_keys.totalBP])['min'] && client[field_keys.totalBP] <= JSON.parse(filter[field_keys.totalBP])['max']))

                    && (!filter[field_keys.total] || (client[field_keys.total] >= JSON.parse(filter[field_keys.total])['min'] && client[field_keys.total] <= JSON.parse(filter[field_keys.total])['max']))

                    && (!filter[field_keys.grade] || grade_res > -1)

            });
        }

    }
    window.data_controller = data_controller;

    data_controller.clients = loadData;

    jsGrid.sortStrategies.client = function(index1, index2){
        index1 = $(index1).find('span').text();
        index2 = $(index2).find('span').text();
        var client1 = data_controller.clients[index1];
        var client2 = data_controller.clients[index2];
        if(client1[field_keys.total] < client2[field_keys.total]) return -1;
        if(client1[field_keys.total] === client2[field_keys.total]) return 0;
        if(client1[field_keys.total] > client2[field_keys.total]) return 1;
    };

    jsGrid.sortStrategies.negative = function(index1, index2){
        if(parseInt(index1) > parseInt(index2)) return 1;
        if(parseInt(index1) == parseInt(index2)) return 0;
        if(parseInt(index1) < parseInt(index2)) return -1;

    };

    jsGrid.sortStrategies.byText = function(value1, value2){
        return $(value1).text().localeCompare($(value2).text());
    };

    
    var MyCheckboxField = function(config) {
        jsGrid.Field.call(this, config);
    }

    MyCheckboxField.prototype = new jsGrid.Field({
        css: 'checkbox-field',
        autosearch: true,
        filterValue: function() {
            var checkbox_cont = $('.' + this.name),
                checkboxes = {};
            if (checkbox_cont.length>0){
                $.each(checkbox_cont.find('input:checked'),function(i,checkbox) {
                    checkboxes[$(checkbox).val()] = true;
                });
            } else {
                checkboxes = get_column_info(this.name.toLowerCase());
            }

            return checkboxes;
        }

    });

    jsGrid.fields.checkbox = MyCheckboxField;

    var MyRangeField = function(config) {
        jsGrid.Field.call(this, config);
    };
    MyRangeField.prototype = new jsGrid.Field({
        css: "range-field",
        autosearch: true,
        filterValue: function(){
            var id = this.name.slice(0, 3);
            var  min = -1000;
            var  max = 5000;
            if($('#'+id).length > 0){
                min = parseFloat($('#'+id).closest('.'+id).find('.left-val').text()).toFixed(2);
                max = parseFloat($('#'+id).closest('.'+id).find('.right-val').text()).toFixed(2);
            }
            return JSON.stringify({min:min,max:max});
        }
    });
    jsGrid.fields.range = MyRangeField;

    var fieldIndex = '';
    var countClick = 1;
    $(document).on('click','.jsgrid-header-row th:not(.number,.details)',function(){
        var index = $(this).index();
        if(fieldIndex == index){
            countClick+=1;
        }else{
            fieldIndex = index;
            countClick = 1;
        }
        if(countClick%3 == 0){
            $("#gridContainer").jsGrid("_sortReset");
        }
    });

    $("#gridContainer").jsGrid({
        height: "70%",
        width: "100%",
        filtering: true,
        sorting: true,
        autoload: true,
        pageSize: 5,
        pageButtonCount: 3,
        controller: data_controller,
        fields: [
            { name: field_keys.no, type: "hidden", width: 25, sorting: false, css: 'number'},
            { name: field_keys.first_name, type: "text", width: 50, sorter: 'byText', css: 'first_name' },
            { name: field_keys.last_name, type: "text", width: 50, sorter: 'byText', css: 'last_name' },
            { name: field_keys.sections, type: "checkbox", width: 70 },
            { name: field_keys.score, type: "range", width: 40 },
            { name: field_keys.bonuses, type: "range", width: 40 },
            { name: field_keys.penalties, type: "range",width:45, sorter: 'negative' },
            { name: field_keys.possible_bonus, type: "range",width:35 },
            { name: field_keys.probable_penalty, type: "range",width:40, sorter: 'negative'},
            { name: field_keys.totalBP, type: "range", width:35, sorter: 'negative'},
            { name: field_keys.total, type: "range", width:40 },
            { name: field_keys.grade, type: "checkbox", width:70, sorter: 'client' },
            { name: field_keys.details, type: "hidden", width:30, sorting: false, css: 'details' },
            { type: 'control', editButton: false, deleteButton: false, clearFilterButton: false, modeSwitchButton: false , width:0 }
        ],
        onDataLoaded: function(args) {
            var td = $('.jsgrid-grid-header tr').eq(1).find('td');
            $('.jsgrid-search-button').hide();
            td.eq(td.length-2).html('<a data-toggle="modal" style="outline:none;" href="#content-confirmation"><i class="fa fa-cog table_set"></i></a>').css('text-align','center');
            if(getStorage('ListSetup')){
                hide_or_show(jQuery.parseJSON(getStorage('ListSetup')));
            }
            if($('.filter_checkbox').length == 0){
                var sections = get_checkboxes('sections'),
                    grade = get_checkboxes('grade');

                $('.jsgrid-grid-header').find('td.checkbox-field').html('<i class="fa fa-filter filter_checkbox"></i>');
                $('.jsgrid-grid-header').find('td.checkbox-field').eq(0).append('<div class="my-arrow"></div><div class="checkbox_filter_conternt Sections" style="display:none;">'+ sections +'</div>');
                $('.jsgrid-grid-header').find('td.checkbox-field').eq(1).append('<div class="my-arrow"></div><div class="checkbox_filter_conternt Grade" style="display:none;">'+ grade +'</div>');
            }
        },
        onRefreshed: function(args) {
            if(args.grid.data.length == 0)
                return;

            $.each(args.grid.data, function(i,row){
            $('.jsgrid-grid-body').find('tr').eq(i).find('td').eq(0).html(i+=1);
            });
        }
    });

    $('.jsgrid-table tr').eq(1).find('td input[type=text]').each(function(a,b) {
        $(b).closest('td').append('<i class="filter fa fa-filter"></i>');
    }).focusin(function(){
        $(this).next().hide();
    }).focusout(function() {
        if($(this).val()){
            $(this).next().hide();
        }else{
            $(this).next().show();
        }
    });

    var columns = '';
    var check = getStorage('ListSetup');
    if (check !== null) {
        columns = jQuery.parseJSON(check);
    } else {
        columns = {
            0:{
                column:'#',
                column_title:'',
                show: true,
            },
            1:{
                column:'first_name',
                column_title:'First Name',
                show: true,
            },
            2:{
                column:'last_name',
                column_title:'Last Name',
                show: true,
            },
            3:{
                column:'sections',
                column_title:'Sections',
                show: true,
            },
            4:{
                column:'score',
                column_title:'Exp<span class="one_point"></span> Pts<span class="one_point"></span>',
                show: true,
            },
            5:{
                column:'bonuses',
                column_title:'Bonus',
                show: true,
            },
            6:{
                column:'penalties',
                column_title:'Penalties',
                show: true,
            },
            7:{
                column:'probable_penalty',
                column_title:'Prob<span class="one_point"></span> Penalty',
                show: true,
            },
            8:{
                column:'possible_bonus',
                column_title:'Poss<span class="one_point"></span> Bonus',
                show: true,
            },
            9:{
                column:'totalBP',
                column_title:'Total B/P',
                show: true,
            },
            10:{
                column:'total',
                column_title:'Current Score',
                show: true,
            },
            11:{
                column:'grade',
                column_title:'Grade',
                show: true,
            }
        };
        setStorage('ListSetup',JSON.stringify(columns));
    }

    var rangeIcon = '<i class="fa fa-arrows-h range" aria-hidden="true"></i>';
    $('.jsgrid-filter-row').find('.range-field').html(rangeIcon);
    var popup = $('#content-confirmation .modal-body');
    var local = new Object;
    var str = '';
    var checked_column = '';
    $.each(columns, function(a, b) {
        if (b.column != '#') {
            if(b.show){
                checked_column = 'checked="checked"';
            } else {
                checked_column = '';
            }

            str +=  '<li><div class="checkbox custom-checkbox"><input name="checkbox" value="' + a + '" type="checkbox"' + checked_column + ' id="' + b.column + '"/><label  class="" for="' + b.column + '">' + b.column_title + '</label></div></li>';

        }
    });

    if(!$('.with-checkboxes').length){
        popup.append('<div class="control-simplelist with-checkboxes is-sortable" ><ul data-disposable>' + str + '</ul></div>');
    }

    hide_or_show(columns);

    $('#content-confirmation .modal-footer button').eq(0).click(function() {


        $('#content-confirmation input[type=checkbox]').each(function(a,b) {

            var key = $(b).val();
            if ($(b).is(':checked')) {
                columns[key]['show'] = true;
            } else {
                columns[key]['show'] = false;
            }
        });
        hide_or_show(columns);
        setStorage('ListSetup',JSON.stringify(columns));
    });

}

function hide_or_show(args) {
    var check_uncheck = [];
    $.each(args, function(a,b) {
        if (!b.show) {
            check_uncheck[a] = false;
        } else {
            check_uncheck[a] = true;
        }

    });
    $('.jsgrid-table tr').each(function(a, b){
        $.each(check_uncheck, function(c,d) {
            if (d) {
                $('.jsgrid-table').find('tr').eq(a).children('th, td').eq(c).show();
            } else {
                $('.jsgrid-table').find('tr').eq(a).children('th, td').eq(c).hide();
            }
        });
    });
}
function getStorage(key) {
    return localStorage.getItem(key);
}

function setStorage(key,args) {
    localStorage.setItem(key,args);
}

function callStudentsMilestoneInfo(studentsArr)
{
    d3.select("#gridContainer").style("display","block");
    var idsArr =[];
    for(var i=0;i<=studentsArr.length-1;i++)
        // for(var i=0;i<=9;i++)
    {
        var currentStudent = studentsArr[i];
        idsArr.push(currentStudent.user_id);
        //if( ( i!=0 ) && ( i % 10 == 0 ) )
        //{
        //we'll send requests every 10 students.

          //  $.get("gradebook/getSetOfUsersMilestoneInfo",{experienceInstanceId:experienceInstanceId, userIds:(idsArr)},function(data,status,xhr)
           // {
             //   d3.select(".bottomSpinnerDiv").style("display","none");
               // bottomExperienceScores = bottomExperienceScores.concat(data);//append the new data to the old
            //    buildTable(data);
            //);

            //var idsArr =[];
            //initialize the array again.
        //}

    }

    //send a last request with the remaining IDS
    if(idsArr.length>0)
    {
        $.get("gradebook/getSetOfUsersMilestoneInfo",{experienceInstanceId:experienceInstanceId, userIds:(idsArr)},function(data,status,xhr)
        {
            d3.select(".bottomSpinnerDiv").style("display","none");
            buildTable(data);

        });
    }
}

function showStudentDetails(studentSummaryData)
{
    d3.select("#gradebook").style("display", "none");
    d3.select("#spinner").style("display","block");
    //top table
    d3.select("#studentTitle").html(studentSummaryData.name);
    d3.select("#tdExpPoints").html(roundToTwo(studentSummaryData.score));
    d3.select("#tdBonus").html(roundToTwo(studentSummaryData.bonuses));
    d3.select("#tdPenalties").html(roundToTwo(studentSummaryData.penalties));
    d3.select("#tdTotalPoints").html(roundToTwo(studentSummaryData.total));

    //
    var currGrade = d3.select("#spanCurrentGrade").html(studentSummaryData.grade);

    //get bottom data
    var userId = studentSummaryData.id;
    var promise = $.get("getStudentGradebookData",{studentId:userId});
    promise.then(function (data, textStatus, jqXHR) {
            d3.select("#spinner").style("display","none");
            makeTables(data);
            d3.select("#gradebook").style("display", "block");
        })
        .fail(function (data2) {
        });
}

function roundToTwo(num) {
    return +(Math.round(num + "e+2")  + "e-2");
}

function chartDragPoints() {
    $.each($('input.single:checked'), function(k,input){
        var id = parseInt($(input).val());
        uncheckedBox(id);
        checkedBox(id);
    });
}

function chartDateRange() {
    var date = getChartDate();
    var orangeLine = x(date);
    $('.todayLine').attr('x',orangeLine);
    chartDragPoints();
}

var rMax = d3.max(data, function (d) {return d.points;});
$(".range-slider").slider({
    max: rMax,
    values: [0,rMax],
    step: 1,
    range: true
}).slider("pips")
.on("slidechange", function(e,d) {
}).slider('float', {
    labels: true
});

var dTo = new Date();
var endDate = Date.parse(endDate);
var dFrom = d3.min(data, function(d){return d.date}),
    rDTo = Date.parse(dTo),
    speed;
var endDateTo = (endDate < dTo) ? endDate : rDTo;
labels = [];
dateRange = [];
for(var i = dFrom; i<=endDateTo; i+=86400000){
    labels.push(parseDayMonth(i));
    dateRange.push(new Date(i));
}
if(endDate>dTo){
    labels.push(parseDayMonth(endDateTo));
    dateRange.push(new Date(endDateTo));
}
var dateMax = labels.length - 1;
$(".my-ui-slider").slider({
    min: 0,
    max: dateMax,
    value: dateMax,
    step: 1
}).slider("pips")
.on("slidechange", function(e,d) {
    pointDate = $('.my-ui-slider .ui-slider-pip-selected').index()-1;
}).slider('float', {
    labels: labels
});

$('.my-ui-slider .ui-slider-pip-first').find('.ui-slider-label').text(parseDayMonth(dFrom));
$('.my-ui-slider .ui-slider-pip-last').find('.ui-slider-label').text(parseDayMonth(endDate));


pointDate = 0;
function intervalIts() {
    var points = $('.my-ui-slider').children('span').not('.ui-slider-handle');
    var count = points.length;
    if(count > pointDate){
        var point = points.eq(pointDate); 
        var left = point[0].style.left;
        var index = point.find('.ui-slider-label').attr('data-value');
        var val = labels[index];
        $('.my-ui-slider').find('.ui-slider-handle').css('left',left).find('span').text(val);
        $('.ui-slider-pip').removeClass('ui-slider-pip-selected').eq(pointDate).addClass('ui-slider-pip-selected');
        chartDateRange();
    }else{
        $('button.player').find('i').removeClass('fa-pause').addClass('fa-play');
        pointDate = -1;
        clearInterval(speed);
    }
    pointDate++;
}

$(document).on('click','.player', function() {
    var i = $(this).find('i');
    if(i.hasClass('fa-play')){ 
        speed = setInterval(intervalIts,500);
        i.removeClass('fa-play').addClass('fa-pause');
    } else { 
        i.removeClass('fa-pause').addClass('fa-play');
        clearInterval(speed);
    }
});

$(document).bind('keydown change', '.multiselect', function(event) {

    if($(this,':checkbox:checked').length == 1) {
        
        if(event.which >= 37 && event.which <= 40) {
            $(this).find(':checkbox:checked')[0].scrollIntoView(false);
        }
    }
});