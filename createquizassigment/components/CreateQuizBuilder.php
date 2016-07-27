<?php
/**
 CreateQuizBuilder.php
 */

namespace Delphinium\CreateQuizAssigment\Components;

use Cms\Classes\ComponentBase;
//use Delphinium\Dev\Components\TestRoots;
use Delphinium\Roots\Roots;
use Delphinium\Roots\Requestobjects\QuizRequest;
use Delphinium\Roots\Enums\ActionType;
//use Delphinium\Quiz\Components\Session;
//use Delphinium\Quiz\Components\DbHelper;
use Delphinium\Roots\DB\DbHelper;
use Delphinium\Roots\Lmsclasses\CanvasHelper;

use Delphinium\Quiz\Models\quiz;
use Session;
use App;

class CreateQuizBuilder extends ComponentBase
{
    public $myDummyVariable;
    public $questions;

    public $roots;
    /*
   * Selected quiz questions
   * @var array
   */
    public $selection;


    /*
     * get all quizzes for a specific class
     * @var array
     */
    public $quizzes;

    public $datas;

    public $canvasHelper;

    public $quiz_attributes;

    public $questionId;
    //public $temp_quiz_attributes;
    /*
     * All quizzes
     * @var string
     */
    public $name;


    /*
    * question grades
    * @var string
    */
    public $gradePerQuestion;
    /**
     * Session instance.
     */
    protected $session;
    public function componentDetails()
    {
        return [
            'name'        => 'CreateQuizBuilder Component',
            'description' => 'No description provided yet...'
        ];
    }

    public function defineProperties()
    {
        return [];
    }
    public function onRun()
    {
        $this->myDummyVariable = "it's cold";
        // gets root for getting canvas data
        $this->roots = new Roots();
        // Crete a quiz request and populates  $this->quizzes to be use to create drop down at default.htm which is the view
        $req = new QuizRequest(ActionType::GET, null, $fresh_data = true);
        $res = $this->roots->quizzes($req);
        $this->quizzes = $res;
        // session_start();

    }

    public function onRetryQuestion()
    {
        // for submission create an instance of submision whichn has all quiz
        // data submision check submision.php
        // testIsQuestionAnswered() regresa answer if succes or no
        // NOTE: submit one and check if submission feedback
        //$studentId = $_SESSION['userID'];

        //note
        //stdClass is PHP's generic empty class, kind of like Object in Java or object in Python
        //(Edit: but not actually used as universal base class; thanks @Ciaran for pointing this out).
        //It is useful for anonymous objects, dynamic properties, etc.

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
        $retString = $this->gradePerQuestion;
        echo json_encode($result, JSON_PRETTY_PRINT);// $retString;
    }


    public function onGradeQuestion()
    {
        $this->roots = new Roots();

        $questionId = $_POST["val"];//get passed parameter from js
        $quizSubmissionId = 8287196;
        $quizId = 621794;

        $correct="";
        $incorrect="";
        $neutral="";

        $quizesInfo = Session::get('question_attributes');// onSelect ::put('question_attributes', $tempAtt);
        foreach($quizesInfo as $info) {

            if(isset($info)) {
                $val = (string)$questionId;
                // session data
                $temp = (string)isset($info[$val]["question_id"]) ? $info[$val]["question_id"] : -1;

                if ($temp == $val) {
                    $correct = $info[$questionId]["correct_comments"];
                    $incorrect = $info[$questionId]["incorrect_comments"];
                    $neutral = $info[$val]["neutral_comments"];
                }
            }
        }
        //$questionId = 10902238;
        // check if a question has been answered
        $answered = $this->roots->isQuestionAnswered($quizId,$questionId,$quizSubmissionId);

        // assign comments to quiz question
        $result = array(
            'correct_comments' => $correct,
            'incorrect_comments' => $incorrect,
            'neutral_comments' => $neutral
        );

        $this->datas = $result;
        $this->page['setOfDatas'] = $result;

        //header('Content-Type: application/json');
        $quizId = -1;
        $questionId = $_POST["val"];//get passed parameter from js

        // returning strings
        $retString = $questionId." id";
        foreach ($result as $key => $value)
        {
            $retString .=  $key. ":". $value .",";
        }

        if($dd = 1){
            print $retString;
        }
        else{
            echo "Fail";
        }
    }



    public function onSelect()
    {
        //session_start();


        $SELECTOPTION = 'Select';
        $tempAtt = array();
        // gets root for getting canvas data
        $this->roots = new Roots();
        // gets post variable from user quiz selection
        $this->page['selection'] = post('selectedQuiz');

        // get quiz_id to get all questions that belong to this quiz
        $quiz_id = $this->page['selection'];
        // avoid getting wrong data
        if($quiz_id != $SELECTOPTION) {
            // performe question search by quiz id
            $req = new QuizRequest(ActionType::GET, intval($quiz_id), false, true);
            // result get all questions
            $result = $this->roots->quizzes($req);
            // temp array to dynamicaly get the quiz id and the actual question
            $temp = array();
            $tempAnswers = array();
            //$info = '';
            foreach ($result['questions'] as $datas) {
                // use fro debugging data
                //$info = $datas['quiz_id'].'      '.$datas['question_id'].  '.- '. $datas['text'].'</br>';
                $answers = $datas['answers'];
                $obj = json_decode($answers, true);

                $questionType = "";

                if(count($obj) == 2 )
                    $questionType= 'trueFalse';
                if(count($obj) > 2 )
                    $questionType= 'multipleChoice';

                $ans = "";
                $questionAnswer = "";

                // add end of answers
                $tempAnswers = array();
                foreach ($obj as $answer) {
                    //$answer has an id for the answer
                    $ans = json_encode($answer['text']) . ",";
                    $questionAnswer .= $ans;

                    $t = trim($ans, ' \" ');

                    array_push($tempAnswers, trim($ans, ' \" '));
                }

                // create array containing question_id and actual question and answers (single or multiple choice)
                $question = array($datas['question_id'], $datas['text'], $tempAnswers,$questionType,$quiz_id);

                // add dynamically question info
                array_push($temp, $question);
                //$quiz_attributes=[$datas['question_id']=>$datas['attributes']];
                // this gets the question properties
                $temp_quiz_attributes = [$datas['question_id']=>$datas['attributes']];
                array_push($tempAtt,
                    $temp_quiz_attributes
                );
                $stop = 1;

            }
            $this->quiz_attributes = $tempAtt;

            Session::put('question_attributes', $tempAtt);

            // once we dynamically generate set of question its time to make it accessible to the partial
            // this is the way to pass data to the partial by creating page variables
            $this->page['setOfQuestions'] = $temp;

        }

    }

}