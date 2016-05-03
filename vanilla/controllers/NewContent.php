<?php namespace Delphinium\Vanilla\Controllers;

use BackendMenu;
use Backend\Classes\Controller;

/**
 * New Back-end Controller
 */
class NewContent extends Controller
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

        BackendMenu::setContext('Delphinium.Vanilla', 'vanilla', 'delphiniumize');
    }
}