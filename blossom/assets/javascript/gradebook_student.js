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

function Student() 
{
    this.__init.call(this);
};

Student.prototype = {

    options: {},
    submissions:[],
    pointDate: 0,
    user: user,
    monthNames: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],

    __init: function () 
    {
        this._options();
        this.domFunctions();
        this.promise();
    },

    _options: function() 
    {
        this.options.endDate = endDate;
        Object.freeze(this.options);
    },

    domFunctions: function () 
    {
        var self = this;
/*        $.ajax({
            url: 'gradebook/getStudentSubmission',
            success: function (data) {
                console.log(data);
            }
        });*/
        /*var monthNames = [
            "Jan", "Feb", "Mar",
            "Apr", "May", "Jun", "Jul",
            "Aug", "Sep", "Oct",
            "Nov", "Dec"
        ];*/
        ////////////////////////////////////////////////////////
        self.margin = {top: 30, right: 20, bottom: 30, left: 50};
        self.width = 800 - self.margin.left - self.margin.right;
        self.height = 400 - self.margin.top - self.margin.bottom;
        var allSelected = true;
        // Parse the date / time
        var parseDate = d3.time.format("%d-%b-%y").parse;

        // Set the ranges
        self.x = d3.time.scale().range([0, self.width]);
        self.y = d3.scale.linear().range([self.height, 0]);


        // Define the axes
        var xAxis = d3.svg.axis().scale(self.x)
            .orient("bottom").ticks(6);

        var yAxis = d3.svg.axis().scale(self.y)
            .orient("left").ticks(10);

        // Adds the svg canvas
        var svg = d3.select("#chart")
            .append("svg")
            .attr("id", "svg")
            .attr("width", self.width + self.margin.left + self.margin.right)
            .attr("height", self.height + self.margin.top + self.margin.bottom);
        self.g = svg.append("g")
            .attr("transform",
                "translate(" + self.margin.left + "," + self.margin.top + ")")
            .attr("id", "gChart");
        //Milestone data
        var yArr = [];
        // Get the data
        self.data = self.parseDates(chartData);
        // Scale the range of the data
        self.x.domain(d3.extent(self.data, function (d) {
            return d.date;
        }));
        self.y.domain([0, d3.max(self.data, function (d) {
            return d.points;
        })]);

        // Add the X Axis
        self.g.append("g")
            .attr("class", "x axis")
            .attr("transform", "translate(0," + self.height + ")")
            .call(xAxis);

        // Add the Y Axis
        self.g.append("g")
            .attr("class", "y axis")
            .call(yAxis);

        // Add Milestones horizontal lines
        chartData.forEach(function(v){
            if($.inArray(v.points,yArr) == -1 && parseInt(v.points) != 0){
                yArr.push(v);
            }
        });
        self.addMilestonesLine(yArr);
        // add the red line chart
        self.addLine(self.data, "red", "red");
        // add circles for each milestone
        self.addRedLineDots();
        //add a vertical rect denoting today
        var todayDate = new Date();
        todayDate = (Date.parse(self.options.endDate) > Date.parse(todayDate) || self.submissions.length == 0) ? todayDate : new Date(self.options.endDate);
        var parsed = Date.parse(todayDate);
        self.g.append("svg:rect")
            .attr("x", function (d) {
                return self.x(parsed);
            })
            .attr("y", function (d) {
                return 0;
            })
            .attr("height", function (d) {
                return self.height;
            })
            .attr("width", function (d) {
                return 0.5;
            })
            .attr("stroke-width", 1.5)
            .attr("class", "todayLine")
            .on("mouseover", function (d) {
                self.addTooltipProfessorGradebook(todayDate.toDateString());
            })
            .on("mouseout", function (d) {
                self.removeTooltipProfessorGradebook();
            });

            var rMax = d3.max(self.data, function (d) {return d.points;});
            $(".range-slider").slider({
                min: 0,
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
            var endDate = Date.parse(self.options.endDate);
            self.dFrom = d3.min(self.data, function(d){return d.date}),
            self.rDTo = Date.parse(dTo),
            self.speed;
            var endDateTo = (endDate < dTo) ? endDate : self.rDTo;
            labels = [];
            dateRange = [];
            for(var i = self.dFrom; i <= endDateTo; i += 86400000){
                labels.push(self.parseDayMonth(i));
                dateRange.push(new Date(i));
            }
            if(endDate > dTo){
                labels.push(self.parseDayMonth(endDateTo));
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
                if(pointDate == dateMax){
                  pointDate = 0;  
                }
            }).slider('float', {
                labels: labels
            });

            $('.my-ui-slider .ui-slider-pip-first').find('.ui-slider-label').text(self.parseDayMonth(self.dFrom));
            $('.my-ui-slider .ui-slider-pip-last').find('.ui-slider-label').text(self.parseDayMonth(endDateTo));
        $(document).on('click','.Q123MinMax .btn-group',function(){
            if(!$(this).find('label').hasClass('disabled')){
                self.addQuartileMinMaxLine();
            }
        });

        $(document).on('click','.player', function() {
            var i = $(this).find('i');
            if (i.hasClass('fa-play')) {
                self.speed = setInterval(function(){
                    self.intervalIts();
                },500);
                i.removeClass('fa-play').addClass('fa-pause');
            } else {
                i.removeClass('fa-pause').addClass('fa-play');
                clearInterval(self.speed);
            }
        });

        $(document).on('mouseover','.line:not(.bluePath)',function(event) {
            div.transition()
                .duration(200)
                .style("opacity", .9);
            div.html('Expected Performance')
                .style("left", (event.pageX) + "px")
                .style("top", (event.pageY - 28) + "px");
        }).mouseout(function() {
            self.removeTooltipProfessorGradebook();
        });

        $(document).ready(function () {
            div = d3.select("body").append("div")
                .attr("class", "tooltip")
                .style("opacity", 0);
            d3.select("#iGradeTooltip").on("mouseover", function (d) {
                var str = "<table class='table table-condensed table-gradingScheme'><thead> <tr> <th>Points</th> <th>Letter Grade</th></tr> </thead> <tbody> ";
                for(var i=0;i<=gradingScheme.length-1;i++)
                {
                    var item = gradingScheme[i];

                    str+="<tr><td>"+item.value+"</td> <td>"+item.name+"</td> </tr>";
                }
                str+="</tbody> </table>";
                self.addTooltipGrade(str, event);
            })
            .on("mouseout", function (d) {
                self.removeTooltipGrade();
            });
        });
        ////////////////////////////////////////////////////////////////////
        div = d3.select("body").append("div")
            .attr("class", "tooltip")
            .style("opacity", 0);

        //to save the last active tab after page reload
        var tabIndex = self.getStorage('tab');
        if(tabIndex == null)
        {
            tabIndex = 0;
        }

        $('.grade-tabs li').eq(tabIndex).addClass('active');
        $('.tab-pane').eq(tabIndex).addClass('active');
        $(document).on('click','.grade-tabs li',function(){
            self.setStorage('tab', $(this).index());
        });

    },

    getChartDate: function() {
        var index = $('.ui-slider-pip-selected').find('.ui-slider-label').attr('data-value');
        return Date.parse(dateRange[index]);
    },

    promise: function() 
    {
        var self = this;
        var promise = $.get("gradebook/getAllStudentSubmissions");
        promise.then(function (data1, textStatus, jqXHR) {
            self.submissions = data1;
            $('.Q123MinMax, .histogramGroup').find('.btn-group').find('.btn-info').removeClass('disabled');
            $('.histogramRVS').removeClass('histogramRVS');
            self.callStudentsMilestoneInfo(students);
            var inputs = document.getElementsByClassName('checkboxMultiselect');
            for(var i = 0; i < inputs.length; i++) {
                inputs[i].disabled = false;
            }
            d3.select('.spinnerDiv').style("display","none");
            d3.select("#chart").style("opacity","1");
            d3.select("#topRight").style("opacity","1");
        })
        .fail(function (data2) {
            console.log("Unable to retrieve student submissions");
        });

    },

    callStudentsMilestoneInfo: function (studentsArr) 
    {
        var self = this;
        d3.select("#gridContainer").style("display","block");
        var idsArr =[];
        for(var i = 0; i <= studentsArr.length - 1; i++)
        {
            var currentStudent = studentsArr[i];
            idsArr.push(currentStudent.user_id);
        }
        if(idsArr.length > 0)
        {
            $.each(studentsArr, function (a, b)
            {
                if (parseInt(b.user_id) === self.user) {
                   var num = parseInt(b.user_id);
                   self.checkedBox(num);
                }
            });
        }
        $(document).on("change", '.deselectAll',  function () {
            var th = $(this);
            $.each(studentsArr, function (d, i)
            {
                if (!th.closest('label').hasClass('active')) {
                    var num = parseInt(i.user_id);
                    self.checkedBox(num);
                } else {
                    if (parseInt(i.user_id) != self.user) {
                        self.uncheckedBox(i.user_id);
                    }
                }
            });

        });
    },

    addLine: function (data, strokeColor, id)
    {
        var self = this;
        if($("#path" + id).length > 0){
            return false;
        }

        // Define the line
        var valueline = d3.svg.line()
            .x(function (d) {
                var dddate = new Date(d.date);
                return self.x(d.date);
            })
            .y(function (d) {
                return self.y(d.points);
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
        if (id === self.user) {
            strokeColor = '#024b88';
        }
        self.g.append("path")
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
                    self.addTooltipProfessorGradebook(text);
                }
            })
            .on("mouseout", function (d) {
                if (id != "red")
                {
                    self.removeTooltipProfessorGradebook();
                }
            });
        self.g.selectAll("dot")
            .data(data.filter(function (d, i) {
                if (i === 0) {
                    return d;
                }
                if((id != "red") && (data[i].date != data[i - 1].date))
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
                return self.x(d.date);
            })
            .attr("cy", function (d) {
                return self.y(d.points);
            })
            .on("mouseover", function (d) {
                if (id != "red")
                {
                    var date = new Date(d.date);
                    var day = date.getDate();
                    var monthIndex = date.getMonth();
                    var time = self.formatAMPM(date);
                    self.addTooltipProfessorGradebook(text +" -- "+self.roundToTwo(d.points) + " pts earned on " + self.monthNames[monthIndex] + " " + day + " @ " + time);
                }
            })
            .on("mouseout", function (d) {
                if (id != "red")
                {
                    self.removeTooltipProfessorGradebook();
                }
            });

        if (id != "red")
        {
            var paragraphs = self.g.selectAll(".cir" + id);
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

    },

    uncheckedBox: function(id)
    {
        d3.select("#path" + id).remove();
        var selector = (".cir" + id).toString();
        d3.selectAll(selector).remove();
    },

    checkedBox: function (id, slideDays)
    {
        var self = this;
        var masterArr = self.submissions.filter(function (d) {
            if (slideDays && !$('.nameLabel').hasClass('active')) {
                return d.id === self.user;
            } else {
                return d.id === id;
            }
        }),
            min_max = self.getCHartDragPoints(),
            dragDate = self.getChartDate();
        if (masterArr.length > 0)
        {
            if (slideDays) {
                var masterItems = masterArr[0].items,
                    newData = [];
                $.each(masterItems, function(k,v) {
                    if(v.points >= min_max[0] && v.points <= min_max[1]) {
                        if(Date.parse(v.date) <= parseInt(dragDate) || v.date <= parseInt(dragDate)){
                            newData.push(v);
                        }
                    }
                });
                var parsedData = self.parseDates(newData);
                self.addLine(parsedData, "steelblue", masterArr[0].id);
            } else {
                var masterItems = masterArr[0].items;
                var show_student_line = false;
                var maxPoint = masterItems[masterItems.length-1].points;
                var maxDate = masterItems[masterItems.length-1].date;
                if(maxPoint >= min_max[0] && maxPoint <= min_max[1]) {
                    if(Date.parse(maxDate) <= parseInt(dragDate) || maxDate <= parseInt(dragDate)){
                        show_student_line = true;
                    }
                }
                var parsedData = (show_student_line) ? self.parseDates(masterItems) : self.parseDates([]);
                self.addLine(parsedData, "steelblue", masterArr[0].id);
            }
        }
    },

    chartDateRange: function(days) {
        var date = this.getChartDate();
        var orangeLine = this.x(date);
        $('.todayLine').attr('x',orangeLine);
        this.chartDragPoints(days);
    },

    intervalIts: function() {
        var self = this;
        var points = $('.my-ui-slider').children('span').not('.ui-slider-handle');
        var count = points.length;
        if(count > self.pointDate){
            var point = points.eq(self.pointDate); 
            var left = point[0].style.left;
            var index = point.find('.ui-slider-label').attr('data-value');
            var val = labels[index];
            $('.my-ui-slider').find('.ui-slider-handle').css('left',left).find('span').text(val);
            $('.ui-slider-pip').removeClass('ui-slider-pip-selected').eq(self.pointDate).addClass('ui-slider-pip-selected');
            self.chartDateRange(true);
        } else {
            $('button.player').find('i').removeClass('fa-pause').addClass('fa-play');
            self.pointDate = -1;
            clearInterval(self.speed);
        }
        self.pointDate++;
    },

    addRedLineDots: function()
    {
        var self = this;
        self.g.selectAll("dot")
            .data(self.data.filter(function (d, i) {
                if (i === 0) {
                    return d;
                }
                if (self.data[i].points != self.data[i - 1].points)
                {
                    return d;
                }
            }))
            .enter().append("circle")

            .attr("r", 2)
            .attr("cx", function (d) {
                return self.x(d.date);
            })
            .attr("cy", function (d) {
                return self.y(d.points);
            })
            .on("mouseover", function (d) {
                self.addTooltipProfessorGradebook(self.roundToTwo(d.points) + " points due " + self.parseTimestamp(d.date));
            })
            .on("mouseout", function (d) {
                self.removeTooltipProfessorGradebook();
            });
    },

    addMilestonesLine: function(yArr)
    {
        var self = this;
        yArr.forEach(function(v){
            self.g.append("svg:rect")
            .attr("x", 0)
            .attr("y", self.y(v.points))
            .attr("height", 0.1)
            .attr("width", self.width)
            .attr("stroke-width", 0.5)
            .attr("class", "milestone")
            .on("mouseover", function () {
                self.addTooltipProfessorGradebook(self.roundToTwo(v.points) + " points due " + self.parseTimestamp(v.date));
            })
            .on("mouseout", function () {
                self.removeTooltipProfessorGradebook();
            });
        });
    },

    addTooltipGrade: function(text, event)
    {
        div.transition()
            .duration(200)
            .style("opacity", .9);
        div.html(text)
            .style("left", (d3.event.pageX) + "px")
            .style("top", (d3.event.pageY - 28) + "px");
    },

    removeTooltipGrade: function()
    {
        div.transition()
            .duration(500)
            .style("opacity", 0);
    },

    getStorage: function(key) 
    {
        return localStorage.getItem(key);
    },

    setStorage: function(key,args)
    {
        localStorage.setItem(key,args);
    },

    removeTooltipProfessorGradebook: function()
    {
        div.transition()
            .duration(500)
            .style("opacity", 0);
    },

    addTooltipProfessorGradebook: function(text)
    {
        div.transition()
            .duration(200)
            .style("opacity", .9);
        div.html(text)
            .style("left", (d3.event.pageX) + "px")
            .style("top", (d3.event.pageY - 28) + "px");
    },

    getMin: function()
    {
        var self = this;
        var min,
            minDataDay = [],
            daysDate = self.getSubmissionsDays();
         $.each(daysDate,function(k,day){
            var minValArr = [];
            $.each(day,function(itemK,itemV){
                minValArr.push(itemV.points);
            });
            var minValArr = minValArr.slice().sort(function (a, b){
                return a-b;
            });
            point = minValArr[0];
            minDataDay.push({date:k,point:point});
        });
        min = $.extend([], minDataDay);
        return min;
    },

    getMax: function(){
        var self = this;
        var max,
            maxDataDay = [],
            daysDate = self.getSubmissionsDays();
         $.each(daysDate,function(k,day){
            var maxValArr = [];
            $.each(day,function(itemK,itemV){
                maxValArr.push(itemV.points);
            });
            var maxValArr = maxValArr.slice().sort(function (a, b){
                return b-a;
            });
            point = maxValArr[0];
            maxDataDay.push({date:k,point:point});
        });
        max = $.extend([], maxDataDay);
        return max;
    },

    getMean: function ()
    {
        var self = this;
        var mean,
            meanDataDay = [],
            daysDate = self.getSubmissionsDays();
         $.each(daysDate,function(k,day){
            var meanValArr = [];
            $.each(day,function(itemK,itemV){
                meanValArr.push(itemV.points);
            });
            point = (meanValArr.reduce(function(a, b){return a+b;}))/meanValArr.length;
            meanDataDay.push({date:k,point:point});
        });
        mean = $.extend([], meanDataDay);
        return mean;
    },

    getSubmissionsDays: function()
    {
        var self = this;
        var submissionsDays = {},
            usersOldValue = [];
        $.each(dateRange, function(dK,dV){
            var pushVal = [];
            $.each(self.submissions, function(k,v){
                var pushUserVal = [];
                $.each(v.items,function(itemK,itemV){
                    var itemVDate = (typeof itemV.date == 'number') ? itemV.date : Date.parse(itemV.date);
                    if(itemVDate >= Date.parse(dateRange[dK]) && itemVDate <= Date.parse(dateRange[dK+1]) && typeof dateRange[dK+1] != 'undefined'){
                        pushUserVal[v.id] = itemV;
                        usersOldValue[v.id] = itemV;
                    } else if(itemVDate >= Date.parse(dateRange[dK]) && typeof dateRange[dK+1] == 'undefined'){
                        pushUserVal[v.id] = itemV;
                        usersOldValue[v.id] = itemV;
                    }
                });
                if(pushUserVal.length == 0 && dK == 0){
                    pushUserVal[v.id] = {points:0,date:Date.parse(dateRange[dK]),id:v.id};
                    usersOldValue[v.id] = {points:0,date:Date.parse(dateRange[dK])};
                }else if(pushUserVal.length == 0 && dK > 0){
                    pushUserVal[v.id] = usersOldValue[v.id];
                    pushUserVal[v.id].id = v.id;
                }
                pushVal.push(pushUserVal[v.id]);
            });
            submissionsDays[dV] = pushVal;
        });
        return submissionsDays;
    },

    getQ1Q2Q3: function(del){
        var self = this;
        var Q1,
            Q1DataDay = [],
            point,
            daysDate = self.getSubmissionsDays();
        $.each(daysDate,function(k,day){
            var Q1ValArr = [];
            $.each(day,function(itemK,itemV){
                Q1ValArr.push(itemV.points);
            });
            var Q1ValArr = Q1ValArr.slice().sort(function (a, b){
                return a-b;
            });
            var key = Math.floor((Q1ValArr.length - 1)*del);
            point = Q1ValArr[key];
            Q1DataDay.push({date:k,point:point});
        });
        Q1 = ($.extend([], Q1DataDay));
        return Q1;
    },

    // Add median, min, max, Q1 and Q3 lines
    addQuartileMinMaxLine: function(){
        var self = this;
        var Q123MinMax = {
            avQ1: self.getQ1Q2Q3(1/4),
            avMedian: self.getQ1Q2Q3(2/4),
            avQ3: self.getQ1Q2Q3(3/4),
            avMin: self.getMin(),
            avMax: self.getMax(),
            avMean: self.getMean()
        };
        $.each(Q123MinMax,function(k,lineData){
            if($('.Q123MinMax #'+k).is(':checked')){
                $('path#' + k).remove();
                self.addQuartileMinMax(k,lineData);
            }else{
                $('path#' + k).remove();
            }
        });
    },

    addQuartileMinMax: function(id,data){
        var self = this;
        var endDate = self.getChartDate(),
            newDate = [];
        data.unshift({date: new Date(self.dFrom),point: 0});
        $.each(data,function(k,d){
            if(Date.parse(d.date) <= endDate){
                newDate.push(d);
            }
        });
        data = newDate;
        valueline = d3.svg.line()
            .x(function (d) {
                return self.x(Date.parse(d.date));
            })
            .y(function (d) {
                return self.y(d.point);
            });
        self.g.append("path")
            .attr("id",id)
            .attr("class","greenLine")
            .attr("d", valueline(data))
            .style("stroke", "#27b327")
            .on("mouseover", function (d) {
                var getDate = self.x.invert(d3.mouse(this)[0]),
                    getPoint;
                $.each(data,function(k,v){
                    if(typeof data[k+1] != 'undefined'){
                        if(Date.parse(data[k].date) <= Date.parse(getDate) && Date.parse(data[k+1].date) >= Date.parse(getDate)){
                            getPoint = v.point;
                        }
                    }else if(Date.parse(data[k].date) <= Date.parse(getDate)){
                        getPoint = v.point;
                    }
                });
                var date = new Date(getDate);
                var day = getDate.getDate();
                var monthIndex = getDate.getMonth();
                var time = self.formatAMPM(getDate);
                var text = id.slice(2);
                self.addTooltipProfessorGradebook(text + " " + self.roundToTwo(getPoint) + " pts earned on " + self.monthNames[monthIndex] + " " + day + " @ " + time);
            })
            .on("mouseout", function (d) {
                self.removeTooltipProfessorGradebook();
            });
    },

    roundToTwo: function(num) {
        //return +(Math.round(num + "e+2")  + "e-2");
        return +(Math.round(num));
    },

    formatAMPM: function(date) {
        var hours = date.getHours();
        var minutes = date.getMinutes();
        var ampm = hours >= 12 ? 'pm' : 'am';
        hours = hours % 12;
        hours = hours ? hours : 12; // the hour '0' should be '12'
        minutes = minutes < 10 ? '0' + minutes : minutes;
        var strTime = hours + ':' + minutes + ' ' + ampm;
        return strTime;
    },

    parseTimestamp: function(UNIX_timestamp)
    {
        var date = new Date(UNIX_timestamp);
        var day = date.getDate();
        var monthIndex = date.getMonth();
        var year = date.getFullYear();

        var time = this.formatAMPM(date);
        return this.monthNames[monthIndex] + " " + day + " @ " + time;
    },

    parseDayMonth: function(UNIX_timestamp) {
        var date = new Date(UNIX_timestamp);
        var day = date.getDate();
        var monthIndex = date.getMonth();

        return this.monthNames[monthIndex] + " " + day;
    },

    parseDates: function(data)
    {
        data.forEach(function (d) {
            var newDate = Date.parse(d.date);
        //if the user has been selected before the dates have already been parsed. Trying to parse them again will throw an error
            if (isNaN(newDate))
            {
                return;
            }
            d.date = Date.parse(d.date);

            d.points = +d.points;
        });
        return data;
    },

    chartDragPoints: function (days) {
        var self = this;
        self.addQuartileMinMaxLine();
        $.each(students, function(k,input){
            var id = parseInt(input.user_id);
            self.uncheckedBox(id);
            (days) ? self.checkedBox(id,true) : self.checkedBox(id,false);
        });
    },

    getCHartDragPoints: function() {
        var min_max = [],
            rangeSliderContainer = $('.range-slider');
        min_max[0] = parseInt(rangeSliderContainer.find('.ui-slider-handle').eq(0).find('.ui-slider-tip').text());
        min_max[1] = parseInt(rangeSliderContainer.find('.ui-slider-handle').eq(1).find('.ui-slider-tip').text());

        return min_max;
    }

}

var StudentClass = new Student();