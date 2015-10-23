<?php

namespace Delphinium\Blossom\Components;

use Cms\Classes\ComponentBase;
use Delphinium\Blossom\Models\Experience as ExperienceModel;
use Delphinium\Blossom\Components\Experience as ExperienceComponent;

class Bonus extends ComponentBase {

    public function componentDetails() {
        return [
            'name' => 'Bonus',
            'description' => 'Displays bonus'
        ];
    }

    public function defineProperties() {
        return [
            'Experience' => [
                'title' => 'Experience instance',
                'description' => 'Select the experience instance to display the student\'s bonus and penalties',
                'type' => 'dropdown'
            ],
            'Size' => [
                'title' => 'Widget Size',
                'description' => 'Select the size of the component',
                'type' => 'dropdown'
            ]
        ];
    }

    public function getExperienceOptions()
    {
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
    
    public function getSizeOptions()
    {
        $arr=['small','medium','large'];
        return $arr;
    }
    
    public function onRun() {
        $experienceInstance = ExperienceModel::find($this->property('Experience'));

        
        $bonusPenalties = $this->getBonusPenalties();
        
        $this->page['bonus'] = $bonusPenalties === 0 ? 0 : round($bonusPenalties->bonus, 2);
        $this->page['penalties'] = $bonusPenalties === 0 ? 0 : round($bonusPenalties->penalties, 2);
            
            
        $this->page['maxBonus'] = $experienceInstance->bonus_days * $experienceInstance->bonus_per_day;
        $this->page['minBonus'] = $experienceInstance->penalty_days * $experienceInstance->penalty_per_day;
        $this->page['bonusSize'] = $this->property('Size');
        $this->page['role'] = 'Learner';//$_POST['roles'];
    }
    
    public function onRender()
    {
        $this->addJs("/plugins/delphinium/blossom/assets/javascript/d3.min.js");
        $this->addJs("/plugins/delphinium/blossom/assets/javascript/bonus.js");
        $this->addCss("/plugins/delphinium/blossom/assets/css/main.css");
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

}
