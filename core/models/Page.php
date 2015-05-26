<?php namespace Delphinium\Core\Models;
use Delphinium\Core\Enums\ModuleItemEnums\PageEditingRoles;

class Page
{   
    public $title;
    public $body;
    public $pageEditingRole;
    public $notifyOfUpdate;
    public $published;
    public $frontPage;
    
    function __construct($title, $body, $pageEditingRole,  $notifyOfUpdate, $published, $frontPage) 
    {
        if(!is_string($title))
        {
            throw new InvalidParameterInRequestObjectException(get_class($this),"title", "Parameter must be a string");
        }
        if(!PageEditingRoles::isValidValue($pageEditingRole))
        {
            throw new InvalidParameterInRequestObjectException(get_class($this),"pageEditingRole", "Parameter is not valid");
        }
        if(!is_bool($notifyOfUpdate))
        {
            throw new InvalidParameterInRequestObjectException(get_class($this),"notifyOfUpdate", "Parameter must be a boolean");
        }
        if(!is_bool($published))
        {
            throw new InvalidParameterInRequestObjectException(get_class($this),"published", "Parameter must be a boolean");
        }
        if(!is_bool($frontPage))
        {
            throw new InvalidParameterInRequestObjectException(get_class($this),"frontPage", "Parameter must be a boolean");
        }
        $this->title = $title;
        $this->body = $body;
        $this->pageEditingRole = $pageEditingRole;
        $this->notifyOfUpdate = $notifyOfUpdate;
        $this->published = $published;
        $this->frontPage = $frontPage;
    }
    
}