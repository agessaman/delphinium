<?php namespace Delphinium\Blossom\Controllers;

use BackendMenu;
use Backend\Classes\Controller;

/**
 * Experience Back-end Controller
 */
class Experience extends Controller
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