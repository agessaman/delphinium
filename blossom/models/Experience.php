<?php
/**
 * Copyright (C) 2012-2016 Project Delphinium - All Rights Reserved
 *
 * This file is subject to the terms and conditions defined in
 * file 'https://github.com/ProjectDelphinium/delphinium/blob/master/EULA',
 * which is part of this source code package.
 *
 * NOTICE:  All information contained herein is, and remains the property of Project Delphinium. The intellectual and technical concepts contained
 * herein are proprietary to Project Delphinium and may be covered by U.S. and Foreign Patents, patents in process, and are protected by trade secret or copyright law.
 * Dissemination of this information or reproduction of this material is strictly forbidden unless prior written permission is obtained
 * from Project Delphinium.
 *
 * THE RECEIPT OR POSSESSION OF THIS SOURCE CODE AND/OR RELATED INFORMATION DOES NOT CONVEY OR IMPLY ANY RIGHTS
 * TO REPRODUCE, DISCLOSE OR DISTRIBUTE ITS CONTENTS, OR TO MANUFACTURE, USE, OR SELL ANYTHING THAT IT  MAY DESCRIBE, IN WHOLE OR IN PART.
 *
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Non-commercial use only, you may not charge money for the software
 * You can modify personal copy of source-code but cannot distribute modifications
 * You may not distribute any version of this software, modified or otherwise
 */

namespace Delphinium\Blossom\Models;

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

    protected $fillable = ['*'];

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
        'size' => 'required',
        'course_id' => 'required'
    ];
    
    public $hasMany = [
        'milestones' => ['Delphinium\Blossom\Models\Milestone'],
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