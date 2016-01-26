var submissions = [];
var bottomExperienceScores=[];

//GET DATA FOR THE TOP CHART
var promise = $.get("gradebook/getAllStudentSubmissions");
promise.then(function (data1, textStatus, jqXHR) {
        //console.log(data1);
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
        addTooltip(todayDate.toDateString());
    })
    .on("mouseout", function (d) {
        removeTooltip();
    });


function addLine(data, strokeColor, id)
{
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
                addTooltip(text);
            }
        })
        .on("mouseout", function (d) {
            if (id != "red")
            {
                removeTooltip();
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
                addTooltip(d.points + " pts earned on " + monthNames[monthIndex] + " " + day + " @ " + time);
            }
        })
        .on("mouseout", function (d) {
            if (id != "red")
            {
                removeTooltip();
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
    });
    if (masterArr.length > 0)
    {
        var parsedData = parseDates(masterArr[0].items);
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

function addTooltip(text)
{
    div.transition()
        .duration(200)
        .style("opacity", .9);
    div.html(text)
        .style("left", (d3.event.pageX) + "px")
        .style("top", (d3.event.pageY - 28) + "px");
}

function removeTooltip()
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
            addTooltip(d.points + " points due " + parseTimestamp(d.date));
        })
        .on("mouseout", function (d) {
            removeTooltip();
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

function buildTable(data)
{
    d3.select("#summaryTable").style("display","block");
    var tr = d3.select("#tableBody")
        .selectAll('tr').remove();//Because of the complexity of updating a table, we'll remove the nodes and add them again
    tr = d3.select("#tableBody")
        .selectAll('tr').data(data);

    tr.enter()
        .append('tr')
        .append('td')
        .attr('class', 'title')
        .html(function(m, i) { return i+1; });

    tr.append('td')
        .attr('class', 'tdName')
        .append("a")
        .attr("href", function(m){ return m.profile_url;})
        .attr("target", "_blank")
        .html(function(m) { return m.name; });

    tr.append('td')
        .attr('class', 'title')
        .html(function(m) { return d3.round(m.score, 2); });

    tr.append('td')
        .attr('class', 'title')
        .html(function(m) { return d3.round(m.bonuses,2); });

    tr.append('td')
        .attr('class', 'title')
        .html(function(m) { return d3.round(m.penalties,2); });

    tr.append('td')
        .attr('class', 'title')
        .html(function(m) { return d3.round(m.totalBP,2); });

    tr.append('td')
        .attr('class', 'title')
        .html(function(m) { return d3.round(m.total,2); });

    tr.append('td')
        .attr('class', 'title')
        .html(function(m) { return m.grade; });

    tr.append('td')
        .append("input")
        .attr("type","button")
        .attr("value","Details")
        .attr("class","btn btn-info btn-lg btn-sm")
        .attr("data-toggle","modal")
        .attr("data-target","#modalStudentGradebook")
        .on('click', function(d){
            showStudentDetails(d);
        });
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
        if((i!=0)&&(i%10==0))
        {//we'll send requests every 10 students.

            var secondPromise = $.get("gradebook/getSetOfUsersMilestoneInfo",{experienceInstanceId:experienceInstanceId, userIds:(idsArr)});
            secondPromise.then(function (data, textStatus, jqXHR) {
                    d3.select(".bottomSpinnerDiv").style("display","none");
                    console.log(data);
                    bottomExperienceScores = bottomExperienceScores.concat(data);//append the new data to the old
                    buildTable(bottomExperienceScores);
                })
                .fail(function (data2) {
                    console.log("Unable to retrieve bottom chart data.");
                });

            var idsArr =[];//initialize the array again.
        }

    }

    //send a last request with the remaining IDS
    if(idsArr.length>0)
    {

        var secondPromise = $.get("gradebook/getSetOfUsersMilestoneInfo",{experienceInstanceId:experienceInstanceId, userIds:(idsArr)});
        secondPromise.then(function (data, textStatus, jqXHR) {
                d3.select(".bottomSpinnerDiv").style("display","none");
                console.log(data);
                bottomExperienceScores = bottomExperienceScores.concat(data);//append the new data to the old
                buildTable(bottomExperienceScores);
            })
            .fail(function (data2) {
                console.log("Unable to retrieve bottom chart data.");
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
    var currGrade = d3.select("#tdCurrentGrade");
    var span = currGrade.insert("span", "#aGradeHover");
    span.html(studentSummaryData.grade);

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