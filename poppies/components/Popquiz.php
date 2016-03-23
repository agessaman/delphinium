<?php namespace Delphinium\Poppies\Components;

use Cms\Classes\ComponentBase;
use Delphinium\Roots\Roots;
use Delphinium\Roots\Enums\ActionType;
use Delphinium\Roots\Models\Quizquestion;
use Delphinium\Roots\Requestobjects\QuizRequest;
use Delphinium\Roots\Models\Assignment;
use Delphinium\Roots\Requestobjects\AssignmentsRequest;// Add Update?
use Delphinium\Poppies\Models\Popquiz as popquizModel;
//TEST
use Delphinium\Roots\Models\Quizquestion as questionsModel;
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
			if (!isset($_SESSION)) { session_start(); }

            $courseID = $_SESSION['courseID'];
            // if instance has been set
            if( $this->property('instance') )
            {
                //use the instance set in CMS dropdown
                $config = popquizModel::find($this->property('instance'));
                $config->course_id = $_SESSION['courseID'];//$course->id;
                $config->save();//update original record now in case it did not have course

            } else {
                // if copy has a name.
				// note: it will after the first dynamic is created
                $copyLength = strlen($this->property('copy_id'));
                if($copyLength > 0 )
                {
                    // find all matching course 
                    $instances = popquizModel::where('course_id','=', $courseID)->get();
                    $instCount = count($instances);
                    if($instCount == 0) { 
						// none found so set to catch condition for dynamic
                        $copyLength = 0;
                    } else {
                        // find instance with copy
                        $flag=false;
                        foreach ($instances as $instance)
                        {
                           if($instance->copy_id == $this->property('copy_id') )
                           {
                               $config = $instance;
                               $flag=true;
                               break;// got first one found
                           }
                        }
                        //yes found courses but not matching copy. use the first one found with course id
                        if( !$flag ) { $config = $instances[0]; }
                    }
                }
                // no match found so create new dynamic instance
                if($copyLength == 0 )
                {
                    $config = new popquizModel;// db record
                    $config->name = 'dynamic_';//+ total records count?
                    // add your fields
                    //$config->quiz_name = '';
					//quiz_description,game_style,questions
                    $config->course_id = $_SESSION['courseID'];
                    $config->copy_id = $this->property('copy_id');
                    $config->save();// save the new record
                }
            }
			// use the record in the component and frontend form 
            $this->page['config'] = json_encode($config);
            
			/** get roles, a comma delimited string
			 * check if Student
			 * if not then set to Instructor. disregard any other roles?
			 * role is used to determine functions and display options
			 */
            $role = $_SESSION['roles'];
			
            if(stristr($role, 'Learner')) {
                $role = 'Learner';
            } else { 
                $role = 'Instructor';
            }
            $this->page['role'] = $role;// either Learner or Instructor
			
            $this->addCss("/plugins/delphinium/poppies/assets/css/popquiz.css");
			
			if($role=='Instructor')
			{
				// Build a back-end form with the context of 'frontend'
				$formController = new \Delphinium\Poppies\Controllers\Popquiz();
				$formController->create('frontend');
				
                //this is the primary key of the record you want to update
                $this->page['recordId'] = $config->id;
				// Append the formController to the page
				$this->page['form'] = $formController;
                
                // Append Instructions page
                $instructions = $formController->makePartial('instructions');
				
				// instructor code component specific
                $this->page['instructions'] = $instructions;
				$quizList = $this->getAllQuizzes();// choose quiz questions to use
				$this->page['quizList'] = $quizList;
                
                //if questions stored then need them too
			}
			if($role=='Learner')
			{
				/*get questions from $config->questions and show game chosen
                    selected questions are stored in db as array of question_id
                    retrieve questions from delphinium_roots_quiz_questions
                */
                
			}
			// code for both
            $gameQuest = $this->getSomeQuestions($config->questions);
            $this->page['gameQuest'] = $gameQuest;
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

	/* Instructor can choose questions from multiple quizzes
        return list of quizzes for instructor to choose questions
	*/
	public function getAllQuizzes()
    {
        $fresh_data = false;//true;
		$roots = new Roots();
		$req = new QuizRequest(ActionType::GET, null, $fresh_data, true);
        //echo json_encode($roots->quizzes($req));
		
		// remove quizzes with no questions
		
		//contains questions and answers for each quiz
		return json_encode($roots->quizzes($req));
    }
    
    /* Learner needs question objects for game
        use Delphinium\Roots\Models\Quizquestion as questionsModel; 
        
        $gameQuest = $this->getSomeQuestions($config->questions);
        $this->page['gameQuest'] = $gameQuest;
    */
    public function getSomeQuestions($idList)
    {
        $ids = explode(",", $idList);
        $length = count($ids);
        $questionArray = array();
        for ($i=0; $i<$length; $i++) {
            // get question matching question_id
            $question = questionsModel::where(array('question_id'=>$ids[$i]))->first();
            //array_push($questionArray, $ids[$i]);//test ok
            array_push($questionArray, $question);
        }
        return json_encode($questionArray);
    }
    
    
	/*UNUSED SO FAR
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

	/**
	*  https://octobercms.com/docs/plugin/components#dropdown-properties
	*  The method should have a name in the following format: get*Property*Options()
	*  where Property is the property name
	*  Fills the Configuration [dropdown] in CMS
	*/
    public function getInstanceOptions()
    {
		$instances = popquizModel::all();// records
        $array_dropdown = ['0'=>'- select Instance - '];//id, text in dropdown
		// populate CMS dropdown
        foreach ($instances as $instance)
        {
            $array_dropdown[$instance->id] = $instance->Name;
        }
        return $array_dropdown;
    }
    
    /**Added
	*  frontend update component submit button
	*  save to database and return updated record
    *
    *  id is disabled in fields.yaml
    *  id, course & copy can also be hidden
    *  $data gets .id from config.id at instructor.htm
    *  called from instructor.htm configure settings modal
	*/
	public function onUpdate()
    {
        $data = post('Popquiz');//component name
        $did = intval($data['id']);// convert string to integer
        $config = popquizModel::find($did);// retrieve existing record
        $config->quiz_name = $data['quiz_name'];// change to new data
        //echo json_encode($config);//($data);// testing
        
        $config->quiz_description=$data['quiz_description'];
		$config->game_style=$data['game_style'];
		$config->questions=$data['questions'];

		$config->course_id = $data['course_id'];//hidden in frontend
        $config->copy_id = $data['copy_id'];//hidden
		$config->save();// update original record 
		return json_encode($config);// back to instructor view
    }
}