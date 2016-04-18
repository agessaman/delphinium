/*
 * Copyright (C) 2012-2016 Project Delphinium - All Rights Reserved
 *
 * This file is subject to the terms and conditions defined in
 * file 'https://github.com/ProjectDelphinium/delphinium/blob/master/EULA',
 * which is part of this source code package.
 *
 * NOTICE:  All information contained herein is, and remains the property of Project Delphinium. The intellectual and technical concepts contained
 * herein are proprietary to Project Delphinium and may be covered by U.S. and Foreign Patents, patents in process, and are protected by trade secret or copyright law.
 * Dissemination of this information or reproduction of this material is strictly forbidden unless prior written permission is obtained
 * from Project Delphinium.
 *
 * THE RECEIPT OR POSSESSION OF THIS SOURCE CODE AND/OR RELATED INFORMATION DOES NOT CONVEY OR IMPLY ANY RIGHTS
 * TO REPRODUCE, DISCLOSE OR DISTRIBUTE ITS CONTENTS, OR TO MANUFACTURE, USE, OR SELL ANYTHING THAT IT  MAY DESCRIBE, IN WHOLE OR IN PART.
 *
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Non-commercial use only, you may not charge money for the software
 * You can modify personal copy of source-code but cannot distribute modifications
 * You may not distribute any version of this software, modified or otherwise
 */

$(document).ready(function(){
	div = d3.select("body").append("div")
		.attr("class", "tooltip")
		.style("opacity", 0);
	scaleGrade();

	$( "#iGradeTooltip" ).mouseenter(function(event) {

			console.log(gradingScheme);
			var str = "<table class='table table-condensed table-gradingScheme'><thead> <tr> <th>Points</th> <th>Letter Grade</th></tr> </thead> <tbody> ";
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