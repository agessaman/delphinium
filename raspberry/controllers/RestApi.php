<?php namespace Delphinium\Raspberry\Controllers;

use Illuminate\Routing\Controller;
use Delphinium\Raspberry\Models\OrderedModule;
use Delphinium\Raspberry\Classes\Api;


class RestApi extends Controller {
	
    public function index()
    {
        return "Hello, from RestApi";
    }

    public function saveModules()
    {
        $courseId = \Input::get('courseId');
        $modulesArray = \Input::get('modulesArray');

        $decoded = json_decode($modulesArray);
        
        $flat = $this->flatten($decoded, $courseId);
        $api = new Api();

        //TODO: use an actual cacheTime parameter, rather than hardcoding it
        $mods = $api->saveIrisModules($courseId, $flat, 10); 
        $iris = new \Delphinium\Iris\Classes\Iris();
        $result = $iris->buildTree($mods);
        return $result;
    }

    private function flatten(array $array, $courseId) 
    {
        //we will pass this value by reference
        $flatArray = array();
        $order = 0;

        $iris = new \Delphinium\Iris\Classes\Iris();
        $iris->recursive($courseId, $array, $flatArray);
        return $flatArray;
    }




    public function updateModule()
    {
        $moduleId = \Input::get('moduleId');
        $keyValueParams = json_decode(\Input::get('keyValueParams'), true);
        $api = new Api();
        return $api->updateModule($moduleId, $keyValueParams);
    }

    public function getModuleItems()
    {
        $moduleId = \Input::get('moduleId');
        $courseId = \Input::get('courseId');
        $api = new Api();
        $items = $api->getModuleItems($courseId, $moduleId);
        return json_encode($items);
    }

    public function getTags()
    {
        $contentId = \Input::get('contentId');

        $api = new Api();
        $tags = $api->getTags($contentId);
        return $tags;
    }

    public function addTags()
    {
        $contentId = \Input::get('contentId');
        $tags = \Input::get('tags');
        $courseId = \Input::get('courseId');
        $api = new Api();
        return $api->addTags($contentId, json_decode($tags), $courseId);

    }

    public function deleteTag()
    {
        $contentId = \Input::get('contentId');
        $tag = \Input::get('tag');
        $api = new Api();
        return $api->deleteTag($contentId, $tag);

    }

    public function getAvailableTags()
    {
        $courseId = \Input::get('courseId');
        $api = new Api();
        $result = $api->getAvailableTags($courseId);

        return $result;
    }

    public function getModuleStates()
    {
        $studentId = \Input::get('studentId');
        $courseId = \Input::get('courseId');
        $api = new Api();
        $data = $api->getModuleStates($courseId, $studentId);
        return $data;
    }

    public function getStudentSubmissions()
    {
        $studentId = \Input::get('studentId');
        $courseId = \Input::get('courseId');
        $api = new Api();
        $data = $api->getStudentSubmissions($courseId, $studentId);
        return $data;
    }
}