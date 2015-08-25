<?php namespace Delphinium\Blossom\Controllers;

use Flash;
use BackendMenu;
use Backend\Classes\Controller;
use Delphinium\Blossom\Models\Milestone as Model;

/**
 * Milestone Back-end Controller
 */
class Milestone extends Controller
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
     * Delete 
     */
    public function index_onDelete()
    {
        if (($checkedIds = post('checked')) && is_array($checkedIds) && count($checkedIds)) {

            foreach ($checkedIds as $milestoneId) {
                if (!$milestone = Model::find($milestoneId)) continue;
                $milestone->delete();
            }

            Flash::success("Successfully deleted");
        }
        else {
            Flash::error("An error occurred when trying to delete this item");
        }

        return $this->listRefresh();
    }
}