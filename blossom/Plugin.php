<?php namespace Delphinium\Blossom;

use System\Classes\PluginBase;
use Backend;
Use Event;

/**
 * Blossom Plugin Information File
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
            'name'        => 'Blossom',
            'description' => 'Plug-in used to develop view for displaying grade information',
            'author'      => 'Jacob Reid',
            'icon'        => 'icon-asterisk'
        ];
    }

    public function registerComponents()
    {
        return [
            '\Delphinium\Blossom\Components\Grade' => 'grade',
			'\Delphinium\Blossom\Components\Bonus' => 'bonus',
            '\Delphinium\Blossom\Components\Experience' => 'experience',
            '\Delphinium\Blossom\Components\Leaderboard' => 'leaderboard',
            '\Delphinium\Blossom\Components\Competencies' => 'competencies',
        ];
    }
	
	
	public function boot()
    {
    	
    	Event::listen('backend.menu.extendItems', function($manager){	
    	
            $manager->addSideMenuItems('Delphinium.Greenhouse', 'greenhouse', [
                'Blossom' => [
					'label' => 'Blossom',
					'icon' => 'icon-asterisk',
					'owner' => 'Delphinium.Greenhouse',
					'url' => Backend::url('delphinium/blossom/configuration'),
				]
            ]);
            
        });
    }
}
