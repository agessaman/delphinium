<?php namespace Delphinium\Vanilla\Controllers;

use BackendMenu;
use Backend\Classes\Controller;
use Delphinium\Vanilla\Classes\Plugin;
use Delphinium\Vanilla\Widgets\PluginList;

/**
 * Index Back-end Controller
 */
class Index extends Controller
{
    public function __construct()
    {
        parent::__construct();

        BackendMenu::setContext('Delphinium.Vanilla', 'vanilla', 'index');

        //plugins directory
        $destinationPath = '/plugins/';

        $this->plugin = Plugin::load($destinationPath);
        try {
            //this is the plugin list from builder. Used to select the active plugin
            new PluginList($this, 'pluginList');
//
//            new ComponentList($this, 'componentList');
//            new AssetList($this, 'assetList', $destinationPath);
//            new Widget($this, 'delphiniumize');
        }
        catch (Exception $ex) {
            $this->handleError($ex);
        }

        return;
    }

    public function index()
    {

    }
}