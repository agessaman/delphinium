<?php
/**
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

namespace Delphinium\Orchid\Components;

use Cms\Classes\ComponentBase;
use Delphinium\Roots\Roots;
use Delphinium\Roots\Enums\ActionType;
use Delphinium\Roots\Models\Quizquestion;
use Delphinium\Roots\Requestobjects\QuizRequest;
use Delphinium\Orchid\Models\Quizlesson as QuizlessonModel;

class Quizlesson extends ComponentBase
{

    public function componentDetails()
    {
        return [
            'name'        => 'Quiz Lesson',
            'description' => 'Embed quiz questions into Canvas Pages'
        ];
    }

    public function defineProperties()
    {
        return [
            'instance'	=> [
                'title'             => '(Optional) instance',
                'description'       => 'Select an instance',
                'type'              => 'dropdown',
                'default'           => 0
            ]
        ];
    }
    
    public function getInstanceOptions()
    {
		$instances = QuizlessonModel::all();// records
		if(count($instances) === 0) {
			return $array_dropdown = ['0' => "No instance available."];
		} else {
			$array_dropdown = ['0'=>'- select Instance - '];//id, text in dropdown
			// populate CMS dropdown
			foreach ($instances as $instance) {
				$array_dropdown[$instance->id] = $instance->Name;
			}
		}
        return $array_dropdown;
    }
    
    public function onRun()
    {
       // try
       // {
            /*Notes:
            -When configuring the component on a page we will provide the option of selecting a backend instance of the component. 
                if an instance has been selected, then we will just use that.
            -If no instance has been selected, then we will look in the DB for a component with the alias_courseId name. If found, we'll return it.
            -If not found, then we will create a new instance with the name alias_courseId.
            
			Requires minimal.htm layout
            Requires the Dev component set up from Here:
            https://github.com/ProjectDelphinium/delphinium/wiki/3.-Setting-up-a-Project-Delphinium-Dev-environment-on-localhost
            */
			
            if (!isset($_SESSION)) { session_start(); }
            $courseID = $_SESSION['courseID'];
			$name = $this->alias .'_'. $_SESSION['courseID'];
            // if instance has been set
            if( $this->property('instance') )
            {
                //use the instance set in CMS dropdown
                $config = QuizlessonModel::find($this->property('instance'));

            } else {
				// look for instances created for this course
				$instances = QuizlessonModel::where('name','=', $name)->get();
				
				if(count($instances) === 0) { 
					// no record found so create a new dynamic instance
					$config = new QuizlessonModel;// db record
                    $config->name = $name;
					// add your fields
					//$config->size = '20%';
                    //$config->quiz_name = '';
                    //$config->quiz_id = '';
                    $config->course_id = $courseID;
					$config->save();// save the new record
				} else {
					//use the first record matching course
					$config = $instances[0];
				}
            }
			// use the record in the component and frontend form 
            $this->page['orchidConfig'] = json_encode($config);
            
			/** get roles, a comma delimited string
			 * check if Student
			 * if not then set to Instructor. disregard any other roles?
			 * role is used to determine functions and display options
			 */
            $roleStr = $_SESSION['roles'];
			
            if(stristr($roleStr, 'Learner')) {
                $roleStr = 'Learner';
            } else { 
                $roleStr = 'Instructor';
            }
                //$roleStr = 'Learner';// TEST
            $this->page['role'] = $roleStr;// only one or the other
            
            // include the backend form with instructions for constructing Canvas page
            /** This component switches by messageType and role
                https://www.imsglobal.org/specs/lticiv1p0/specification-3
            */
            if (isset($_POST['lti_message_type'])) {
                
                echo 'message_type: '.$_POST['lti_message_type'];// TEST
                foreach($_POST as $key => $value ) { echo "$key = $value <br/>"; }
            }
            
                //$messageType = $_POST['lti_message_type'];// online
                $messageType ="ContentItemSelectionRequest";// "basic-lti-launch-request";// TEST manually
                $this->page['messageType'] = $messageType;
                 
                switch ($messageType) {
            
                    case "ContentItemSelectionRequest": // select questions to add
                        
                        //FRONTEND FORM IS UNUSED : REMOVE?
                        // Build a back-end form with the context of 'frontend'
                        $formController = new \Delphinium\Orchid\Controllers\Quizlesson();
                        $formController->create('frontend');
                        
                        // Use the primary key of the record you want to update
                        $this->page['orchidrecordId'] = $config->id;
                        // Append the formController to the page
                        $this->page['orchidform'] = $formController;
                        
                        // INSTRUCTIONS IN MODAL
                        // Append the Instructions to the page
                        $instructions = $formController->makePartial('orchidinstructions');
                        $this->page['orchidinstructions'] = $instructions;
                        
                        $this->page['return_url'] = $_POST["content_item_return_url"];// first launch
                        //$this->page['key'] = $_POST["oauth_consumer_key"];
                        //$this->page['nonce'] = $_POST["oauth_nonce"];
                        //$this->page['times'] = $_POST["oauth_timestamp"];
                        //$this->page['signature'] = $_POST["oauth_signature"];
                        break;

                    case "basic-lti-launch-request": // second launch
                        
                        // display renderQuestion.htm with
                        // array of question_id's from content_items
                        $this->page['content_items'] = json_encode($_POST['content_items']);
                        break;
                }
            //}// End isset
            
			$quizList = $this->getAllQuizzes();// choose quiz questions to use
			$this->page['quizList'] = $quizList;
			
			// ready to finish loading assets. storm changes modal-header. override in css
			$this->addCss("/modules/system/assets/ui/storm.css", "core");
			$this->addJs("/modules/system/assets/ui/storm-min.js", "core");
			$this->addCss("/plugins/delphinium/orchid/assets/css/quizlesson.css");
			$this->addJs("/plugins/delphinium/orchid/assets/javascript/quizlesson.js");
			
/*        }
        catch (\GuzzleHttp\Exception\ClientException $e) {
            return;
        }
        catch(Delphinium\Roots\Exceptions\NonLtiException $e)
        {
            if($e->getCode()==584)
            {
                return \Response::make($this->controller->run('nonlti'), 500);
            }
        }
        catch(\Exception $e)
        {
            if($e->getMessage()=='Invalid LMS')
            {
                return \Response::make($this->controller->run('nonlti'), 500);
            }
            return \Response::make($this->controller->run('error'), 500);
        }
*/    }
	
	/** 
    *   return list of quizzes for instructor to choose one
    *   contains questions and answers for each quiz
    */
	public function getAllQuizzes()
    {
        $fresh_data = false;//true; // instructor may have just built one!
		$roots = new Roots();
		$req = new QuizRequest(ActionType::GET, null, $fresh_data, true);
		$result = $roots->quizzes($req);
        
		// remove quizzes with no questions
		
		return json_encode($result);
    }
	
    /**
    *  frontend update component submit button
    *  save to database and return updated record
    *
    *  id can be disabled in fields.yaml
    *  id & course can be hidden
    *  $data gets .id from config.id instructor.htm
    *  called from instructor.htm configure settings modal
    */
    public function onUpdate()
    {
        $data = post('Quizlesson');//component name
        $did = intval($data['id']);// convert string to integer
        $config = QuizlessonModel::find($did);// retrieve existing record
        //echo json_encode($config);//($data);// testing
        $config->quiz_name = $data['quiz_name'];;
        $config->quiz_id = $data['quiz_id'];
        $config->course_id = $data['course_id'];//hidden field
        $config->questions_used = $data['questions_used'];
        $config->save();// update original record 
        // orchidCompleted must be in instructor.htm
        return json_encode($config);// back to instructor.htm
    }
    
    /**
    *   Render Questions
    */
 /*
    public function onRetryQuestion()
    {
        // for submission create an instance of submission which has all quiz
        // data submission check submission.php
        // IsQuestionAnswered() returns answer if success or no
        // NOTE: submit one and check if submission feedback
        //$studentId = $_SESSION['userID'];

        //note jesus code
        $result = array(
            'correct_comments' => 'correct one point deducted!',
            'incorrect_comments' => 'Upps.',
            'neutral_comments' => ' McDonals is noe hirimg'
        );

        $retString = "";
        foreach ($result as $key => $value)
        {
            $retString .=  $key. ":". $value .",";
        }

        $this->gradePerQuestion .= 5; // add grade to array of grades
        //$retString = $this->gradePerQuestion;
        echo json_encode($result, JSON_PRETTY_PRINT);// $retString;
    }
*/

    /**
    *   only called if role is Learner
    *   requires studentId
    */
    public function onGradeQuestion()
    {
        // see testQuizTakingWorkflow in TestRoots
        //get passed parameters from js
        $questionId = $_POST["quest"];
        $quizId = $_POST["quiz"];
        //$answerArr = $_POST["selectedAnswer"];
        
        $roots = new Roots();
        $quizSubmissionId = $roots->getQuizSubmission($quizId);
        //$quizSubmissionId = 9443207;// TEST
        //data: {"result":"null"} :after postQuizTaking below: already exist err
        
        if ($quizSubmissionId == null) {
            //$studentId = $_SESSION['studentId'];// Not available if role is instructor
            $studentId = 1604486;// TEST set manually to mine
            $quizSubmissionId = $roots->postQuizTakingSession($quizId, $studentId);
           // $result->quiz_submission_id = 9443207
        }
        // check if a question has been answered
        $isAnswered = $roots->isQuestionAnswered($quizId,$questionId,$quizSubmissionId);// null = page not found
        //return json_encode($isAnswered);//data: {"result":"false"} :for unanswered questions
        
        // if false send answer selected to postAnswerQuestion()
        if ($isAnswered) {
            echo "was already answered";//do something if the question has been answered
        } else {
            //answer it. Still working on this
            echo "postAnswerQuestion WIP";
            //$result =$this->canvasHelper->postAnswerQuestion($quizSubmission, $answerArr, $studentId);
            //var_dump($result);
        }
        
    }

    /* End of class */
}