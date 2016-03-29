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
    public $experienceInstanceId;
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
            'experience' => [
                'title' => '(Optional) Experience instance',
                'description' => 'Select the experience instance to display the student\'s stats',
                'type' => 'dropdown'
            ],
            'stats' => [
                'title' => '(Optional) Stats instance',
                'description' => 'Select the stats instance to display. If an instance is selected, it will take precedence over the alias name',
                'type' => 'dropdown',
            ]
        ];
    }

    public function getExperienceOptions() {
        $instances = ExperienceModel::all();

        if (count($instances) === 0) {
            return $array_dropdown = ["0" => "No instances available."];
        } else {
            $array_dropdown = ["0" => "- select Experience Instance - "];
            foreach ($instances as $instance) {
                $array_dropdown[$instance->id] = $instance->name;
            }
            return $array_dropdown;
        }
    }

    public function getStatsOptions()
    {
        $instances = StatsModel::all();

        if (count($instances) === 0) {
            return $array_dropdown = ["0" => "No instances available"];
        } else {
            $array_dropdown = ["0" => "- select Stats Instance - "];
            foreach ($instances as $instance) {
                $array_dropdown[$instance->id] = $instance->name;
            }
            return $array_dropdown;
        }
    }

    public function onRun()
    {
        $this->addCss('/modules/system/assets/ui/storm.css', 'core');
//        try
//        {
        $statsInstance = $this->firstOrNewCourseInstance();
        $this->statsInstanceId = $statsInstance->id;
        $this->page['instance_id'] =  $statsInstance->id;
        $experienceInstance = $this->findExperienceInstance();

        //if no instance exists of this component, create a new one. It will be tied to the experience component they have selected
        $this->addJs("/plugins/delphinium/blossom/assets/javascript/d3.min.js");
        $this->addJs("/plugins/delphinium/blossom/assets/javascript/stats.js");
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

        if(stristr($roleStr, 'Instructor')||stristr($roleStr, 'TeachingAssistant'))
        {//only instructors will be able to configure the component
            $this->instructor();
        }
        else
        {
            $this->page['nonstudent']=0;
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

    private function firstOrNewCourseInstance($copyName=null)
    {
        if (!isset($_SESSION)) {
            session_start();
        }
        $courseId = $_SESSION['courseID'];
        $this->courseId = $courseId;
        $courseInstance = null;

        //if they have selected a backend instance, that will take precedence over creating a dynamic instance based on the component alias
        if(($this->property('stats'))>0)
        {
            $courseInstance =StatsModel::firstOrNew(array('id' => $this->property('stats')));
        }
        else
        {//didn't select a backend instance. Create the component based on the copy name, or the alias name if the copy name was not provided
            if(is_null($copyName))
            {
                $copyName =$this->alias;
            }
            $courseInstance =StatsModel::firstOrNew(array('course_id' => $courseId,'name'=>$copyName));
            $courseInstance->course_id = $courseId;
            $courseInstance->name = $copyName;
        }

        if(is_null($courseInstance->animate)){$courseInstance->animate = 1;}
        if(is_null($courseInstance->size)){$courseInstance->size = 'medium';}
        $courseInstance->save();

        return $courseInstance;
    }

    private function findExperienceInstance()
    {
        $experienceModel=null;
        if(is_null($this->property('experience'))||$this->property('experience')==0)
        {//find an instance of experience with the same course id
            if (!isset($_SESSION)) {
                session_start();
            }
            $courseId = $_SESSION['courseID'];
            $experienceModel = ExperienceModel::where('course_id','=',$courseId)->first();
            if(is_null($experienceModel))
            {//if no experience was created we will create one on the fly and tell the user to go configure it
                $experienceModel = ExperienceModel::firstOrNew(array('course_id' => $courseId));
                $experienceModel->name = "Experience_auto";
                $experienceModel->total_points = 1000;
                $today = new \DateTime('now');
                $experienceModel->start_date = $today;
                $newDate = new \DateTime('now');
                $tomorrow = $newDate->add(new \DateInterval('P10D'));
                $experienceModel->end_date = $tomorrow;
                $experienceModel->bonus_per_day = 1;
                $experienceModel->penalty_per_day = 1;
                $experienceModel->bonus_days = 5;
                $experienceModel->penalty_days = 5;
                $experienceModel->animate = 1;
                $experienceModel->size = 'medium';
                $experienceModel->course_id = $courseId;
                $experienceModel->save();
                $this->page['configureExperience']=1;
            }
            else
            {
                $this->page['configureExperience']=0;
            }
            $this->page['experienceInstanceId'] =$experienceModel->id;
            $this->experienceInstanceId = $experienceModel->id;
            return $experienceModel;
        }
        else
        {//use the selected instance
            $experienceModel= ExperienceModel::find($this->property('experience'))->first();
            $this->page['experienceInstanceId'] =$experienceModel->id;
            $this->page['configureExperience']=0;

            $this->experienceInstanceId = $experienceModel->id;
            return $experienceModel;
        }
    }

    private function instructor()
    {//add backend styles
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

    public function getStatsData($experienceInstanceId)
    {
        if (!isset($_SESSION)) {
            session_start();
        }
        $roleStr = $_SESSION['roles'];
        if(stristr($roleStr, 'Learner'))
        {
            return $this->student($experienceInstanceId);
        }
        else if(stristr($roleStr, 'Instructor')||stristr($roleStr, 'TeachingAssistant'))
        {
            return $this->nonStudent();//everyone else will just see a blank component
        }
        return [];
    }

    private function student($experienceInstanceId)
    {
        if (!isset($_SESSION)) {
            session_start();
        }
        $studentId = $_SESSION['userID'];
        //get min and max gap =
        // MAX = experience points + max bonus points
        //MIN = experience points + max penalty points
        $experience = ExperienceModel::find($experienceInstanceId);
        $expTotalPoints = $experience->total_points;
        $maxBonus = $experience->bonus_per_day * $experience->bonus_days;
        $maxPenalties = $experience->penalty_per_day * $experience->penalty_days;

        //get red line points
        $experienceComp = new ExperienceComponent();
        $redLine = $experienceComp->getRedLinePoints($experienceInstanceId);
        //get milestoneClearance info to get potential bonuses and penalties
        $milestoneClearanceInfo = $experienceComp->getMilestoneClearanceInfo($experienceInstanceId);
        $potentialBonus =0.0;
        $potentialPenalties=0.0;
        $penalties=0.0;
        $bonus=0.0;

        foreach($milestoneClearanceInfo as $mileInfo)
        {
            if($mileInfo->cleared)
            {
                if($mileInfo->bonusPenalty>=0)
                {
                    $bonus+=$mileInfo->bonusPenalty;
                }
                else{
                    $penalties+=$mileInfo->bonusPenalty;
                }
            }
            else
            {
                if($mileInfo->bonusPenalty>=0)
                {
                    $potentialBonus+=$mileInfo->bonusPenalty;
                }
                else{
                    $potentialPenalties+=$mileInfo->bonusPenalty;
                }
            }

        }

        $potential = new \stdClass();
        $potential->bonus = $potentialBonus;
        $potential->penalties = $potentialPenalties;

        $milestoneSummary =new \stdClass();
        $milestoneSummary->bonuses = $bonus;
        $milestoneSummary->penalties = $penalties;
        $milestoneSummary->total = $experienceComp->getUserPoints();

        $milestoneNum = count($experience->milestones);
        $healthObj = new \stdClass();
        $healthObj->maxPenalties = $maxPenalties*$milestoneNum;
        $healthObj->maxBonuses = $maxBonus*$milestoneNum;

        $gap = new \stdClass();
        $gap->minGap = $expTotalPoints+$maxPenalties;
        $gap->maxGap = $expTotalPoints+$maxBonus;

        $stamina = $this->calculateStamina();

        $returnObj = new \stdClass();
        $returnObj->nonstudent = 0;
        $returnObj->potential = $potential;
        $returnObj->redLine = $redLine;
        $returnObj->milestoneSummary = $milestoneSummary;
        $returnObj->health = $healthObj;
        $returnObj->gap = $gap;
        $returnObj->stamina = $stamina;
        return $returnObj;
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

    private function nonStudent()
    {
        $potential = new \stdClass();
        $potential->bonus = 0;
        $potential->penalties = 0;

        $milestoneSummary = new \stdClass();
        $milestoneSummary->bonuses = 0;
        $milestoneSummary->penalties = 0;
        $milestoneSummary->total = 0;

        $healthObj = new \stdClass();
        $healthObj->maxPenalties = 0;
        $healthObj->maxBonuses = 0;

        $gap = new \stdClass();
        $gap->minGap = 0;
        $gap->maxGap = 0;

        $stamina = new \stdClass();
        $stamina->ten = 0;
        $stamina->total = 0;

        $returnObj = new \stdClass();
        $returnObj->nonstudent = 1;
        $returnObj->potential = $potential;
        $returnObj->redLine = 0;
        $returnObj->milestoneSummary = $milestoneSummary;
        $returnObj->health = $healthObj;
        $returnObj->gap = $gap;
        $returnObj->stamina = $stamina;
        return $returnObj;
    }

    public function onSave()
    {
        $data = post('Stats');
        $statsInstance = $this->firstOrNewCourseInstance($data['name']);//get the instance
        $statsInstance->name = $data['name'];
        $statsInstance->size = $data['size'];
        $statsInstance->animate = $data['animate'];
        $statsInstance->course_id = $data['course_id'];
        $statsInstance->save();// update original record
        return json_encode($statsInstance);
    }
}