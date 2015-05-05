$(document).ready(function(){
	scaleExperience();
	drawAxis();
    drawExperience();
    drawScatterplot();
});

var bottom = 500;

function scaleExperience(){
	var experienceView = d3.select("#experienceView");
	var experienceSVG = d3.select("#experienceSVG");
	var experienceHeight = 500;
	var experienceWidth = 260;

	if(experienceSize == "Small"){
		experienceWidth= experienceWidth / 2;
		experienceHeight= experienceHeight / 2;
		experienceView.attr('transform', "scale(.5)");
	}else if(experienceSize == "Large"){
		experienceWidth= experienceWidth *1.5;
		experienceHeight= experienceHeight *1.5;
		experienceView.attr('transform', "scale(1.5)");
	}
	experienceSVG.attr('width', experienceWidth)
				.attr('height', experienceHeight);
}

function drawAxis(){

	var encouragment = ["You can do it!", "Keep going!", "New Hire", "Trainee", "Star Employee", "Manager", "Executive", "CEO"];
	var bonus_penalties = [{"key": 5, "awarded": "y"}, {"key": -13, "awarded": "y"}, {"key": 25, "awarded": "y"},
							{"key": 0, "awarded": "n"}, {"key": 5, "awarded": "n"}, {"key": 12, "awarded": "n"},
							{"key": 34, "awarded": "n"}];

	var encouragmentAxisScale = d3.scale.linear()
		.domain([0,7])
		.range([bottom-5, 10]);

	var bpAxisScale = d3.scale.linear()
		.domain([0,6])
		.range([bottom- 45, 45]);

	var encouragmentAxis = d3.svg.axis()
		.scale(encouragmentAxisScale)
		.orient('left')
		.tickFormat(function(d) { return encouragment[d];});

	var xAxisGrid = encouragmentAxis.ticks(encouragment.length)
	    .tickSize(165, 0)
	    .orient("right");

	var bpAxis = d3.svg.axis()
		.scale(bpAxisScale)
		.orient('right')
		.ticks(7)
		.tickFormat(function(d) { return bonus_penalties[d].key;});

	d3.select("#experienceAxis")
		.attr('class', 'axis')
		.call(encouragmentAxis);

	var bp_axis = d3.select("#bpAxis")
		.attr("transform", "translate(180,-12)")
		.attr('class', 'bp')
		.call(bpAxis);

	bp_axis.selectAll("text")
		.style('fill',  function(d) {
			if(bonus_penalties[d].key == 0){
				return 'black';
			} else if(bonus_penalties[d].key < 0){
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
	    	if(bonus_penalties[d].awarded == "y"){
				return '\uf023';
			}
		});
}

function drawExperience(){

	var courseDays = Math.round(dayDifference(parseDate(startDate),parseDate(endDate)));
	var currentDays = Math.round(dayDifference(parseDate(startDate),parseDate(date)));
	var redLineY = bottom - Math.round(currentDays / courseDays * bottom);

	var scale = d3.scale.linear()
		.domain([0, maxXP])
		.range([0, bottom]);

	var therm = d3.select("#experienceRect")
		.attr('y', bottom)
		.style("fill","white")
		.transition()
			.delay(2000)
			.style('fill', "steelblue")
			.attr('height', scale(experienceXP))
			.attr('y', bottom - scale(experienceXP))
			.duration(1000)
			.ease('bounce');

	var redLine = d3.select("#redLine")
		.attr('y', bottom - 10)
		.style("fill","red")
	    .transition()
	    	.delay(8000)
	    	.duration(1000)
	    	.attr('height', 5)
			.attr('y', redLineY);

	var bonus = d3.select("#eBonusRect")
		.attr('y', bottom - scale(experienceXP))
		.style("fill","white")
	    .transition()
	    	.delay(4000)
	    	.duration(1000)
	    	.style("fill","green")
	    	.attr('y', bottom - (scale(experienceXP) + scale(experienceBonus)))
	    	.attr('height', scale(experienceBonus))
	    	.ease('bounce');

	var penalties = d3.select("#ePenaltiesRect")
		.attr('y', bottom - (scale(experienceXP) + scale(experienceBonus)))
		.style("fill","white")
	    .transition()
	    	.delay(5000)
	    	.duration(1000)
	    	.style("fill","red")
	    	.attr('height', scale(experiencePenalties * -1))
	    	.ease('bounce');

	penalties.transition()
		.delay(6000)
		.duration(1000)
		.style("fill","white");

	bonus.transition()
		.delay(7000)
		.duration(1000)
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

	function parseDate(str){
		var mdy = str.split("/");
		return new Date(mdy[2], mdy[0]-1, mdy[1]);
	}
	function dayDifference(first, second){
		return (second-first)/(1000*60*60*24);
	}
	
}

function drawScatterplot() {
	var data = [[0.5,8790.777],[0.5,2788.271],[0.5,4887.234],[0.5,6386.836],[0.5,2879.017],[0.5,3969.253],
				[0.5,4765.566],[0.5,4960.24],[0.5,6729.363],[0.5,5004.666],[0.5,6659.622],[0.5,5591.625],
				[0.5,5715.239],[0.5,6911.882],[0.5,4918.968],[0.5,7027.656],[0.5,6886.025],[0.5,5505.728],
				[0.5,5133.118],[0.5,8170.039],[0.5,10500.49],[0.5,3930.065],[0.5,8262.328],[0.5,2429.093],
				[0.5,6734.244],[0.5,5348.829],[0.5,7117.302],[0.5,6532.128],[0.5,7019.704],[0.5,6557.395],
				[0.5,6128.633],[0.5,5936.047],[0.5,5208.552],[0.5,7662.147],[0.5,4216.855],[0.5,4051.146],
				[0.5,4594.671],[0.5,5232.17],[0.5,5527.529],[0.5,3768.33],[0.5,1850.974],[0.5,5433.962],
				[0.5,6689.143],[0.5,6588.201],[0.5,4924.478],[0.5,5016.182],[0.5,7483.872],[0.5,5083.348],
				[0.5,4208.795],[0.5,7069.633],[0.5,9485.622],[0.5,4243.505],[0.5,5573.407],[0.5,8020.497],
				[0.5,5798.24],[0.5,3991.015],[0.5,5168.429],[0.5,5297.4],[0.5,5168.307],[0.5,4331.118],
				[0.5,5497.109],[0.5,2304.076],[0.5,4894.777],[0.5,5244.005],[0.5,2974.623],[0.5,6294.117],
				[0.5,8567.073],[0.5,6220.729],[0.5,4985.493],[0.5,2547.244],[0.5,5861.808],[0.5,5806.616],
				[0.5,5683.785],[0.5,3398.168],[0.5,5410.605],[0.5,6388.737],[0.5,4988.897],[0.5,5320.751],
				[0.5,7844.465],[0.5,4357.029],[0.5,5764.447],[0.5,4955.536],[0.5,5486.629],[0.5,6855.486],
				[0.5,5253.601],[0.5,5791.596],[0.5,4866.246],[0.5,2647.759],[0.5,4968.463],[0.5,6450.722],
				[0.5,5895.416],[0.5,3614.017],[0.5,8303.46],[0.5,6228.541],[0.5,5278.108],[0.5,5020.427],
				[0.5,5133.305],[0.5,4858.765],[0.5,4765.443],[0.5,4053.07],[0.5,5827.915],[0.5,6520.09],
				[0.5,4923.848],[0.5,4345.71],[0.5,2284.347],[0.5,6356.664],[0.5,5061.347],[0.5,4122.123],
				[0.5,5993.034],[0.5,6095.256],[0.5,5496.54],[0.5,8368.834],[0.5,6622.121],[0.5,7152.15],
				[0.5,3556.442],[0.5,6559.931],[0.5,5523.795],[0.5,5863.989],[0.5,3268.911],[0.5,4014.486],
				[0.5,3985.323],[0.5,3395.702],[0.5,6042.845],[0.5,5564.969],[0.5,3413.684],[0.5,7372.445],
				[0.5,7715.168],[0.5,4022.355],[0.5,3492.689],[0.5,4431.584],[0.5,6291.16],[0.5,2251.378],
				[0.5,5066.56],[0.5,3719.415],[0.5,4382.818],[0.5,6079.261],[0.5,2429.923],[0.5,4675.077],
				[0.5,5566.87],[0.5,5635.83],[0.5,4657.67],[0.5,6056.476],[0.5,5358.288],[0.5,3071.424],
				[0.5,5094.233],[0.5,7329.341],[0.5,4286.011],[0.5,6142.051],[0.5,6553.975],[0.5,5535.082],
				[0.5,4836.723],[0.5,4615.781],[0.5,3864.957],[0.5,5391.707],[0.5,3264.854],[0.5,5189.918],
				[0.5,3696.263],[0.5,4911.441],[0.5,8098.538],[0.5,5490.824],[0.5,5312.101],[0.5,5521.122],
				[0.5,4157.464],[0.5,5136.687],[0.5,5726.737],[0.5,5219.359],[0.5,3356.764],[0.5,4805.295],
				[0.5,3775.352],[0.5,3396.589],[0.5,2109.082],[0.5,2547.71],[0.5,3909.6],[0.5,4852.336],
				[0.5,3285.731],[0.5,6375.632],[0.5,3552.5],[0.5,2940.961],[0.5,6516],[0.5,4404.961],
				[0.5,4475.708],[0.5,5309.994],[0.5,4137.869],[0.5,5107.718],[0.5,3986.276],[0.5,5860.806],
				[0.5,7406.773],[0.5,5074.19],[0.5,3962.946],[0.5,4987.309],[0.5,4214.576],[0.5,3777.626],
				[0.5,5643.626],[0.5,4079.575],[0.5,6618.036],[0.5,4459.49],[0.5,3874.239],[0.5,3450.216],
				[0.5,4152.931],[0.5,4234.611],[0.5,4113.628],[0.5,7036.857],[0.5,5059.207],[0.5,3247.364],
				[0.5,3652.257],[0.5,4740.53],[0.5,5608.272],[0.5,5404.751],[0.5,2510.119],[0.5,5836.572],
				[0.5,4994.293],[0.5,3191.484],[0.5,4452.976],[0.5,2487.747],[0.5,5652.411],[0.5,4841.05],
				[0.5,6294.898],[0.5,3881.13],[0.5,3205.065],[0.5,2110.822],[0.5,5956.884],[0.5,4635.556],
				[0.5,4871.005],[0.5,6779.729],[0.5,5141.2],[0.5,6687.344],[0.5,5263.328],[0.5,8119.635],
				[0.5,5451.058],[0.5,5432.999],[0.5,5586.183],[0.5,894.3492],[0.5,5772.726],[0.5,7381.811],
				[0.5,4466.445],[0.5,4534.751],[0.5,6227.939],[0.5,4709.905],[0.5,5709.955],[0.5,5877.774],
				[0.5,3994.753],[0.5,7166.214],[0.5,6422.32],[0.5,6583.847],[0.5,4984.612],[0.5,8390.458],
				[0.5,6234.258],[0.5,4440.383],[0.5,3445.775],[0.5,5798.742],[0.5,6612.999],[0.5,4979.907],
				[0.5,5368.945],[0.5,4087.23],[0.5,4283.303]]; 

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
		  .attr("cx", function (d,i) { return x(d[0]); } )
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

