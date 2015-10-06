var div;
var encouragementAxisScale;
var maxPoints;
var bottom = 500;
var radius = 6;
$(document).ready(function () {
    div = d3.select("body").append("div")
            .attr("class", "tooltip")
            .style("opacity", 0);

    //scale experience
    scaleExperience(experienceSize);
    var interval = initScatterplot();
    
    //prepare milestone info. 
    var experiencePromise = prepareMilestoneInfo();//prepare experience info
    experiencePromise.then(function (resolve) {
        drawExperience(redLine);
        //draw scatterplot
        drawScatterplot(interval);
    }, function (reject) {
        console.log(reject);
    });

});


function scaleExperience(experienceSize) {
    var experienceView = d3.select("#experienceView");
    var experienceSVG = d3.select("#experienceSVG");
    var experienceHeight = 520;
    var experienceWidth = 600;

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

function prepareMilestoneInfo() {

    var bonus_penalties;
    //get milestoneClearance info
    var promise = $.get("getMilestoneClearanceInfo", {experienceInstanceId: instanceId});
    promise.then(function (data1, textStatus, jqXHR) {
        bonus_penalties = data1;
        drawMilestoneInfo(bonus_penalties);
    })
            .fail(function (data2) {
                console.log("An error has occurred. Please notify your instructor");
            });
    return promise;
}

function drawMilestoneInfo(bonus_penalties) {

    //create the milestone scale
    maxPoints = d3.max(bonus_penalties, function (d) {
        return +d.points;
    });
    encouragementAxisScale = d3.scale.linear()
            .domain([0, maxPoints])//maxPoints
            .rangeRound([bottom - 5, 10])
            .nice(2);

    //add a line per milestone
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
            .attr("stroke-width", 0.5)
            .attr("stroke", "lightgray")
            .on("mouseover", function (d) {
                var date = new Date(d.due_at.date);
                addTooltip(d.points + " pts due " + date.toDateString() + " at " + date.toLocaleTimeString())
            })
            .on("mouseout", function (d) {
                removeTooltip();
            });

    var monthNames = [
        "Jan", "Feb", "Mar",
        "Apr", "May", "Jun", "Jul",
        "Aug", "Sep", "Oct",
        "Nov", "Dec"
    ];
    //Add  milestone text
    var mileLabels = g.selectAll("scatter-dots")
            .data(bonus_penalties)
            .enter().append("svg:text")
            .attr('width', '200')
            .attr("x", function (d) {
                return (195);
            })
            .attr("y", function (d) {
                return encouragementAxisScale(d.points) + 4;//minus 2 so line and text are center aligned
            })
            .text(function (d) {
                return d.name;
            })
            .call(wrap, 380, 195)
            .on("mouseover", function (d) {

                var date = new Date(d.due_at.date);
                var day = date.getDate();
                var monthIndex = date.getMonth();
                var year = date.getFullYear();

                var time = formatAMPM(date);

                addTooltip(d.points + " pts due " + monthNames[monthIndex] + " " + day + " @ " + time);
            })
            .on("mouseout", function (d) {
                removeTooltip();
            });


    //bonus/penalties   
    g.selectAll("scatter-dots")
            .data(bonus_penalties)
            .enter().append("svg:text")
            .attr("x", function (d) {
                return (195);
            })
            .attr("y", function (d) {
                return encouragementAxisScale(d.points) + 15;//minus 6 so bonus/penalties are underneath milestone name
            })
            .text(function (d) {
                return roundToTwo(d.bonusPenalty);
            })
            .attr("class", "bonusPoints")
            .attr("transform", "translate(15,5)")
            .text(function (d) {
                return roundToTwo(d.bonusPenalty);
            })
            .style('fill', function (d) {
                if (d.bonusPenalty === 0) {
                    return 'black';
                } else if (d.bonusPenalty < 0) {
                    return 'red';
                } else {
                    return 'green';
                }
            })
            .on("mouseover", function (d) {
                var text = getBonusPenaltyTooltipText(d);
                addTooltip(text);
            })
            .on("mouseout", function (d) {
                removeTooltip();
            });


    // add bonus
    g.selectAll("scatter-dots")
            .data(bonus_penalties)
            .enter().append("svg:text")
            .attr("x", function (d) {
                return (235);
            })
            .attr("y", function (d) {
                return encouragementAxisScale(d.points) + 15;//minus 6 so bonus/penalties are underneath milestone name
            })
            .attr('font-family', 'FontAwesome')
            .attr('font-size', '11px')
            .attr("fill", "gray")
            .attr("transform", "translate(15,5)")
            .text(function (d) {
                if (d.cleared) {
                    return '\uf023';
                }
                else
                {
                    if (d.bonusPenalty < 0 || d.bonusPenalty < maxBonus)
                    {
                        return "\uf017";
                    }
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
                var text = getBonusPenaltyTooltipText(d);
                addTooltip(text);
            })
            .on("mouseout", function (d) {
                removeTooltip();
            });
}

function pulseRedLine(redLineElement, interval, normalRedLineValue)
{
    redLineElement.transition()
        .duration(interval/2)
        .ease("quad-in-out")
        .attr('height', 4)
        .attr('y',normalRedLineValue-1)
        .attr('opacity',1)
        .each("end", function () {
            redLineElement
                .transition()
                .duration(interval/2)
                .ease("quad-in-out")
                .attr('height', 2)
                .attr('y',normalRedLineValue)
                .attr('opacity',0.5);
        });
}




function drawExperience(redLine)
{
    var datelong = new Date();
    var date = (datelong.getMonth() + 1) + '/' + datelong.getDate() + '/' + datelong.getFullYear();
    var scale = d3.scale.linear()
            .domain([0, maxPoints])
//            .range([0, bottom]);
            .rangeRound([10, bottom - 5]);

    var redLineY = encouragementAxisScale(redLine);

    var redLineDom = d3.select("#redLine");
    
    d3.select("#redLine")
        .attr('y', bottom)
        .style("fill", "red")
        .on("mouseover", function (d) {
            addTooltip("Ideal progress: " + redLine + " pts by today. Reach the next milestone before this line to earn a bonus. \n\
                If this line beats you, you will receive a penalty.")
        })
        .on("mouseout", function (d) {
            removeTooltip();
        })
        .transition()
        .delay(1000)
        .duration(1000)
        .attr('height', 2)
        .attr('stroke-width',1)
        .attr('stroke-linecap', 'round')
        .attr('y', redLineY);


//add an interval transition for the red line
//        pulseRedLine(redLineDom,4000);
var interval = 2000;
//    bounceCircle(false, circle, initx, interval);
    myVar = setInterval(function () {
        pulseRedLine(redLineDom,interval, redLineY);
    }, interval);


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
            .style('fill', "steelblue")
            .attr('height', encouragementAxisScale(Math.round(experienceXP)))
            .attr('y', bottom-encouragementAxisScale(Math.round(experienceXP)))
            .duration(1000)
            .ease('bounce');


    var experienceText = d3.select("#experienceView").append("text")
            .attr("fill", "steelblue")
            .on("mouseover", function (d) {
                addTooltip("Your total points");
            })
            .on("mouseout", function (d) {
                removeTooltip();
            })
            .transition()
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

function drawScatterplot(interval) {
    //get student scores
    var promise = $.get("getStudentsScores");
    promise.then(function (data1, textStatus, jqXHR) {

        //kill the bouncy ball interval
        clearInterval(interval);
        var initx = bottom - radius;
        studentScores = data1;
        var data = [];
        var counter = 0;
        for (var i = 0; i <= studentScores.length - 1; i++)
        {
            if(studentScores[i]!= experienceXP)//*sigh* SVG doesn't play nice with z-index. 
            //current user's score must be added LAST so the dot appears on top. 
            {
                data.push([0.5, studentScores[i]]);
            }
            else
            {
                counter++;
            }
        }

        for(var i=0;i<=counter;i++)
        {
            data.push([0.5, experienceXP]);
        }
         
        var margin = {top: 10, bottom: 10, }, height = 500 - margin.top - margin.bottom;

        var x = d3.scale.linear()
                .domain([0, 1])
                .range([0, 25]);

        var y = d3.scale.linear()
                .domain([0, maxXP])
                .range([height, 0]);

        var g = d3.select("#circles");
        var circ = g.selectAll("circle")
                .data(data);

        // ENTER
        // Create new elements as needed.
        circ.enter().append("circle");
        
        //update new and old elements
        circ.on("mouseover", function (d) {
                    if((d[1] === experienceXP))
                    {
                        addTooltip("You: " + experienceXP + " points");
                    }
                    else
                    {
                        addTooltip("Your peer: " + d[1] + " points")
                    }
                })
                .on("mouseout", function (d) {
                    removeTooltip();
                })
                .attr("cx", function (d) {
                    return x(d[0]);
                })
                .attr("cy", function (d) {
                    return (initx);
                })
                .attr("r", radius)
                .attr("transform", "translate(140,0)")
                .attr("class", function (d) {
                    if (d[1] === experienceXP)//this is the current student
                    {
                        return "gradedot";
                    }
                    else
                    {
                        return "dot";
                    }
                })
                .transition()
                .duration(800)
                .ease("quad-in-out")
                .attr("cy", function (d) {
                    return (encouragementAxisScale(d[1]));
                });
                
        //remove old elements
        circ.exit().remove();

    })
    .fail(function (data2) {
    });
}

function initScatterplot()
{
    var x = d3.scale.linear()
            .domain([0, 1])
            .range([0, 25]);
    var initx = bottom - radius;

    var data = [0.5, initx];


    var g = d3.select("#experienceView").append("g")
            .attr("id", "circles");
    var circle = g.selectAll("circle")
            .data(data)
            .enter()
            .append("circle")
            .attr("cx", x(data[0]))
            .attr("cy", x(data[1]))
            .attr("r", radius)
            .attr("id", "dot")
            .attr("transform", "translate(140,0)");

    //make the ball bounce until data arrives
    var interval = 2000;
    bounceCircle(false, circle, initx, interval);
    myVar = setInterval(function () {
        bounceCircle(false, circle, initx, interval);
    }, interval);

    return myVar;
}


function bounceCircle(stopTransition, circleElement, initx, interval)
{
    var stop = 0;
    var cy = 1;
    if (stopTransition)
    {
        stop = bottom - radius;

    }
    else
    {
        stop = bottom - radius;
        cy = bottom - radius - 50;
    }
    circleElement.transition()
            .duration(800)
            .attr("cy", cy)
            .each("end", function () {
                circleElement
                        .transition()
                        .duration(800)
                        .ease("bounce")
                        .attr({
                            cy: stop
                        });
            });
}

function getBonusPenaltyTooltipText(d)
{
    var text = "";
    if (d.cleared === 0)
    {
        if (d.bonusPenalty < 0)
        {
            text = "Limit your penalties to " + roundToTwo(d.bonusPenalty) + " points by reaching this milestone now";
        }
        else if (d.bonusPenalty > 0)
        {
            text = "Bonus you could earn if you reach this milestone now";
        }
    }
    if (d.cleared === 1)
    {
        if (d.bonusPenalty < 0)
        {
            text = "Points lost for falling behind in this milestone";
        }
        else if (d.bonusPenalty > 0)
        {
            text = "Bonus earned for this milestone ";
        }
    }
    return text;
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

//from http://stackoverflow.com/questions/8888491/how-do-you-display-javascript-datetime-in-12-hour-am-pm-format
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

function wrap(text, width, x) {
    text.each(function () {
        var text = d3.select(this);
        var words = text.text().split(/\s+/).reverse(),
                word,
                line = [],
                lineNumber = 0,
                lineHeight = 1.1; // ems
        var y = (text.attr("y"));
        var dy = 1,
                tspan = text.text(null).append("tspan").attr("x", x).attr("y", y);
        while (word = words.pop()) {
            line.push(word);
            tspan.text(line.join(" "));
            if (tspan.node().getComputedTextLength() > width) {
                line.pop();
                tspan.text(line.join(" "));
                line = [word];
                tspan = text.append("tspan").attr("x", x).attr("y", y).attr("dy", (++lineNumber - 1) * lineHeight + dy + "em").text(word);
            }
        }
    });
}