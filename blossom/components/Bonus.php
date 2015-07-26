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
			
			'maxBonus' => [
				'title'        => 'Maximum Bonus points',
				'description'  => 'Enter Maximum Bonus points',
				'type'         => 'string',
				'default'      => '300',
				'validationPattern' => '^[0-9]+$',
                'validationMessage' => 'The Maximum Bonus points value is required and should be integer.'
			],
			
			'minBonus' => [
				'title'        => 'Minimum Bonus points',
				'description'  => 'Enter Minimum Bonus points',
				'type'         => 'string',
				'default'      => '-500',
			],
			
			'Animate' => [
				'title'        => 'Animate',
				'type'         => 'dropdown',
				'default'      => 'true',
				'options'      => ['true'=>'True', 'false'=>'False']
			],

			'Size' => [
				'title'        => 'Size',
				'type'         => 'dropdown',
				'default'      => 'Medium',
				'options'      => ['Small'=>'Small', 'Medium'=>'Medium', 'Large'=>'Large']
			],

			 'Instance' => [
                'title' => 'Instance',
                'description' => 'Select the Bonus instance',
                'type' => 'dropdown',
            ]
		];
    }
	
	 public function onRender()
    {
		$this->page['Bonus'] = $this->property('Bonus');
		$this->page['Penalty'] = $this->property('Penalty');
		$this->page['maxBonus'] = $this->property('maxBonus');
		$this->page['minBonus'] = $this->property('minBonus');
		$this->page['bonusAnimate'] = $this->property('Animate');
		$this->page['bonusSize'] = $this->property('Size');
    }
	
	public function onRun()
	{
		$this->addJs("/plugins/delphinium/blossom/assets/javascript/bonus.js");
		$this->addJs("/plugins/delphinium/blossom/assets/javascript/d3.min.js");
		$this->addCss("/plugins/delphinium/blossom/assets/css/main.css");
		//$config = Bonus::find($this->property('Instance'));
	}

	public function getBonusInstanceOptions() {
        $instances = BonusModel::all();
        $array_dropdown = ['0' => '- select a Bonus Instance - '];

        foreach ($instances as $instance) {
            $array_dropdown[$instance->id] = $instance->Name;
        }

        return $array_dropdown;
    }
}