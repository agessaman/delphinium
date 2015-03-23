<?php namespace Delphinium\Core\UpdatableObjects;

use \DateTime;
use Delphinium\Core\Exceptions\InvalidParameterInRequestObjectException;

class Module {
    public $name;
    public $unlock_at;
    public $prerequisite_module_ids;
        
    function __construct($name,  DateTime $utc_unlock_at = null, array $prerequisite_module_ids = null)
    {
        if (!is_string($name)) {
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
    }
}