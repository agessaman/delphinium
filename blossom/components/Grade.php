<?php

namespace Delphinium\Blossom\Components;

use Cms\Classes\ComponentBase;
use Delphinium\Blossom\Models\Grade as GradeModel;
use Delphinium\Blossom\Models\Experience as ExperienceModel;
use Delphinium\Blossom\Components\Experience as ExperienceComponent;
use Delphinium\Blossom\Models\Milestone;
use Delphinium\Roots\Roots;
use \DateTime;

class Grade extends ComponentBase {

    public function componentDetails() {
        return [
            'name' => 'Grade',
            'description' => 'Calculates and displays current grade'
        ];
    }

    public function defineProperties() {
        return [
            'experienceInstance' => [
                'title' => 'Experience instance',
                'description' => 'Select the experience instance. If one is provided, the grade calculation will include bonus and '
                    . 'penalties. If none are available the grade will be pulled from Canvas',
                'type' => 'dropdown',
            ],
            'size' => [
                'title' => 'Widget Size',
                'description' => 'Enter the size of the component (as a percentage, no sign)',
                'type' => 'string',
                'default' => '100',
                'validationPattern' => '^([1-9]|[1-9][0-9]|[1][0-9][0-9]|20[0-0])$',
                'validationMessage' => 'A number between 1 and 200 is required',
                'placeholder' => 'Enter a number w/o sign'
            ]
        ];
    }

    public function onRun()
    {
        try
        {
            if(!is_null($this->property('experienceInstance')))
            {
                $instance = ExperienceModel::find($this->property('experienceInstance'));
                $maxExperiencePts = $instance->total_points;


                $exComp = new ExperienceComponent();
                $exComp->initVariables($this->property('experienceInstance'));
                $points = $exComp->getUserPoints();

                $roots = new Roots();
                $standards = $roots->getGradingStandards();
                $grading_scheme = $standards[0]->grading_scheme;
                $bonusPenaltiesObj = $exComp->calculateTotalBonusPenalties($this->property('experienceInstance'));
                $totalBonusPenalties = ($bonusPenaltiesObj->bonus)+($bonusPenaltiesObj->penalties);//penalties come with negative sign

                $totalPoints = $points +$bonusPenaltiesObj->bonus + $bonusPenaltiesObj->penalties;
                $letterGrade = $this->getLetterGrade($totalPoints, $maxExperiencePts, $grading_scheme);

                //modify grading scheme for display to users
                foreach($grading_scheme as $grade)
                {
                    $grade->value = $grade->value * $maxExperiencePts;
                }
                $this->page['grading_scheme'] = json_encode($grading_scheme);


                $this->page['XP'] = round($points,2);
                $this->page['gradeBonus'] = round($totalBonusPenalties,2);
                $this->page['letterGrade'] = $letterGrade;
            }
            else
            {
                $this->page['XP'] = 0;
                $this->page['gradeBonus'] = 0;
                $this->page['letterGrade'] = "F";
            }

            //todo: get the bonus, etc from blade, not from experience
            $size = $this->property('size');
            $this->page['gradeSize'] = $size;

            $this->addJs("/plugins/delphinium/blossom/assets/javascript/grade.js");
            $this->addCss("/plugins/delphinium/blossom/assets/css/animate.css");
            $this->addCss("/plugins/delphinium/blossom/assets/css/grade.css");
            $this->addCss("/plugins/delphinium/blossom/assets/css/main.css");
        }
        catch(\Delphinium\Roots\Exceptions\InvalidRequestException $e)
        {
            if($e->getCode()==401)//meaning there are two professors and one is trying to access the other professor's grades
            {
                return;
            }
            else
            {
                return \Response::make($this->controller->run('error'), 500);
            }
        }
        catch (\GuzzleHttp\Exception\ClientException $e) {
            echo "In order for experience to work properly you must be a student, or go into 'Student View'";
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

    public function getExperienceInstanceOptions()
    {
        $instances = ExperienceModel::all();

        if(count($instances)===0)
        {
            return $array_dropdown = ['0'=>'No instances available'];
        }
        else
        {
            $array_dropdown = ['0'=>'- select Experience Instance - '];
            foreach ($instances as $instance)
            {
                $array_dropdown[$instance->id] = $instance->name;
            }
            return $array_dropdown;
        }
    }

    public function getLetterGrade($studentPoints, $maxPoints, $gradingScheme) {
        if ($maxPoints === 0) {
            return "F";
        }
        foreach ($gradingScheme as $grade) {
            $newVal = $grade->value * $maxPoints;
            if ($studentPoints >= $newVal) {
                return $grade->name;
            }
        }
    }
}
