<?php namespace Delphinium\Greenhouse\Templates;

use Delphinium\Greenhouse\TemplateBase;

class Component extends TemplateBase
{
    /**
     * @var array A mapping of stub to generated file.
     */
    protected $fileMap = [
        'component/component.stub'  => 'components/{{studly_name}}.php',
        'component/default.stub' => 'components/{{lower_name}}/default.htm',
        'component/display.stub' => 'components/{{lower_name}}/display.htm',
        'component/instructor.stub' => 'components/{{lower_name}}/instructor.htm',
        'component/student.stub' => 'components/{{lower_name}}/student.htm',
    ];
}