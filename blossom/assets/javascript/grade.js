$(document).ready(function(){
    scaleGrade();
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