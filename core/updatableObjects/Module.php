<?php namespace Delphinium\Core\UpdatableObjects;

use \DateTime;
use Delphinium\Core\Exceptions\InvalidParameterInRequestObjectException;

class Module {
    public $name;
    public $unlock_at;
    public $prerequisite_module_ids;
        
    function __construct($name,  DateTime $unloack_at = null, array $prerequisite_module_ids = null)
    {
        if (!is_string($name)) {
            throw new InvalidParameterInRequestObjectException(get_class($this),"name", "Parameter must be a string");
        }
        
        $current_date = new \DateTime("now");
        if (($unloack_at)&&($current_date >  $unloack_at))
        {
            throw new InvalidParameterInRequestObjectException(get_class($this),"unloack_at", "Unlock at date must be in the future");
        }
        
        if(($prerequisite_module_ids)&&!is_array($prerequisite_module_ids))
        {
            throw new InvalidParameterInRequestObjectException(get_class($this),"prerequisite_module_ids", "Parameter must be an array");
        }
        
        
        $this->name = $name;
        $this->unlock_at = ($unloack_at) ? date_format($unloack_at, 'Y-m-d H:i:s') : null;
        $this->prerequisite_module_ids = $prerequisite_module_ids;
    }
}