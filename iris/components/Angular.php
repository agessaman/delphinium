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
        \Cache::flush();
        $this->addJs("/plugins/delphinium/iris/assets/javascript/jquery.min.js");
        $this->addJs("/plugins/delphinium/iris/assets/javascript/angular.min.js");
        $this->addJs("/plugins/delphinium/iris/assets/javascript/angular-ui-tree.js");
        $this->addJs("/plugins/delphinium/iris/assets/javascript/tree.js");
    	$this->addCss('/plugins/delphinium/iris/assets/css/module-tree.css');	
        $this->addCss('/plugins/delphinium/iris/assets/css/angular-ui-tree.min.css');
        
//        session_start();
//        
//        if(isset($_SESSION['courseID']))
//        {
//            $courseId = $_SESSION['courseID'];
//            $encryptedToken = $_SESSION['userToken'];
//        
//            $decrypted =$encryptedToken;//\Crypt::decrypt($encryptedToken);
//            $this->prepareData($courseId, $decrypted, 10, false);
//        }
        $courseId = 343331;
        $this->page['userId'] = 1489289;
        $this->page['courseId'] = $courseId;
        $decrypted ="sdf";//\Crypt::decrypt($encryptedToken);
        $this->prepareData($courseId, $decrypted, 10, false);
    }
    
    public function onRefreshCache()
    {
        
//        session_start();
//        if(isset($_SESSION['courseID']))
//        {
//            $courseId = $_SESSION['courseID'];
//            $encryptedToken = $_SESSION['userToken'];
//        
//            $decrypted =$encryptedToken;//\Crypt::decrypt($encryptedToken);
//            
//            //by doing $cacheTime = -1 we grab fresh data
//            $this->prepareData($courseId, $decrypted, 0, false);
//        }
        $courseId = 343331;
        $decrypted ="sdf";//\Crypt::decrypt($encryptedToken);
        $this->prepareData($courseId, $decrypted, -1, false);
        
    }
    
    private function prepareData($courseId, $decrypted, $time, $forever)
    {
        $iris = new IrisClass();
        $moduleData = $iris->getModules($courseId, $decrypted, $time, $forever, null);

        $this->page['courseId'] = $courseId;
        $result = $iris->buildTree($moduleData);
//        var_dump($moduleData);
        $tempArray =array();

        if(count($result)<1) //there weren't any parent-child relationships
        {
            $parent;
            $allChildren;
            $final = array();

            //The parent will be the first PUBLISHED item
            $firstItem;
            foreach($moduleData as $item)
            {
                if($item['published'] == "1")
                {
                    $firstItem = $item;
                    break;
                }
            }
            $newArr = $this->unsetValue($moduleData, $firstItem);//remove parent from array
            $firstParentId=$firstItem["moduleId"];
            $i=0;
            foreach($newArr as $item)
            {
                $item["parentId"] = $firstParentId;
            //each item must have a parentId of the first module
                $item["children"] = [];
                $item["order"] = $i;
                $final[] = $item;
                $i++;
            }

            //remove the first Item (which is the parent)
            $firstItem["parentId"] = 1;
            $firstItem["children"]=$final;
            $firstItem["order"]=0;

            $tempArray[] = $firstItem;
            
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
    
    private function unsetValue(array $array, $value, $strict = TRUE)
    {
        if(($key = array_search($value, $array, $strict)) !== FALSE) {
            unset($array[$key]);
        }
        return $array;
    }
}