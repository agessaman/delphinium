<?php namespace Delphinium\Vanilla\Controllers;

use BackendMenu;
use Backend\Classes\Controller;

/**
 * Vanilla Back-end Controller
 */
class Vanilla extends Controller
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
		//BackendMenu::setContext('Delphinium.Vanilla', 'vanilla', 'vanilla');
		//The first parameter specifies the author and plugin names.
		//The second parameter sets the menu code.
		//The optional third parameter specifies the submenu code.
		BackendMenu::setContext('Delphinium.Greenhouse', 'greenhouse', 'greenhouse');
    }
}