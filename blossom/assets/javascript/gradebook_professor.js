div = d3.select("body").append("div")
        .attr("class", "tooltip")
        .style("opacity", 0);
        
var selectedStudents = [];
var margin = {top: 30, right: 20, bottom: 30, left: 50},
width = 600 - margin.left - margin.right,
        height = 270 - margin.top - margin.bottom;

// Parse the date / time
var parseDate = d3.time.format("%d-%b-%y").parse;

// Set the ranges
var x = d3.time.scale().range([0, width]);
var y = d3.scale.linear().range([height, 0]);


// Define the axes
var xAxis = d3.svg.axis().scale(x)
        .orient("bottom").ticks(6);

var yAxis = d3.svg.axis().scale(y)
        .orient("left").ticks(5);

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


//add a line for each student
// parseStudentData(submissions);

// Get the data
//console.log(chartData);
data = parseDates(chartData);
// Scale the range of the data
x.domain(d3.extent(data, function (d) {
    return d.date;
}));
y.domain([0, d3.max(data, function (d) {
        return d.points;
    })]);

// add the red line chart
addLine(data, "red", "red");
// add circles for each milestone
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

        .attr("r", 4)
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


// Add the X Axis
g.append("g")
        .attr("class", "x axis")
        .attr("transform", "translate(0," + height + ")")
        .call(xAxis);

// Add the Y Axis
g.append("g")
        .attr("class", "y axis")
        .call(yAxis);


var todayDate = new Date();
    g.selectAll("scatter-dots")
    	.data(today)
        .enter()
    	.append("svg:rect")
            .attr("x", function (d) {
                return x(Date.parse(d));
            })
            .attr("width", function (d) {
                return (185);
            })
            .attr("y", function (d) {
                return y(d);
            })
            .attr("height", function (d) {
                return 0.5;
            })
            .attr("stroke-width", 0.5)
            .attr("stroke", "lightgray")
            .on("mouseover", function (d) {
                addTooltip("Today: " + d + " points");
            })
            .on("mouseout", function (d) {
                removeTooltip();
            });

function addLine(data, strokeColor, id)
{
	console.log(data);
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
	var match = d.id === parseInt(id)
            return match;
        });
var text = "User Id: "+id;
        if(filteredData.length>0)
        {
        	text = filteredData[0].name;
        }
// Add the valueline path.
    g.append("path")
            .attr("id", "path"+id)
            .attr("class", "line")
            .attr("d", valueline(data))
            .style("stroke", strokeColor)
            .on("mouseover", function (d) {
            	addTooltip(text);
	        })
    	    .on("mouseout", function (d) {
        	    removeTooltip();
	        });
     
     console.log(data.length);           
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
        .attr("class",function(d)
        {
        	return "cir"+id;
        })
        .attr("fill","steelblue")
        .attr("cx", function (d) {
            return x(d.date);
        })
        .attr("cy", function (d) {
            return y(d.points);
        })
        .on("mouseover", function (d) {
            addTooltip(d.points);
        })
        .on("mouseout", function (d) {
            removeTooltip();
        });
        
        if(id!="red")
        {
        
        // var red = d3.select("#pathred");
//         red.attr("stroke","blue");
        	// var paragraphs = g.selectAll(".cir"+id);
//         	
//         	for(var k=0;k<=paragraphs[0].length-1;k++)
//         	{
//         		if(k===paragraphs[0].length-1)
//         		{
//         			paragraphs[0][k].attr("fill", "orange");
//         		}
//         	}
//         	paragraphs.each(function(d,i)
//         	{
//         		var lastChild = this.lastChild;
//         		if(this === lastChild)
//         		{
//         		    d3.select(this).attr("fill", "orange");
//         		}
// 
// //         		var item = paragraphs[0][i+1];
// //         		item.attr("fill", "orange");
//         		
//         	});
// 			var lastParag  = paragraphs[0].pop();
// 	        lastParag.attr("fill","orange");
        }

}

d3.select("#selection").on("change", multipleChange);


function multipleChange()
{
    var selection = this.selectedOptions;

    var masterArr = [];
    var newSelectedStudents = [];

    //add each selected student
    for (var k in selection)
    {
        var num = parseInt(selection[k].value);

        if (!isNaN(num))
        {
            newSelectedStudents.push(num);
        }
        else
        {
        	continue; 
        }


        var filteredData = submissions.filter(function (d) {
            return d.id === parseInt(selection[k].value);
        });

        masterArr = masterArr.concat(filteredData);
    }

    if (selectedStudents.length > 0)
    {
        for (var l in selectedStudents)
        {
            var index = newSelectedStudents.indexOf(selectedStudents[l]);
            if (index < 0)
            {
                //remove the old lines that are not currently selected
                d3.select("#path" + selectedStudents[l]).remove();
                var selector = ".cir"+selectedStudents[l].toString();
                d3.selectAll(selector).remove();
            }

        }
    }
    if (masterArr.length > 0)
    {
        for (var p in masterArr) {
            if (selectedStudents.length > 0)
            {
                var index = selectedStudents.indexOf(parseInt(masterArr[p].id));
                if (index ===-1)//don't add the path line if it's already on the page
                { 
            		var parsedData = parseDates(masterArr[p].items);
            		addLine(parsedData, "steelblue", masterArr[p].id);
                }
            }
            else
            {//first time running the chart selectedStudents will be null;
            	var parsedData = parseDates(masterArr[p].items);
            	addLine(parsedData, "steelblue", masterArr[p].id);
            }
        }

        selectedStudents = newSelectedStudents;//reset the array with all the students that were selected this time
    }
    else
    {
        alert("The selection is invalid or the selected user has no submissions");
    }

}

function parseDates(data)
{
    data.forEach(function (d) {

        var newDate = Date.parse(d.date);
        if(isNaN(newDate))//if the user has been selected before the dates have already been parsed. Trying to parse them again will throw an error
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