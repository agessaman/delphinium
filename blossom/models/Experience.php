<?php namespace Delphinium\Blossom\Models;

use Model;

/**
 * experience Model
 */
class Experience extends Model
{

    /**
     * @var string The database table used by the model.
     */
    public $table = 'delphinium_blossom_experiences';

    public $rules = [
        'Name'=>'required',
        'Maximum'=>'required',
        'Milestones' => 'required',
        'Start Date' => 'required',
        'End Date' => 'required',
        'Animate'=>'required',
        'Size' => 'required'
    ];

}