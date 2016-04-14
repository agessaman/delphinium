<?php namespace Delphinium\Testing\Controllers;

use BackendMenu;
use Backend\Classes\Controller;

/**
 * My Controller Back-end Controller
 */
class MyController extends Controller
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

        BackendMenu::setContext('Delphinium.Testing', 'testing', 'mycontroller');
    }
}