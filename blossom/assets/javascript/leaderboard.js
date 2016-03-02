// calling_user.alias = 'General Electric';


var scores = [];
$(document).ready(function(){
	getUserData();
});

function getUserData()
{
	idsArr=[];
	// for(var i=0;i<=19;i++)
	for(var i=0;i<=users.length-1;i++)// for(var i=0;i<=9;i++)
	{
		var currentStudent = users[i];
		idsArr.push(currentStudent.user_id);
		if((i!=0)&&(i%10==0))
		{//we'll send requests every 10 students.

			$.get("gradebook/getSetOfUsersTotalScores",{experienceInstanceId:experienceInstanceId, userIds:(idsArr)},function(data,status,xhr)
			{
				scores= scores.concat(data);
			});
			var idsArr =[];//initialize the array again.
		}

	}
	if(idsArr.length>0)
	{
		var promise =$.get("gradebook/getSetOfUsersTotalScores",{experienceInstanceId:experienceInstanceId, userIds:(idsArr)});
		promise.then(function (data, textStatus, jqXHR, data2) {
				scores= scores.concat(data);
				document.getElementById("spinnerDiv").style.display = "none";
				makeAccordion();
			})
			.fail(function (data2) {
			});
	}
}
function scaleLeaderboard(){
	var height,width;
	if(leaderboardSize == "Small"){
		height = "350px";
		width = "200px";
	}else if(leaderboardSize == "Medium"){
		height = "525px";
		width = "300px";
	}else{
		height = "787px";
		width = "450px";
	}
	document.getElementById("accordion").style.height = height;
	document.getElementById("accordion").style.width = width;
}

function makeAccordion() {
	var tabs = ["Platinum", "Diamond", "Gold", "Silver", "Bronze"];
	scores.sort(sortNumber);

	//find this user's score in the array of scores
	var studentScore = 0;
	var obj = scores.filter(function (obj) {
		//add each prereq to an array
		if (obj.alias === calling_user.alias)
		{
			return obj;
		}
	})[0];
	if (obj !== undefined)
	{
		studentScore = obj.score;
	}

	var count = 0;
	for (var i = 0; i < scores.length; i++) {
		if (scores[i].score > studentScore) {
			scores[i].place = 2;
		} else if (scores[i].score == studentScore) {
			count = i;
		} else if (scores[i].place < studentScore) {
			scores[i].place = -2;
		}

	}

	if(count>0)
	{
		scores[count - 1].place = 1;
	}
	if(count<scores.length)
	{
		scores[count + 1].place = -1;
	}

	if(tabs.length>scores.length)
	{
		tabs = tabs.slice(0,scores.length);
	}

	var contentNumber = parseInt(scores.length / tabs.length);
	for (var i = 0; i < tabs.length; i++) {
		var tab = document.createElement('div');
		tab.className = 'accordionTab panel panel-default';
		//<a class="accordion-toggle" data-toggle="collapse" data-parent="#accordion" href="#collapseOne">
		tab.innerHTML = "<a class='accordionTabTitle' data-toggle='collapse' data-parent='#accordion' href='#tab-" + i + "'>" + tabs[i] + "</a>";

		//<div id="collapseOne" class="panel-collapse collapse in" role="tabpanel" aria-labelledby="headingOne">
		//<div id="collapseOne" class="panel-collapse collapse in">
		var content = document.createElement('div');
		content.className = 'accordionTabContent panel-collapse collapse';
		content.id = "tab-" + i;

		var rank = 1;
		for (var j = contentNumber * (i); j < contentNumber * (i + 1); j++)
		{
			var student = document.createElement('div');
			var competition;
			var className = 'accordionTabContentStudent panel-body';
			if (scores[j].place == 2) {
				competition = "Your Competition";
			} else if (scores[j].place == 1) {
				competition = "Your Next Conquest!";
			} else if (scores[j].place == 0) {
				competition = "You";
				className = 'accordionTabContentStudentCurrent';
				tab.innerHTML = "<a class='accordionTabTitle active' data-toggle='collapse' data-parent='#accordion'  href='#tab-" + i + "'>" + tabs[i] + "</a>";
				content.className = 'accordionTabContent panel-collapse collapse in';
				content.style.display = 'block';
			} else if (scores[j].place == -1) {
				competition = "Your Last Conquest!";
			} else if (scores[j].place == -2) {
				competition = "Conquered!";
			} else {
				competition = "Error";
			}

			student.className = className;
			var html = "";
			html += "<div class = 'contentContainer'>";
			html += "<div class = 'studentRank'>";
			html += (rank);
			html += "</div>";
			html += "<div class = 'rightDiv'>";
			html += roundToTwo(scores[j].score) + " pts.";
			html += "</div>";
			html += "<div class = 'centerDiv'>";
			html += "<b>" + scores[j].alias + "</b><br/>";
			html += competition;
			html += "</div>";
			html += "</div>";
			student.innerHTML = html;
			content.appendChild(student);

			if(rank%contentNumber==0)
			{
				rank = 1;
			}
			else{
				rank++;
			}
		}
		;
		tab.appendChild(content);
		document.getElementById("accordion").appendChild(tab);
	}
}


function sortNumber(a,b) {
	return b.score - a.score;
}

function roundToTwo(num) {
	return +(Math.round(num + "e+2")  + "e-2");
}

function animateAccordion(){

	function closeContent() {
		$('#accordion .accordionTabTitle').removeClass('active');
		$('#accordion .accordionTabContent').slideUp(100).removeClass('open');
	}

	$('.accordionTabTitle').click(function(e) {
		// Grab current anchor value
		var currentAttrValue = $(this).attr('href');

		if($(e.target).is('.active')) {
			closeContent();
		}else {
			closeContent();

			// Add active class to tab title
			$(this).addClass('active');
			// Open up the hidden content panel
			//$('#accordion ' + currentAttrValue).slideDown(100).addClass('open');
		}

		e.preventDefault();
	});
}