<?php namespace Delphinium\Blossom\Models;

use Model;

/**
 * pace Model
 */
class Pace extends Model
{

    /**
     * @var string The database table used by the model.
     */
    public $table = 'delphinium_blossom_paces';

    public $rules = [
        'Name'=>'required',
        'Maximum'=>'required',
        'Minimum' => 'required',
        'Animate'=>'required',
        'Size' => 'required'
    ];


}