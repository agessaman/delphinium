<?php namespace Delphinium\Blossom\Models;

use Model;

/**
 * Model
 */
class Gradebook extends Model
{
    use \October\Rain\Database\Traits\Validation;

    /*
     * Validation
     */
    public $rules = [
        'name'=>'required',
        'animate'=>'required',
        'size' => 'required',
        'course_id'=> 'required'
    ];

    /*
     * Disable timestamps by default.
     * Remove this line if timestamps are defined in the database table.
     */
    public $timestamps = false;

    /**
     * @var string The database table used by the model.
     */
    public $table = 'delphinium_blossom_gradebook';
}