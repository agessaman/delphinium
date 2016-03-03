<?php namespace Delphinium\Blossom\Models;

use Model;

/**
 * stats Model
 */
class Stats extends Model
{

    /**
     * @var string The database table used by the model.
     */
    public $table = 'delphinium_blossom_stats';

    public $rules = [
        'Name'=>'required',
        'Animate'=>'required',
        'Size' => 'required'
    ];


}