var div;
$(document).ready(function () {
    div = d3.select("body").append("div")
        .attr("class", "tooltip")
        .style("opacity", 0);

    scaleExperience(experienceSize);
    drawAxis();
    drawExperience(redLine);
    drawScatterplot(studentScores);
});

var bottom = 500;

function scaleExperience(experienceSize) {
    var experienceView = d3.select("#experienceView");
    var experienceSVG = d3.select("#experienceSVG");
    var experienceHeight = 520;
    var experienceWidth = 300;

    if (experienceSize === "small") {
        experienceWidth = experienceWidth / 2;
        experienceHeight = experienceHeight / 2;
        experienceView.attr('transform', "scale(.5)");
    } else if (experienceSize === "large") {
        experienceWidth = experienceWidth * 1.5;
        experienceHeight = experienceHeight * 1.5;
        experienceView.attr('transform', "scale(1.5)");
    }
    experienceSVG.attr('width', experienceWidth)
            .attr('height', experienceHeight);
}

function drawAxis() {
    var bonus_penalties = milestoneClearance;

    var maxPoints = d3.max(bonus_penalties, function (d) {
        return +d.points;
    });

//create the milestone scale
    var encouragementAxisScale = d3.scale.linear()
            .domain([0, maxPoints])//maxPoints
            .rangeRound([bottom - 5, 10])
            .nice(2);
//create the milestone axis
    var encouragementAxis = d3.svg.axis()
            .scale(encouragementAxisScale)
            .orient('left')
            .ticks(bonus_penalties.length)
            .tickFormat(function (d) {
                return;
            })
            .tickSize(0, 0);


    //add a line per each milestone
    var g = d3.select("#experienceAxis").append("svg:g");
    g.selectAll("scatter-dots")
            .data(bonus_penalties)
            .enter().append("svg:rect")
            .attr("x", function (d) {
                return (0);
            })
            .attr("width", function (d) {
                return (185);
            })
            .attr("y", function (d) {
                return encouragementAxisScale(d.points);
            })
            .attr("height", function (d) {
                return 0.5;
            })
            .attr("stroke-width", 1)
            .attr("stroke", "black")
            .on("mouseover", function (d) {
                var date = new Date(d.due_at.date);
                addTooltip(d.points + " pts due " + date.toDateString() + " at " + date.toLocaleTimeString())
            })
            .on("mouseout", function (d) {
                removeTooltip();
            });

    //Add text
    g.selectAll("scatter-dots")
            .data(bonus_penalties)
            .enter().append("svg:text")
            .attr("x", function (d) {
                return (185);
            })
            .attr("y", function (d) {
                return encouragementAxisScale(d.points - 2);//minus 2 so line and text are center aligned
            })
            .text(function (d) {
                return d.name;
            })
            .on("mouseover", function (d) {
                var date = new Date(d.due_at.date);
                addTooltip(d.points + " pts due " + date.toDateString() + " at " + date.toLocaleTimeString())
            })
            .on("mouseout", function (d) {
                removeTooltip();
            });


    // add bonus
    g.selectAll("scatter-dots")
            .data(bonus_penalties)
            .enter().append("svg:text")
            .attr("x", function (d) {
                return (183);
            })
            .attr("y", function (d) {
                return encouragementAxisScale(d.points - 23);//minus 6 so bonus/penalties are underneath milestone name
            })
            .text(function (d) {
                return roundToTwo(d.bonusPenalty);
            })
            .on("mouseover", function (d) {
//                var penalti
                addTooltip()
            })
            .on("mouseout", function (d) {
                removeTooltip();
            })
            .attr('font-family', 'FontAwesome')
            .attr("fill", "gray")
            .attr("transform", "translate(15,5)")
            .text(function (d) {
                if (d.cleared) {
                    return '\uf023';
                }
                else
                {
                    return "\uf13e";
                }
            }).style('fill', function (d) {
        if (d.bonusPenalty === 0) {
            return 'black';
        } else if (d.bonusPenalty < 0) {
            return 'red';
        } else {
            return 'green';
        }
    })
            .on("mouseover", function (d) {
                var text = (d.cleared === 1) ? "Locked in points" : "Pts not locked in";
                addTooltip(text);
            })
            .on("mouseout", function (d) {
                removeTooltip();
            });

    g.selectAll("scatter-dots")
            .data(bonus_penalties)
            .enter().append("svg:text")
            .attr("x", function (d) {
                return (195);
            })
            .attr("y", function (d) {
                return encouragementAxisScale(d.points - 23);//minus 6 so bonus/penalties are underneath milestone name
            })
            .text(function (d) {
                return roundToTwo(d.bonusPenalty);
            })
            .attr("transform", "translate(15,5)")
            .text(function (d) {
                return roundToTwo(d.bonusPenalty);
            });

//apply the axis to a dom object
    var encourage = d3.select("#experienceAxis")
            .attr('class', 'axis')
            .call(encouragementAxis);

}

function drawExperience(redLine) {
    var datelong = new Date();
    var date = (datelong.getMonth() + 1) + '/' + datelong.getDate() + '/' + datelong.getFullYear();
    var redLineY = bottom - redLine;
    var scale = d3.scale.linear()
            .domain([0, maxXP])
            .range([0, bottom]);

    var redLineDom = d3.select("#redLine")
            .attr('y', bottom - 10)
            .style("fill", "red")
            .on("mouseover", function (d) {
                addTooltip("Ideal progress: " + redLine + " pts by today. Getting ahead or behind of the line will result in bonus\n\
                    or penalties respectively")
            })
            .on("mouseout", function (d) {
                removeTooltip();
            })
            .transition()
            .delay(4000)
            .duration(1000)
            .attr('height', 2, 5)
            .attr('y', redLineY);

    var therm = d3.select("#experienceRect")
            .attr('y', bottom)
            .style("fill", "white")
            .on("mouseover", function (d) {
                addTooltip("Your points: " + roundToTwo(experienceXP))
            })
            .on("mouseout", function (d) {
                removeTooltip();
            })
            .transition()
            .delay(2000)
            .style('fill', "steelblue")
            .attr('height', Math.round(scale(experienceXP)))
            .attr('y', bottom - Math.round(scale(experienceXP)))
            .duration(1000)
            .ease('bounce');


    var experienceText = d3.select("#experienceView").append("text")
            .attr("fill", "steelblue")
//            .on("mouseover", function (d) {
//                addTooltip("This is your total number of points, including bonus and penalties")
//            })
//            .on("mouseout", function (d) {
//                removeTooltip();
//            })
            .transition()
            .delay(3000)
            .attr("fill", "#FD994C")
            .style("text-anchor", "middle")
            .attr('font-size', "20px")
            .attr('font-weight', "bold")
            .attr('x', 75)
            .attr('y', 450)
            .text((experienceXP));

    function dayDifference(first, second) {
        return (second - first) / (1000 * 60 * 60 * 24);
    }

}

function drawScatterplot(studentScores) {
    var data = [];
    for (var i = 0; i <= studentScores.length - 1; i++)
    {
        data.push([0.5, studentScores[i]]);
    }

    var margin = {top: 10, bottom: 10, }, height = 500 - margin.top - margin.bottom;

    var x = d3.scale.linear()
            .domain([0, 1])
            .range([0, 25]);

    var y = d3.scale.linear()
            .domain([0, maxXP])
            .range([height, 0]);



    var g = d3.select("#experienceView").append("svg:g");


    g.selectAll("scatter-dots")
            .data(data)
            .enter().append("svg:circle")
            .attr("cx", function (d) {
                return x(d[0]);
            })
            .attr("cy", function (d) {
                return y(d[1]);
            })
            .attr("r", 6)
            .attr("class", "dot")
            .attr("transform", "translate(140,15)")
            .on("mouseover", function (d) {
                addTooltip("Your peers: " + d[1] + " points")
            })
            .on("mouseout", function (d) {
                removeTooltip();
            });

    g.append("svg:circle")
            .attr("cx", x(0.5))
            .attr("cy", y(experienceXP))
            .attr("r", 6)
            .attr("id", "gradedot")
            .style("stroke", "black")
            .attr("transform", "translate(140,15)")
            .on("mouseover", function (d) {
                addTooltip("You: " + experienceXP + " points")
            })
            .on("mouseout", function (d) {
                removeTooltip();
            });

}

function roundToTwo(num) {
    return +(Math.round(num + "e+2") + "e-2");
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