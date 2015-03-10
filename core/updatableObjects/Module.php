<?php namespace Delphinium\Core\UpdatableObjects;

use \DateTime;
use Delphinium\Core\Exceptions\InvalidParameterInRequestObjectException;
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
class Module {
    private $name;
    private $unlock_at;
    private $published;
    private $prereq_mod_ids;
        
    function __construct($name, DateTime $unloack_at, $published,  $prereq_mod_ids)
    {
        if (!is_string($name)) {
            throw new InvalidParameterInRequestObjectException(get_class($this),"name", "Parameter must be a string");
        }
        
        $current_date = new \DateTime("now");
        if ($current_date >  $unloack_at)
        {
            throw new InvalidParameterInRequestObjectException(get_class($this),"unloack_at", "Unlock at date must be in the future");
        }
        
        if (!is_bool($published))
        {
            throw new InvalidParameterInRequestObjectException(get_class($this),"published", "Parameter must be boolean");
        }
        
        try
        {
            $arr = explode(", ", $prereq_mod_ids);
        } 
        catch (Exception $ex) {
            throw new InvalidParameterInRequestObjectException(get_class($this),"prereq_mod_ids", "Parameter must be a CSV string");
        }
        
        $this->name = $name;
        $this->unlock_at = $unloack_at;
        $this->published = $published;
        $this->prereq_mod_ids = $prereq_mod_ids;
    }
}