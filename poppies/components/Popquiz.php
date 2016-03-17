<?php namespace Delphinium\Poppies\Components;

use Cms\Classes\ComponentBase;
use Delphinium\Roots\Roots;
//use Delphinium\Roots\Utils;
//use Delphinium\Roots\Guzzle\GuzzleHelper;
//use Delphinium\Roots\Lmsclasses\CanvasHelper;

use Delphinium\Roots\Enums\ActionType;
use Delphinium\Roots\Models\Quizquestion;
use Delphinium\Roots\Requestobjects\QuizRequest;
use Delphinium\Roots\Models\Assignment;
use Delphinium\Roots\Requestobjects\AssignmentsRequest;// Add Update?
use \DateTime;

class Popquiz extends ComponentBase
{

    public function componentDetails()
    {
        return [
            'name'        => 'Pop Quiz Component',
            'description' => 'Quiz Game'
        ];
    }

    public function defineProperties()
    {
        return [

            'copy_id' => [
                'title'        => 'Copy Name:',
                'type'         => 'string',
                'default'      => 'copy-1',
                'required'     => 'true',
                'validationPattern' => '^(?!\s*$).+',
                'validationMessage' => 'This field cannot be left blank.'
            ],
            'instance'	=> [
                'title'             => 'Configuration:',
                'description'       => 'Select an instance',
                'type'              => 'dropdown',
                'default'           => 0
            ]
        ];
    }
	
	public function onRun()
	{
		try
        {
			$this->addCss("/plugins/delphinium/poppies/assets/css/bootstrap.min.css");
            $this->addCss("/plugins/delphinium/poppies/assets/css/popquiz.css");
			//$this->addCss("/plugins/delphinium/poppies/assets/css/university-ave.css");
            //$this->addJs("/plugins/delphinium/poppies/assets/javascript/jquery.min.js");
			//$this->addJs("/plugins/delphinium/poppies/assets/javascript/jquery-ui.min.js");
			//$this->addJs("/plugins/delphinium/poppies/assets/javascript/jquery.spritely.js");
			//$this->addJs("/plugins/delphinium/poppies/assets/javascript/bootstrap.min.js");
			
			$role = $this->setup();
			$this->page['role'] = $role;// either Learner or Instructor
			if($role=='Instructor')
			{
				$quizList = $this->getAllQuizzes();// choose quiz to use
				$this->page['quizList'] = $quizList;
			}
			if($role=='Learner')
			{
			
			}
			// main code here
		}
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
	}
	
	/*
		common funtions:
			define Role: either Instructor or Learner
			
	*/
	public function setup()
	{
		if (!isset($_SESSION)) { session_start(); }
    
        //$courseID = $_SESSION['courseID'];// store if needed
		// dynamic instance?
		
		// update this Assignment?
		
		// comma delimited string
        $roleStr = $_SESSION['roles'];
        if(stristr($roleStr, 'Learner')) {
            $roleStr = 'Learner';
        } else { 
            $roleStr = 'Instructor';
        }
		return $roleStr;
	}
	
	/*
		Pop Quiz Game:
		Instructor
			choose which quiz to use from getAllQuizzes
			choose # of questions to use from total
			add Intro text, used in game
			each question has points_possible
			adjust total points or use total possible
			choose game type from list [YouGotThis, ...]
			un-publish quiz chosen?
			
		Learner:
			see Intro text, Play game
			getQuizQuestions
			pass back points for LTI assignment, not quiz
	*/
	public function getAllQuizzes()
    {
        $fresh_data = false;//true;
		$roots = new Roots();
		$req = new QuizRequest(ActionType::GET, null, $fresh_data, true);
        //echo json_encode($roots->quizzes($req));
		
		// return list of quiz_id only? or simplified for choosing
		//contains questions and answers for each !
		return json_encode($roots->quizzes($req));
    }
   // if learner just get one
    public function getQuizQuestions($quiz_id)
    {
        $quiz_id =623912;// call with id
		$fresh_data=false;
		$roots = new Roots();
		$req = new QuizRequest(ActionType::GET, $quiz_id, $fresh_data, true);
        $result = $roots->quizzes($req);

        echo json_encode($result);
//        foreach($result['questions'] as $question)
//        {
//
//            $answers = $question['answers'];
//            $obj = json_decode($answers, true);
//            foreach($obj as $answer)
//            {
//                echo json_encode($answer['text']);
//            }
//        }
    }
	
	// create new Assignment and un-publish Quiz used ???
	public function addAssignment()
    {
        $date = new DateTime("now");
        $assignment = new Assignment();
        $assignment->name = "my new name";// Set by Instructor
        $assignment->description = "This assignment was created from backend";
        $assignment->points_possible = 30;
        $assignment->due_at = $date;
		
		$roots = new Roots();
        $req = new AssignmentsRequest(ActionType::POST, null, null, $assignment);
        $res = $roots->assignments($req);
        return json_encode($res);
    }
}