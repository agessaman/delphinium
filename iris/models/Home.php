<?php namespace Delphinium\Iris\Models;

use Model;
use Delphinium\Roots\Classes\CustomModel;
use October\Rain\Support\ValidationException;
use Delphinium\Blade\Models\Rule;

class Home extends CustomModel
{
    use \October\Rain\Database\Traits\Validation;

    public $table = 'delphinium_iris_charts';

    /*
     * Validation
     */
    public $rules = [
        'Name' => 'required',
    ];

    public function save(array $data = null, $sessionKey = null)
    {
//        if(!isset($_SESSION)) 
//        { 
//            session_start(); 
//    	}
//        
//        $courseId = $_SESSION['courseID'];
//        $rule = Rule::firstOrNew(array('name' => 'bonus_'.$courseId));
//        $rule->save();
//        do somethin
        return parent::save($data, $sessionKey);
    }
    
    public function delete()
    {
//        if(!isset($_SESSION)) 
//        { 
//            session_start(); 
//    	}
//        
//        $courseId = $_SESSION['courseID'];
//        $rule = Rule::where(array(
//                    'name' => 'bonus_'.$courseId))->first();
//        if($rule)
//        {
//            $rule->delete();
//        }
        //do something
       return parent::delete();//the parent doesn't have a delete method, so gotta call the grandparent 
    }
}