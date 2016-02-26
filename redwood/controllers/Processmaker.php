<?php namespace Delphinium\Redwood\Controllers;

use BackendMenu;
use Backend\Classes\Controller;
use Flash;
use Delphinium\Redwood\Models\Processmaker as ProcessmakerModel;

/**
 * Processmaker Back-end Controller
 */
class Processmaker extends Controller
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

    public function index_onDelete()
    {
        if (($checkedIds = post('checked')) && is_array($checkedIds) && count($checkedIds)) {

            foreach ($checkedIds as $pmId) {
                if (!$pm = ProcessmakerModel::find($pmId)) continue;
                $pm->delete();
            }

            Flash::success("Successfully deleted");
        }
        else {
            Flash::error("An error occurred when trying to delete this item");
        }

        return $this->listRefresh();
    }
}