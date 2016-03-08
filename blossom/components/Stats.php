<?php namespace Delphinium\Blossom\Components;

use Cms\Classes\ComponentBase;
use Delphinium\Blossom\Models\Experience as ExperienceModel;
use Delphinium\Blossom\Components\Experience as ExperienceComponent;
use Delphinium\Blossom\Components\Gradebook;
use Delphinium\Roots\Roots;
use Delphinium\Roots\Requestobjects\AssignmentsRequest;
use Delphinium\Roots\Requestobjects\SubmissionsRequest;

class Stats extends ComponentBase
{

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
                'type' => 'dropdown'
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


    public function onRun() {
//        try
//        {
            $experienceInstance = ExperienceModel::find($this->property('Experience'));
            $this->page['statsSize'] = $experienceInstance->size;
            $this->page['statsAnimate'] = $experienceInstance->animate;
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

//        }
//        catch (\GuzzleHttp\Exception\ClientException $e) {
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


    private function student()
    {
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

        $this->page['stamina'] = $this->calculateStamina();
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
        if (!isset($_SESSION)) {
            session_start();
        }
        $analytics = $roots->getAnalyticsStudentAssignmentData(false);

        $average = 0.0;
        $percentageArr = array();
        foreach($analytics as $item)
        {
            if(!is_null($item->submission->score))
            {
                $percentageArr[] = ($item->submission->score/$item->points_possible)*100;
            }
        }

        $average = array_sum($percentageArr) / count($percentageArr);
        return $average;
    }
}