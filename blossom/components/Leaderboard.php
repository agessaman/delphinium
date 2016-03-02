<?php namespace Delphinium\Blossom\Components;

use Cms\Classes\ComponentBase;
use Delphinium\Roots\Roots;
use Delphinium\Blossom\Components\Gradebook;
use Delphinium\Blossom\Models\Experience as ExperienceModel;
use Delphinium\Blossom\Components\Experience as ExperienceComponent;


class Leaderboard extends ComponentBase
{

    public $roots;
    public $gradebook;
    
    public function componentDetails()
    {
        return [
            'name'        => 'Leaderboard',
            'description' => 'Shows where student sits compaired to others in the class'
        ];
    }

    public function defineProperties() {
        return [
            'Experience' => [
                'title' => 'Experience Instance',
                'description' => 'Select the experience instance to display the student\'s bonus and penalties',
                'type' => 'dropdown',
            ]
        ];
    }

    public function getExperienceOptions() {
        $instances = ExperienceModel::all();

        if (count($instances) === 0) {
            return $array_dropdown = ["0" => "No instances available"];
        } else {
            $array_dropdown = ["0" => "- select Experience Instance - "];
            foreach ($instances as $instance) {
                $array_dropdown[$instance->id] = $instance->name;
            }
            return $array_dropdown;
        }
    }

    public function onRender(){
        //try
        //{
            $this->addJs("/plugins/delphinium/blossom/assets/javascript/leaderboard.js");
            $this->addCss("/plugins/delphinium/blossom/assets/css/main.css");
            $this->addCss("/plugins/delphinium/blossom/assets/css/leaderboard.css");
            
            $this->roots = new Roots();
            $this->gradebook = new Gradebook();

            //get all students in course
            $users = $this->roots->getStudentsInCourse();

            //get all scores
            //$scores = $this->gradebook->aggregateSubmissionScores();
            echo $this->gradebook->aggregateSubmissionScores();
            //$experienceInstance = ExperienceModel::find($this->property('Experience'));

            //$list = $this->gradebook->matchSubmissionsAndUsers($users, $scores, $experienceInstance);
        /*}
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
        }*/
    }

}