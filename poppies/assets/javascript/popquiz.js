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
var quests=[];// quiz questions to selected from


/* delphinium functions MOVE TO JS STILL role=='Instructor' */
    $('#popinfo').attr('data-content','Select a Quiz, Add Selected Questions, Modify the question list as needed then Click Use These Questions. Your students will receive points for playing the game. What a great way to learn.');
	$('#popinfo').popover();// activate ? instructions
    /* hide the instance name field */
    $('#Form-field-Popquiz-name-group').hide();
	/* these form fields are hidden in fields.yaml so repopulate form for submit */
	$('#Form-outsideTabs').append('<input type="hidden" name="Popquiz[id]" value="'+config.id+'" /> ');
    
	/* show instructions first? */
    
    /* manually activate the modal */
    $('#cog').on('click', function() {
        $('#poppies-configuration').modal('toggle');
    });
    /*  updated record is returned 
        form can be submitted from cog.Update button or useit.submit()
    */
    function completed(data) {
        //console.log('completed:',data);
        //console.log('config:',config);
        $('#poppies-configuration').modal('hide');// if cog
	}
	
	/* component functions */
	/*	Note: these functions are only for instructor view
		may get moved to /assets/popquiz.js
		list of all quizzes for instructor
		to choose questions for game
        //ONLY? if type == "multiple_choice_question" ONLY?
        ToDo:
        instructions are in the cog instructions tab
        remove accordion-3
    */
	//quizList = {{quizList|raw}};
    //console.log('quizList:', quizList.length, quizList);
    showQuizzes();// all quizzes to choose from
    console.log('if gameQuest:', gameQuest.length);//, gameQuest);
    if(gameQuest != null && gameQuest.length > 1) {
        //gameitems = {{gameQuest|raw}};
        showSelected();// from config.questions
    }
    
	nextcount=0;//index for question details
	
	
    
	/* activate accordion and button functions */
    $('.accordion-section-title').click(function(e) {
        e.preventDefault();
		// Grab current anchor value
		var currentAttrValue = $(this).attr('href');
		if($(e.target).is('.active')) {
			close_accordion_section();
		}else {
			close_accordion_section();
			// Add active class to section title
			$(this).addClass('active');
            if(currentAttrValue == '#accordion-5') { showIntro(); }
			// Open up the hidden content panel
			$('.accordion ' + currentAttrValue).slideDown(300).addClass('open'); 
		}
	});
    
    /* accordion-2 selected questions
        http://api.jqueryui.com/1.12/selectable/
        single click, drag or control click
    */
    $("#quizselectable").selectable({
        // on mouse up
        selected: function() {
            selecteditems=[];// accumulated selections if mouse dragged
            $( ".ui-selected", this ).each(function() {
             var index = $( "#quizselectable li" ).index( this );// list[index]
             var qid = $(this).attr('data-id');
            if(qid) { selecteditems.push(qid); }
            });// or only one clicked
            console.log('selecteditems:',selecteditems);// array of question_ids
        }
    });
    
    /* accordion-4 selected game questions */
    $("#gameselectable").selectable({
        // on mouse up
        selected: function() {
            chosenitems=[];// accumulated selections if mouse dragged
            var items=$( ".ui-selected", this ).each(function() {
             var index = $( "#gameselectable li" ).index( this );// list[index]
             var qid = $(this).attr('data-id');
             chosenitems.push(qid);// push id for each to an array
            });// or only one clicked
            console.log('chosenitems:',chosenitems);// array of question_ids
			//set nextcount to gameitems.indexOf(chosenitems[0]) for constructQuestion
			for(var i=0; i<gameitems.length; i++) {
				if(chosenitems[0]==gameitems[i].question_id){ nextcount=i; }
			}
			console.log('nextcount:',nextcount);
        }
    });
    
    /* Add Selected Questions button in accordion-2 */
    $('#confirmit').click(function(e) {
        e.preventDefault();
        // selecteditems is array of question_id
        // transfer selecteditems[question_id,] to qameitems[question object,]
		console.log('selecteditems:',selecteditems);
        for(var i=0; i<selecteditems.length; i++)
        {
            var quest = $.grep(quests, function(elem, index){
                return elem.question_id == selecteditems[i];
            });
            gameitems.push(quest[0]);//quest[0] array of objects fix this
        }
        console.log('gameitems:',gameitems);
        // should be array of objects
        // reset gameselectable list
        showSelected();
        selecteditems=[];
    });
    
     /* Remove All Questions button from gameitems */
    $('#clearit').click(function(e) {
        e.preventDefault();
		current=0;// questnum
		nextcount=0;// see question
        gameitems=[];
        chosenitems=[];
		$('#questcount').html(gameitems.length+' Questions');
        $('#gameselectable').empty();
    });
    
    /* Remove Selected Questions btn in chosenitems from gameitems */
    $('#removeit').click(function(e) {
        e.preventDefault();
        for(var i=0; i<chosenitems.length; i++)
        {
            var item = 0;
            for(var c=0; c<gameitems.length; c++) {
                if(gameitems[c].question_id == chosenitems[i]) { item=c; break; }
            }
            gameitems.splice(item,1);// remove from array
            // remove <li>st node
            $('#gameselectable').find('[data-id='+chosenitems[i]+']').remove();
			$('#questcount').html(gameitems.length+' Game Questions');
        }
        //console.log('chosenitems:',chosenitems);
        //console.log('gameitems:',gameitems);
        chosenitems=[];
		nextcount = 0;//none selected
    });
    
    /*  use gameitems to play game 
		save gameitem to db questions as array of question_id
		retrieve questions from delphinium_roots_quiz_questions
	*/
    $('#useit').click(function(e) {
        e.preventDefault();
        
		//console.log('gameitems:',gameitems.length, gameitems);
		if(gameitems.length>0) { 
            /* gameitems = questions to use in game */
			gameQuest=gameitems;
			/* gameQuest is Array of questions in game
                store config.questions[question_id, ...]
                in delphinium_poppies_popquizzes
                to retrieve question objects 
                from delphinium_roots_quiz_questions
                construct array then submit the form
                ends up as a comma delimited string 
            */
			var idArray = [];
			for(var i=0; i<gameitems.length; i++) {
            //ONLY? if type == "multiple_choice_question" ONLY?
				idArray.push(gameitems[i].question_id);
			}
			//console.log('idArray:',idArray);
            /* update the form and config
                then submit to onSave in Poppies.php
                which returns to completed(data) above
            */
			$('#Form-field-Popquiz-questions').val(idArray);
            config.questions=idArray;// and internal array
            console.log('config:',config.questions);
			$('#updateForm').submit();// update the db record
			//open the game
			close_accordion_section();
			$('#accordion-5').addClass('active');
			$('.accordion #accordion-5').slideDown(300).addClass('open');
			showIntro();// restart game with updated questions
		}
		// else no questions to use
	});

    /*  see details of each question with answers and comments
        in #detailed modal
        nextcount is index of starting question
        use next btn to view each question
    */
	$('#seeit').click(function(e) {
        e.preventDefault();
		if(gameitems.length>0) { 
			constructQuestion(nextcount);
			$('#detailed').modal('show');
		 }
		 // else no questions to see
    });
    $('#nextbtn').click(function(e) {
        e.preventDefault();
        //detailed-body replace content with next question
		nextcount++;
		if(nextcount==gameitems.length){ nextcount=0; }
		constructQuestion(nextcount);
    });// backbtn ?
	/* also see selected questions accordion-2 quests[]
	   currently only game questions gameitems[]
	   index is first selected question to see in #detailed modal
       construct: type, points, question, answers and comments
	*/
	function constructQuestion(index)
	{	
		var quest = gameitems[index];
		$('#detailed-title').html('Question '+(index+1));
		$('#qtype').html('Type: '+gameitems[index].type);
		$('#qpoints').html('Points: '+gameitems[index].points_possible);
		var txt = gameitems[index].text;//.toString();
			txt = $.parseHTML(txt);
			txt = txt[0].textContent;
		$('#qtext').html(txt);
		var answers= $.parseJSON(gameitems[index].answers);
		var ansdiv='';
		console.log(answers);//for each
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
		if(gameitems[index].correct_comments!= "")
		{
			comdiv+='<div class=bg-success>';
			comdiv+=gameitems[index].correct_comments;
			comdiv+='</div>';
		}
		if(gameitems[index].incorrect_comments != "")
		{
			comdiv+='<div class=bg-danger>';
			comdiv+=gameitems[index].incorrect_comments;
			comdiv+='</div>';
		}
		if(gameitems[index].neutral_comments != "")
		{
			comdiv+='<div class=bg-warning>';
			comdiv+=gameitems[index].neutral_comments;
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
