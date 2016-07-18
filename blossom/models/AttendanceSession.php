<?php namespace Delphinium\Blossom\Models;

use Model;

/**
 * Model
 */
class AttendanceSession extends Model
{
    use \October\Rain\Database\Traits\Validation;

    /*
     * Validation
     */

    public $rules = [
        'title'=>'required',
        'course_id'=>'required',
        'assignment_id' => 'required',
        'start_at'=> 'required',
        'duration_minutes'=> 'required',
        'percentage_fifteen'=> 'required',
        'percentage_thirty'=> 'required',
        'code'=> 'required'
    ];

    /*
     * Disable timestamps by default.
     * Remove this line if timestamps are defined in the database table.
     */
    public $timestamps = false;

    /**
     * @var string The database table used by the model.
     */
    public $table = 'delphinium_blossom_attendance_sessions';

}