//statsAnimate = false;
//tooltip
div = d3.select(".tooltip");

var statsHeight = 240;
var statsWidth = 290;
var trueHeight = 200;
var trueWidth = 250;
var halfWidth = trueWidth / 2;

var positivePace = milestoneSummary.bonuses;//10;//
console.log("locked bonuses: "+positivePace);
var negativePace = 10;//-milestoneSummary.penalties;//-10;// (must be positive value)
console.log("locked penalties: "+negativePace);
var maxPace = healthObj.maxBonuses;
var minPace = healthObj.maxPenalties;
var positivePaceWidth = positivePace / maxPace * halfWidth;
var negativePaceWidth = negativePace / minPace * halfWidth;
var positivePaceX = halfWidth + (positivePaceWidth / 2);
var negativePaceX = halfWidth - (negativePaceWidth / 2);

//also draw potential bonus and penalties
var positivePotential = potential.bonus;//40;//
console.log("potential bonuses: "+positivePotential);
var negativePotential = -potential.penalties;//-10;// (must be positive value)
console.log("potential penalties: "+negativePotential);
var posPotentialWidth = positivePotential / maxPace * halfWidth;
var negPotentialWidth = negativePotential/ minPace * halfWidth;
var posPotentialX = halfWidth + positivePaceWidth + (posPotentialWidth/2);
var negPotentialX = halfWidth -negativePaceWidth - (negPotentialWidth/2);


var health = milestoneSummary.bonuses + milestoneSummary.penalties;
var maxHealth = healthObj.maxBonuses;
var minHealth = healthObj.maxPenalties;
var healthWidth, healthX;
if (health > 0) {
    healthWidth = health / maxHealth * halfWidth;
    healthX = halfWidth + (healthWidth / 2);
} else {
    healthWidth = health / minHealth * halfWidth * -1;
    healthX = halfWidth - (healthWidth / 2);
}


var gap = milestoneSummary.total - redLine;
var maxGap = gapObj.maxGap;
var minGap = gapObj.minGap;
var gapWidth, gapX;
if (gap > 0) {
    gapWidth = gap / maxGap * halfWidth;
    gapX = halfWidth + (gapWidth / 2);
} else {
    gapWidth = gap / minGap * halfWidth * -1;
    gapX = halfWidth - (gapWidth / 2);
}

var multiplier = stamina>100?stamina:100;//if for some weird reason a student were to get over a 100% in an assignment, their stamina could potentially be above 100,
// so we must adjust the multiplier
var staminaWidth = stamina / multiplier * trueWidth;
var staminaX = (staminaWidth / 2) + 10;

scaleStats();
drawStats();

function scaleStats() {
    var statsView = d3.select("#statsView");
    var statsSVG = d3.select("#statsSVG");

    if (statsSize == "small") {
        statsSVG.attr('width', halfWidth + 40)
            .attr('height', trueHeight / 2 + 40);
        statsView.attr('transform', "scale(.5)");
    } else if (statsSize == "medium") {
        statsSVG.attr('width', statsWidth)
            .attr('height', statsHeight);
    } else {
        statsSVG.attr('width', trueWidth * 1.5 + 40)
            .attr('height', trueHeight * 1.5 + 40);
        statsView.attr('transform', "scale(1.5)");
    }
}

function drawStats() {

    var view = d3.select("#statsView");

    drawPace();
    drawHealth();
    drawGap();
    drawStamina();

    function drawPace()
    {
        var count = 0;
        drawText(count, "Pace");
        var text = getExplanation("pace");
        y=count* 50 + 10;
        drawIcon('\uf05a', 0, y, text, "11px", '#444444');

        //BONUS
        if (positivePace > 0) {
            drawPositive(0, positivePaceWidth);
            //apend the lock icon (must be text. inside of svg we can't add span or i)
            view.append("svg:text")
                .attr("x",function(d)
                {
                    return positivePaceX;
                })
                .attr("y",  0*50+30)
                .attr('font-family', 'FontAwesome')
                .attr('font-size', '8px')
                .attr("fill", function(d)
                {
                    var x = positivePace>0?"white":"gray";
                    return x;
                })
                .on("mouseover", function (d) {
                    addTooltipStats("Locked in bonus: "+ roundToTwo(positivePace));
                })
                .on("mouseout", function (d) {
                    removeTooltipStats();
                })
                .text(function (d) {
                    return '\uf023';
                });


            drawNumber(count, positivePaceX, roundToTwo(positivePace));
        }

        //PENALTIES
        if (negativePace > 0) {
            box = drawNegative(0, negativePaceWidth);
            view.append("svg:text")
                .attr("x",function(d)
                {
                    return negativePaceX;
                })
                .attr("y",  0*50+30)
                .attr('font-family', 'FontAwesome')
                .attr('font-size', '8px')
                .attr("fill", function(d)
                {
                    var x = negativePace>0?"white":"gray";
                    return x;
                })
                .on("mouseover", function (d) {
                    addTooltipStats("Locked in penalties: "+ roundToTwo(-negativePace));
                })
                .on("mouseout", function (d) {
                    removeTooltipStats();
                })
                .text(function (d) {
                    return '\uf023';
                });

            drawNumber(count, negativePaceX, roundToTwo(-negativePace));
        }


        //POTENTIAL BONUSES
        if (positivePotential > 0) {
            drawPositive(0, posPotentialWidth, positivePaceWidth, 0.4);
            view.append("svg:text")
                .attr("x",function(d)
                {
                    return posPotentialX;
                })
                .attr("y",  0*50+30)
                .attr('font-family', 'FontAwesome')
                .attr('font-size', '8px')
                .attr("fill", function(d)
                {
                    var x = positivePotential>0?"white":"gray";
                    return x;
                })
                .on("mouseover", function (d) {
                    addTooltipStats("Potential bonus: "+ roundToTwo(positivePotential));
                })
                .on("mouseout", function (d) {
                    removeTooltipStats();
                })
                .text(function (d) {
                    return '\uf017';
                });

            drawNumber(count, posPotentialX, roundToTwo(positivePotential));
        }

        //POTENTIAL PENALTIES
        if (negativePotential > 0) {
            drawNegative(0, negPotentialWidth, negativePaceWidth, 0.4);

            view.append("svg:text")
                .attr("x",function(d)
                {
                    return negPotentialX;
                })
                .attr("y",  0*50+30)
                .attr('font-family', 'FontAwesome')
                .attr('font-size', '8px')
                .attr("fill", function(d)
                {
                    var x = negativePotential>0?"white":"gray";
                    return x;
                })
                .on("mouseover", function (d) {
                    addTooltipStats("Potential penalties: "+ roundToTwo(-negativePotential));
                })
                .on("mouseout", function (d) {
                    removeTooltipStats();
                })
                .text(function (d) {
                    return '\uf017';
                });

            drawNumber(count, negPotentialX, roundToTwo(-negativePotential));
        }

        drawBox(count, "pace");
        drawCenter(count);
    }

    function drawHealth() {
        var count = 1;
        drawText(count, "Health");

        var text = getExplanation("health");
        y=count* 50 + 10;
        drawIcon('\uf05a', 0, y, text, "11px", '#444444');
        if (health > 0)
            drawPositive(1, healthWidth);
        else
            drawNegative(1, healthWidth);
        drawBox(count, "health");
        drawCenter(count);
        drawNumber(count, healthX, roundToTwo(health));
    }

    function drawGap() {
        var count = 2;
        drawText(count, "Gap");
        var text = getExplanation("gap");
        y=count* 50 + 10;
        drawIcon('\uf05a', 0, y, text, "11px", '#444444');
        if (gap > 0)
            drawPositive(2, gapWidth);
        else
            drawNegative(2, gapWidth);
        drawBox(count, "gap");
        drawCenter(count);
        drawNumber(count, gapX, roundToTwo(gap));
    }

    function drawStamina() {
        var count = 3;
        drawText(count, "Stamina");
        //draw info icon
        var text = getExplanation("stamina");
        y=count* 50 + 10;
        drawIcon('\uf05a', 0, y, text, "11px", '#444444');

        if (statsAnimate) {
            view.append('rect')
                .attr('rx', 3)
                .attr('ry', 3)
                .attr('height', 25)
                .attr('width', 0)
                .attr('fill', "white")
                .attr('y', 3 * 50 + 20)
                .transition()
                .delay(4000)
                .duration(1000)
                .attr('width', staminaWidth)
                .attr('fill', "green")
                .ease('bounce');
        } else {
            view.append('rect')
                .attr('rx', 3)
                .attr('ry', 3)
                .attr('height', 25)
                .attr('width', staminaWidth)
                .attr('fill', "green")
                .attr('y', 3 * 50 + 20);
        }
        drawBox(count, "stamina");
        drawNumber(count, staminaX, roundToTwo(stamina)+"%");
    }

    function drawText(count, text) {
        view.append('text')
            .text(text)
            .attr('x',13)
            .attr('y', count * 50 + 10)
            .attr("fill",'#444444')
            .attr('transform', "translate(0,7)");
    }

    function drawIcon(iconCode, x, y, mouseInText, fontSize, color)
    {
        view.append("svg:text")
            .attr("x",x)
            .attr("y", y)
            .attr('font-family', 'FontAwesome')
            .attr('font-size', fontSize)
            .attr("fill",color)
            .on("mouseover", function (d) {
                addTooltipStats(mouseInText);
            })
            .on("mouseout", function (d) {
                removeTooltipStats();
            })
            .text(function (d) {
                return iconCode;
            })
            .attr('transform', "translate(0,7)");
    }

    function drawBox(count, component) {
        var explanation = getExplanation(component);

        view.append('rect')
            .attr('rx', 3)
            .attr('ry', 3)
            .attr('height', 25)
            .attr('width', 250)
            .attr('stroke-width', "2")
            .attr('stroke', "#444444")
            .attr('fill', "none")
            .attr('y', count * 50 + 20);
    }

    function drawCenter(count) {
        view.append('line')
            .attr('y1', count * 50 + 20)
            .attr('y2', count * 50 + 45)
            .attr('x1', halfWidth)
            .attr('x2', halfWidth)
            .attr('stroke-width', "2")
            .attr('stroke', "#444444")
            .attr('fill', "none")
    }

    function drawPositive(count, value, startingX, opacity) {
        startingX = startingX || 0;
        var color = color || "green";
        opacity = opacity || 1;
        if (statsAnimate) {
            var box = view.append('rect')
                //.attr('rx', 3)
                //.attr('ry', 3)
                .attr('height', 25)
                .attr('width', 0)
                .attr('fill', "white")
                .attr('y', count * 50 + 20)
                .attr('x', startingX+ trueWidth / 2)
                .transition()
                .delay(count * 1000 + 1000)
                .duration(1000)
                .attr('width', value)
                .attr('fill', color)
                .attr("opacity", opacity)
                .ease('bounce');
            return box;
        } else {
            var box = view.append('rect')
                //.attr('rx', 3)
                //.attr('ry', 3)
                .attr('height', 25)
                .attr('width', value)
                .attr('fill', color)
                .attr('y', count * 50 + 20)
                .attr("opacity", opacity)
                .attr('x', startingX + trueWidth / 2);
            return box;
        }
    }

    function drawNegative(count, value, substract, opacity) {
        substract = substract || 0;
        var color = "red";
        opacity = opacity || 1;
        if (statsAnimate) {
            var box = view.append('rect')
                //.attr('rx', 3)
                //.attr('ry', 3)
                .attr('height', 25)
                .attr('width', 0)
                .attr('fill', "white")
                .attr('y', count * 50 + 20)
                .attr('x', halfWidth-substract)
                .attr("opacity", opacity);
            box.transition()
                .delay(count * 1000 + 1000)
                .duration(1000)
                .attr('x', halfWidth - value-substract)
                .attr('width', value)
                .attr('fill', color)
                .ease('bounce');

            return box;
        } else {
            var box = view.append('rect')
                //.attr('rx', 3)
                //.attr('ry', 3)
                .attr('height', 25)
                .attr('width', value)
                .attr('fill', color)
                .attr('y', count * 50 + 20)
                .attr('x', halfWidth - value-substract)
                .attr("opacity", opacity);

            return box;
        }
    }

    function drawNumber(count, x, text) {
        var text = d3.select('#statsView').append("text")
            .attr("fill", "none")
            .style("text-anchor", "middle")
            .attr('font-size', "16px")
            .attr('x', x)
            .attr('y', count * 50 + 40)
            .text((text));

        text.transition()
            .delay(count * 1000 + 1500)
            .attr("fill", "black");
    }
}


function addTooltipStats(text) {
    div.transition()
        .duration(200)
        .style("opacity", .9)
        .style("left", (d3.event.pageX) + "px")
        .style("top", (d3.event.pageY - 28) + "px");
    div.html(text);
}

function removeTooltipStats() {
    div.transition()
        .duration(100)
        .style("opacity", 0);
}



function roundToTwo(num) {
    return +(Math.round(num + "e+2")  + "e-2");
}

function getExplanation(component)
{
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
            explanation = 'Pace shows how well you have been keeping up with the red line as it crosses milestones. It also shows what you can limit ' +
                'penalty points to if you catch up to the red line now, and it shows how many bonuses you could earn if you get ahead of the red line.';
            break;
        default:
            explanation = 'Statistics';
    }
    return explanation;
}