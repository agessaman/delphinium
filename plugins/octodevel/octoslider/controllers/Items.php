<?php namespace OctoDevel\OctoSlider\Controllers;

use Flash;
use BackendMenu;
use Backend\Classes\Controller;
use OctoDevel\OctoSlider\Models\Item;

class Items extends Controller
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

        BackendMenu::setContext('OctoDevel.OctoSlider', 'octoslider', 'items');
    }

    public function index_onDelete()
    {
        if (($checkedIds = post('checked')) && is_array($checkedIds) && count($checkedIds)) {

            foreach ($checkedIds as $itemId) {
                if (!$item = Item::find($itemId))
                    continue;

                $item->delete();
            }

            Flash::success('Successfully deleted those selected.');
        }

        return $this->listRefresh();
    }
}