<?php namespace Delphinium\Blossom\Controllers;

use BackendMenu;
use Backend\Classes\Controller;
use Delphinium\Blossom\Models\Stats as StatsModel;
use Flash;
use Event;
/**
 * Stats Back-end Controller
 */
class Stats extends Controller
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

        Event::listen('backend.page.beforeDisplay', function($controller, $action, $params) {
//            $controller->addJs("/plugins/delphinium/blossom/assets/javascript/jquery.min.js");
        });
    }

    public function index_onDelete()
    {
        if (($checkedIds = post('checked')) && is_array($checkedIds) && count($checkedIds)) {

            foreach ($checkedIds as $statsId) {
                if (!$stats = StatsModel::find($statsId)) continue;
                $stats->delete();
            }

            Flash::success("Successfully deleted");
        }
        else {
            Flash::error("An error occurred when trying to delete this item");
        }

        return $this->listRefresh();
    }
}