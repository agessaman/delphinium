<?php namespace Delphinium\Blossom\Components;

use \DateTime;
use Cms\Classes\ComponentBase;
use Delphinium\Blossom\Models\Experience as Model;

class ExperienceManager extends ComponentBase
{

    public function componentDetails()
    {
        return [
            'name'        => 'Experience Manager',
            'description' => 'No description provided yet...'
        ];
    }

    public function onRun()
    {
        $this->addJs("/plugins/delphinium/blossom/assets/javascript/jquery.min.js");
        $this->addJs("/plugins/delphinium/blossom/assets/javascript/bootstrap.min.js");
        $this->addJs("/plugins/delphinium/blossom/assets/javascript/bootstrap-datepicker.min.js");
        $this->addJs("/plugins/delphinium/blossom/assets/javascript/experience_manager.js");
        
        $this->addCss("/plugins/delphinium/blossom/assets/css/bootstrap-datepicker.min.css");
        
        $this->page['items'] = Model::all();
    }
    public function onAdd()
    {       
        $items = post('items', []);
        
        $name = post('new_name');
        $totalPoints = post('new_total_points');
        $startDate = post('new_start_date');
        $endDate = post('new_end_date');
        $bonusPerDay = post('new_bonus_per_day');
        $bonusDays = post('new_bonus_days');
        $penaltyPerDay = post('new_penalty_per_day');
        $penaltyDays = post('new_penalty_days');

        $experience = new Model();
        $experience->name = $name;
        $experience->total_points = $totalPoints;
        $start = new DateTime($startDate);
        $experience->start_date = $start->format('c');
        $end = new DateTime($endDate);
        $experience->end_date = $end->format('c');
        $experience->bonus_per_day = $bonusPerDay;
        $experience->bonus_days = $bonusDays;
        $experience->penalty_per_day = $penaltyPerDay;
        $experience->penalty_days = $penaltyDays;
        $experience->save();
        
        $this->page['items'] = Model::all();
        
    }
    
    public function onDelete()
    {
        $id = post('id');
        
        Model::where('id', '=', $id)->delete();
    }

}