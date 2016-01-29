<?php namespace Delphinium\Blossom\Components;

use Cms\Classes\ComponentBase;
use Delphinium\Blossom\Models\Experience as ExperienceModel;
use Delphinium\Blossom\Components\Experience as ExperienceComponent;

class Pace extends ComponentBase
{

    public function componentDetails()
    {
        return [
            'name'        => 'Pace',
            'description' => 'Displays Pace, Health, Gap and Stamina.'
        ];
    }

    public function defineProperties()
    {
        return [
            'Experience' => [
                'title' => 'Experience instance',
                'description' => 'Select the experience instance to display the student\'s bonus and penalties',
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
        try
        {
            $experienceInstance = ExperienceModel::find($this->property('Experience'));

            //don't multiply by zero!
            $milestoneNum = count($experienceInstance->milestones) > 0 ? count($experienceInstance->milestones) : 1;

            $this->page['maxPace'] = $experienceInstance->bonus_days * $experienceInstance->bonus_per_day * $milestoneNum;
            $this->page['minPace'] = -$experienceInstance->penalty_days * $experienceInstance->penalty_per_day * $milestoneNum;

            $this->page['paceSize'] = $experienceInstance->size;
            $this->page['paceAnimate'] = $experienceInstance->animate;

            $pacePenalties = $this->getPacePenalties();

            $this->page['totalPace'] = $pacePenalties === 0 ? 0 : round($pacePenalties->bonus, 2);
            $this->page['totalPenalties'] = $pacePenalties === 0 ? 0 : round($pacePenalties->penalties, 2);

            if (!isset($_SESSION)) {
                session_start();
            }
            $roleStr = $_SESSION['roles'];

            $this->page['role'] = $roleStr;
            $this->addCss("/plugins/delphinium/blossom/assets/css/main.css");
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


    private function getPacePenalties($userId = null) {
        $experienceComp = new ExperienceComponent();
        if ((!is_null($this->property('Experience'))) && ($this->property('Experience') > 0)) {
            return $experienceComp->calculateTotalBonusPenalties($this->property('Experience'), $userId);
        } else {
            $obj = new \stdClass();
            $obj->pace = 0;
            $obj->penalties = 0;
        }
    }

}