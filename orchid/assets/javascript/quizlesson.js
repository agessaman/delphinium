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
    -In text editor, add text
    -select the editor LTI resources drop down icon
    
	-select a quiz to use
	save id and display the questions
		-ability to choose another quiz?
		-cannot replace questions already placed on page
	
	-select questions with check box, view details with eye icon
    -select Add QuestIons to place a group of questions on page
	-use editor to place text between question groups
	-place a quiz submit button after all questions are placed
	
	//start with a text field OBSOLETE
    
    todo:
    https://www.imsglobal.org/specs/lticiv1p0/specification-3
    
    submit button must contain quiz_id for quiz submission api call
*/

/* Delphinium functions:
	set up the popover content text and activate it
*/
     if (messageType == "ContentItemSelectionRequest") {
        $('#orchid-popinfo').attr("data-content","Choose a Quiz. Then select questions and add them to the page. Be sure to use all questions from the same quiz! Submit Quiz button will grade the quiz.");
        $('#orchid-popinfo').popover();// activate info
        /* 
        set id & course for the POST if they are hidden in fields.yaml
        Add hidden input fields so they will transfer to onUpdate
        if a field is set to hidden: true it does not appear in the form at all
        */
        $('#Form-outsideTabs').append('<input type="hidden" name="Quizlesson[id]" value="'+orchidConfig.id+'" /> ');
        $('#Form-outsideTabs').append('<input type="hidden" name="Quizlesson[course_id]" value="'+orchidConfig.course_id+'" /> ');
        $('#Form-outsideTabs').append('<input type="hidden" name="Quizlesson[questions_used]" value="'+orchidConfig.questions_used+'" id="Form-field-OrchidQuestions" /> ');

        $('#orchid-cog').on('click', function(e){
            $('#orchid-configuration').modal('show');// form & instructions
        });
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
    var index=0;// quests[index]

    // setup tools
    $('#questiongroup').hide();
    $('#addsubmit').attr("disabled","disabled");

    console.log('quiz_id:', orchidConfig.quiz_id);
    
    if (messageType == "basic-lti-launch-request") {
        //display a list of questions sent from content_items.custom
        //TEST
        var content = {
            "quizid":464865, 
            "questions":[8369049,8369050]
        };
        
        render(content.quizid, content.questions);
        
    } else {
    
        if (orchidConfig.quiz_id != undefined && orchidConfig.quiz_id != "") {
            selectedQuiz = orchidConfig.quiz_id;
            showQuizQuestions(selectedQuiz);// from quiz_id
        } else {
            // all quizzes to choose from
            showQuizzes();
        }
    }
    
    function validateQuizzes()
    {
        /* remove quizzes that do not have questions from quizList
            do this in php
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
            e.preventDefault();
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
        $('#qtitle').html(quiz[0].title+'<span class="right"> Points: '+quiz[0].points_possible+'</span>');
        
        // show the quiz questions
        $('#questionlist').empty();
        usedcount=0;// reset
        selecteditems=[];// no questions selected
        qitems=[];// question divs
        for (var i=0; i<quests.length; i++) {
            var txt = quests[i].text;
            //console.log(txt);// &lt;strong&gt; ...
            txt = $.parseHTML(txt);
            txt = txt[0].textContent;
            //console.log(txt);//<strong>
            
            // hide when selected, track id of selecteditems 
            var content = '<div class="questitem" id="'+quests[i].question_id+'">';
                content+= '<input type="checkbox" name="'+quests[i].question_id+'" value="" /> ';//checkbox
                content+= '<a id="'+i+'" class="seeit" href="#"><i class="icon-eye"></i></a> ';// view details icon
                content+= txt;
                content+= '</div>';
            $('#questionlist').append(content);
            //selecteditems.push(quests[i].question_id);// all questions selected
            qitems.push(content);// each div
        }
        
        // for each questions_used disable #questionlist.questitem
        console.log("Hide Used:", orchidConfig.questions_used);
        if (orchidConfig.questions_used.length > 0) {
            $('#replacequiz').attr("disabled","disabled");// cannot change quiz
            var ara = orchidConfig.questions_used.split(",");
            for (i=0; i<ara.length; i++) {
                // if question_id == used id then add used class, select the checkbox 
                $('#'+ara[i]).addClass('used');
                $('#'+ara[i]+' input').prop('disabled',true);
            }
        }
        $('.seeit').on('click',function(e){
            e.preventDefault();
            nextcount=e.currentTarget.id;
            constructQuestion(nextcount);
            $('#quest-details').modal('show');
        });
    }
    
	//add selected questions to page
	$('#addselected').on('click', function(e){
		e.preventDefault();
		// cannot change quiz if questions are added
        $('#replacequiz').attr("disabled","disabled");
        
        var content = '';
        var qids = '';
        var height = 250;
		var items = $( "input:checked" );
		//console.log(items.length);
        for (var i=0; i<items.length; i++) {	
			//console.log($(items[i]).attr('name'));
			//console.log($(items[i]).parent().attr('id'));
            $(items[i]).parent().addClass('used');
            $(items[i]).prop('disabled',true);
			// uncheck items so they dont get counted again
			$(items[i]).prop( "checked", false );
			// name = question_id 
			selecteditems.push($(items[i]).attr('name'));//questions selected
            
            // comma separated string
            if (orchidConfig.questions_used.length > 0) {
                orchidConfig.questions_used += ","+$(items[i]).parent().attr('id');
                qids += ","+$(items[i]).parent().attr('id');// used in content_items
                height += 250;// iframe height, guessing for now
            } else {
                orchidConfig.questions_used += $(items[i]).parent().attr('id');
                qids += $(items[i]).parent().attr('id');
            }
            
            //construct question for page
            
            // for testing
            content += '<div class="question-content">'+$(items[i]).parent().attr('id')+'</div>';
			usedcount+=1;// add to used count
		}
		
		/* if all questions have been used, 
        *   enable Add Submit Quiz button,
        *   let user add it to the page, they may want more text still
        */
		if (usedcount == quests.length) {
			$('#addsubmit').removeAttr( "disabled" );
			$('#addselected').attr("disabled","disabled");
		}
        
        $('.show-content').append(content);// testing
            
        // update db record orchidConfig
        console.log('Used:', orchidConfig.questions_used);
        $('#Form-field-OrchidQuestions').val(orchidConfig.questions_used);
		$('#updateOrchidForm').submit();// update the db record flashMsg
        
        // update Canvas Page
        //construct content_items, add it to page by submitting form to return_url
        // '<form action="return_url" method="post" encType="application/x-www-form-urlencoded">';
        // form content points to a partial that renders a given question in the canvas iframe, for each question? 
        var contentval = '{ "@context" : "http://purl.imsglobal.org/ctx/lti/v1/ContentItem","@graph" : [{';
            contentval += ' "@type" : "LtiLinkItem", "@id" : ":item1",';
            contentval += ' "url" : "https://mediafiles.uvu.edu/delphinium/quizlesson",';
            contentval += ' "title" : "Quiz Questions", "text" : "could possibly contain quizid, qid data",';
            contentval += ' "mediaType" :  "application/vnd.ims.lti.v1.ltilink",';
            contentval += ' "custom" : { "quizid" : '+orchidConfig.quiz_id+', "questionid" : [ '+qids+'] }';
            contentval += ' "placementAdvice" : { "presentationDocumentTarget" : "iframe",';
            contentval += ' "displayWidth" : 100%,  "displayHeight" : '+height+' }} ]} ';
            
        $('#content_items').val(contentval);// autosubmit form will url encode this
        //$('#contentSelector').attr('action', 'return_url');
        //$('#contentSelector').submit();
        
        // warn user to close the selection modal and NOT add any more questions
        
    });
    
	// add submit quiz button to page
	$('#addsubmit').on('click', function(e){
        e.preventDefault();
        // wrap in a div and center? or right?
		// configure quiz submission button and add to page
 //<button type="submit" class="btn submit_button quiz_submit btn-secondary" id="submit_quiz_button" data-action="/courses/368564/quizzes/556620/submissions?user_id=1568377">Submit Quiz</button>
        
        var subtn = '<div class="form-actions">';
            subtn +='<button type="submit" class="btn submit_button quiz_submit btn-secondary" id="submit_quiz_button"';
            subtn +='data-action="" >';//configure button
            subtn +='Submit Quiz</button></div>';
        $('.show-content').append(subtn);
		// disable addsubmit
		$('#addsubmit').attr("disabled","disabled");
	});
    
    // choose a different quiz
    $('#replacequiz').on('click', function(e){
        e.preventDefault();
        $('#quizlist').show();
        $('#questiongroup').hide();
		// enable add questions, disable addsubmit
		$('#addselected').removeAttr( "disabled" );
		$('#addsubmit').attr("disabled","disabled");
        selecteditems=[];// empty array
        // reset the CMS page OBSOLETE
        $('.show-content').empty();
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
	*   index is first selected question to see in #quest-details modal
    *   construct: type, points, question, answers and comments
	*/
	function constructQuestion(index)
	{	
		var quest = quests[index];
        index = parseInt(index);// string to integer
        //console.log(index, quest);
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
    
    /* Render Questions.htm 
    *   functions
    *   
    */
    function gradeQuestion(quizid,qid){
        console.log('called: gradeQuestion', quizid, qid);// selectedAnswer ???
        
        // if role == "instructor" 
        if(role=='Instructor') {
            showFeedback(quizid,qid);// only display feedback. 
            
        } else {
            // student role is required to answer a question
            $.request('onGradeQuestion', {
                data: {'quiz':quizid,'quest':qid}, // pass parameters to the function in Quizlesson.php
                dataType: 'text',// returning info type. returns an array

                success: function(data) { // data contains the returning result from Quizlesson.php file
                    console.log('data:', data);//{"result":"\"464865\""}
                    showFeedback(quizid,qid);
                    // hide check answer & and show retry
                    //  "<button class='btn btn-warning' onclick='gradeRetry()'>Retry</button><br>";
                }
            });
        }
    }
    function showFeedback(quizid,qid) {
        // find the quiz
        var quiz= $.grep(quizList, function(elem, indx){
            return elem.quiz_id == quizid; }
        );
        // find the question
        var quest = $.grep(quiz[0].questions, function(elem,index) {
            return elem.question_id == qid;
        });
        console.log('showFeedback:', quest);
        // find the comments
        var correct = quest[0].correct_comments;
        var incorrect = quest[0].incorrect_comments;
        var neutral =quest[0].neutral_comments;
        
        var feedtxt = ''; // if length>0 display feedback
        if (correct.length > 0) {
            feedtxt += "<i class='fa fa-check-circle' style='color:green'></i>"+correct;
        }
        if (incorrect.length > 0) {
            feedtxt += "<br><i class='fa fa-times' style='color:red'></i> "+incorrect;
        }
        if (neutral.length > 0) {
            feedtxt += "<br><i class='fa fa-comments' style='color:#2E9AFE'></i>"+neutral;
        }
        $('#feedback'+qid).html(feedtxt+'<hr/>');
    }
    /*
    function gradeRetry(){
         console.log('called: gradeRetry');
        $.request('onRetryQuestion', {
            data: {val:123}, // pass parameters to the function in Quizlesson.php
            dataType: 'text',// returning info type. returns an array
            success: function(data) { // data contains the result from Quizlesson.php file

                //var t = JSON.parse(data);
                var comments  = data.split(",");

                // data will have the new grade
                console.log("comments ", comments);
                var showQuestions = document.getElementById("printQuestions");
                showQuestions.innerHTML += " <div> <i class='fa fa-check-circle' style='color:green'></i>"+comments[0]+"</div>";
            }
        });
    }
    */
    function render(quizid, questions) {
        console.log('render:', quizid, questions);
        
        var quiz= $.grep(quizList, function(elem, indx){
            return elem.quiz_id == quizid; }
        );
        //var quests=quiz[0].questions;
        //console.log('quests:',quests);// all in quiz
        
        var quests =  $.grep(quiz[0].questions, function(elem, indx){
           // return elem.question_id == questions[0];
           for (var q=0; q<questions.length; q++) {
               if (elem.question_id == questions[q]) {
                   return elem;
               }
           }
        });
        console.log('quest:', quests.length, quests);
        
        var pageContent = '';
        for (var i=0; i<quests.length; i++) {
            var qid = quests[i].question_id;
            var txt = quests[i].text; //console.log(txt);// &lt;strong&gt; ...
            txt = $.parseHTML(txt);
            txt = txt[0].textContent;// <strong>
            
            var questxt = '<div class="questbox"><div class="questxt">'+txt+'<br/></div>';
            console.log('Type: ',quests[i].type);
            var answers= $.parseJSON(quests[i].answers);
            var ansdiv='';
            //console.log(answers);//for each
            for (var a=0; a<answers.length; a++) {
                
                ansdiv+='<div class="answer">';
                if (quests[i].type == "multiple_choice_question") {
                    ansdiv += '<input type="radio" name="answer'+i+'" />';
                }
                ansdiv+=' '+answers[a].text;
                ansdiv+='</div>';
            }
            
            pageContent += questxt + ansdiv;// +'</div>';
            pageContent += '<hr/><div id="feedback'+qid+'"></div>';
            pageContent += '<div id="printQuestions"></div>';// retry button
            //pageContent +='<button class="btn btn-xs btn-info" onclick="gradeQuestion('+quizid+','+qid+')">Submit Answer</button></div>';
            pageContent +='<button class="btn btn-xs btn-info subanswer" data-quiz="'+quizid+'" data-question="'+qid+'">Submit Answer</button></div>';
        }
        $('#q').html(pageContent);
        $('.subanswer').on('click', function(e){
            var quizid = $(this).data('quiz');
            var qid = $(this).data('question');
            console.log(quizid,qid);
            gradeQuestion(quizid,qid);
        });
    }
/*
content_items example:
{
    "@context" : "http://purl.imsglobal.org/ctx/lti/v1/ContentItem",
    "@graph" : [
        {
            "@type" : "LtiLinkItem",
            "@id" : ":item1",
            "url" : "https://mediafiles.uvu.edu/delphinium/quizlesson",
            "title" : "Quiz Questions",
            "text" : "could possibly contain quizid, qid data",
            "mediaType" :  "application/vnd.ims.lti.v1.ltilink",
            "custom" : {
                "quizid" : 12345,
                "questionid" : [ 111, 222]
            }
            "placementAdvice" : {
                "presentationDocumentTarget" : "iframe",
                "displayWidth" : fixed,
                "displayHeight" : variable
            }
        }
    ]
}


*/
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

//End document.ready
});