<?php namespace Delphinium\Vanilla\Components;

use Cms\Classes\ComponentBase;
use Delphinium\Roots\Requestobjects\AssignmentsRequest;
use Delphinium\Roots\Requestobjects\QuizRequest;
use Delphinium\Roots\Enums\ActionType;
use Delphinium\Roots\Roots;
use \DateTime;

class Vanilla extends ComponentBase
{
	public $roots;
	
    public function componentDetails()
    {
        return [
            'name'        => 'vanilla Component',
            'description' => 'Vanilla Comp Desc.'
        ];
    }

    public function defineProperties()
    {
        return [];
    }
	
	public function onRun(){
		
        $this->addCss("/plugins/delphinium/vanilla/assets/css/main.css");
        $this->addCss("/plugins/delphinium/vanilla/assets/css/bootstrap.min.css");
        $this->addCss("/plugins/delphinium/vanilla/assets/css/bootstrap-theme.min.css");
        $this->addJs("/plugins/delphinium/vanilla/assets/javascript/bootstrap.min.js");
		$this->addJs("/plugins/delphinium/vanilla/assets/javascript/d3.min.js");
        ///$this->addJs("/plugins/delphinium/vanilla/assets/javascript/jquery.min.js");
        
		//Switched to DEV Config: Sandbox:Student /courses/368564/quizzes/657678
		//token from course: 14~0c94slDv0JoamEzSA3OZTEanVvdAPRiqQaHCjvDl4amD2XyVgq0BLfV0KHRt15Yc
		//3262753;//Sandbox.WebGL assignment//
		// no data returned but no Error either.
        
        echo "<h4>Vanilla.php onRun</h4>";
		$assignment_id = 1660430;// define, must exist
        $freshData = false;// not in DB yet
        $includeTags = true;
        $req = new AssignmentsRequest(ActionType::GET, $assignment_id, $freshData, null, $includeTags);
		$this->roots = new Roots();
        $res = json_encode($this->roots->assignments($req));
		//test obj as json || or store a global $Results ?
		//echo "<p>";
        //echo $res;// or // return json_encode($res);
		//echo "</p>";

		$this->page['theAssignment']=$res;//[0] make available to twig!
		//657678;//Sandbox.delpiquiz//
		$quiz_id=657776;//621794;// one question T/F
		$qres=json_encode($this->getQuiz($quiz_id));
		$this->page['aQuiz']=$qres;//then store it for twig 'aQuiz'
		
		//$this->getQuizQuestions($quiz_id);// see it
		
		/*
		My sandbox: 368564 DEV_Aviation-Course1-2015
		question_banks/437156 has 15 questions basic knowledge multiple_choice
		https://uvu.instructure.com/courses/368564/question_banks/437156

		DelphiTest Quiz id: 657678
		.../courses/368564/quizzes/657678
		use 368564 in new DevConfig option
		*/
		echo"<hr/>";
		
	}
	
	public function getAllQuizzes()
    {
        $req = new QuizRequest(ActionType::GET, null, $fresh_data = false, true);
		$result = json_encode($this->roots->quizzes($req));// null-id, = all
        echo $result;
		$this->page['allQuizzes']=$result;// available to twig!
    }
    public function getQuiz($quiz_id)
    {   
        $req = new QuizRequest(ActionType::GET, $quiz_id, $fresh_data = false, true);
        $result = $this->roots->quizzes($req);//623912 ,true
		
		//echo $result;// display on screen for testing
		//echo "<br/><br/>";
		return $result;// local variable
    }
    
    public function getQuizQuestions($quiz_id)
    {
        $req = new QuizRequest(ActionType::GET, $quiz_id, $fresh_data = false, true);
        $result = $this->roots->quizzes($req);// test // id 623912
        
        //echo json_encode($result);
		//echo "<hr/>";// view obj & details

        foreach($result['questions'] as $question)
        {
            $answers = $question['answers'];
            $obj = json_decode($answers, true);
			echo $question."<hr/>";
			echo $answers."<hr/>";
			
			echo $question['text']."<br>";
            foreach($obj as $answer)
            {
                echo json_encode($answer['text'])."<br/>";
            }
        }
    }
}