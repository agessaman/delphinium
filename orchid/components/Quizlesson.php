<?php namespace Delphinium\Orchid\Components;

use Cms\Classes\ComponentBase;

class Quizlesson extends ComponentBase
{

    public function componentDetails()
    {
        return [
            'name'        => 'Quiz Lesson',
            'description' => 'Embed quiz questions into Canvas Pages'
        ];
    }

    public function defineProperties()
    {
        return [];
    }

}