$(document).ready(function(){
	scaleCompetencies();
	drawCompetencies();
});

function scaleCompetencies(){
	var competenciesView = d3.select("#competenciesView");
	var competenciesSVG = d3.select("#competenciesSVG");
	var competenciesHeight = 200;
	var competenciesWidth = 250;
	if(competenciesSize == "Small"){
		competenciesSVG.attr('width', competenciesWidth / 2)
				.attr('height', competenciesHeight / 2);
		competenciesView.attr('transform', "scale(.5)");
	}else if(competenciesSize == "Medium"){
		competenciesSVG.attr('width', competenciesWidth)
				.attr('height', competenciesHeight);
	}else{
		competenciesSVG.attr('width', competenciesWidth * 1.5)
				.attr('height', competenciesHeight * 1.5);
		competenciesView.attr('transform', "scale(1.5)");
	}
}

function drawCompetencies(){
	var objectives = [{"name": "Teamwork", "value": 185}, {"name": "Delegation", "value": 60}, 
					  {"name": "Feedback", "value": 225}, {"name": "Vocabulary", "value": 125}];

	var competencies = d3.select("#competenciesView");

	for (var i = 0; i < objectives.length; i++) {
		competencies.append('text')
			.text(objectives[i].name)
			.attr('y', i * 50 + 10);

		if(competenciesAnimate == true){
			competencies.append('rect')
					.attr('height', 25)
					.attr('width', 0)
					.attr('fill', "white")
					.attr('y', i * 50 + 20)
					.transition()
						.delay(2000)
						.duration(1000)
						.attr('width', objectives[i].value)
						.attr('fill', "orange")
						.ease('bounce');
		} else {
			competencies.append('rect')
					.attr('height', 25)
					.attr('width', 0)
					.attr('fill', "white")
					.attr('y', i * 50 + 20)
					.attr('width', objectives[i].value)
					.attr('fill', "orange");
		}
					
					
		competencies.append('rect')
				.attr('height', 25)
				.attr('width', 250)
				.attr('stroke-width', "2")
				.attr('stroke', "black")
				.attr('fill', "none")
				.attr('y', i * 50 + 20);
	};
		

}