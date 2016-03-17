<?php namespace Delphinium\Vanilla;

use System\Classes\PluginBase;
use Backend;
use Event;

class Plugin extends PluginBase
{
     public function pluginDetails()
     {
       return [
         'name'        => 'vanilla',
         'description' => 'Test Plug-in',
         'author'      => 'T.Jones',
         'icon'        => 'icon-thumbs-up'
       ];
     }
    public function registerComponents()
    {
        return [
            '\Delphinium\Vanilla\Components\Vanilla' => 'vanilla'
          ];
    }
    public function boot()
    {
      Event::listen('backend.menu.extendItems', function($manager) {
        $manager->addSideMenuItems('Delphinium.Greenhouse', 'greenhouse', [
            'Vanilla' => [
              'label' => 'Vanilla',
              'icon'  => 'icon-thumbs-up',
              'owner' => 'Delphinium.Greenhouse',
              'url' => Backend::url('delphinium/vanilla/vanilla')
            ]
          ]);
      });
    }
    public function registerSettings()
    {
    }
}
