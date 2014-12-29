<?php namespace Delphinium\Orchid\Controllers;

use BackendMenu;
use BackendAuth;
use Backend\Classes\Controller;

class Board extends Controller
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

		//The first parameter specifies the author and plugin names. 
		//The second parameter sets the menu code. 
		//The optional third parameter specifies the submenu code. 
        BackendMenu::setContext('Delphinium.Greenhouse', 'greenhouse', 'greenhouse');

    }
}