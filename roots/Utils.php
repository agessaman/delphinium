<?php namespace Delphinium\Roots;

use \DateTime;
use \DateTimeZone;

class Utils
{   
    public static function setLocalTimezone($value)
    {
        if(!isset($_SESSION)) 
        { 
            session_start(); 
    	}
        $localTimeZone = $_SESSION['timezone'];
        
        if(is_string($value))
        {
            return new DateTime($value,$localTimeZone);
        }
        else if($value instanceof DateTime)
        {
            return $value->setTimezone($localTimeZone);
        }
    }
    
    public static function setUTCTimezone($value)
    {
        if(is_string($value))
        {
            return new DateTime($value,new DateTimeZone('UTC'));
        }
        else if($value instanceof DateTime)
        {
            return $value->setTimezone(new DateTimeZone('UTC'));
        }
    }
    
    public static function getLocalTimeZone()
    {
        if(!isset($_SESSION)) 
        { 
            session_start(); 
    	}
        return $_SESSION['timezone'];
    }
}