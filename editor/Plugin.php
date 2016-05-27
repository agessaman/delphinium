<?php namespace Delphinium\Editor;

use Backend;
use System\Classes\PluginBase;

/**
 * Editor Plugin Information File
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
            'name'        => 'Editor',
            'description' => 'No description provided yet...',
            'author'      => 'Delphinium',
            'icon'        => 'icon-leaf'
        ];
    }

    /**
     * Registers any front-end components implemented in this plugin.
     *
     * @return array
     */
    public function registerComponents()
    {
        return []; // Remove this line to activate

        return [
            'Delphinium\Editor\Components\MyComponent' => 'myComponent',
        ];
    }

    /**
     * Registers any back-end permissions used by this plugin.
     *
     * @return array
     */
    public function registerPermissions()
    {
        return []; // Remove this line to activate

        return [
            'delphinium.editor.some_permission' => [
                'tab' => 'Editor',
                'label' => 'Some permission'
            ],
        ];
    }

    /**
     * Registers back-end navigation items for this plugin.
     *
     * @return array
     */
    public function registerNavigation()
    {
        return [
            'editor' => [
                'label'       => 'Editor',
                'url'         => \Backend::url('delphinium/editor'),
                'icon'        => 'icon-lemon-o',
                'permissions' => ['delphinium.editor.*'],
                'order'       => 500,

                'sideMenu' => [
                    'delphiniumize' => [
                        'label'       => 'Plugins',
                        'icon'        => 'icon-files-o',
                        'url'         => 'javascript:;',
                        'attributes'  => ['data-menu-item'=>'delphiniumize']
                    ],
                    'components' => [
                        'label'       => 'Components',
                        'icon'        => 'icon-puzzle-piece',
                        'url'         => 'javascript:;',
                        'attributes'  => ['data-menu-item'=>'components']
                    ],
                    'assets' => [
                        'label'       => 'Assets',
                        'icon'        => 'icon-picture-o',
                        'url'         => 'javascript:;',
                        'attributes'  => ['data-menu-item'=>'assets']
                    ]
                ]
            ]
        ];
    }

}
