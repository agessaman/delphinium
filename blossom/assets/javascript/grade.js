$(document).ready(function(){
	div = d3.select("body").append("div")
		.attr("class", "tooltip")
		.style("opacity", 0);
	scaleGrade();

	$( "#iGradeTooltip" ).mouseenter(function(event) {
			var str = "<table class='table table-condensed table-gradingScheme'><thead> <tr> <th>Value</th> <th>Letter Grade</th></tr> </thead> <tbody> ";
			for(var i=0;i<=gradingScheme.length-1;i++)
			{
				var item = gradingScheme[i];
				str+="<tr><td>"+item.value+"</td> <td>"+item.name+"</td> </tr>";
			}
			str+="</tbody> </table>";
			addGradeTooltip(str, event);
		})
		.mouseleave(function() {
			removeGradeTooltip();
		});
});

function scaleGrade(){
	var multiplier = gradeSize/100;

	var dataFont = 20 * multiplier;
	var labelFont = 14 * multiplier;

	var dataClass = document.getElementsByClassName("data");
	for (var i = 0; i < dataClass.length; i++) {
		dataClass[i].style.fontSize = dataFont+"px";
	}
	var labelClass = document.getElementsByClassName("labels");
	for (var i = 0; i < labelClass.length; i++) {
		labelClass[i].style.fontSize = labelFont+"px";
	}
	document.getElementById("grade").style.fontSize = dataFont;
}


function addGradeTooltip(text, event)
{
	div.transition()
		.duration(200)
		.style("opacity", .9);
	div.html(text)
		.style("left", (event.pageX) + "px")
		.style("top", (event.pageY - 28) + "px");
}


function removeGradeTooltip()
{
	div.transition()
		.duration(500)
		.style("opacity", 0);
}