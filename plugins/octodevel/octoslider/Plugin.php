<?php namespace OctoDevel\OctoSlider;

use Backend;
use Controller;
use System\Classes\PluginBase;

class Plugin extends PluginBase
{

    public function pluginDetails()
    {
        return [
            'name'        => 'Octo Slider',
            'description' => 'Create a slideshow for your website using jquery slideshow plugins.',
            'author'      => 'Octo Devel',
            'icon'        => 'icon-play-circle-o'
        ];
    }

    public function registerComponents()
    {
        return [
            'OctoDevel\OctoSlider\Components\Camera' => 'octosliderCamera',
            'OctoDevel\OctoSlider\Components\Nivo' => 'octosliderNivo',
            'OctoDevel\OctoSlider\Components\Simple' => 'octosliderSimple',
        ];
    }

    public function registerNavigation()
    {
        return [
            'octoslider' => [
                'label'       => 'Octo Slider',
                'url'         => Backend::url('octodevel/octoslider/items'),
                'icon'        => 'icon-play-circle-o',
                'permissions' => ['octoslider.*'],
                'order'       => 500,
                'sideMenu' => [
                    'items' => [
                        'label'       => 'Sliders',
                        'icon'        => 'icon-picture-o',
                        'url'         => Backend::url('octodevel/octoslider/items'),
                        'permissions' => ['octoslider.access_items'],
                    ]
                ]
            ]
        ];
    }

}