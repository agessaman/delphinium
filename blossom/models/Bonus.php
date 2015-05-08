<?php namespace Delphinium\Blossom\Models;

use Model;

/**
 * bonus Model
 */
class Bonus extends Model
{

    /**
     * @var string The database table used by the model.
     */
    public $table = 'delphinium_blossom_bonuses';

    public $rules = [
        'Name'=>'required',
        'Component' => 'required'
    ];


}