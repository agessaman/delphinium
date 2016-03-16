

//tooltip
div = d3.select(".tooltip");

var statsHeight = 240;
var statsWidth = 290;
var trueHeight = 200;
var trueWidth = 250;
var halfWidth = trueWidth / 2;
var border = 1;//thickness of border, to be accounted for when filling boxes
//SET VARIABLES
var positivePace = milestoneSummary.bonuses;20;//
//console.log("locked bonuses: "+positivePace);
var negativePace = -milestoneSummary.penalties;//15;// (must be positive value)
//console.log("locked penalties: "+negativePace);
var maxPace = healthObj.maxBonuses;
var minPace = healthObj.maxPenalties;
var positivePaceWidth = positivePace / maxPace * halfWidth;
var negativePaceWidth = negativePace / minPace * halfWidth;
var positivePaceX = halfWidth + (positivePaceWidth / 2);
var negativePaceX = halfWidth - (negativePaceWidth / 2);

//also draw potential bonus and penalties
var positivePotential = potential.bonus;//15;//
//console.log("potential bonuses: "+positivePotential);
var negativePotential =-potential.penalties;//15;// (must be positive value)
//console.log("potential penalties: "+negativePotential);
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

//STAMINA VARS
var lastTen=false;
var multiplier = stamina.total>100?stamina.total:100;//if for some weird reason a student were to get over a 100% in an assignment, their stamina could potentially be above 100,
// so we must adjust the multiplier
var staminaWidth = stamina.total / multiplier * trueWidth;
var staminaX = 6;
if(stamina.total>0)
{
    staminaX = (staminaWidth / 2) + 10;
}

scaleStats();
fillInterface()

function scaleStats() {
    var statsView = d3.select("#statsView");
    var statsSVG = d3.select("#statsSVG");
    var switchComp = d3.select("#switch");
    var lis = d3.selectAll(".switch-li");
    var switchTop = switchComp.style("top").replace("px", "");

    if (statsSize == "small") {
        statsSVG.attr('width', halfWidth + 100)
            .attr('height', trueHeight / 2 + 100);
        statsView.attr('transform', "scale(.8)");
        switchComp.style('left', "120px")
            .style('top', "151px");
        lis.classed('smallLi',true);
    } else if (statsSize == "medium") {
        statsSVG.attr('width', statsWidth)
            .attr('height', statsHeight);
        switchComp.style('left', "135px")
            .style('top', "184px");
    } else {
        statsSVG.attr('width', trueWidth * 1.5 + 40)
            .attr('height', trueHeight * 1.5 + 40);
        statsView.attr('transform', "scale(1.5)");
        switchComp.style('left', "177px")
            .style('top', "265px");
        lis.classed('largeLi',true);
    }
}

function fillInterface()
{
    var view = d3.select("#statsView");
    //FILL EACH BAR
    paceInterface();
    gapInterface();
    healthInterface();
    staminaInterface();

    function paceInterface()
    {
        var count =0;
        //BONUS
        if (positivePace > 0) {
            drawPositive(count, positivePaceWidth);
            drawNumber(count, positivePaceX, roundToTwo(positivePace), "paceVars");
            //apend the lock icon (must be text. inside of svg we can't add span or i)
            view.append("svg:text")
                .attr("x",function(d)
                {
                    return positivePaceX;
                })
                .attr("y", count*50+30)
                .attr('font-family', 'FontAwesome')
                .attr('font-size', '8px')
                .attr("cursor", "pointer")
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
        }

        //PENALTIES
        if (negativePace > 0) {
            box = drawNegative(count, negativePaceWidth);
            drawNumber(count, negativePaceX, roundToTwo(-negativePace),"paceVars");
            view.append("svg:text")
                .attr("x",function(d)
                {
                    return negativePaceX;
                })
                .attr("y", count*50+30)
                .attr('font-family', 'FontAwesome')
                .attr('font-size', '8px')
                .attr("cursor", "pointer")
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
        }


        //POTENTIAL BONUSES
        if (positivePotential > 0) {

            drawNumber(count, posPotentialX, roundToTwo(positivePotential), "paceVars");
            drawPositive(count, posPotentialWidth, positivePaceWidth, 0.4);
            view.append("svg:text")
                .attr("x",function(d)
                {
                    return posPotentialX;
                })
                .attr("y",  count*50+30)
                .attr('font-family', 'FontAwesome')
                .attr('font-size', '8px')
                .attr("cursor", "pointer")
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
        }

        //POTENTIAL PENALTIES
        if (negativePotential > 0) {

            drawNumber(count, negPotentialX, roundToTwo(-negativePotential),"paceVars");
            drawNegative(count, negPotentialWidth, negativePaceWidth, 0.4);

            view.append("svg:text")
                .attr("x",function(d)
                {
                    return negPotentialX;
                })
                .attr("y",  count*50+30)
                .attr('font-family', 'FontAwesome')
                .attr('font-size', '8px')
                .attr("cursor", "pointer")
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
        }

        if (positivePace == 0&&negativePace==0&&positivePotential==0&&negativePotential==0) {
            drawNumber(count, 0, 0,"paceVars");
        }
        drawCenter(count);
    }
    function gapInterface()
    {
        var count = 1;
        drawText(count, "Gap");
        var text = getExplanation("gap");
        y=count* 50 + 10;
        //drawIcon('\uf05a', 0, y, text, "14px", '#444444');
        if (gap > 0)
            drawPositive(count, gapWidth);
        else
            drawNegative(count, gapWidth);
        drawNumber(count, gapX, roundToTwo(gap, "gapVars"));
        drawCenter(count);
    }
    function healthInterface()
    {
        var count = 2;
        var text = getExplanation("health");
        y=count* 50 + 10;
        //drawIcon('\uf05a', 0, y, text, "14px", '#444444');
        if (health > 0)
            drawPositive(count, healthWidth);
        else
            drawNegative(count, healthWidth);
        drawNumber(count, healthX, roundToTwo(health), "healthVars");
        drawCenter(count);
    }
    function staminaInterface()
    {
        var count = 3;
       //draw stamina bar
        variableStamina(stamina.total, staminaWidth, count, true);
        //add a checkbox so students can select if they want to see their averages for the last 10 assignments or all their assignments
        var options = ['All','Last 10'];
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

    function drawText(count, text) {
        view.append('text')
            .text(text)
            .attr('x',15)
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
            .attr("cursor", "pointer")
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
    function drawPositive(count, value, startingX, opacity) {
        if(nonstudent){return;}
        startingX = startingX || 0;
        var color = color || "green";
        opacity = opacity || 1;
        if (statsAnimate) {
            var box = view.append('rect')
                //.attr('rx', 3)
                //.attr('ry', 3)
                .attr('height', 25-(2*border))
                .attr('width', 0)
                .attr('fill', "white")
                .attr('y', count * 50 + 20 + border)
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
                .attr('height', 25 -(2*border))
                .attr('width', value)
                .attr('fill', color)
                .attr('y', count * 50 + 20 + border)
                .attr("opacity", opacity)
                .attr('x', startingX + trueWidth / 2);
            return box;
        }
    }

    function drawNegative(count, value, substract, opacity) {
        if(nonstudent){return;}
        substract = substract || 0;
        var color = "red";
        opacity = opacity || 1;
        if (statsAnimate) {
            var box = view.append('rect')
                //.attr('rx', 3)
                //.attr('ry', 3)
                .attr('height', 25-(2*border))
                .attr('width', 0)
                .attr('fill', "white")
                .attr('y', count * 50 + 20+border)
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
                .attr('height', 25-(2*border))
                .attr('width', value)
                .attr('fill', color)
                .attr('y', count * 50 + 20+border)
                .attr('x', halfWidth - value-substract)
                .attr("opacity", opacity);

            return box;
        }
    }
}

//tooltips
function addTooltipExplanation(component)
{
    var text = getExplanation(component);
    addTooltipStats(text);
}

function drawNumber(count, x, text, className, delay) {
    delayValue= count * 1000 + 1500;
    if(delay!=undefined && !delay )//if defined and set to false
    {
        delayValue=100;
    }
    var text = d3.select('#statsView').append("text")
        .attr("fill", "none")
        .style("text-anchor", "middle")
        .attr('font-size', "16px")
        .attr('x', x)
        .attr('y', count * 50 + 40)
        .attr('class',className)
        .text((text));

    //if (statsAnimate) {
    text.transition()
        .delay(delayValue)
        .attr("fill", "black");
    //}
}

function variableStamina(number, width, count, delay) {

    delayValue = 0;
    if (delay) {
        delayValue = 4000;
    }
    var view = d3.select("#statsView");
    var firstColor ='#FF0000', secondColor ='#FF0000', thirdColor='#FF0000', fourthColor ='#FF0000';

    if(number>=70){
        thirdColor = '#FFB630';//orange
        fourthColor ='#FFB630';//orange
    }
    if (number >= 80) {
        fourthColor ='#FFF830';//yellow
    }
    if (number >= 90) {
        secondColor = '#FFB630';//orange
        thirdColor = '#FFF830';//yellow
        fourthColor ='#008000';//green
    }
    d3.select('#firstStop')
        .attr({
            style: 'stop-color:'+firstColor+';stop-opacity:1'
        });

    d3.select('#secondStop')
        .attr({
            style: 'stop-color:'+secondColor+';stop-opacity:1'
        });

    d3.select('#thirdStop')
        .attr({
            style:'stop-color:'+thirdColor+';stop-opacity:.9'
        });

    d3.select('#fourthStop')
        .attr({
            style:'stop-color:'+fourthColor+';stop-opacity:1'
        });

    if (statsAnimate) {
        view.append('rect')
            .attr({
                class:'staminaVars',
                rx:3,
                ry:3,
                height:25-(2*border),
                width:0,
                fill:'white',
                y:3 * 50 + 20+border
            })
            .transition()
            .delay(delayValue)
            .duration(1000)
            .attr({
                width:width,
                fill:'url(#staminaGradient)'
            })
            .ease('bounce');
    } else {
        view.append('rect')
            .attr({
                class:'staminaVars',
                rx:3,
                ry:3,
                height:25,
                width:width,
                fill:'url(#staminaGradient)',
                y:3 * 50 + 20,
                class:'staminaVars'
            });
    }
    drawNumber(count, staminaX, roundToTwo(number)+"%","staminaVars", delay);
}

function toggleStamina(value)
{
    lastTen=value==10?true:false;
    d3.select(".all")
        .classed("on", !lastTen);
    d3.select(".ten")
        .classed("on", lastTen);

    //remove stamina
    d3.selectAll(".staminaVars").remove();

    staminaX = 6;
    var staminaValue = lastTen?stamina.ten:stamina.total;
    //recalculate and draw it again
    staminaWidth = staminaValue / multiplier * trueWidth;
    if(staminaValue>0)
    {
        staminaX = (staminaWidth / 2) + 10;
    }

    variableStamina(staminaValue, staminaWidth, 3, false);
}
function addTooltipStats(text) {
    div.transition()
        .duration(200)
        .style("opacity", 1)
        .style("display","block")
        .style("left", (d3.event.pageX + 10) + "px")
        .style("top", (d3.event.pageY) + "px");
    div.html(text);
}

function removeTooltipStats() {
    div.transition()
        .duration(100)
        .style("display","none")
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
