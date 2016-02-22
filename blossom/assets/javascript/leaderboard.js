var scores = [];
$(document).ready(function(){
	getUserData();
	//scaleLeaderboard();
	makeAccordion();
	//animateAccordion();
});

function getUserData()
{
	idsArr=[];
	for(var i=0;i<=users.length-1;i++)// for(var i=0;i<=9;i++)
	{
		var currentStudent = users[i];
		idsArr.push(currentStudent.user_id);
		if((i!=0)&&(i%10==0))
		{//we'll send requests every 10 students.

			$.get("gradebook/getSetOfUsersMilestoneInfo",{experienceInstanceId:experienceInstanceId, userIds:(idsArr)},function(data,status,xhr)
			{
				scores= scores.concat(data);
			});

			var idsArr =[];//initialize the array again.
		}

	}
	if(idsArr.length>0)
	{
		$.get("gradebook/getSetOfUsersMilestoneInfo",{experienceInstanceId:experienceInstanceId, userIds:(idsArr)},function(data,status,xhr)
		{
			scores= scores.concat(data);
			console.log(scores);
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

function makeAccordion(){
	var tabs = [ "Platinum", "Diamond", "Gold", "Silver", "Bronze"];
	var scores = [
		{"score":8790, "place":0},{"score":2788, "place":0},{"score":4887, "place":0},{"score":6386, "place":0},{"score":2879, "place":0},
		{"score":3969, "place":0},{"score":4765, "place":0},{"score":4960, "place":0},{"score":6729, "place":0},{"score":5004, "place":0},
		{"score":6659, "place":0},{"score":5591, "place":0},{"score":5715, "place":0},{"score":6911, "place":0},{"score":4918, "place":0},
		{"score":7027, "place":0},{"score":6886, "place":0},{"score":5133, "place":0},{"score":8170, "place":0},{"score":10500, "place":0},
		{"score":3930, "place":0},{"score":8262, "place":0},{"score":2429, "place":0},{"score":7117, "place":0},{"score":6532, "place":0},
		{"score":7019, "place":0},{"score":6557, "place":0},{"score":7662, "place":0},{"score":4216, "place":0},{"score":4051, "place":0},
		{"score":5232, "place":0},{"score":5527, "place":0},{"score":3768, "place":0},{"score":1850, "place":0},{"score":5433, "place":0},
		{"score":6588, "place":0},{"score":4924, "place":0},{"score":5016, "place":0},{"score":7483, "place":0},{"score":5083, "place":0},
		{"score":7069, "place":0},{"score":9485, "place":0},{"score":5573, "place":0},{"score":8020, "place":0},{"score":5798, "place":0},
		{"score":3991, "place":0},{"score":5168, "place":0},{"score":5297, "place":0},{"score":4331, "place":0},{"score":5968, "place":0},
	];
	var aliases = [
		"Acura", "Audi", "Bentley", "BMW", "Buick", 
		"Cadillac", "Chevrolet", "Chrysler", "Dodge", "Ferrari",
		"Fait", "Ford", "Geo", "GMC", "Honda", 
		"Hummer", "Hyundai", "Infiniti", "Isuzu", "Jaguar", 
		"Jeep", "Kia", "Lamborghini", "Land Rover", "Lexus", 
		"Lincoln", "Mazda", "Mercedes", "Mini", "Mitsubishi", 
		"Mercury", "Nissan", "Oldsmobile", "Packard", "Plymouth", 
		"Pontiac", "Porsche", "Ram", "Rolls-Royce", "Saleen", 
		"Saturn", "Shelby", "Smart", "Subaru", "Suzuki", 
		"Tesla", "Toyota", "VW", "Volvo", "Yamaha"
	];

	scores.sort(sortNumber);
	shuffle(aliases);

	var studentScore = 6911;
	var count;

	for (var i = 0; i < scores.length; i++) {
		if(scores[i].score > studentScore){
			scores[i].place = 2;
		} else if(scores[i].score == studentScore){
			count = i;
		}else if(scores[i].place < studentScore){
			scores[i].place = -2;
		}

	}
	scores[count -1].place = 1;
	scores[count +1].place = -1;

	var contentNumber = scores.length / tabs.length;

	for (var i = 0; i < tabs.length; i++) {

		var tab = document.createElement('div');
		tab.className='accordionTab';
		tab.innerHTML="<a class='accordionTabTitle' href='#tab-" + i + "'>" + tabs[i] + "</a>";

		var content = document.createElement('div');
		content.className='accordionTabContent';
		content.id="tab-" + i;
		content.style.overflow='auto';
		content.style.height= '250px';
		for (var j = contentNumber * (i); j < contentNumber * (i+1); j++) {
			var student = document.createElement('div');
			var competition;
			if(scores[j].place == 2){
				competition = "Your Competition";
				student.className='accordionTabContentStudent';
			}else if(scores[j].place == 1){
				competition = "Your Next Conquest!";
				student.className='accordionTabContentStudent';
			}else if(scores[j].place == 0){
				competition = "You";
				student.className='accordionTabContentStudentCurrent';
				tab.innerHTML="<a class='accordionTabTitle active' href='#tab-" + i + "'>" + tabs[i] + "</a>";
				content.className='accordionTabContent open';
				content.style.display='block';
			}else if(scores[j].place == -1){
				competition = "Your Last Conquest!";
				student.className='accordionTabContentStudent';
			}else if(scores[j].place == -2){
				competition = "Conquered!";
				student.className='accordionTabContentStudent';
			}else{
				competition = "Error";
				student.className='accordionTabContentStudent';
			}
			
			var html ="";
			html +=	"<span class = 'studentRank'>";
			html +=	(j + 1);
			html += "</span>";
			html += "<span style =' display:inline-block;'>";
			html +=		"<b>" + aliases[j] + "</b> <br> ";
			html += 	competition;

			html += "</span>";
			html += "<span style ='display:inline-block; float:right; padding-right: 10px;'>";
			html +=		" <br>" + scores[j].score + " pts.";
			html +=	"</span>";
			student.innerHTML=html;
			content.appendChild(student);
		};

		tab.appendChild(content);
		document.getElementById("accordion").appendChild(tab);
	};

	function shuffle(array) {
	  var currentIndex = array.length, temporaryValue, randomIndex ;

	  // While there remain elements to shuffle...
	  while (0 !== currentIndex) {

	    // Pick a remaining element...
	    randomIndex = Math.floor(Math.random() * currentIndex);
	    currentIndex -= 1;

	    // And swap it with the current element.
	    temporaryValue = array[currentIndex];
	    array[currentIndex] = array[randomIndex];
	    array[randomIndex] = temporaryValue;
	  }

	  return array;
	}

	function sortNumber(a,b) {
	    return b.score - a.score;
	}
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
			$('#accordion ' + currentAttrValue).slideDown(100).addClass('open'); 
		}
 
		e.preventDefault();
	});
}