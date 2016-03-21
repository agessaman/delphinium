<?php namespace Delphinium\Poppies\Components;

use Cms\Classes\ComponentBase;
use Delphinium\Roots\Roots;
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
	
	/* This is added as an Assignment.
		How can we get this assignment object
		Need Intro text and points to display in game
			if not done as a Canvas Assignment
		
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
	public function onRun()
	{
		try
        {
			$this->addCss("/plugins/delphinium/poppies/assets/css/bootstrap.min.css");
            $this->addCss("/plugins/delphinium/poppies/assets/css/popquiz.css");
			
			$role = $this->setup();
			$this->page['role'] = $role;// either Learner or Instructor
			if($role=='Instructor')
			{
				$quizList = $this->getAllQuizzes();// choose quiz questions to use
				$this->page['quizList'] = $quizList;
			}
			if($role=='Learner')
			{
				//get questions from model and show game chosen
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
		common functions:
			define Role: either Instructor or Learner
			dynamic instance:
			pass back grade for assignment
	*/
	public function setup()
	{
		if (!isset($_SESSION)) { session_start(); }
    
        //$courseID = $_SESSION['courseID'];// store if needed
		// dynamic instance? not set up yet...
		
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
		Instructor can choose questions from multiple quizzes
	*/
	public function getAllQuizzes()
    {
        $fresh_data = false;//true;
		$roots = new Roots();
		$req = new QuizRequest(ActionType::GET, null, $fresh_data, true);
        //echo json_encode($roots->quizzes($req));
		
		// remove quizzes with no questions
		// return list of quizzes for instructor to choose questions
		
		//contains questions and answers for each quiz
		return json_encode($roots->quizzes($req));
    }
	
	/*
		How can we get (this) assignment object
		Need Intro text and points to display in game
			if not done as a Canvas Assignment
	*/
	public function getSingleAssignment()
    {
        $assignment_id = 1660430;// Need current assignment
        $freshData = false;
        $includeTags = true;
        $req = new AssignmentsRequest(ActionType::GET, $assignment_id, $freshData, null, $includeTags);
		$roots = new Roots();
        $res = $this->roots->assignments($req);
        echo json_encode($res);
    }

}