<?php namespace Delphinium\Blossom\Components;

use Cms\Classes\ComponentBase;
use Delphinium\Roots\Roots;
use Delphinium\Roots\Requestobjects\SubmissionsRequest;
use Delphinium\Roots\Enums\ActionType;
use Delphinium\Blossom\Components\Gradebook;
use Delphinium\Blossom\Models\Experience as ExperienceModel;
use Delphinium\Blossom\Components\Experience as ExperienceComponent;
use Delphinium\Roots\Db\DbHelper;

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
                'description' => '(OPTIONAL) Select the experience instance to include the student\'s bonus and penalties',
                'type' => 'dropdown',
            ]
        ];
    }

    public function getExperienceOptions() {
        $instances = ExperienceModel::all();

        if (count($instances) === 0) {
            return $array_dropdown = ["0" => "No instances available."];
        } else {
            $array_dropdown = ["0" => "- select Experience Instance - "];
            foreach ($instances as $instance) {
                $array_dropdown[$instance->id] = $instance->name;
            }
            return $array_dropdown;
        }
    }

    public function onRender()
    {
        try {
            $this->addJs("/plugins/delphinium/blossom/assets/javascript/leaderboard.js");
            $this->addCss("/plugins/delphinium/blossom/assets/css/main.css");
            $this->addCss("/plugins/delphinium/blossom/assets/css/leaderboard.css");

            $this->roots = new Roots();
            $this->gradebook = new Gradebook();
            $users = $this->roots->getStudentsInCourse();
            $this->page['users'] = json_encode($users);
            $this->page['experienceInstanceId']=$this->property('Experience');

            if(!isset($_SESSION))
            {
                session_start();
            }
            $userId = $_SESSION['userID'];
            $courseId = $_SESSION['courseID'];
            $dbHelper = new DbHelper();
            $user = $dbHelper->getUserInCourse($courseId, $userId);
            $this->page['calling_user'] = json_encode($user);
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

}