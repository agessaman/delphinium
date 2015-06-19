<?php namespace Delphinium\Roots\Models;

use Delphinium\Roots\Exceptions\InvalidParameterInRequestObjectException;

class File 
{
    public $name;
    public $size;
    public $content_type;
    public $on_duplicate;
    
    function __construct($name, $size, $content_type, $on_duplicate)
    {
        if(!is_string($name))
        {
            throw new InvalidParameterInRequestObjectException(get_class($this),"name", "Name must be a string");
        }
        
        if(!is_int($size))
        {
            throw new InvalidParameterInRequestObjectException(get_class($this),"size", "Size must be an integer");
        }
        
        if(!is_string($content_type))
        {
            throw new InvalidParameterInRequestObjectException(get_class($this),"content_type", "Content_type must be a string");
        }
        
        if(!is_string($on_duplicate))
        {
            throw new InvalidParameterInRequestObjectException(get_class($this),"on_duplicate", "On_duplicate must be a string");
        }
        
        $this->name = $name;
        $this->size = $size;
        $this->content_type = $content_type;
        $this->on_duplicate = $on_duplicate;
    }
}