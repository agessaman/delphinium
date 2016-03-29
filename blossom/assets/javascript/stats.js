var statsObj;
//tooltip
// statsDiv = d3.select(".statsTooltip");
var statsDiv = d3.select("body").append("div")
    .attr("class", "statsTooltip")
    .style("opacity", 0);
var wrapper = d3.select("#statsWrapper").node();

var statsHeight = 200;
var statsWidth = 250;
var trueHeight = 200;
var trueWidth = 250;
var halfWidth = trueWidth / 2;
var border = 1;//thickness of border, to be accounted for when filling boxes
var multiplier = 0;
var staminaX = 0;
scaleStats();
//getData
$.get("stats/getStatsData", {experienceInstanceId: experienceInstanceId}, function (data, status, xhr) {
    //enable stamina clicks
    d3.select(".spinnerStats").style("display","none");
    d3.selectAll(".switch-li").classed("disabled",false);
    statsObj = JSON.parse(data);

//SET VARIABLES
    var positivePace = statsObj.milestoneSummary.bonuses;//20;//
//console.log("locked bonuses: "+positivePace);
    var negativePace = -statsObj.milestoneSummary.penalties;//-milestoneSummary.penalties;//15;// (must be positive value)
//console.log("locked penalties: "+negativePace);
    var maxPace = statsObj.health.maxBonuses;
    var minPace = statsObj.health.maxPenalties;
    var positivePaceWidth = positivePace / maxPace * halfWidth;
    var negativePaceWidth = negativePace / minPace * halfWidth;
    var positivePaceX = halfWidth + (positivePaceWidth / 2);
    var negativePaceX = halfWidth - (negativePaceWidth / 2);

//also draw potential bonus and penalties
    var positivePotential = statsObj.potential.bonus;//15;//
//console.log("potential bonuses: "+positivePotential);
    var negativePotential = -statsObj.potential.penalties;//15;// (must be positive value)
//console.log("potential penalties: "+negativePotential);
    var posPotentialWidth = positivePotential / maxPace * halfWidth;
    var negPotentialWidth = negativePotential / minPace * halfWidth;
    var posPotentialX = halfWidth + positivePaceWidth + (posPotentialWidth / 2);
    var negPotentialX = halfWidth - negativePaceWidth - (negPotentialWidth / 2);


    var health = statsObj.milestoneSummary.bonuses + statsObj.milestoneSummary.penalties;
    var maxHealth = statsObj.health.maxBonuses;
    var minHealth = statsObj.health.maxPenalties;
    var healthWidth, healthX;
    if (health > 0) {
        healthWidth = health / maxHealth * halfWidth;
        healthX = halfWidth + (healthWidth / 2);
    } else {
        healthWidth = health / minHealth * halfWidth * -1;
        healthX = halfWidth - (healthWidth / 2);
    }


    var gap = statsObj.milestoneSummary.total - statsObj.redLine;
    var maxGap = statsObj.gap.maxGap;
    var minGap = statsObj.gap.minGap;
    var gapWidth, gapX;
    if (gap > 0) {
        gapWidth = gap / maxGap * halfWidth;
        gapX = halfWidth + (gapWidth / 2);
    } else {
        gapWidth = gap / minGap * halfWidth * -1;
        gapX = halfWidth - (gapWidth / 2);
    }

    //STAMINA VARS
    var lastTen = false;
    multiplier = statsObj.stamina.total > 100 ? statsObj.stamina.total : 100;//if for some weird reason a student were to get over a 100% in an assignment, their stamina could potentially be above 100,
    // so we must adjust the multiplier
    var staminaWidth = statsObj.stamina.total / multiplier * trueWidth;
    staminaX = 6;
    if (statsObj.stamina.total > 0) {
        staminaX = (staminaWidth / 2) + 10;
    }

    //fill in data
    fillInterface();

    //add remaining tooltips
    d3.select("#paceExp").on("mouseover", function (event) {
            var text = getExplanation('pace');
            addTooltipStats(text);
        })
        .on("mouseout", function () {
            removeTooltipStats();
        });

    d3.select("#gapExp").on("mouseover", function (event) {
            var text = getExplanation('gap');
            addTooltipStats(text);
        })
        .on("mouseout", function () {
            removeTooltipStats();
        });

    d3.select("#healthExp").on("mouseover", function (event) {
            var text = getExplanation('health');
            addTooltipStats(text);
        })
        .on("mouseout", function () {
            removeTooltipStats();
        });

    d3.select("#staminaExp").on("mouseover", function (event) {
            var text = getExplanation('stamina');
            addTooltipStats(text);
        })
        .on("mouseout", function () {
            removeTooltipStats();
        });

    function fillInterface() {
        var view = d3.select("#statsView");
        //FILL EACH BAR
        paceInterface();
        gapInterface();
        healthInterface();
        staminaInterface();

        function paceInterface() {
            if(!statsAnimate)
            {
                delayValue = 0;
                duration =0;
            }
            else
            {
                delayValue = count * 1000 + 1000;
                duration = 1000;
            }
            var count = 1;
            var text ='';
            //BONUS
            if (positivePace > 0) {
                if((positivePaceWidth)<20) {
                    text = "<i class='fa fa-lock'></i> Locked in bonuses: "+roundToOne(positivePace);
                }
                else
                {
                    text = "Locked in bonuses: "+roundToOne(positivePace);
                }
                drawPositive(text,count, positivePaceWidth);
                if((positivePaceWidth)>20)
                {
                    drawNumber(count, positivePaceX, roundToOne(positivePace), "paceVars", 1,text);

                //apend the lock icon (must be text. inside of svg we can't add span or i)
                view.append("svg:text")
                    .attr("x", function (d) {
                        return positivePaceX;
                    })
                    .attr("y", count * 50 + 30)
                    .attr('font-family', 'FontAwesome')
                    .attr('font-size', '8px')
                    .attr("cursor", "pointer")
                    .on("mouseover", function (d) {
                        addTooltipStats("Locked in bonus: " + roundToOne(positivePace));
                    })
                    .on("mouseout", function (d) {
                        removeTooltipStats();
                    })
                    .text(function (d) {
                        return '\uf023';
                    })
                    .attr("fill", function (d) {
                        var x = positivePace > 0 ? "white" : "gray";
                        return x;
                    })
                    .attr("display",'none')
                    .transition()
                    .delay(delayValue)
                    .duration(duration)
                    .attr("display",'block')
                    .ease('bounce');
                }
            }

            //PENALTIES
            if (negativePace > 0) {
                if((negativePaceWidth)<20) {
                    text = "<i class='fa fa-lock'></i> Locked in penalties: "+roundToOne(-negativePace);
                }
                else {
                    text = "Locked in penalties: "+roundToOne(-negativePace);
                }
                drawNegative(text, count, negativePaceWidth);
                if((negativePaceWidth)>20)
                {
                    drawNumber(count, negativePaceX, roundToOne(-negativePace), "paceVars",1, text);

                view.append("svg:text")
                    .attr("x", function (d) {
                        return negativePaceX;
                    })
                    .attr("y", count * 50 + 30)
                    .attr('font-family', 'FontAwesome')
                    .attr('font-size', '8px')
                    .attr("cursor", "pointer")
                    .attr("fill", function (d) {
                        var x = negativePace > 0 ? "white" : "gray";
                        return x;
                    })
                    .attr("display",'none')
                    .on("mouseover", function (d) {
                        addTooltipStats("Locked in penalties: " + roundToOne(-negativePace));
                    })
                    .on("mouseout", function (d) {
                        removeTooltipStats();
                    })
                    .text(function (d) {
                        return '\uf023';
                    })
                    .transition()
                    .delay(delayValue)
                    .duration(duration)
                    .attr("display",'block')
                    .ease('bounce');
                }
            }


            //POTENTIAL BONUSES
            if (positivePotential > 0) {
                if((posPotentialWidth)<20) {
                    text = "<i class='fa fa-clock-o'></i> Potential bonuses: "+roundToOne(positivePotential);
                }
                else
                {
                    text = "Potential bonuses: "+roundToOne(positivePotential);
                }
                drawPositive(text, count, posPotentialWidth, positivePaceWidth, 0.4);
                if((posPotentialWidth)>20)
                {
                    drawNumber(count, posPotentialX, roundToOne(positivePotential), "paceVars", 1, text);

                view.append("svg:text")
                    .attr("x", function (d) {
                        return posPotentialX;
                    })
                    .attr("y", count * 50 + 30)
                    .attr('font-family', 'FontAwesome')
                    .attr('font-size', '8px')
                    .attr("cursor", "pointer")
                    .attr("fill", function (d) {
                        var x = positivePotential > 0 ? "white" : "gray";
                        return x;
                    })
                    .on("mouseover", function (d) {
                        addTooltipStats("Potential bonus: " + roundToOne(positivePotential));
                    })
                    .on("mouseout", function (d) {
                        removeTooltipStats();
                    })
                    .text(function (d) {
                        return '\uf017';
                    })
                    .attr("display",'none')
                    .transition()
                    .delay(delayValue)
                    .duration(duration)
                    .attr("display",'block')
                    .ease('bounce');
                }
            }

            //POTENTIAL PENALTIES
            if (negativePotential > 0) {
                if((negPotentialWidth)<20) {
                    text = "<i class='fa fa-clock-o'></i> Potential penalties: "+roundToOne(-negativePotential);
                }
                else
                {
                    text = "Potential penalties: "+roundToOne(-negativePotential);
                }
                drawNegative(text,count, negPotentialWidth, negativePaceWidth, 0.4);
                if((negPotentialWidth)>20)
                {
                    drawNumber(count, negPotentialX, roundToOne(-negativePotential), "paceVars", 1, text);

                view.append("svg:text")
                    .attr("x", function (d) {
                        return negPotentialX;
                    })
                    .attr("y", count * 50 + 30)
                    .attr('font-family', 'FontAwesome')
                    .attr('font-size', '8px')
                    .attr("cursor", "pointer")
                    .attr("fill", function (d) {
                        var x = negativePotential > 0 ? "white" : "gray";
                        return x;
                    })
                    .on("mouseover", function (d) {
                        addTooltipStats("Potential penalties: " + roundToOne(-negativePotential));
                    })
                    .on("mouseout", function (d) {
                        removeTooltipStats();
                    })
                    .text(function (d) {
                        return '\uf017';
                    })
                    .attr("display",'none')
                    .transition()
                    .delay(delayValue)
                    .duration(duration)
                    .attr("display",'block')
                    .ease('bounce');
                }

            }

            if (positivePace == 0 && negativePace == 0 && positivePotential == 0 && negativePotential == 0) {
                drawNumber(count, 0, 0, "paceVars");
            }
            drawCenter(count);
        }

        function gapInterface() {
            var count = 0;
            var word = gap>0?"ahead of":"behind";
            var encouragement = gap>0?"Good job!":"You need to catch up with the red line to avoid penalty points";
            var text = "You are "+roundToOne(gap)+" points "+word+ " the red line. "+encouragement;
            y = count * 50 + 10;
            //drawIcon('\uf05a', 0, y, text, "14px", '#444444');

            if (gap > 0)
                drawPositive(text,count, gapWidth);
            else
                drawNegative(text,count, gapWidth);
            drawCenter(count);
            drawNumber(count, gapX, roundToOne(gap), "gapVars", 1, text);
        }

        function healthInterface() {
            var count = 2;
            var text = "Your bonus/penalty balance is "+roundToOne(health)+" points";
            y = count * 50 + 10;
            //drawIcon('\uf05a', 0, y, text, "14px", '#444444');
            if((healthWidth)<20) {
                text = "Health: "+ roundToOne(health)+"<br/>"+text;
            }
            if (health > 0)
                drawPositive(text,count, healthWidth);
            else
                drawNegative(text,count, healthWidth);
            drawCenter(count);
            drawNumber(count, healthX, roundToOne(health), "healthVars", 1, text);

        }

        function staminaInterface() {
            var count = 3;
            //draw stamina bar
            variableStamina(statsObj.stamina.total, staminaWidth, count, true);
            //add a checkbox so students can select if they want to see their averages for the last 10 assignments or all their assignments
            var options = ['All', 'Last 10'];
        }


        function drawCenter(count) {
            view.append('line')
                .attr({
                    y1:count * 50 + 20,
                    y2:count * 50 + 45,
                    x1:halfWidth,
                    x2:halfWidth,
                    stroke:"#444444",
                    fill:'none',
                    'stroke-width':1
                });
        }

        function drawText(count, text) {
            view.append('text')
                .text(text)
                .attr({
                    x:15,
                    y:count * 50 + 10,
                    fill:'#444444',
                    transform:"translate(0,7)"
                });
        }

        function drawIcon(iconCode, x, y, mouseInText, fontSize, color) {
            view.append("svg:text")
                .attr({
                    y:y,
                    x:x,
                    'font-family':'FontAwesome',
                    'font-size':fontSize,
                    fill:color,
                    cursor:pointer
                })
                .on("mouseenter", function (d) {
                    addTooltipStats(mouseInText);
                })
                .on("mouseleave", function (d) {
                    removeTooltipStats();
                })
                .text(function (d) {
                    return iconCode;
                })
                .attr('transform', "translate(0,7)");
        }

        function drawPositive(text, count, value, startingX, opacity) {
            if (statsObj.nonstudent) {
                return;
            }
            startingX = startingX || 0;
            var color = color || "green";
            opacity = opacity || 1;
            if (statsAnimate) {
                var box = view.append('rect')
                    .attr({
                        height: 25 - border,
                        width: 0,
                        fill: 'white',
                        y: count * 50 + 20 + (border/2),
                        x: startingX + trueWidth / 2
                    })
                    .on("mouseenter", function (d) {
                        addTooltipStats(text);
                    })
                    .on("mouseleave", function (d) {
                        removeTooltipStats();
                    })
                    .transition()
                    .delay(count * 1000 + 1000)
                    .duration(1000)
                    .attr({
                        width:value,
                        fill:color,
                        opacity:opacity
                    })
                    .ease('bounce');
                return box;
            } else {
                var box = view.append('rect')
                    .attr({
                        height:25 -  border,
                        width:value,
                        fill:color,
                        y:count * 50 + 20 + (border/2),
                        opacity: opacity,
                        x: startingX + trueWidth / 2
                    })
                    .on("mouseenter", function (d) {
                        addTooltipStats(text);
                    })
                    .on("mouseleave", function (d) {
                        removeTooltipStats();
                    });
                return box;
            }
        }

        function drawNegative(text, count, value, substract, opacity) {
            if (statsObj.nonstudent) {
                return;
            }
            substract = substract || 0;
            var color = "red";
            opacity = opacity || 1;
            if (statsAnimate) {
                var box = view.append('rect')
                    .attr({
                        height:25 - border,
                        width:0,
                        fill:'white',
                        y:count * 50 + 20 + (border/2),
                        x:halfWidth - substract,
                        opacity:opacity
                    })
                    .on("mouseenter", function (d) {
                        addTooltipStats(text);
                    })
                    .on("mouseleave", function (d) {
                        removeTooltipStats();
                    });
                box.transition()
                    .delay(count * 1000 + 1000)
                    .duration(1000)
                    .attr({
                        x:halfWidth - value - substract,
                        width:value,
                        fill:color
                    })
                    .ease('bounce');

                return box;
            } else {
                var box = view.append('rect')
                    .attr({
                        height:25 - border,
                        width:value,
                        fill:color,
                        y:count * 50 + 20 +(border/2),
                        x:halfWidth - value - substract,
                        opacity:opacity
                    })
                    .on("mouseenter", function (d) {
                        addTooltipStats(text);
                    })
                    .on("mouseleave", function (d) {
                        removeTooltipStats();
                    });

                return box;
            }
        }
    }


});


function scaleStats() {
    var wrapper = d3.select(".statsWrapper");
    var statsView = d3.select("#statsView");
    var statsSVG = d3.select("#statsSVG");
    var switchComp = d3.select("#switch");
    var lis = d3.selectAll(".switch-li");
    var switchTop = switchComp.style("top").replace("px", "");

    if (statsSize == "small") {
        wrapper.style('width', halfWidth + 100)
            .style('height', trueHeight / 2 + 100);
        statsSVG.attr('width', halfWidth + 100)
            .attr('height', trueHeight / 2 + 100);
        statsView.attr('transform', "scale(.8)");
        switchComp.style('left', "60px")
            .style('top', "-82px");
        lis.classed('smallLi', true);
    } else if (statsSize == "medium") {
        wrapper.style('width', statsWidth)
            .style('height', statsHeight);
        statsSVG.attr('width', statsWidth)
            .attr('height', statsHeight);
        switchComp.style('left', "75px")
            .style('top', "-48px");
    } else {
        wrapper.style('width', trueWidth * 1.5 + 40)
            .style('height',  trueHeight * 1.5 + 40);
        statsSVG.attr('width', trueWidth * 1.5 + 40)
            .attr('height', trueHeight * 1.5 + 40);
        statsView.attr('transform', "scale(1.5)");
        switchComp.style('left', "115px")
            .style('top', "-106px");
        lis.classed('largeLi', true);
    }
}

//tooltips
function addTooltipStats(text) {
    statsDiv.transition()
        .duration(200)
        .style("opacity", 1);
    statsDiv.html(text)
        .style("left", (d3.event.pageX +20) + "px")
        .style("top", (d3.event.pageY +20) + "px");
}

function removeTooltipStats() {
    statsDiv.transition()
        .duration(100)
        .style("opacity", 0);
}

function roundToOne(num) {
    return Math.round( num * 10 ) / 10;
}

function getExplanation(component) {
    var explanation = '';
    switch (component) {
        case 'gap':
            explanation = 'Gap shows how many points you are ahead or behind the red line. Be sure to stay in the green to earn bonus!'
            break;
        case 'stamina':
            explanation = 'Stamina is a measure of the quality of your work. If your work quality is low, you will "run out of gas" early and' +
                ' not be able to reach higher milestones because you will run out of points to earn. You can select to view the percent of points ' +
                'earned for all assignments you have completed so far, or just the last 10.';
            break;
        case 'health':
            explanation = 'Health shows your current, locked in penalty/bonus balance, be sure to stay in the green! You can cancel out penalty' +
                ' points by getting ahead of the red line. ';
            break;
        case 'pace':
            explanation = 'Pace shows how well you have been keeping up with the red line as it crosses milestones. Dark colors show how many bonus ' +
                'and penalty points you currently have locked in. Light colors show potential bonus and penalty points you may earn. If you are behind ' +
                'the red line, light red shows what you can limit penalty points to if you catch up now, and light green shows how many bonuses you ' +
                'could still earn if you stay ahead of the red line.';
            break;
        default:
            explanation = 'Statistics';
    }
    return explanation;
}

function toggleStamina(value) {
    lastTen = value == 10 ? true : false;
    d3.select(".all")
        .classed("on", !lastTen);
    d3.select(".ten")
        .classed("on", lastTen);

    //remove stamina
    d3.selectAll(".staminaVars").remove();

    staminaX = 6;
    var staminaValue = lastTen ? statsObj.stamina.ten : statsObj.stamina.total;
    //recalculate and draw it again
    staminaWidth = staminaValue / multiplier * trueWidth;
    if (staminaValue > 0) {
        staminaX = (staminaWidth / 2) + 10;
    }

    variableStamina(staminaValue, staminaWidth, 3, false);
}

function variableStamina(number, width, count, delay) {

    delayValue = 0;
    if (delay) {
        delayValue = 4000;
    }
    var text = "Your average score is: "+roundToOne(number);
    var view = d3.select("#statsView");
    var firstColor = '#FF0000', secondColor = '#FF0000', thirdColor = '#FF0000', fourthColor = '#FF0000';

    if (number >= 70) {
        thirdColor = '#FFB630';//orange
        fourthColor = '#FFB630';//orange
    }
    if (number >= 80) {
        fourthColor = '#FFF830';//yellow
    }
    if (number >= 90) {
        secondColor = '#FFB630';//orange
        thirdColor = '#FFF830';//yellow
        fourthColor = '#008000';//green
    }
    d3.select('#firstStop')
        .attr({
            style: 'stop-color:' + firstColor + ';stop-opacity:1'
        });

    d3.select('#secondStop')
        .attr({
            style: 'stop-color:' + secondColor + ';stop-opacity:1'
        });

    d3.select('#thirdStop')
        .attr({
            style: 'stop-color:' + thirdColor + ';stop-opacity:1'
        });

    d3.select('#fourthStop')
        .attr({
            style: 'stop-color:' + fourthColor + ';stop-opacity:1'
        });

    if (statsAnimate) {
        view.append('rect')
            .attr({
                class: 'staminaVars',
                rx: 3,
                ry: 3,
                height: 25 -border,
                width: 0,
                fill: 'white',
                y: 3 * 50 + 20 + (border/2),
                x:border
            })
            .on("mouseenter", function (d) {
                addTooltipStats(text);
            })
            .on("mouseleave", function (d) {
                removeTooltipStats();
            })
            .transition()
            .delay(delayValue)
            .duration(1000)
            .attr({
                width: width,
                fill: 'url(#staminaGradient)'
            })
            .ease('bounce');
    } else {
        view.append('rect')
            .attr({
                class: 'staminaVars',
                rx: 3,
                ry: 3,
                height: 25 - border,
                width: width,
                fill: 'url(#staminaGradient)',
                y: 3 * 50 + 20+ (border/2),
                class: 'staminaVars',
                x:border
            })
            .on("mouseenter", function (d) {
                addTooltipStats(text);
            })
            .on("mouseleave", function (d) {
                removeTooltipStats();
            });
    }
    drawNumber(count, staminaX, roundToOne(number) + "%", "staminaVars", delay, text);

}

function drawNumber(count, x, text, className, delay, tooltipText) {
    console.log(className + delay);
    delayValue = count * 1000 + 1500;
    if (delay != undefined && !delay)//if defined and set to false
    {
        delayValue = 100;
    }

    if(!statsAnimate)
    {
        delayValue = 0;
    }
    var text = d3.select('#statsView').append("text")
        .attr("fill", "none")
        .style("text-anchor", "middle")
        .attr('font-size', "12px")
        .attr('x', x)
        .attr('y', count * 50 + 40)
        .attr('class', className)
        .text((text))
        .on("mouseenter", function (d) {
            if(tooltipText!=undefined)
            {
                addTooltipStats(tooltipText);
            }
        })
        .on("mouseleave", function (d) {
            if(tooltipText!=undefined)
            {
                removeTooltipStats();
            }
        });

    //if (statsAnimate) {
    text.transition()
        .delay(delayValue)
        .attr("fill", "black");
    //}
}
