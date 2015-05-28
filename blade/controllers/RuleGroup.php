<?php namespace Delphinium\Blade\Controllers;

use BackendMenu;
use Backend\Classes\Controller;

/**
 * RuleGroup Back-end Controller
 */
class RuleGroup extends Controller
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

        BackendMenu::setContext('Delphinium.Blade', 'blade', 'rulegroup');
    }
}