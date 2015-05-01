$(document).ready(function(){
	scaleBonus();
    drawBonus();
});

var textSize;

function scaleBonus(){
	var bonusView = d3.select("#bonusView");
	var bonusSVG = d3.select("#bonusSVG");
	var bonusHeight = 115;
	var bonusWidth = 340;
	if(bonusSize == "Small"){
		bonusSVG.attr('width', bonusWidth / 2)
				.attr('height', bonusHeight / 2);
		bonusView.attr('transform', "scale(.5)");
	}else if(bonusSize == "Medium"){
		bonusSVG.attr('width', bonusWidth)
				.attr('height', bonusHeight);
	}else{
		bonusSVG.attr('width', bonusWidth * 1.5)
				.attr('height', bonusHeight * 1.5);
		bonusView.attr('transform', "scale(1.5)");
	}
}

function drawBonus(){
	
	var origin = 170;
	var textx = origin;
	var height = 115;

	var minScale = d3.scale.sqrt()
		.domain([minBonus, .001])
		.range([163, 0]);
		
	var maxScale = d3.scale.sqrt()
		.domain([.001, maxBonus])
		.range([0, 163]);
	
	var rectangle = d3.select("#bonusRect");

	rectangle.style("fill","white")
			.attr('x', origin);

	if(bonusAnimate){
		if(bonus == 0){

		}
		if(bonus <0){
			textx = origin - (minScale(bonus) / 2);
				rectangle.transition()
					.delay(1000)
					.style('fill', "#FF4747")
					.attr('width', minScale(bonus))
					.attr('x', origin - minScale(bonus))
					.duration(1000)
					.ease('bounce');
		} else{
			textx = origin + (maxScale(bonus) / 2);
				rectangle.transition()
					.delay(1000)
					.style('fill', "#66FF33")
					.attr('width', maxScale(bonus))
					.attr('x', origin)
					.duration(1000)
					.ease('bounce');
		}
	}else{
		if(bonus == 0){

		}
		if(bonus <0){
			textx = origin - (minScale(bonus) / 2);
					rectangle.style('fill', "#FF4747")
					.attr('width', minScale(bonus))
					.attr('x', origin - minScale(bonus));
		} else{
			textx = origin + (maxScale(bonus) / 2);
					rectangle.style('fill', "#66FF33")
					.attr('width', maxScale(bonus))
					.attr('x', origin);
		}
	}


	var text = d3.select('#bonusView').append("text")
      .attr("fill", "black")
      .style("text-anchor", "middle")
      .attr('font-size', "20px")
      .attr('x', textx)
      .attr('y', height/2)
      .text(bonus);

}

