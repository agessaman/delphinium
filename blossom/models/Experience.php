<?php namespace Delphinium\Blossom\Models;

use Delphinium\Xylum\Models\CustomModel;
use Delphinium\Roots\Utils;
use \DateTime;
use \DateTimeZone;
use Carbon\Carbon;
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

    protected $dates = ['start_date','end_date'];
    
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
    {//when coming from DB the date gets stripped of its timezone. So we need to set it back to UTC and then to local timezone
        if(!is_null($value))
        {//return a Carbon instance so that DatePicker doesn't crash
            $utc = Utils::setUTCTimezone($value);
            $date=  Utils::setLocalTimezone($utc);
            $carbon= Carbon::instance($date)->timezone(Utils::getLocalTimeZone()); 
           return $carbon;
        }
    }
    
    public function getEndDateAttribute($value)
    {//return a Carbon instance so that DatePicker doesn't crash
        if(!is_null($value))
        {
            $utc = Utils::setUTCTimezone($value);//when coming from DB the date gets stripped of its timezone. So we need to set it back to UTC and then to local timezone
            $date=  Utils::setLocalTimezone($utc);
            $carbon= Carbon::instance($date)->timezone(Utils::getLocalTimeZone()); 
            return $carbon;
        }
    }
     
    public function setStartDateAttribute($value)
    {//first convert it to local so it has the right timezone property. Then save it in UTC to the DB
        $date = Utils::setLocalTimezone($value);
        $this->attributes['start_date'] = Utils::setUTCTimezone($date);
    }
    
    public function setEndDateAttribute($value)
    {//first convert it to local so it has the right timezone property. Then save it in UTC to the DB
        $date = Utils::setLocalTimezone($value);
        $this->attributes['end_date'] = Utils::setUTCTimezone($date);
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