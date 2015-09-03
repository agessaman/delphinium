$(document).ready(function(){
    scaleExperience(experienceSize);
    drawAxis();
    drawExperience(redLine);
    drawScatterplot(studentScores);
});

var bottom = 500;

function scaleExperience(experienceSize){
	var experienceView = d3.select("#experienceView");
	var experienceSVG = d3.select("#experienceSVG");
	var experienceHeight = 520;
	var experienceWidth = 280;

	if(experienceSize === "small"){
		experienceWidth= experienceWidth / 2;
		experienceHeight= experienceHeight / 2;
		experienceView.attr('transform', "scale(.5)");
	}else if(experienceSize === "large"){
		experienceWidth= experienceWidth *1.5;
		experienceHeight= experienceHeight *1.5;
		experienceView.attr('transform', "scale(1.5)");
	}
	experienceSVG.attr('width', experienceWidth)
				.attr('height', experienceHeight);
}

function drawAxis(){
        var bonus_penalties = milestoneClearance;
        console.log(bonus_penalties);

	var encouragementAxisScale = d3.scale.linear()
		.domain([0,encouragement.length-1])
		.range([bottom-5, 10]);

	var bpAxisScale = d3.scale.linear()
		.domain([0,encouragement.length-1])
		.range([bottom-5, 10]);

	var encouragementAxis = d3.svg.axis()
		.scale(encouragementAxisScale)
		.orient('left')
		.tickFormat(function(d) { return encouragement[d];});

	var xAxisGrid = encouragementAxis
            .ticks(encouragement.length)
	    .tickSize(165, 0)
	    .orient("right");

	var bpAxis = d3.svg.axis()
		.scale(bpAxisScale)
		.orient('right')
		.ticks(bonus_penalties.length-1)
		.tickFormat(function(d) { return roundToTwo(bonus_penalties[d].bonusPenalty);});

	d3.select("#experienceAxis")
		.attr('class', 'axis')
		.call(encouragementAxis);

	var bp_axis = d3.select("#bpAxis")
		.attr("transform", "translate(180,12)")
		.attr('class', 'bp')
		.call(bpAxis);

	bp_axis.selectAll("text")
		.style('fill',  function(d) {
			if(bonus_penalties[d].bonusPenalty == 0){
				return 'black';
			} else if(bonus_penalties[d].bonusPenalty < 0){
				return 'red';
			} else {
				return 'green';
			}
		})
		.attr('font-size',"10px")
	    .style("text-anchor", "end");

	bp_axis.selectAll(".tick").append('text')
	    .attr('font-family', 'FontAwesome')
	    .attr("fill", "gray")
	    .attr("transform", "translate(15,5)")
	    .text(function(d) { 
	    	if(bonus_penalties[d].cleared){
				return '\uf023';
			}
		});
}

function drawExperience(redLine){
	var datelong = new Date();
	var date = (datelong.getMonth() + 1) + '/' + datelong.getDate() + '/' +  datelong.getFullYear();
        var redLineY = bottom-redLine;
	var scale = d3.scale.linear()
		.domain([0, maxXP])
		.range([0, bottom]);

	var redLine = d3.select("#redLine")
		.attr('y', bottom - 10)
		.style("fill","red")
	    .transition()
	    	.delay(8000)
	    	.duration(1000)
	    	.attr('height', 2,5)
                .attr('y', redLineY);

	var therm = d3.select("#experienceRect")
		.attr('y', bottom)
		.style("fill","white")
		.transition()
			.delay(2000)
			.style('fill', "steelblue")
			.attr('height', Math.round(scale(experienceXP)))
			.attr('y', bottom - Math.round(scale(experienceXP)))
			.duration(1000)
			.ease('bounce');

	var bonus = d3.select("#eBonusRect")
		.attr('y', bottom - Math.round(scale(experienceXP)))
		.style("fill","white")
	    .transition()
	    	.delay(4000)
	    	.duration(1000)
	    	.style("fill","green")
	    	.attr('y', bottom - Math.round(scale(experienceXP) + scale(experienceBonus )))
	    	.attr('height', Math.round(scale(experienceBonus)))
	    	.ease('bounce');

	var penalties = d3.select("#ePenaltiesRect")
		.attr('y', bottom - Math.round(scale(experienceXP) + scale(experienceBonus)))
		.style("fill","white")
	    .transition()
	    	.delay(5000)
	    	.duration(1000)
	    	.style("fill","red")
	    	.attr('height', Math.round(scale(experiencePenalties * -1)))
	    	.ease('bounce');

	penalties.transition()
		.delay(6000)
		.style("fill","white");

	bonus.transition()
		.delay(7000)
		.style("fill","steelblue");

	var experienceText = d3.select("#experienceView").append("text")
		.attr("fill", "steelblue")
		.transition()
			  .delay(7000)
		      .attr("fill", "#FD994C")
		      .style("text-anchor", "middle")
		      .attr('font-size',"20px")
		      .attr('font-weight',"bold")
		      .attr('x', 75)
		      .attr('y', 450)
		      .text(experienceGrade);

	function dayDifference(first, second){
		return (second-first)/(1000*60*60*24);
	}
	
}

function drawScatterplot(studentScores) {
    var data =[];
    for(var i=0;i<=studentScores.length-1;i++)
    {
        data.push([0.5,studentScores[i]]);
    }

    var margin = {top: 10, bottom: 10, }, height = 500 - margin.top - margin.bottom;

    var x = d3.scale.linear()
                      .domain([0, 1])
                      .range([ 0, 25 ]);

    var y = d3.scale.linear()
                      .domain([0, maxXP])
                      .range([ height, 0 ]);



    var g = d3.select("#experienceView").append("svg:g"); 


    g.selectAll("scatter-dots")
      .data(data)
      .enter().append("svg:circle")
              .attr("cx", function (d) { return x(d[0]); } )
              .attr("cy", function (d) { return y(d[1]); } )
              .attr("r", 6)
              .attr("class","dot")
              .attr("transform", "translate(140,10)");

    g.append("svg:circle")
              .attr("cx", x(0.5))
              .attr("cy", y(experienceGrade))
              .attr("r", 6)
              .attr("id","gradedot")
              .style("stroke", "black")
              .attr("transform", "translate(140,10)");

}

function roundToTwo(num) {    
    return +(Math.round(num + "e+2")  + "e-2");
}

