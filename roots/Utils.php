<?php namespace Delphinium\Roots;

use \DateTime;
use \DateTimeZone;

class Utils
{
    public static function convertUTCDateTimetoLocal($inUtcDateTime)
    {
        if(!isset($_SESSION)) 
        { 
            session_start(); 
    	}
        
        $localTimeZone = $_SESSION['timezone'];
        
        //check if we have a string or a DateTime obj
        if(is_string($inUtcDateTime))
        {
            return new DateTime($inUtcDateTime,$localTimeZone);
        }
        else
        {
            return $inUtcDateTime->setTimezone($localTimeZone);
        }
    }
    
    public static function convertLocalDateTimeToUTC($inLocalDateTime)
    {
        if(is_string($inLocalDateTime))
        {
            return new DateTime($inLocalDateTime,new DateTimeZone('UTC'));
        }
        else
        {
            return $inLocalDateTime->setTimezone(new DateTimeZone('UTC'));
        }
    }
}