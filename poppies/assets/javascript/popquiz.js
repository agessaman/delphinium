/* popquiz.js 

	Pop Quiz Game:
	Instructor
		choose which quiz to use from getAllQuizzes
		un-published quzzes are available in list
			click quiz to see questions 
		select questions to use in game
			can choose questions from multiple quizzes
			questions are added to db? or ids?
			? is it possible to get question_banks instead?
			
		select total points for game in the assignment
		add Intro text in assignment, used in game
		
		each question has points_possible but is not used
		choose game type from list [YouGotThis, ...]?
		possibly let the Learner choose which game?
		un-publish quiz chosen?
		preview of game with questions chosen
	
	Learner:
	let the Learner choose which game?
		get Questions from db?
		see Intro text, Play game
		
		pass back points for LTI assignment, not a single quiz
		
		
*/

// Instructor view vars & functions ONLY?
// globals
var selecteditems=[];// quiz question_id selections
var chosenitems=[];// question_id of gameitems to use, remove clear all
var gameitems=[];// questions selected for game
var quests=[];// quiz questions to selected from

function validateQuizzes()
{
	/* remove quizzes that do not have questions from quizList
		should do this in php
	*/
	var tempList = $.grep(quizList, function(elem,index){
		return elem.questions.length > 0;
	});
	quizList = tempList;
}
/* show all valid quizzes */
function showQuizzes()
{
	validateQuizzes();// remove quizzes with no questions
	//console.log('quizList.length:',quizList.length);
	
	/* put 1/3 in each column inc to count, reset : inc colm */
	var count = Math.ceil(quizList.length/3);
	var col=0;
	var row=0;
	for(var i=0; i<quizList.length; i++)
	{
		content = '<div id='+quizList[i].quiz_id+' class="alert alert-info">';// blue
		content += quizList[i].title;
		content += ': total questions: '+quizList[i].questions.length;//.question_count;
		//content += ' worth: '+quizList[i].points_possible;
		content += '</div>';
		$('#col_'+col).append(content);
		row++;
		if(row==count){ row=0; col++; }
	}
	//click quiz to view questions
	$('.alert').on('click', function(e){
		showQuizQuestions(e.target.id);// quiz_id
	});
	// open All Quizzes panel
	$('#accordion-1').addClass('active');
	$('.accordion #accordion-1').slideDown(300).addClass('open');
}

/*	show all questions from selected quiz
	Questions are selectable or all
	submit btn adds questions to gameitems
*/
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
	
	// show the quiz questions in modal when clicked?
	$('#quizselectable').empty();
	selecteditems=[];// no questions selected
	for(var i=0; i<quests.length; i++)
	{
		var txt = quests[i].text;
		//console.log(txt);// &lt;strong&gt; ...
		txt = $.parseHTML(txt);
		txt = txt[0].textContent;
		//console.log(txt);//<strong>
		$('#quizselectable').append('<li class="ui-widget-content" data-id="'+quests[i].question_id+'">'+txt+'</li>');
		selecteditems.push(quests[i].question_id);// all questions selected
	}
	
	//http://api.jqueryui.com/1.12/selectable/#entry-examples
	close_accordion_section();
	$('#accordion-2').addClass('active');
	// Open up the hidden content panel
	$('.accordion #accordion-2').slideDown(300).addClass('open');
}

/* Display questions selected for game */
function showSelected()
{
    //gameitems [question, ]
    $('#gameselectable').empty();
	$('#questcount').html(gameitems.length+' Questions');
	chosenitems=[];// none yet
    for(var i=0; i<gameitems.length; i++)
	{
		var txt = gameitems[i].text;//.toString();
		//console.log(txt);// &lt;strong&gt; ...
		txt = $.parseHTML(txt);
		txt = txt[0].textContent;
		//console.log(txt);//<strong>
		$('#gameselectable').append('<li class="ui-widget-content" data-id="'+gameitems[i].question_id+'">'+txt+'</li>');
		chosenitems.push(gameitems[i].question_id);
	}
	
	close_accordion_section();
	$('#accordion-4').addClass('active');
	$('.accordion #accordion-4').slideDown(300).addClass('open');
}

function close_accordion_section() {
	$('.accordion .accordion-section-title').removeClass('active');
	$('.accordion .accordion-section-content').slideUp(100).removeClass('open');
}
