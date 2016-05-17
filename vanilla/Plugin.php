<?php namespace Delphinium\Vanilla;
/**
 * Copyright (C) 2012-2016 Project Delphinium - All Rights Reserved
 *
 * This file is subject to the terms and conditions defined in
 * file 'https://github.com/ProjectDelphinium/delphinium/blob/master/EULA',
 * which is part of this source code package.
 *
 * NOTICE:  All information contained herein is, and remains the property of Project Delphinium. The intellectual and technical concepts contained
 * herein are proprietary to Project Delphinium and may be covered by U.S. and Foreign Patents, patents in process, and are protected by trade secret or copyright law.
 * Dissemination of this information or reproduction of this material is strictly forbidden unless prior written permission is obtained
 * from Project Delphinium.
 *
 * THE RECEIPT OR POSSESSION OF THIS SOURCE CODE AND/OR RELATED INFORMATION DOES NOT CONVEY OR IMPLY ANY RIGHTS
 * TO REPRODUCE, DISCLOSE OR DISTRIBUTE ITS CONTENTS, OR TO MANUFACTURE, USE, OR SELL ANYTHING THAT IT  MAY DESCRIBE, IN WHOLE OR IN PART.
 *
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Non-commercial use only, you may not charge money for the software
 * You can modify personal copy of source-code but cannot distribute modifications
 * You may not distribute any version of this software, modified or otherwise
 */
use Event;
use Backend;
use System\Classes\PluginBase;

/**
 * Vanilla Plugin Information File
 */
class Plugin extends PluginBase
{

    protected $controlLibrary;
    /**
     * Returns information about this plugin.
     *
     * @return array
     */
    public function pluginDetails()
    {
        return [
            'name'        => 'Vanilla',
            'description' => "This component speeds up the development of Project Delphinium\'s content",
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
            'Delphinium\Vanilla\Components\MyComponent' => 'myComponent',
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
        //TODO create permissions

        return [
            'delphinium.vanilla.some_permission' => [
                'tab' => 'Vanilla',
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
            'vanilla' => [
                'label'       => 'Vanilla',
                'url'         => \Backend::url('delphinium/vanilla'),
                'icon'        => 'icon-lemon-o',
                'permissions' => ['delphinium.vanilla.*'],
                'order'       => 500,

                'sideMenu' => [
                    'newContent' => [
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

    public function register()
    {
        //\BackendMenu::registerContextSidenavPartial('Delphinium.Vanilla', 'vanilla', '@/plugins/delphinium/vanilla/partials/_sidebar.htm');
    }

    public function boot()
    {

//        //here we fire the event so all other plugins can register
//        Event::fire('delphinium.vanilla.load');
//
//
//        Event::listen('backend.menu.extendItems', function($manager){
//
//            $manager->addSideMenuItems('Delphinium.Vanilla', 'vanilla', [
//                'new' => [
//                    'label' => 'New content',
//                    'icon' => 'icon-heart',
//                    'owner' => 'Delphinium.Vanilla',
//                    'url' => Backend::url('delphinium/vanilla/newcontent')
//                ],
//
//                'assets' => [
//                    'label' => 'Assets',
//                    'icon' => 'icon-sliders',
//                    'owner' => 'Delphinium.Vanilla',
//                    'url' => Backend::url('delphinium/vanilla/editor')
//                ]
//            ]);
//
//        });

//        //load code editor
//        Event::listen('pages.builder.registerControls', function($controlLibrary) {
//            $this->controlLibrary = $controlLibrary;
//
//            $this->registerCodeEditorWidget();
//        });
    }

    public function registerFormWidgets()
    {
        return [
            'Backend\FormWidgets\CodeEditor' => [
                'label' => 'Code editor',
                'code'  => 'codeeditor'
            ]
        ];
    }


//    protected function registerCodeEditorWidget()
//    {
//        $ignoreProperties = [
//            'placeholder',
//            'default',
//            'defaultFrom',
//            'dependsOn',
//            'trigger',
//            'preset',
//            'attributes'
//        ];
//
//        $properties = $this->getFieldSizeProperties();
//
//        $properties = array_merge($properties, [
//            'size' =>  [
//                'title' => Lang::get('rainlab.builder::lang.form.property_attributes_size'),
//                'type' => 'dropdown',
//                'options' => [
//                    'tiny' => Lang::get('rainlab.builder::lang.form.property_attributes_size_tiny'),
//                    'small' => Lang::get('rainlab.builder::lang.form.property_attributes_size_small'),
//                    'large' => Lang::get('rainlab.builder::lang.form.property_attributes_size_large'),
//                    'huge' => Lang::get('rainlab.builder::lang.form.property_attributes_size_huge'),
//                    'giant' => Lang::get('rainlab.builder::lang.form.property_attributes_size_giant')
//                ],
//                'sortOrder' => 81
//            ],
//            'language' => [
//                'title' => Lang::get('rainlab.builder::lang.form.property_code_language'),
//                'group' => Lang::get('rainlab.builder::lang.form.property_group_code_editor'),
//                'type' => 'dropdown',
//                'default' => 'php',
//                'options' => [
//                    'css' => 'CSS',
//                    'html' => 'HTML',
//                    'javascript' => 'JavaScript',
//                    'less' => 'LESS',
//                    'markdown' => 'Markdown',
//                    'php' => 'PHP',
//                    'plain_text' => 'Plain text',
//                    'sass' => 'SASS',
//                    'scss' => 'SCSS',
//                    'twig' => 'Twig'
//                ],
//                'sortOrder' => 82
//            ],
//            'theme' => [
//                'title' => Lang::get('rainlab.builder::lang.form.property_code_theme'),
//                'group' => Lang::get('rainlab.builder::lang.form.property_group_code_editor'),
//                'type' => 'dropdown',
//                'default' => '',
//                'ignoreIfEmpty' => true,
//                'options' => [
//                    '' => Lang::get('rainlab.builder::lang.form.property_theme_use_default'),
//                    'ambiance' => 'Ambiance',
//                    'chaos' => 'Chaos',
//                    'chrome' => 'Chrome',
//                    'clouds' => 'Clouds',
//                    'clouds_midnight' => 'Clouds midnight',
//                    'cobalt' => 'Cobalt',
//                    'crimson_editor' => 'Crimson editor',
//                    'dawn' => 'Dawn',
//                    'dreamweaver' => 'Dreamweaver',
//                    'eclipse' => 'Eclipse',
//                    'github' => 'Github',
//                    'idle_fingers' => 'Idle fingers',
//                    'iplastic' => 'IPlastic',
//                    'katzenmilch' => 'Katzenmilch',
//                    'kr_theme' => 'krTheme',
//                    'kuroir' => 'Kuroir',
//                    'merbivore' => 'Merbivore',
//                    'merbivore_soft' => 'Merbivore soft',
//                    'mono_industrial' => 'Mono industrial',
//                    'monokai' => 'Monokai',
//                    'pastel_on_dark' => 'Pastel on dark',
//                    'solarized_dark' => 'Solarized dark',
//                    'solarized_light' => 'Solarized light',
//                    'sqlserver' => 'SQL server',
//                    'terminal' => 'Terminal',
//                    'textmate' => 'Textmate',
//                    'tomorrow' => 'Tomorrow',
//                    'tomorrow_night' => 'Tomorrow night',
//                    'tomorrow_night_blue' => 'Tomorrow night blue',
//                    'tomorrow_night_bright' => 'Tomorrow night bright',
//                    'tomorrow_night_eighties' => 'Tomorrow night eighties',
//                    'twilight' => 'Twilight',
//                    'vibrant_ink' => 'Vibrant ink',
//                    'xcode' => 'XCode'
//                ],
//                'sortOrder' => 83
//            ],
//            'showGutter' => [
//                'title' => Lang::get('rainlab.builder::lang.form.property_gutter'),
//                'group' => Lang::get('rainlab.builder::lang.form.property_group_code_editor'),
//                'type' => 'dropdown',
//                'default' => '',
//                'ignoreIfEmpty' => true,
//                'booleanValues' => true,
//                'options' => [
//                    '' => Lang::get('rainlab.builder::lang.form.property_use_default'),
//                    'true' => Lang::get('rainlab.builder::lang.form.property_gutter_show'),
//                    'false' => Lang::get('rainlab.builder::lang.form.property_gutter_hide'),
//                ],
//                'sortOrder' => 84
//            ],
//            'wordWrap' => [
//                'title' => Lang::get('rainlab.builder::lang.form.property_wordwrap'),
//                'group' => Lang::get('rainlab.builder::lang.form.property_group_code_editor'),
//                'type' => 'dropdown',
//                'default' => '',
//                'ignoreIfEmpty' => true,
//                'booleanValues' => true,
//                'options' => [
//                    '' => Lang::get('rainlab.builder::lang.form.property_use_default'),
//                    'true' => Lang::get('rainlab.builder::lang.form.property_wordwrap_wrap'),
//                    'false' => Lang::get('rainlab.builder::lang.form.property_wordwrap_nowrap'),
//                ],
//                'sortOrder' => 85
//            ],
//            'fontSize' => [
//                'title' => Lang::get('rainlab.builder::lang.form.property_fontsize'),
//                'group' => Lang::get('rainlab.builder::lang.form.property_group_code_editor'),
//                'type' => 'dropdown',
//                'default' => '',
//                'ignoreIfEmpty' => true,
//                'options' => [
//                    '' => Lang::get('rainlab.builder::lang.form.property_use_default'),
//                    '10' => '10px',
//                    '11' => '11px',
//                    '12' => '11px',
//                    '13' => '13px',
//                    '14' => '14px',
//                    '16' => '16px',
//                    '18' => '18px',
//                    '20' => '20px',
//                    '24' => '24px'
//                ],
//                'sortOrder' => 86
//            ],
//            'codeFolding' => [
//                'title' => Lang::get('rainlab.builder::lang.form.property_codefolding'),
//                'group' => Lang::get('rainlab.builder::lang.form.property_group_code_editor'),
//                'type' => 'dropdown',
//                'default' => '',
//                'ignoreIfEmpty' => true,
//                'options' => [
//                    '' => Lang::get('rainlab.builder::lang.form.property_use_default'),
//                    'manual' => Lang::get('rainlab.builder::lang.form.property_codefolding_manual'),
//                    'markbegin' => Lang::get('rainlab.builder::lang.form.property_codefolding_markbegin'),
//                    'markbeginend' => Lang::get('rainlab.builder::lang.form.property_codefolding_markbeginend'),
//                ],
//                'sortOrder' => 87
//            ],
//            'autoClosing' => [
//                'title' => Lang::get('rainlab.builder::lang.form.property_autoclosing'),
//                'group' => Lang::get('rainlab.builder::lang.form.property_group_code_editor'),
//                'type' => 'dropdown',
//                'default' => '',
//                'ignoreIfEmpty' => true,
//                'booleanValues' => true,
//                'options' => [
//                    '' => Lang::get('rainlab.builder::lang.form.property_use_default'),
//                    'true' => Lang::get('rainlab.builder::lang.form.property_enabled'),
//                    'false' => Lang::get('rainlab.builder::lang.form.property_disabled')
//                ],
//                'sortOrder' => 88
//            ],
//            'useSoftTabs' => [
//                'title' => Lang::get('rainlab.builder::lang.form.property_soft_tabs'),
//                'group' => Lang::get('rainlab.builder::lang.form.property_group_code_editor'),
//                'type' => 'dropdown',
//                'default' => '',
//                'ignoreIfEmpty' => true,
//                'booleanValues' => true,
//                'options' => [
//                    '' => Lang::get('rainlab.builder::lang.form.property_use_default'),
//                    'true' => Lang::get('rainlab.builder::lang.form.property_enabled'),
//                    'false' => Lang::get('rainlab.builder::lang.form.property_disabled')
//                ],
//                'sortOrder' => 89
//            ],
//            'tabSize' => [
//                'title' => Lang::get('rainlab.builder::lang.form.property_tab_size'),
//                'group' => Lang::get('rainlab.builder::lang.form.property_group_code_editor'),
//                'type' => 'dropdown',
//                'default' => '',
//                'ignoreIfEmpty' => true,
//                'options' => [
//                    '' => Lang::get('rainlab.builder::lang.form.property_use_default'),
//                    2 => 2,
//                    4 => 4,
//                    8 => 8
//                ],
//                'sortOrder' => 90
//            ],
//            'readOnly' => [
//                'title' => Lang::get('rainlab.builder::lang.form.property_readonly'),
//                'group' => Lang::get('rainlab.builder::lang.form.property_group_code_editor'),
//                'default' => 0,
//                'ignoreIfEmpty' => true,
//                'type' => 'checkbox'
//            ]
//        ]);
//
//        $this->controlLibrary->registerControl('codeeditor',
//            'rainlab.builder::lang.form.control_codeeditor',
//            'rainlab.builder::lang.form.control_codeeditor_description',
//            ControlLibrary::GROUP_WIDGETS,
//            'icon-code',
//            $this->controlLibrary->getStandardProperties($ignoreProperties, $properties),
//            null
//        );
//    }

}

