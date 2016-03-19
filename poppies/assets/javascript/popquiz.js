/* popquiz.js 

	Pop Quiz Game:
	Instructor
		choose which quiz to use from getAllQuizzes
			click quiz to see questions 
		select questions to use 
			questions are added to db? or ids?
			select questions from multiple quizzes
		select total points for game
			
		choose # of questions to use from total
		add Intro text, used in game
		each question has points_possible
			adjust total points or use total possible
		choose game type from list [YouGotThis, ...]
		un-publish quiz chosen?
		preview of game with questions chosen
	
	Learner:
		see Intro text, Play game
		getQuizQuestions
		pass back points for LTI assignment, not quiz
		
		
	http://inspirationalpixels.com/tutorials/creating-an-accordion-with-html-css-jquery
*/

// Instructor view
// globals
var selecteditems=[];// quiz question_id selections
var chosenitems=[];// question_id of gameitems to use, remove clear all
var gameitems=[];// questions for game
var quests=[];// quiz questions to selected from
// gameitems = '{items:[ obj, obj, ... ]}';


/* show all quizzes */
function showQuizzes()
{
	var count = Math.ceil(quizList.length/3);
	//console.log('count:',count);
	// put 1/3 in each column
	//inc to count, reset : inc colm
	//console.log('quizList.length:',quizList.length);
	var col=0;
	var row=0;
	for(var i=0; i<quizList.length; i++)
	{
		if(quizList[i].questions.length == 0) { continue; }
		content = '<div id='+quizList[i].quiz_id+' class="alert alert-info">';// blue
		content += quizList[i].title;
		content += ': total questions: '+quizList[i].question_count;
		//content += ' worth: '+quizList[i].points_possible;
		content += '</div>';
		$('#col_'+col).append(content);
		row++;
		if(row==count){ row=0; col++; }
	}
	/*
		store which quiz the questions came from?
	*/
	//click quiz to view questions
	$('.alert').on('click', function(e){
		showQuizQuestions(e.target.id);
	});
	// open All Quizzes
	$('#accordion-1').addClass('active');
	// Open content panel
	$('.accordion #accordion-1').slideDown(300).addClass('open');
}

/* show all questions in selected quiz */
function showQuizQuestions(id)
{
	// fill id=quizdata with selected quiz and show it
	// qtitle quiz_details, quizdata
	console.log('view quiz_id:'+id);
	var quiz= $.grep(quizList, function(elem, indx){
        return elem.quiz_id == id; }
	);
	console.log('quiz:', quiz);
	quests=quiz[0].questions;
	//console.log('quests keys:',Object.keys(quests));
	console.log('quests:',quests);
	//properties of quiz, title, q_count, points, id?
	$('#qtitle').text(quiz[0].title+' Points: '+quiz[0].points_possible);
	//var details = 'Points: '+quiz[0].points_possible;
	//$('#quiz_details').html(details);
	
	// show the quiz questions in modal when clicked?
	// needs 2 columns or ?
	$('#quizselectable').empty();
	var i=0;
	for(var obj in quests)
	{
		var txt = quests[i].text;
		//console.log(txt);// &lt;strong&gt; ...
		txt = $.parseHTML(txt);
		txt = txt[0].textContent;
		//console.log(txt);//<strong>
		$('#quizselectable').append('<li class="ui-widget-content" data-id="'+quests[i]["question_id"]+'">'+txt+'</li>');
		i+=1;// select by question_id
	}
	
	//http://api.jqueryui.com/1.12/selectable/#entry-examples
	close_accordion_section();
	$('#accordion-2').addClass('active');
	// Open up the hidden content panel
	$('.accordion #accordion-2').slideDown(300).addClass('open');
	// select btn chooses this quiz for game
}

/* Display selected questions for game */
function showSelected()
{
    //gameitems [question, ]
    $('#gameselectable').empty();
	$('#questcount').html(gameitems.length+' Questions');
    for(var i=0; i<gameitems.length; i++)
	{
		var txt = gameitems[i][0].text;//.toString();
		//console.log(txt);// &lt;strong&gt; ...
		txt = $.parseHTML(txt);
		txt = txt[0].textContent;
		//console.log(txt);//<strong>
		$('#gameselectable').append('<li class="ui-widget-content" data-id="'+gameitems[i][0].question_id+'">'+txt+'</li>');
	}
	
	close_accordion_section();
	$('#accordion-4').addClass('active');
	// Open content panel
	$('.accordion #accordion-4').slideDown(300).addClass('open');
}

function close_accordion_section() {
	$('.accordion .accordion-section-title').removeClass('active');
	$('.accordion .accordion-section-content').slideUp(100).removeClass('open');
}
