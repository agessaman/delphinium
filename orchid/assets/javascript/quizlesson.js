/*
	Quiz Lessons:
	Instructor actions:
	-select a quiz to use from all valid
	save id and get the questions
		-ability to choose another quiz?
		-would replace questions already placed on page? by index
	
	-place a group of questions
	-place a text field between question groups
	-place a quiz submit button after all questions placed
	
	
*/

/* Delphinium functions:
	set up the popover content text and activate it*/
	$('#popinfo').attr("data-content","Here is some amazing content. It's very engaging. Right?");
    $('#popinfo').popover();// activate info
    /* set id & course for the POST if they are hidden in fields.yaml
        Add hidden input fields so they will transfer to onUpdate
        if a field is set to hidden: true it does not appear in the form at all
    */
    $('#Form-outsideTabs').append('<input type="hidden" name="Quizlesson[id]" value="'+config.id+'" /> ');
    $('#Form-outsideTabs').append('<input type="hidden" name="Quizlesson[course_id]" value="'+config.course_id+'" /> ');
    
	function completed(data)
	{
        /* updated record is returned */
        location.reload();
	}
/* End Delphinium functions*/

/* Quizlesson functions*/
//var quizList = {{quizList|raw}};// all quizzes
//console.log('quizList:', quizList.length, quizList); 
var quests=[];// quiz questions to select from
var selectedQuiz='';// quiz_id
var selecteditems=[];// quiz question_id array
var nextcount=0;// index for question details modal
//$config->quiz_id = '';
if(config.quiz_id != '') {
	selectedQuiz = config.quiz_id;
	showSelected(selectedQuiz);// from quiz_id
} else { showQuizzes(); }

//showQuizzes();// all quizzes to choose from
 
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
		selectedQuiz=e.target.id;// quiz_id
		config.quiz_id = selectedQuiz;
		$('#Form-field-Quizlesson-quiz_id').val(selectedQuiz);
		
		showQuizQuestions(selectedQuiz);
	});
}

function showQuizQuestions(id)
{
	// close quiz selector quizlist
	$('#quizlist').hide();
	
	console.log('view quiz_id:'+id);
	
	var quiz= $.grep(quizList, function(elem, indx){
        return elem.quiz_id == id; }
	);
	console.log('quiz:', quiz);
	config.quiz_name = quiz[0].title;
	$('#Form-field-Quizlesson-quiz_name').val(config.quiz_name);
	quests=quiz[0].questions;
	console.log('quests:',quests);
	//properties: quiz_id, title, q_count, points, description
	$('#qtitle').text(quiz[0].title+' Points: '+quiz[0].points_possible);
	
	// show the quiz questions
	$('#questionlist').empty();
	selecteditems=[];// no questions selected
	for(var i=0; i<quests.length; i++)
	{
		var txt = quests[i].text;
		//console.log(txt);// &lt;strong&gt; ...
		txt = $.parseHTML(txt);
		txt = txt[0].textContent;
		//console.log(txt);//<strong>
		
		// hide when selected, track id of selecteditems 
		var content = '<div class="questitem" data-id="'+quests[i].question_id+'">';
			content+= '<input type="checkbox" class="useit" name="useit" value="'+quests[i].question_id+'"> ';//checkbox
			content+= '<a id="'+i+'" class="seeit" href="#"><i class="icon-star"></i></a> ';// view details icon
			content+= txt;
			content+= '</div>';
		$('#questionlist').append(content);
		selecteditems.push(quests[i].question_id);// all questions selected
	}
	//http://api.jquery.com/prop/
	$('.seeit').on('click',function(e){
		//console.log(e.currentTarget.id);
		e.preventDefault();
		nextcount=e.currentTarget.id;
		constructQuestion(nextcount);
		$('#detailed').modal('show');
	});
}
	
// see question details 
    $('#nextbtn').click(function(e) {
        e.preventDefault();
        //detailed-body replace content with next question
		nextcount++;
		if(nextcount==quests.length){ nextcount=0; }
		constructQuestion(nextcount);
    });// backbtn ?
	/* also see selected questions accordion-2 quests[]
	   currently only game questions quests[]
	   index is first selected question to see in #detailed modal
       construct: type, points, question, answers and comments
	*/
	function constructQuestion(index)
	{	
		var quest = quests[index];
		$('#detailed-title').html('Question '+(index+1));
		$('#qtype').html('Type: '+quests[index].type);
		$('#qpoints').html('Points: '+quests[index].points_possible);
		var txt = quests[index].text;//.toString();
			txt = $.parseHTML(txt);
			txt = txt[0].textContent;
		$('#qtext').html(txt);
		var answers= $.parseJSON(quests[index].answers);
		var ansdiv='';
		//console.log(answers);//for each
		for(var i=0; i<answers.length; i++)
		{
			if(answers[i].weight==0){
				ansdiv+='<div class="alert alert-danger">';
			} else {
				ansdiv+='<div class="alert alert-success">';
			}
			ansdiv+=answers[i].text;
			ansdiv+='</div>';
		}
		$('#qanswers').html('<hr/>Answers:<br/>'+ansdiv);
		
		var comdiv='';
		if(quests[index].correct_comments!= "")
		{
			comdiv+='<div class=bg-success>';
			comdiv+=quests[index].correct_comments;
			comdiv+='</div>';
		}
		if(quests[index].incorrect_comments != "")
		{
			comdiv+='<div class=bg-danger>';
			comdiv+=quests[index].incorrect_comments;
			comdiv+='</div>';
		}
		if(quests[index].neutral_comments != "")
		{
			comdiv+='<div class=bg-warning>';
			comdiv+=quests[index].neutral_comments;
			comdiv+='</div>';
		}
		$('#qfeedback').html('<hr/>Comments:<br/>'+comdiv);
	}

/* question object
    answers: "[
        {"weight":0,"id":2028,"migration_id":"RESPONSE_1113","text":"Organizational change is..."},
        {"weight":60,"id":5595,"migration_id":"RESPONSE_2746","text":"Organizational change..."},
        {"weight":40,"id":6439,"migration_id":"RESPONSE_8703","text":"Organizational change..."},
        {"weight":0,"id":5998,"migration_id":"RESPONSE_1895","text":"Organizational change..."}]"
    
    name: "1.a Which of the followi"
    incorrect_comments: ""
    correct_comments: ""
    neutral_comments: "Organizational change can take many forms."
    points_possible: 1
    position: 1
    question_id: 8369896
    quiz_id: 464878
    text: "Which of the following statements regarding change is INCORRECT?"
    type: "multiple_choice_question"
    created_at: "2016-03-04 20:12:19"
*/


/*	show all questions from selected quiz
	Questions are selectable or all
	submit btn adds questions to quests
*/

