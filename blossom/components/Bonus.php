<?php namespace Delphinium\Blossom\Components;

use Cms\Classes\ComponentBase;

use Delphinium\Blossom\Models\Bonus as BonusModel;


class Bonus extends ComponentBase{

    public function componentDetails()
    {
        return [
            'name'        => 'Bonus',
            'description' => 'Displays bonus'
        ];
    }
	
	public function defineProperties()
    {
        return [
			
			'Bonus' => [
				'title'        => 'Bonus',
				'description'  => 'Enter Bonus',
				'type'         => 'string',
				'default'      => '200'
			],

			'Penalty' => [
				'title'        => 'Penalty',
				'description'  => 'Enter Penalty',
				'type'         => 'string',
				'default'      => '-32'
			],

			 'Instance' => [
                'title' => 'Instance',
                'description' => 'Select the Bonus instance',
                'type' => 'dropdown',
            ]
		];
    }
	
	public function onRun()
	{
		$this->addJs("/plugins/delphinium/blossom/assets/javascript/bonus.js");
		$this->addJs("/plugins/delphinium/blossom/assets/javascript/d3.min.js");
		$this->addCss("/plugins/delphinium/blossom/assets/css/main.css");
		$instance = BonusModel::find($this->property('Instance'));

        $this->page['Bonus'] = $this->property('Bonus');
		$this->page['Penalty'] = $this->property('Penalty');
		$this->page['name'] = $instance->Name;
		$this->page['maxBonus'] = $instance->Maximum;
		$this->page['minBonus'] = $instance->Minimum;
		$this->page['bonusAnimate'] = $instance->Animate;
		$this->page['bonusSize'] = $instance->Size;
		$this->page['role'] = $_POST['roles'];
	}
	
	public function onUpdate(){
			$instance = BonusModel::find($this->property('Instance'));
			$instance->Name = $_POST['name'];
			$instance->Maximum = $_POST['maxBonus'];
			$instance->Minimum = $_POST['minBonus'];
			$instance->Animate = $_POST['animate'];
			$instance->Size = $_POST['size'];
			$instance->save();
	}

	public function getInstanceOptions()
    {
    	$instances = BonusModel::where("id","!=","0")->get();

        $array_dropdown = ['0'=>'- select Bonus Instance - '];

        foreach ($instances as $instance)
        {
            $array_dropdown[$instance->id] = $instance->Name;
        }

        return $array_dropdown;
    }
}