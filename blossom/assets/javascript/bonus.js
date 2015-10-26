$(document).ready(function () {
    scaleBonus();
    drawBonus();
});

var textSize;

function scaleBonus() {
    var bonusView = d3.select("#bonusView");
    var bonusSVG = d3.select("#bonusSVG");
    var bonusHeight = 115;
    var bonusWidth = 340;
    if (bonusSize == "0") {
        bonusSVG.attr('width', bonusWidth / 2)
                .attr('height', bonusHeight / 2);
        bonusView.attr('transform', "scale(.5)");
    } else if (bonusSize == "1") {
        bonusSVG.attr('width', bonusWidth)
                .attr('height', bonusHeight);
    } else {
        bonusSVG.attr('width', bonusWidth * 1.5)
                .attr('height', bonusHeight * 1.5);
        bonusView.attr('transform', "scale(1.5)");
    }
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

    
    if (bonus + penalty > 0) {
        //Positive

        //set text
        textx = origin + (Math.round(maxScale(bonus + penalty) / 2));

        //Set brectangle
        brectangle.style("fill", "white")
                .attr('x', origin);
        //Draw penalty
        prectangle.transition()
                .delay(2000)
                .duration(1000)
                .style('fill', "#FF4747")
                .attr('width', Math.round(minScale(penalty)))
                .attr('x', origin - Math.round(minScale(penalty)))
                .ease('bounce');

        //Change penalty to green	
        prectangle.transition()
                .delay(3000)
                .duration(500)
                .style('fill', "#66FF33")
                .attr('x', origin - Math.round(minScale(penalty)))
                .ease('bounce');

        //Draw bonus
        brectangle.transition()
                .delay(4500)
                .duration(1000)
                .style('fill', "#66FF33")
                .attr('width', (Math.round(maxScale(bonus + penalty))))
                .attr('x', origin)
                .ease('bounce');

        //Erase overlap
        prectangle.transition()
                .delay(4000)
                .duration(500)
                .style('fill-opacity', 0);
        

    } else if (bonus + penalty == 0) {
        //Zero

        //Draw penalty
        prectangle.transition()
                .delay(2000)
                .duration(1000)
                .style('fill', "#FF4747")
                .attr('width', Math.round(minScale(penalty)))
                .attr('x', origin - Math.round(minScale(penalty)))
                .ease('bounce');

        //Change penalty to green	
        prectangle.transition()
                .delay(3000)
                .duration(500)
                .style('fill', "#66FF33")
                .attr('x', origin - Math.round(minScale(penalty)))
                .ease('bounce');

        //Erase overlap
        prectangle.transition()
                .delay(4000)
                .duration(500)
                .style('fill-opacity', 0);

    } else {
        //Negative

        //set text
        textx = origin - Math.round(minScale(bonus+penalty)) ;

        //Set brectangle
        brectangle.style("fill", "white")
                .attr('x', origin - Math.round(minScale(penalty)));


        //Draw penalty
        prectangle.transition()
                .duration(1000)
                .style('fill', "#FF4747")
                .attr('width', Math.round(minScale(penalty)))
                .attr('x', origin - Math.round(minScale(penalty)))
                .ease('bounce');
        
        
        //Draw bonus
        brectangle.transition()
                .delay(1500)
                .duration(1000)
                .style('fill', "#66FF33")
                .attr('width', Math.round(minScale(-bonus)));

//        //Erase overlap
        prectangle.transition()
                .delay(2500)
                .duration(500)
                .attr('width', Math.round(minScale(bonus+penalty)))
                .attr('x', origin - Math.round(minScale(bonus+penalty)));
        
        brectangle.transition()
                .delay(3000)
                .duration(1000)
                .style('fill-opacity', 0);
    }
    
var text = d3.select('#bonusView').append("text")
		.attr("fill", "none")
	    .style("text-anchor", "middle")
	    .attr('font-size', "20px")
	    .attr('x', textx-10)
	    .attr('y', height/2)
	    .text(bonus + penalty)
	
	text.transition()
		.delay(4000)
		.attr("fill", "black");
}
