<?php

namespace Delphinium\Blade;

use System\Classes\PluginBase;

/**
 * Blade Plugin Information File
 */
class Plugin extends PluginBase {

    /**
     * Returns information about this plugin.
     *
     * @return array
     */
    public function pluginDetails() {
        return [
            'name' => 'Blade',
            'description' => 'Data modification layer using a rules engine',
            'author' => 'Delphinium',
            'icon' => 'icon-leaf'
        ];
    }

    public function registerComponents() {
        return [
            'Delphinium\Blade\Components\RuleManager' => 'ruleMgr'
        ];
    }

}
