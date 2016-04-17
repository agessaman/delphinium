<?php namespace Delphinium\BirdoParadise\Components;

use Cms\Classes\ComponentBase;
use Delphinium\Roots\Roots;
use Delphinium\Roots\Enums\ActionType;
use Delphinium\Roots\Requestobjects\AssignmentsRequest;// for submissions
use Delphinium\Roots\Requestobjects\SubmissionsRequest;// student progress

class Modulemap extends ComponentBase
{

    public function componentDetails()
    {
        return [
            'name'        => 'modulemap Component',
            'description' => 'Display Stem module data'
        ];
    }

    public function defineProperties()
    {
        return [];
    }

    public function onRun()
    {
        try
        {   
            if (!isset($_SESSION)) { session_start(); }

            // comma delimited string
            $roleStr = $_SESSION['roles'];

            if(stristr($roleStr, 'Learner')) {
                $roleStr = 'Learner';
            } else { 
                $roleStr = 'Instructor';
            }
            $this->page['role'] = $roleStr;// only one or the other
            
            // code for both 
            $roots = new Roots();
            $moduledata = $roots->getModuleTree(false);
			$this->page['moduledata'] = json_encode($moduledata);
            
            // ready to finish loading assets
            $this->addCss("/plugins/delphinium/birdoparadise/assets/css/font-autumn.css");
            $this->addCss("/plugins/delphinium/birdoparadise/assets/css/bop.css");
            $this->addJs("/plugins/delphinium/birdoparadise/assets/javascript/bop.js");
			
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
}
