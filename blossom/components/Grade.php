<?php namespace Delphinium\Blossom\Components;

use Cms\Classes\ComponentBase;

class Grade extends ComponentBase
{

    public function componentDetails()
    {
        return [
            'name'        => 'Grade',
            'description' => 'Calculates and displays current grade'
        ];
    }

    public function defineProperties()
    {
        return [
			'XP' => [
				'title'        => 'Experience Points',
				'description'  => 'Enter Experience Points',
				'type'         => 'string',
				'default'      => '5800',
				'validationPattern' => '^[0-9]+$',
                'validationMessage' => 'The Experience points value is required and should be integer.'
			],
			
			'Bonus' => [
				'title'        => 'Bonus',
				'description'  => 'Enter Bonus',
				'type'         => 'string',
				'default'      => '168'
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
        $this->page['XP'] = $this->property('XP');
		$this->page['gradeBonus'] = $this->property('Bonus');
		$this->page['gradeAnimate'] = $this->property('Animate');
		$this->page['gradeSize'] = $this->property('Size');

		$this->getGradeData();
    }
	
	public function onRun()
	{
		$this->addJs("/plugins/delphinium/blossom/assets/javascript/grade.js");
		$this->addCss("/plugins/delphinium/blossom/assets/css/animate.css");
		$this->addCss("/plugins/delphinium/blossom/assets/css/grade.css");
		$this->addCss("/plugins/delphinium/blossom/assets/css/main.css");
	}

	private function getGradeData(){
		$jsonData = '{
			"title": "New standard name",
			"id": 1,
			"context_id": 1,
			"context_type": "Course",
			"grading_scheme": [
				{"name": "A+", "value": 9700},
				{"name": "A", "value": 9500},
				{"name": "A-", "value": 9000},
				{"name": "B+", "value": 8700},
				{"name": "B", "value": 8400},
				{"name": "B-", "value": 8100},
				{"name": "C+", "value": 7700},
				{"name": "C", "value": 7400},
				{"name": "C-", "value": 7000},
				{"name": "D+", "value": 6700},
				{"name": "D", "value": 6400},
				{"name": "D-", "value": 6000},
				{"name": "F Keep going", "value": 0}

			]
		}';

		$gradeData = json_decode($jsonData);

		$this->page['apValue'] = $gradeData->grading_scheme[0]->value;
		$this->page['apName'] = $gradeData->grading_scheme[0]->name;
		$this->page['aValue'] = $gradeData->grading_scheme[1]->value;
		$this->page['aName'] = $gradeData->grading_scheme[1]->name;
		$this->page['amValue'] = $gradeData->grading_scheme[2]->value;
		$this->page['amName'] = $gradeData->grading_scheme[2]->name;
		$this->page['bpValue'] = $gradeData->grading_scheme[3]->value;
		$this->page['bpName'] = $gradeData->grading_scheme[3]->name;
		$this->page['bValue'] = $gradeData->grading_scheme[4]->value;
		$this->page['bName'] = $gradeData->grading_scheme[4]->name;
		$this->page['bmValue'] = $gradeData->grading_scheme[5]->value;
		$this->page['bmName'] = $gradeData->grading_scheme[5]->name;
		$this->page['cpValue'] = $gradeData->grading_scheme[6]->value;
		$this->page['cpName'] = $gradeData->grading_scheme[6]->name;
		$this->page['cValue'] = $gradeData->grading_scheme[7]->value;
		$this->page['cName'] = $gradeData->grading_scheme[7]->name;
		$this->page['cmValue'] = $gradeData->grading_scheme[8]->value;
		$this->page['cmName'] = $gradeData->grading_scheme[8]->name;
		$this->page['dpValue'] = $gradeData->grading_scheme[9]->value;
		$this->page['dpName'] = $gradeData->grading_scheme[9]->name;
		$this->page['dValue'] = $gradeData->grading_scheme[10]->value;
		$this->page['dName'] = $gradeData->grading_scheme[10]->name;
		$this->page['dmValue'] = $gradeData->grading_scheme[11]->value;
		$this->page['dmName'] = $gradeData->grading_scheme[11]->name;
		$this->page['fValue'] = $gradeData->grading_scheme[12]->value;
		$this->page['fName'] = $gradeData->grading_scheme[12]->name;

	}
}
