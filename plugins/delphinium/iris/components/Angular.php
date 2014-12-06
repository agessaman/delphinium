<?php namespace Delphinium\Iris\Components;

use Cms\Classes\ComponentBase;
use Delphinium\Iris\Classes\Iris as IrisClass;
/**
 * Description of Angular
 *
 * @author Damaris Zarco
 */
class Angular  extends ComponentBase
{
    public function componentDetails()
    {
        return [
            'name'        => 'Angular + OctoberCms',
            'description' => 'First attempt at using angular with OctoberCms'
        ];
    }
    
    public function onRun()
    {   
//        \Cache::flush();
        $this->addJs("/plugins/delphinium/iris/assets/javascript/angular.min.js");
//        $this->addJs("/plugins/delphinium/iris/assets/javascript/angular-ui-tree.min.js");
        $this->addJs("/plugins/delphinium/iris/assets/javascript/angular-ui-tree.js");
        $this->addJs("/plugins/delphinium/iris/assets/javascript/ng-tags-input.js");
//        $this->addJs("/plugins/delphinium/iris/assets/javascript/ng-modal.min.js");
//        $this->addJs("/plugins/delphinium/iris/assets/javascript/app.js");
        $this->addJs("/plugins/delphinium/iris/assets/javascript/tree.js");
//        $this->addJs("/plugins/delphinium/iris/assets/javascript/jquery.min.js");
        
//    	$this->addCss('/plugins/delphinium/iris/assets/css/ng-modal.css');
    	$this->addCss('/plugins/delphinium/iris/assets/css/module-tree.css');
    	$this->addCss('/plugins/delphinium/iris/assets/css/ng-tags-input.css');		
        $this->addCss('/plugins/delphinium/iris/assets/css/angular-ui-tree.min.css');
        
        session_start();
        
        if(isset($_SESSION['courseID']))
        {
            $courseId = $_SESSION['courseID'];
            $encryptedToken = $_SESSION['userToken'];
        
            $decrypted =$encryptedToken;//\Crypt::decrypt($encryptedToken);
    	
            $iris = new IrisClass();
            $moduleData = $iris->getModules($courseId, $decrypted, 10, false);
            $this->page['courseId'] = $courseId;
            
            $result = $this->buildTree($moduleData);

            $tempArray =array();

            if(count($result)<1) //there weren't any parent-child relationships
            {
                $final = array();
                foreach($moduleData as $item)
                {
                    $item["children"] = [];
                    $final[] = $item;
                }
                $tempArray = $final;
            }
            else
            {
                $tempArray = $result;
            }

            $this->page['moduleData'] = json_encode($tempArray);
            
            
            $tags = $iris->getAvailableTags($courseId);
            if(strlen($tags)>0)
            {
                $tags = explode(', ', $tags);
            }
            else
            {
                $tags = [];
            }
            $this->page['avTags'] = json_encode($tags);
        }
    }
    
    private function buildTree(array &$elements, $parentId = 1) {
        $branch = array();

        foreach ($elements as $key=>$module) {
            if ($module['parentId'] == $parentId) 
            {

                $children = $this->buildTree($elements, $module['moduleId']);
                if ($children) {
                    $module['children'] = $children;
                }
                else
                {
                    $module['children'] = array();
                }
                $branch[] = $module;
                unset($elements[$module['moduleId']]);
            }
        }

        return $branch;

    }
}