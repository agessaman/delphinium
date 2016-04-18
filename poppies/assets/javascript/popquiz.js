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

/* popquiz.js

	Pop Quiz Game:
	Instructor
		choose which quiz to use from getAllQuizzes
			click quiz to see questions 
		select questions to use in game
			can choose questions from multiple quizzes
			question_ids are added to db
is it possible to get question_banks instead?
			
		each question has points_possible but is not used
        select total points for game in the assignment
		add Intro text in assignment, or _description, used in game
		
		choose game type from list [YouGotThis, ...]?
		possibly let the Learner choose which game?
		preview of game with questions chosen
	
	Learner:
	let the Learner choose which game?
		get Questions from db
		see Intro text, Play game
		
		pass back points for LTI assignment, not a quiz
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
		//ONLY? if type == "multiple_choice_question" ONLY?
        if(quests[i].type == "multiple_choice_question") {
            var txt = quests[i].text;
            //console.log(txt);// &lt;strong&gt; ...
            txt = $.parseHTML(txt);
            txt = txt[0].textContent;
            //console.log(txt);//<strong>
            $('#quizselectable').append('<li class="ui-widget-content" data-id="'+quests[i].question_id+'">'+txt+'</li>');
            selecteditems.push(quests[i].question_id);// all questions selected
        }
	}
	
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
	$('#questcount').html(gameitems.length+' Game Questions');
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
