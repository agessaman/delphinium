$(document).ready(function(){
    scaleGrade();
    calculateGrade();
});

function scaleGrade(){
	var dataFont;
	var labelFont;
	if(gradeSize == "small"){
		dataFont = "10px";
		labelFont = "7.5px";
	}else if(gradeSize == "medium"){
		dataFont = "20px";
		labelFont = "14px";
	}else{
		dataFont = "24px";
		labelFont = "20px";
	}
	var dataClass = document.getElementsByClassName("data");
	for (var i = 0; i < dataClass.length; i++) {
	    dataClass[i].style.fontSize = dataFont;
	}
	var labelClass = document.getElementsByClassName("labels");
	for (var i = 0; i < labelClass.length; i++) {
	    labelClass[i].style.fontSize = labelFont;
	}
	document.getElementById("grade").style.fontSize = dataFont;
}

function calculateGrade(){
	
	var achieved = xp + gradeBonus;
	var letterGrade;

	switch(true)
	{
		case (achieved >=thresholds[0][1]):
			letterGrade = thresholds[0][0];
			break;
		case (achieved >=thresholds[1][1]):
			letterGrade = thresholds[1][0];
			break;
		case (achieved >=thresholds[2][1]):
			letterGrade = thresholds[2][0];
			break;
		case (achieved >=thresholds[3][1]):
			letterGrade = thresholds[3][0];
			break;
		case (achieved >=thresholds[4][1]):
			letterGrade = thresholds[4][0];
			break;
		case (achieved >=thresholds[5][1]):
			letterGrade = thresholds[5][0];
			break;
		case (achieved >=thresholds[6][1]):
			letterGrade = thresholds[6][0];
			break;
		case (achieved >=thresholds[7][1]):
			letterGrade = thresholds[7][0];
			break;
		case (achieved >=thresholds[8][1]):
			letterGrade = thresholds[8][0];
			break;
		case (achieved >=thresholds[9][1]):
			letterGrade = thresholds[9][0];
			break;
		case (achieved >=thresholds[10][1]):
			letterGrade = thresholds[10][0];
			break;
		case (achieved >=thresholds[11][1]):
			letterGrade = thresholds[11][0];
			break;
		default:
			letterGrade = thresholds[12][0];  
	}

	if(gradeBonus >= 0){
		document.getElementById("bonus").innerHTML = "+ " + roundToTwo(gradeBonus);
	}else{
		document.getElementById("bonus").innerHTML = roundToTwo(gradeBonus);
	}

	document.getElementById("total").innerHTML = roundToTwo(achieved);
	document.getElementById("grade").innerHTML = letterGrade;


}


function roundToTwo(num) {
    return +(Math.round(num + "e+2") + "e-2");
}