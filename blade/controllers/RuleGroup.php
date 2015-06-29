<?php

namespace Delphinium\Blade\Controllers;

use BackendMenu;
use Backend\Classes\Controller;
use Delphinium\Blade\Models\RuleGroup as RuleGroupModel;

/**
 * RuleGroup Back-end Controller
 */
class RuleGroup extends Controller {

//    public $implement = [
//        'Backend.Behaviors.FormController',
//        'Backend.Behaviors.ListController'
//    ];
//
//    public $formConfig = 'config_form.yaml';
//    public $listConfig = 'config_list.yaml';

    public function __construct() {
        //parent::__construct();
        //BackendMenu::setContext('Delphinium.Blade', 'blade', 'rulegroup');
    }

    public function index() {
        return RuleGroupModel::all();
    }

    public function rules() {
        $id = \Input::get('id');
        $names = explode(',', \Input::get('names'));
       
        $result = RuleGroupModel::find($id);
        if ($result) {
            return $result->rules;
        }
        
        $result = RuleGroupModel::whereIn('name',$names)->get();
        if ($result) {
            return $result;
        }
        
        return null;
    }

}
