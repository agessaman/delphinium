<?php namespace Delphinium\Blossom\Controllers;

use Flash;
use BackendMenu;
use Backend\Classes\Controller;
use Delphinium\Blossom\Models\Experience as Model;
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
    
    /**
     * Delete 
     */
    public function index_onDelete()
    {
        if (($checkedIds = post('checked')) && is_array($checkedIds) && count($checkedIds)) {

            foreach ($checkedIds as $expId) {
                if (!$experience = Model::find($expId)) continue;
                $experience->delete();
            }

            Flash::success("Successfully deleted");
        }
        else {
            Flash::error("An error occurred when trying to delete this item");
        }

        return $this->listRefresh();
    }
}