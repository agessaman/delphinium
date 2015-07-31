<?php namespace Delphinium\Iris\Controllers;

use BackendMenu;
use Flash;
use Backend\Classes\Controller;
use Delphinium\Iris\Models\Home as Chart;

class Home extends Controller
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
    
    /**
     * Deleted checked chart instances.
     */
    public function index_onDelete()
    {
        if (($checkedIds = post('checked')) && is_array($checkedIds) && count($checkedIds)) {

            foreach ($checkedIds as $chartId) {
                if (!$chart = Chart::find($chartId)) continue;
                $chart->delete();
            }

            Flash::success("Successfully deleted");
        }
        else {
            Flash::error("An error occurred when trying to delete this item");
        }

        return $this->listRefresh();
    }
}