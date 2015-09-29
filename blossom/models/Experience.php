<?php namespace Delphinium\Blossom\Models;

use Delphinium\Xylum\Models\CustomModel;
use Delphinium\Roots\Utils;
use \DateTime;
/**
 * experience Model
 */
class Experience extends CustomModel
{
    
    use \October\Rain\Database\Traits\Validation;
    /**
     * @var string The database table used by the model.
     */
    public $table = 'delphinium_blossom_experiences';

    protected $dates = ['start_date', 'end_date'];
    
    public $rules = [
        'name'=>'required',
        'total_points'=>'required',
        'start_date' => 'required',
        'end_date' => 'required',
        'bonus_per_day' => 'required',
        'penalty_per_day' => 'required',
        'bonus_days' => 'required',
        'penalty_days' => 'required',
        'animate'=>'required',
        'size' => 'required'
    ];

    public function getStartDateAttribute($value)
    {
        if(!is_null($value))
        {
            return  Utils::convertUTCDateTimetoLocal($value);
        }
    }
    
    public function getEndDateAttribute($value)
    {
        if(!is_null($value))
        {
            return Utils::convertUTCDateTimetoLocal($value);
        }
    }
     
    public function setStartDateAttribute($value)
    {
        //first convert it to local so it has the right timezone property
        $localDate = Utils::convertUTCDateTimetoLocal($value);
        //then convert it to UTC and store it like that
        $this->attributes['start_date'] = Utils::convertLocalDateTimeToUTC($localDate);
    }
    
    public function setEndDateAttribute($value)
    {
        //first convert it to local so it has the right timezone property
        $localDate = Utils::convertUTCDateTimetoLocal($value);
        //then convert it to UTC and store it like that
        $this->attributes['end_date'] = Utils::convertLocalDateTimeToUTC($localDate);
    }
    
    public function save(array $data = null, $sessionKey = null)
    {
        if(is_null($data))
        {
            $data = array();
        }
        $data['type'] = "Experience";
        
        return parent::save($data);
    }
}