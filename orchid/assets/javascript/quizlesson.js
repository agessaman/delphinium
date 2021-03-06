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

$(document).ready(function() {
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
	
	start with a text field
    in Canvas, append to the body id="tinymce" class="mce-content-body"
*/

/* Delphinium functions:
	set up the popover content text and activate it
*/
	$('#orchid-popinfo').attr("data-content","Choose a Quiz to place in a page. Then select questions and add them to the page. Be sure to use all questions! Submit Button will grade the quiz.");
    $('#orchid-popinfo').popover();// activate info

	if(role=='Instructor') {
		/* set id & course for the POST if they are hidden in fields.yaml
			Add hidden input fields so they will transfer to onUpdate
			if a field is set to hidden: true it does not appear in the form at all
		*/
		$('#Form-outsideTabs').append('<input type="hidden" name="Quizlesson[id]" value="'+orchidConfig.id+'" /> ');
		$('#Form-outsideTabs').append('<input type="hidden" name="Quizlesson[course_id]" value="'+orchidConfig.course_id+'" /> ');
        
		$('#orchid-cog').on('click', function(e){
			$('#orchid-configuration').modal('show');
		});
		
		function orchidCompleted(data)
		{
			/* updated record is returned */
			/* Flash a message then reload page */
			$.oc.flashMsg({
				'text': 'The record has been successfully saved.',
				'class': 'success',
				'interval': 3
			}); 
			location.reload();
		}
	}
/* End Delphinium functions*/

/* Quizlesson functions*/
//var quizList = {{quizList|raw}};// all quizzes
//console.log('quizList:', quizList.length, quizList); 
var quests=[];// quiz questions to select from
var selectedQuiz='';// quiz_id
var selecteditems=[];// quiz question_id array
var qitems=[];// question divs
var usedcount=0;// questions used on page
var nextcount=0;// index for question details modal

// setup tools
$('#questiongroup').hide();
$('#addsubmit').attr("disabled","disabled");
    
// start with a text field
$('.show-content').append('<div class="text-content"></div>');
    
console.log('quiz_id:', orchidConfig.quiz_id); 
if(orchidConfig.quiz_id != undefined && orchidConfig.quiz_id != '') {
	selectedQuiz = orchidConfig.quiz_id;
	showQuizQuestions(selectedQuiz);// from quiz_id
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
		orchidConfig.quiz_id = selectedQuiz;
		$('#Form-field-Quizlesson-quiz_id').val(selectedQuiz);
		
		showQuizQuestions(selectedQuiz);
	});
}

function showQuizQuestions(id)
{
	// close quiz selector quizlist
	$('#quizlist').hide();
	$('#questiongroup').show();
	console.log('view questions for quiz_id:'+id);
	
	var quiz= $.grep(quizList, function(elem, indx){
        return elem.quiz_id == id; }
	);
	console.log('quiz:', quiz);
	orchidConfig.quiz_name = quiz[0].title;
	$('#Form-field-Quizlesson-quiz_name').val(orchidConfig.quiz_name);
	quests=quiz[0].questions;
	console.log('quests:',quests);
	//properties: quiz_id, title, q_count, points, description
	$('#qtitle').text(quiz[0].title+' Points: '+quiz[0].points_possible);
	
	// show the quiz questions
	$('#questionlist').empty();
	usedcount=0;// reset
	selecteditems=[];// no questions selected
	qitems=[];// question divs
	for(var i=0; i<quests.length; i++)
	{
		var txt = quests[i].text;
		//console.log(txt);// &lt;strong&gt; ...
		txt = $.parseHTML(txt);
		txt = txt[0].textContent;
		//console.log(txt);//<strong>
		
		// hide when selected, track id of selecteditems 
		var content = '<div class="questitem" id="'+quests[i].question_id+'">';
			content+= '<input type="checkbox" name="'+quests[i].question_id+'" value="" /> ';//checkbox
			content+= '<a id="'+i+'" class="seeit" href="#"><i class="icon-star"></i></a> ';// view details icon
			content+= txt;
			content+= '</div>';
		$('#questionlist').append(content);
		//selecteditems.push(quests[i].question_id);// all questions selected
		qitems.push(content);// each div
	}
	//http://api.jquery.com/prop/
	$('.seeit').on('click',function(e){
		//console.log(e.currentTarget.id);
		e.preventDefault();
		nextcount=e.currentTarget.id;
		constructQuestion(nextcount);
		$('#quest-details').modal('show');
	});
}
    
    //add a text field on page
    $('#addtext').on('click', function(e){
        e.preventDefault();
        $('.show-content').append('<div class="text-content"></div>');
        
    });
	//add selected questions to page $( "input[type=checkbox]" ).on( "click", countChecked );
	$('#addselected').on('click', function(e){
		e.preventDefault();
		
		var items = $( "input:checked" );
		//console.log(items.length);
        for(var i=0; i<items.length; i++){
			
			//console.log($(items[i]).attr('name'));
			//console.log($(items[i]).parent().attr('id'));
			$(items[i]).parent().hide();// hide it
			// uncheck items so they dont get counted again
			$(items[i]).prop( "checked", false );
			// name = question_id 
			selecteditems.push($(items[i]).attr('name'));//questions selected
			usedcount++;// add to used count
			
			// add it to page in iframe group ? or individual rows
            $('.show-content').append('<div class="question-content"></div>');
		}
		//console.log(usedcount, quests.length);
		// if count = all show Add Submit Quiz button 
		if(usedcount==quests.length) {
			$('#addsubmit').removeAttr( "disabled" );
			$('#addselected').attr("disabled","disabled");
		}
    });
    
	// add submit button to page
	$('#addsubmit').on('click', function(e){
        e.preventDefault();
        // wrap in a div and center? or right?
		// configure button and add to page
 //<button type="submit" class="btn submit_button quiz_submit btn-secondary" id="submit_quiz_button" data-action="/courses/368564/quizzes/556620/submissions?user_id=1568377">Submit Quiz</button>
        
        var subtn = '<div class="form-actions">';
            subtn +='<button type="submit" class="btn submit_button quiz_submit btn-secondary" id="submit_quiz_button"';
            subtn +='data-action="" >';//configure button
            subtn +='Submit Quiz</button></div>';
        $('.show-content').append(subtn);
		// disable addsubmit
		$('#addsubmit').attr("disabled","disabled");
		$('#addtext').attr("disabled","disabled");
	});
    
    // choose a different quiz
    $('#replaceit').on('click', function(e){
        e.preventDefault();
        $('#quizlist').show();
        $('#questiongroup').hide();
		// enable add questions and text, disable addsubmit
		$('#addselected').removeAttr( "disabled" );
		$('#addtext').removeAttr( "disabled" );
		$('#addsubmit').attr("disabled","disabled");
        // empty array ?
        selecteditems=[];
        // reset the page
        $('.show-content').empty();
        $('.show-content').append('<div class="text-content"></div>');
    });
    
    
// see question details 
    $('#nextbtn').on('click', function(e) {
        e.preventDefault();
        //next question
		nextcount++;
		if(nextcount==quests.length){ nextcount=0; }
		constructQuestion(nextcount);
    });
    $('#backbtn').on('click', function(e) {
        e.preventDefault();
        //previous question
		nextcount--;
		if(nextcount<0){ nextcount=quests.length-1; }
		constructQuestion(nextcount);
    });

	/*
	   index is first selected question to see in #quest-details modal
       construct: type, points, question, answers and comments
	*/
	function constructQuestion(index)
	{	
		var quest = quests[index];
		$('#quest-details-title').html('Question '+(index+1));
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

/*  
var currentScroll = 0;
$(window).scroll(function () {
	currentScroll = $(window).scrollTop();
});

var showSnippetNicely = function() {
	$(".scrollingBox").animate({ "top": currentScroll }, 200);
};
*/ 

//End document.ready
});