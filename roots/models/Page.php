<?php namespace Delphinium\Roots\Models;

use Delphinium\Roots\Enums\ModuleItemEnums\PageEditingRoles;
use Delphinium\Roots\Exceptions\InvalidParameterInRequestObjectException;

class Page
{   
    public $title;
    public $body;
    public $pageEditingRole;
    public $notifyOfUpdate;
    public $published;
    public $frontPage;
    
    function __construct($title, $body, $pageEditingRole,  $notifyOfUpdate, $published) 
    {
        if(!is_string($title))
        {
            throw new InvalidParameterInRequestObjectException(get_class($this),"title", "Parameter must be a string");
        }
        if(!PageEditingRoles::isValidValue($pageEditingRole))
        {
            throw new InvalidParameterInRequestObjectException(get_class($this),"pageEditingRole", "Page Editing Role is not valid");
        }
        if(!is_bool($notifyOfUpdate))
        {
            throw new InvalidParameterInRequestObjectException(get_class($this),"notifyOfUpdate", "Parameter must be a boolean");
        }
        if(!is_bool($published))
        {
            throw new InvalidParameterInRequestObjectException(get_class($this),"published", "Parameter must be a boolean");
        }
        $this->title = $title;
        $this->body = $body;
        $this->pageEditingRole = $pageEditingRole;
        $this->notifyOfUpdate = ($notifyOfUpdate)?true:false;
        $this->published = ($published)?true:false;
    }
    
}