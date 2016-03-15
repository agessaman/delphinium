<?php namespace Delphinium\Blossom\Components;

use Cms\Classes\ComponentBase;
use Delphinium\Blossom\Models\Experience as ExperienceModel;
use Delphinium\Blossom\Models\Stats as StatsModel;
use Delphinium\Blossom\Components\Experience as ExperienceComponent;
use Delphinium\Roots\Roots;

class Stats extends ComponentBase
{

    public $courseId;
    public $statsInstanceId;
    public function componentDetails()
    {
        return [
            'name'        => 'Stats',
            'description' => 'Displays Pace, Health, Gap and Stamina.'
        ];
    }

    public function defineProperties()
    {
        return [
            'Experience' => [
                'title' => 'Experience instance',
                'description' => 'Select the experience instance to display the student\'s stats',
                'type' => 'dropdown',
                'validationPattern' => '^[1-9][0-9]*$',//check that they've selected an option from the drop down. The default placeholder is=0
                'validationMessage' => 'Select an instance of Experience from the dropdown'
            ],
//            'Stats' => [
//                'title' => 'Stats instance',
//                'description' => 'Select the stats instance to display',
//                'type' => 'dropdown',
//                'depends'     => ['Experience'],
//                'validationPattern' => '^[1-9][0-9]*$',//check that they've selected an option from the drop down. The default placeholder is=0
//                'validationMessage' => 'Select an instance of stats from the dropdown'
//            ],
            'Copy'	=> [
                'title'             => 'Copy name',
                'description'       => 'Enter the name of this copy of the processmaker component',
                'type'              => 'string',
                'required'          => 'true',
                'validationMessage' => 'Please enter a copy name'
            ]
        ];
    }

    public function getExperienceOptions() {
        $instances = ExperienceModel::all();

        if (count($instances) === 0) {
            return $array_dropdown = ["0" => "No instances available. Component won\'t work"];
        } else {
            $array_dropdown = ["0" => "- select Experience Instance - "];
            foreach ($instances as $instance) {
                $array_dropdown[$instance->id] = $instance->name;
            }
            return $array_dropdown;
        }
    }

//    public function getStatsOptions()
//    {
//        $experienceId = Request::input('Experience'); // Load the country property value from POST
//        $course_id = ExperienceModel::find($experienceId);
//
//        $instances = StatsModel::where('course_id','=',$course_id)->get();;
//
//        if (count($instances) === 0) {
//            return $array_dropdown = ["0" => "No instances available. Component won\'t work"];
//        } else {
//            $array_dropdown = ["0" => "- select Stats Instance - "];
//            foreach ($instances as $instance) {
//                $array_dropdown[$instance->id] = $instance->name;
//            }
//            return $array_dropdown;
//        }
//    }

    public function onRun() {
//        try
//        {//load scripts
//        $this->addJs("/plugins/delphinium/blossom/assets/javascript/jquery-ui.min.js");
//        $this->addJs("/plugins/delphinium/blossom/assets/javascript/jquery.min.js");
            $statsInstance = $this->firstOrNewCourseInstance();

            $this->statsInstanceId = $statsInstance->id;
            //if no instance exists of this component, create a new one. It will be tied to the experience component they have selected
            $this->addJs("/plugins/delphinium/blossom/assets/javascript/d3.min.js");
            $this->addJs("/plugins/delphinium/blossom/assets/javascript/stats.js");
        //add jquery stuff
//        $this->addJs("/plugins/delphinium/blossom/assets/javascript/bootstrap.min.js");
            $this->addCss('/modules/system/assets/ui/storm.css', 'core');
            $this->addCss("/plugins/delphinium/blossom/assets/css/stats.css");


            $statsInstance = StatsModel::find($this->statsInstanceId);
            $this->page['statsSize'] = $statsInstance->size;
            $this->page['statsAnimate'] = $statsInstance->animate;

            if (!isset($_SESSION)) {
                session_start();
            }
            $roleStr = $_SESSION['roles'];

            $this->page['role'] = $roleStr;
            $this->addCss("/plugins/delphinium/blossom/assets/css/main.css");

            if(stristr($roleStr, 'Learner'))
            {
                $this->student();
            }
            else{
                if(stristr($roleStr, 'Instructor')||stristr($roleStr, 'TeachingAssistant'))
                {//only students will be able to configure the component
                    $this->instructor();
                }
                $this->nonStudent();//everyone else will just see a blank component
            }

//        }
//        catch(\Delphinium\Roots\Exceptions\InvalidRequestException $e)
//        {
//            if($e->getCode()==401)//meaning there are two professors and one is trying to access the other professor's grades
//            {
//                return;
//            }
//            else
//            {
//                return \Response::make($this->controller->run('error'), 500);
//            }
//        }
//        catch (\GuzzleHttp\Exception\ClientException $e) {
//            echo "In order for experience to work properly you must be a student, or go into 'Student View'";
//            return;
//        }
//        catch(Delphinium\Roots\Exceptions\NonLtiException $e)
//        {
//            if($e->getCode()==584)
//            {
//                return \Response::make($this->controller->run('nonlti'), 500);
//            }
//        }
//        catch(\Exception $e)
//        {
//            if($e->getMessage()=='Invalid LMS')
//            {
//                return \Response::make($this->controller->run('nonlti'), 500);
//            }
//            return \Response::make($this->controller->run('error'), 500);
//        }
    }

    private function instructor()
    {
        $this->page['nonstudent']=1;
        $formController = new \Delphinium\Blossom\Controllers\Stats();
        $formController->create('frontend');
        // Append the formController to the page
        $this->page['form'] = $formController;
        $this->page['recordId'] = $this->statsInstanceId;

        //add the instructions page for the teacher
        $instructions = $formController->makePartial('instructions');
        $this->page['instructions'] = $instructions;
    }
    private function nonStudent(){
        $this->page['nonstudent']=1;
        $potential = new \stdClass();
        $potential->bonus = 0;
        $potential->penalties = 0;
        $this->page['potential'] = json_encode($potential);
        $this->page['redLine']= 0;

        $milestoneSummary = new \stdClass();
        $milestoneSummary->bonuses = 0;
        $milestoneSummary->penalties = 0;
        $milestoneSummary->total = 0;
        $this->page['milestoneSummary'] = json_encode($milestoneSummary);

        $healthObj = new \stdClass();
        $healthObj->maxPenalties = 0;
        $healthObj->maxBonuses = 0;
        $this->page['health'] = json_encode($healthObj);

        $gap = new \stdClass();
        $gap->minGap = 0;
        $gap->maxGap = 0;
        $this->page['gap'] = json_encode($gap);

        $stamina = new \stdClass();
        $stamina->ten = 0;
        $stamina->total = 0;
        $this->page['stamina'] = json_encode($stamina);
    }

    private function student()
    {
        $this->page['nonstudent']=0;
        //GAP
        if (!isset($_SESSION)) {
            session_start();
        }
        $studentId = $_SESSION['userID'];

        //get min and max gap =
        // MAX = experience points + max bonus points
        //MIN = experience points + max penalty points
        $experience = ExperienceModel::find($this->property('Experience'));
        $expTotalPoints = $experience->total_points;
        $maxBonus = $experience->bonus_per_day * $experience->bonus_days;
        $maxPenalties = $experience->penalty_per_day * $experience->penalty_days;

        //get red line points
        $experienceComp = new ExperienceComponent();
        $redLine = $experienceComp->getRedLinePoints($this->property('Experience'));
        $milestoneClearanceInfo = $experienceComp->getMilestoneClearanceInfo($this->property('Experience'));

        $potentialBonus =0.0;
        $potentialPenalties=0.0;
        foreach($milestoneClearanceInfo as $mileInfo)
        {
            if($mileInfo->cleared)
            {
                continue;
            }

            if($mileInfo->bonusPenalty>=0)
            {
                $potentialBonus+=$mileInfo->bonusPenalty;
            }
            else{
                $potentialPenalties+=$mileInfo->bonusPenalty;
            }
        }

        $potential = new \stdClass();
        $potential->bonus = $potentialBonus;
        $potential->penalties = $potentialPenalties;

        $this->page['potential'] = json_encode($potential);
        $this->page['redLine']= $redLine;
        //get milestone info (total points including bonus and penalties)
        $gradebookComponent = new Gradebook();
        $userIds = array($studentId);
        $milestoneSummary = $gradebookComponent->getSetOfUsersMilestoneInfo($this->property('Experience'), $userIds);

        if(count($milestoneSummary)>0)
        {
            $milestoneSummary = $milestoneSummary[0];
        }
        $this->page['milestoneSummary'] = json_encode($milestoneSummary);

        $milestoneNum = count($experience->milestones);
        $healthObj = new \stdClass();
        $healthObj->maxPenalties = $maxPenalties*$milestoneNum;
        $healthObj->maxBonuses = $maxBonus*$milestoneNum;
        $this->page['health'] = json_encode($healthObj);

        $gap = new \stdClass();
        $gap->minGap = $expTotalPoints+$maxPenalties;
        $gap->maxGap = $expTotalPoints+$maxBonus;
        $this->page['gap'] = json_encode($gap);

        $this->page['stamina'] = json_encode($this->calculateStamina());
    }

    private function getBonusPenalties($userId = null) {
        $experienceComp = new ExperienceComponent();
        if ((!is_null($this->property('Experience'))) && ($this->property('Experience') > 0)) {
            return $experienceComp->calculateTotalBonusPenalties($this->property('Experience'), $userId);
        } else {
            $obj = new \stdClass();
            $obj->bonus = 0;
            $obj->penalties = 0;
        }
    }

    private function calculateStamina()
    {
        $roots = new Roots();
        $analytics = $roots->getAnalyticsStudentAssignmentData(false);
        $average = 0.0;
        $percentageArr = array();
        $i=0;
        $averageObj = new \stdClass();

        foreach($analytics as $item)
        {
            if(isset($item->submission)&&!is_null($item->submission->score)&&!is_null($item->points_possible) &&($item->points_possible>0))
            {
                if($i==10)
                {//take the average of the first 10 assignments
                    $averageObj->ten = array_sum($percentageArr) / count($percentageArr);
                }
                $percentageArr[] = ($item->submission->score/$item->points_possible)*100;

                $i++;
            }
        }

        $average = array_sum($percentageArr) / count($percentageArr);
        $averageObj->total = $average;

        if(count($analytics)<=10)
        {//if there were less than 10 assignments we'll show the same average for both options
            $averageObj->ten = $average;
        }
        return $averageObj;
    }

    public function onSave()
    {
        $data = post('Stats');
        $statsInstance = $this->firstOrNewCourseInstance($data['name']);//get the instance
        $statsInstance = StatsModel::where(array('id' => $statsInstance->id))->first();
        $statsInstance->name = $data['name'];
        $statsInstance->size = $data['size'];
        $statsInstance->animate = $data['animate'];
        $statsInstance->course_id = $data['course_id'];
        $statsInstance->save();// update original record
        return json_encode($statsInstance);
    }

    private function firstOrNewCourseInstance($copyName=null)
    {
        if (!isset($_SESSION)) {
            session_start();
        }
        $courseId = $_SESSION['courseID'];
        $this->courseId = $courseId;

        //first use the copy name passed to this method, if any
        //if null, then use the property defined in the component
        //if null, just get the instance using the course id
        if(is_null($copyName) && !is_null($this->property('Copy')))
        {
            $copyName =$this->property('Copy');
        }

        if(!is_null($copyName))
        {
            $courseInstance =StatsModel::firstOrNew(array('course_id' => $courseId,'name'=>$this->property('Copy')));
            if(is_null($courseInstance->name)){$courseInstance->name=$this->property('Copy');}
        }
        else{
            $courseInstance =StatsModel::firstOrNew(array('course_id' => $courseId));
            if(is_null($courseInstance->name)){$courseInstance->name="CopyA";}
        }

        $this->statsInstanceId = $courseInstance->id;
        $this->page['instance_id'] = $this->statsInstanceId;
        $courseInstance->course_id = $courseId;
        if(is_null($courseInstance->animate)){$courseInstance->animate = 1;}
        if(is_null($courseInstance->size)){$courseInstance->size = 'medium';}
        $courseInstance->save();

        return $courseInstance;
    }
}