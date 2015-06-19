<?php namespace Delphinium\Roots\UpdatableObjects;

use \DateTime;
use Delphinium\Roots\Exceptions\InvalidParameterInRequestObjectException;

class Module {
    public $name;
    public $unlock_at;
    public $prerequisite_module_ids;
      
    public $published;
    public $position;
    
    function getName() {
        return $this->name;
    }

    function getUnlock_at() {
        return $this->unlock_at;
    }

    function getPrerequisite_module_ids() {
        return $this->prerequisite_module_ids;
    }

        
    function __construct($name = null,  DateTime $utc_unlock_at = null, array $prerequisite_module_ids = null, $published = null, $position =1)
    {
        if (($name)&&(!is_string($name))) {
            throw new InvalidParameterInRequestObjectException(get_class($this),"name", "Parameter must be a string");
        }
        
        $current_date = new \DateTime("now");
        if (($utc_unlock_at)&&($current_date >  $utc_unlock_at))
        {
            throw new InvalidParameterInRequestObjectException(get_class($this),"unloack_at", "Unlock at date must be in the future");
        }
        
        if(($prerequisite_module_ids)&&!is_array($prerequisite_module_ids))
        {
            throw new InvalidParameterInRequestObjectException(get_class($this),"prerequisite_module_ids", "Parameter must be an array");
        }
        
        $this->name = $name;
        $this->unlock_at = ($utc_unlock_at) ? $utc_unlock_at->format("c") : null;
        $this->prerequisite_module_ids = $prerequisite_module_ids;
        $this->position = $position;
        
        if(!is_null($published))
        {
            $this->published = ($published) ? 'true' : 'false';
        }
    }
}