<?php namespace Delphinium\CreateQuizAssigment;

use System\Classes\PluginBase;

/**
 * CreateQuizAssigment Plugin Information File
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
            'name'        => 'CreateQuizAssigment',
            'description' => 'Custom Quiz',
            'author'      => 'Delphinium',
            'icon'        => 'icon-leaf'
        ];
    }

    public function registerComponents()
    {
        return [
            'Delphinium\CreateQuizAssigment\Components\CreateQuizBuilder' => 'QuizBuilder'
        ];
    }

}
