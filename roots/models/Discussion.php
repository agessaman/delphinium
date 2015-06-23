<?php namespace Delphinium\Roots\Models;

use Delphinium\Roots\Exceptions\InvalidParameterInRequestObjectException;

class Discussion
{   
    public $title;
    public $published;
    
    function __construct($title, $published) 
    {
        if(!is_string($title))
        {
            throw new InvalidParameterInRequestObjectException(get_class($this),"title", "Title must be a string");
        }
        
        $this->title = $title;
        $this->published = $published;
    }
}