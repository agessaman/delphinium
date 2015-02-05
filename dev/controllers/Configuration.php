<?php namespace Delphinium\Dev\Controllers;

use BackendMenu;
use BackendAuth;
use Backend\Classes\Controller;

class Configuration extends Controller
{

    public $implement = [
        'Backend.Behaviors.FormController',
        'Backend.Behaviors.ListController'
    ];
    public $formConfig = 'config_form.yaml';
    public $listConfig = 'config_list.yaml';
   
    public function __construct()
    {
        parent::__construct();
        BackendMenu::setContext('Delphinium.Greenhouse', 'greenhouse', 'greenhouse');
    }
    
    
}