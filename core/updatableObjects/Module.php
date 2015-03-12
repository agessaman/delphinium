<?php namespace Delphinium\Core\UpdatableObjects;

use \DateTime;
use Delphinium\Core\Exceptions\InvalidParameterInRequestObjectException;
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
class Module {
    public $name;
    public $unlock_at;
    public $published;
    public $prerequisite_module_ids;
        
    function __construct($name, $published,  DateTime $unloack_at = null, array $prerequisite_module_ids = null)
    {
        if (!is_string($name)) {
            throw new InvalidParameterInRequestObjectException(get_class($this),"name", "Parameter must be a string");
        }
        
        $current_date = new \DateTime("now");
        if (($unloack_at)&&($current_date >  $unloack_at))
        {
            throw new InvalidParameterInRequestObjectException(get_class($this),"unloack_at", "Unlock at date must be in the future");
        }
        
        if (!is_bool($published))
        {
            throw new InvalidParameterInRequestObjectException(get_class($this),"published", "Parameter must be boolean");
        }
        
        if(($prerequisite_module_ids)&&!is_array($prerequisite_module_ids))
        {
            throw new InvalidParameterInRequestObjectException(get_class($this),"prerequisite_module_ids", "Parameter must be an array");
        }
        
        
        $this->name = $name;
        $this->unlock_at = ($unloack_at) ? date_format($unloack_at, 'Y-m-d H:i:s') : null;
        $this->published = ($published) ? 'true' : 'false';
        $this->prerequisite_module_ids = $prerequisite_module_ids;
    }
}