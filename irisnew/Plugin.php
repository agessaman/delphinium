<?php

namespace Delphinium\Irisnew;

use System\Classes\PluginBase;
class Plugin extends PluginBase
{
    public function registerComponents()
    {
        $componentArray = array('\\Delphinium\\Irisnew\\Components\\IrisWalkThrough' => 'iriswalkthrough');
        return $componentArray;
    }
    public function registerSettings()
    {
    }
    function boot()
    {
        \Event::listen('backend.menu.extendItems', function ($manager) {
            $manager->addSideMenuItems('Delphinium.Greenhouse', 'greenhouse', array('WalkThrough' => array('label' => 'WalkThrough', 'icon' => 'oc-icon-adjust', 'owner' => 'Delphinium.Greenhouse', 'url' => \Backend::url('delphinium/irisnew/walkthrough'), 'group' => 'irisnew')));
        });
    }
}