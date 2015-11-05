div = d3.select("body").append("div")
        .attr("class", "tooltip")
        .style("opacity", 0);

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

// Get the data
data = parseDates(chartData);



//add the red line chart
addLine(data, "red");
//     add circles for each milestone
g.selectAll("dot")
        .data(data)
        .enter().append("circle")
        .filter(function (d, i) {
            if (i === 0) {
                return d;
            }
            if (data[i].points != data[i - 1].points)
            {
                return d;
            }
        })
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


//add a line for each student
// parseStudentData(submissions);

function parseStudentData(masterArray)
{
    for (var j in masterArray) {
    	var parsedData = parseDates(masterArray[j].items);
        addLine(parsedData, "steelblue");
    }
}
function addLine(data, strokeColor)
{
	// Scale the range of the data
	x.domain(d3.extent(data, function (d) {
    	return d.date;
	}));
	y.domain([0, d3.max(data, function (d) {
        return d.points;
    })]);
    
    // Define the line
    var valueline = d3.svg.line()
            .x(function (d) {
            var dddate = new Date(d.date);
            console.log(d.date);
            if(dddate==="Invalid Date")
            {
            	alert("date was invalid");
            }
            	console.log(dddate);
                return x(d.date);
            })
            .y(function (d) {
            console.log(d.points);
                return y(d.points);
            });
// Add the valueline path.
    g.append("path")
            .attr("class", "line")
            .attr("d", valueline(data))
            .style("stroke", strokeColor);

}

function parseDates(data)
{
	data.forEach(function (d) {
	
	var newDate = Date.parse(d.date);
	// if(isNaN(newDate))
//     {
//     	console.log(d);
//     }
    d.date = Date.parse(d.date);
    
    d.points = +d.points;
});
	return data;
}
function addLineToChart(data)
{
    var margin = {top: 30, right: 20, bottom: 30, left: 50},
    width = 600 - margin.left - margin.right,
            height = 270 - margin.top - margin.bottom;

    var parseDate = d3.time.format("%Y-%m-%dT%H:%M:%SZ").parse;

// Set the ranges
    var x = d3.time.scale().range([0, width]);
    var y = d3.scale.linear().range([height, 0]);

    data.forEach(function (d) {
        d.date = Date.parse(d.date);
        d.points = +d.points;
    });
    // Define the line
    var valueline = d3.svg.line()
            .x(function (d) {
                return x(d.date);
            })
            .y(function (d) {
                return y(d.points);
            })
            .on("mouseover", function (d) {
                addTooltip(d.points + " on " + d.date);
            })
            .on("mouseout", function (d) {
                removeTooltip();
            });

    // Scale the range of the data
    x.domain(d3.extent(data, function (d) {
        return d.date;
    }));
    y.domain([0, d3.max(data, function (d) {
            return d.points;
        })]);

    // Add the valueline path.
    d3.select("#gChart").append("path")
            .attr("class", "line")
            .attr("d", valueline(data))
            .style("stroke", "steelblue")
            .style("opacity", 0.4);

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