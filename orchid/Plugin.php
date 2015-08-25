<?php namespace Delphinium\Orchid;

use System\Classes\PluginBase;
use Backend;
use Event;

/**
 * orchid Plugin Information File
 */
class Plugin extends PluginBase
{

    /**
     * Returns information about this plugin.
     *
     * @return array
     */
    public function pluginDetails()
    {
        return [
            'name'        => 'orchid',
            'description' => 'No description provided yet...',
            'author'      => 'delphinium',
            'icon'        => 'icon-empire'
        ];
    }
	
	public function registerComponents()
	{
	  return [
		'\Delphinium\orchid\Components\quizlesson' => 'quizlesson'
	  ];
	}

	public function boot()
	{
	  Event::listen('backend.menu.extendItems', function($manager) {
		$manager->addSideMenuItems('Delphinium.Greenhouse', 'greenhouse', [
			'Orchid' => [
			  'label' => 'Orchid',
			  'icon'  => 'icon-empire',
			  'owner' => 'Delphinium.Greenhouse',
			  'url' => Backend::url('delphinium/orchid/quizlesson'),
			  'group' => 'Orchid',
			]
		  ]);
	  });
	}
}
