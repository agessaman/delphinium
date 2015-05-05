$(document).ready(function(){
	scaleCompetencies();
	drawCompetencies();
});

function scaleCompetencies(){

}

function drawCompetencies(){
	var objectives = [{"name": "Teamwork", "value": 185}, {"name": "Delegation", "value": 60}, 
					  {"name": "Feedback", "value": 225}, {"name": "Vocabulary", "value": 125}];

	var competencies = d3.select("#competencies").append("svg")
		.attr('height', 200)
		.attr('width', 250);

	for (var i = 0; i < objectives.length; i++) {
		competencies.append('text')
			.text(objectives[i].name)
			.attr('y', i * 50 + 10);

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
					
		competencies.append('rect')
				.attr('height', 25)
				.attr('width', 250)
				.attr('stroke-width', "2")
				.attr('stroke', "black")
				.attr('fill', "none")
				.attr('y', i * 50 + 20);
	};
		

}