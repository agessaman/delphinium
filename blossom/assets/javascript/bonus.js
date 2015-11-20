
var textSize;
var delay = 1000;
var duration = 500;

scaleBonus();
drawBonus();

function scaleBonus() {
    var bonusView = d3.select("#bonusView");
    var bonusSVG = d3.select("#bonusSVG");
    var bonusHeight = 115;
    var bonusWidth = 340;
        bonusSVG.attr('width', bonusWidth *(bonusSize/100))
                .attr('height', bonusHeight *(bonusSize/100));
        var multiplier = bonusSize/100;
        bonusView.attr('transform', "scale("+multiplier+")");
}

function drawBonus() {
    var origin = 170;
    var textx = origin;
    var height = 115;

    var minScale = d3.scale.linear()
            .domain([minBonus, 0])
            .range([163, 0]);

    var maxScale = d3.scale.linear()
            .domain([0, maxBonus])
            .range([0, 163]);

    var prectangle = d3.select("#penaltyRect");
    var brectangle = d3.select("#bonusRect");

    d3.select("#center").style("fill", "black")
            .attr('x', origin)
            .attr('width', 2);

    prectangle.style("fill", "white")
            .attr('x', origin);


    if (totalBonus + totalPenalties > 0) {
        //Positive


        //Set brectangle
        brectangle.style("fill", "white")
                .attr('x', origin);

        bonusDelay = delay;
        //Draw penalty
        if (totalPenalties !== 0)
        {
            prectangle.transition()
                    .delay(delay)
                    .duration(duration)
                    .style('fill', "#FF4747")
                    .attr('width', Math.round(minScale(totalPenalties)))
                    .attr('x', origin - Math.round(minScale(totalPenalties)))
                    .ease('bounce');

            //Change penalty to green	
            prectangle.transition()
                    .delay(delay * 2)
                    .duration(duration)
                    .style('fill', "#66FF33")
                    .attr('x', origin - Math.round(minScale(totalPenalties)))
                    .each("end", function () {
                        //Draw bonus
                        brectangle.transition()
                                .style('fill', "#66FF33")
                                .attr('width', (Math.round(maxScale(totalBonus + totalPenalties))))
                                .attr('x', origin)
                                .ease('exp')
                                .each("end", function () {
                                    //Erase overlap
                                    prectangle.transition()
                                            .style('fill-opacity', 0)
                                            .each("end", function ()
                                            {
                                                drawText(duration * 2);
                                            });
                                });

                    });
            bonusDelay = delay * 2;
        }

        //Draw bonus
        brectangle.transition()
                .delay(bonusDelay)
                .duration(duration * 2)
                .style('fill', "#66FF33")
                .attr('width', (Math.round(maxScale(totalBonus + totalPenalties))))
                .attr('x', origin)
                .ease('exp')
                .each("end", function ()
                {
                    drawText(duration * 2);
                });

        //figure out where to place the text
        textx = origin + Math.round(maxScale(totalBonus + totalPenalties));
        
        
    } else if (totalBonus + totalPenalties == 0) {
        //Zero
        if (totalPenalties !== 0)
        {
            //Draw penalty
            prectangle.transition()
                    .delay(delay)
                    .duration(duration)
                    .style('fill', "#FF4747")
                    .attr('width', Math.round(minScale(totalPenalties)))
                    .attr('x', origin - Math.round(minScale(totalPenalties)))
                    .ease('bounce');

            //Change penalty to green	
            prectangle.transition()
                    .delay(delay * 2)
                    .duration(duration)
                    .style('fill', "#66FF33")
                    .attr('x', origin - Math.round(minScale(totalPenalties)))
                    .ease('exp');

            //Erase overlap
            prectangle.transition()
                    .delay(delay * 2.5)
                    .duration(duration)
                    .style('fill-opacity', 0)
                    .each("end", function ()
                    {
                        drawText(duration);
                    });
        }
        else
        {
            drawText(duration);
        }
        textx = origin;

    } else {
        //Negative

        //set text
        textx = origin - Math.round(minScale(totalBonus + totalPenalties));

        //Set brectangle
        brectangle.style("fill", "white")
                .attr('x', origin - Math.round(minScale(totalPenalties)));


        //Draw penalty
        prectangle.transition()
                .delay(delay)
                .duration(duration)
                .style('fill', "#FF4747")
                .attr('width', Math.round(minScale(totalPenalties)))
                .attr('x', origin - Math.round(minScale(totalPenalties)))
                .ease('bounce');
                
        if (totalBonus > 0)
        {
            //Draw bonus
            brectangle.transition()
                    .delay(delay * 1.5)
                    .duration(duration)
                    .style('fill', "#66FF33")
                    .attr('width', Math.round(minScale(-totalBonus)))
                    .ease('exp');
                    
            //        //Erase overlap
            prectangle.transition()
                    .delay(delay * 1.5)
                    .duration(duration)
                    .attr('width', Math.round(minScale(totalBonus + totalPenalties)))
                    .attr('x', origin - Math.round(minScale(totalBonus + totalPenalties)))
                    .ease('exp');


            brectangle.transition()
                    .delay(delay * 2)
                    .duration(duration)
                    .style('fill-opacity', 0)
                    .each("end", function ()
                    {
                        drawText(duration);
                    });
        }
        else
        {
            drawText(delay * 2);
        }
    }


    function drawText(delay)
    {
        var text = d3.select('#bonusView').append("text")
                .attr("fill", "none")
                .style("text-anchor", "middle")
                .attr('font-size', "20px")
                .attr('x', textx - 10)
                .attr('y', height / 2)
                .text(Math.round(totalBonus + totalPenalties));

        text.transition()
                .delay(delay)
                .attr("fill", "black");
    }
    
    function roundToTwo(num) {
        return +(Math.round(num + "e+2") + "e-2");
    }
}