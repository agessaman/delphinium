<?php namespace Delphinium\Blossom\Components;

use Cms\Classes\ComponentBase;

class Bonus extends ComponentBase
{

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
	}
}