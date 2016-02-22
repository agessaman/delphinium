<?php namespace Delphinium\Blossom\Controllers;

use Flash;
use BackendMenu;
use Backend\Classes\Controller;
use Delphinium\Blossom\FormWidgets\ColorPicker;
use Delphinium\Blossom\Models\Competencies as ConfigModel;
use Delphinium\Blossom\Components\Competencies as ConfigComp;

/**
 * Competencies Back-end Controller
 */
class Competencies extends Controller
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
	/*
	https://octobercms.com/forum/post/plugin-initform-error?page=1
	Make sure there are no tabs in yaml file (spaces only!)
	*/
	/**
     * Delete checked instances.
	 * called from /controllers/list_toolbar.htm Remove button
     */
    public function index_onDelete()
    {
        if (($checkedIds = post('checked')) && is_array($checkedIds) && count($checkedIds)) {

            foreach ($checkedIds as $configId) {
                if (!$config = ConfigModel::find($configId)) continue;
                $config->delete();
            }

            Flash::success("Successfully deleted");
        }
        else {
            Flash::error("An error occurred when trying to delete this item");
        }

        return $this->listRefresh();
    }
	
    public function formExtendFields($form)
	{
		// fill values with instance data, must be in controllers
		// How to get data values
		//$config = ConfigComp::getConfig();
		
		//$form->getField('Name')->value = 'sometext added';
    }
	
}