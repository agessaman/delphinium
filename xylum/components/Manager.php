<?php namespace Delphinium\Xylum\Components;

use Cms\Classes\ComponentBase;
use Delphinium\Xylum\Models\ComponentInstance;

class Manager extends ComponentBase
{

    public function componentDetails()
    {
        return [
            'name'        => 'Manager Component',
            'description' => 'No description provided yet...'
        ];
    }

    public function onRun() {
        try
        {
            $this->addJs("/plugins/delphinium/xylum/assets/javascript/angular.min.js");
            $this->addJs("/plugins/delphinium/xylum/assets/javascript/manager.js");
            $this->addCss('/plugins/delphinium/stem/assets/css/bootstrap.min.css');
            $this->prepareData();
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

    private function prepareData()
    {
        $this->page['allComponents'] = ComponentInstance::all();
    }

}