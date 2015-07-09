<?php namespace Delphinium\Roots\Models;

use Delphinium\Roots\Exceptions\InvalidParameterInRequestObjectException;
use \DateTime;

class Quiz
{   
    public $title;
    public $due_at;
    public $published;
       
    function __construct($title, DateTime $due_at = null, $published = true) 
    {
        if(!is_string($title))
        {
            throw new InvalidParameterInRequestObjectException(get_class($this),"title", "Title must be a string");
        }
        
        $this->title = $title;
        $this->due_at = $due_at;
        $this->published = $published;
    }
}