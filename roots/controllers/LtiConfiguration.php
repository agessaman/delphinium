<?php

namespace Delphinium\Roots\Controllers;

use Flash;
use BackendMenu;
use Backend\Classes\Controller;
use Delphinium\Roots\Models\Developer;

class LtiConfiguration extends Controller {

    public $implement = [
        'Backend.Behaviors.FormController',
        'Backend.Behaviors.ListController'
    ];
    public $formConfig = 'config_form.yaml';
    public $listConfig = 'config_list.yaml';

    public function __construct() {
        parent::__construct();
        BackendMenu::setContext('Delphinium.Greenhouse', 'greenhouse', 'greenhouse');
    }
    
    /**
     * Deleted checked configuration instances.
     */
    public function index_onDelete()
    {
        if (($checkedIds = post('checked')) && is_array($checkedIds) && count($checkedIds)) {

            foreach ($checkedIds as $ltiId) {
                if (!$config = Developer::find($ltiId)) continue;
                $config->delete();
            }

            Flash::success("Successfully deleted");
        }
        else {
            Flash::error("An error occurred when trying to delete this item");
        }

        return $this->listRefresh();
    }

}
