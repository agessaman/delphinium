<?php namespace Delphinium\Vanilla\Controllers;

use Backend\Classes\Controller;
use BackendMenu;

class Vanilla extends Controller
{
    public $implement = ['Backend\Behaviors\ListController','Backend\Behaviors\FormController'];
    
    public $listConfig = 'config_list.yaml';
    public $formConfig = 'config_form.yaml';

    public function __construct()
    {
        parent::__construct();
        //The first parameter specifies the author and plugin names.
        //The second parameter sets the menu code.
        //The optional third parameter specifies the submenu code.
        BackendMenu::setContext('Delphinium.Greenhouse', 'greenhouse', 'greenhouse');
    }
}