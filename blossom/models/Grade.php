<?php namespace Delphinium\Blossom\Models;

use Model;

/**
 * grade Model
 */
class Grade extends Model
{

    /**
     * @var string The database table used by the model.
     */
    public $table = 'delphinium_blossom_grades';

    public $rules = [
        'Name'=>'required',
        'Animate'=>'required',
        'Size' => 'required'
    ];

}