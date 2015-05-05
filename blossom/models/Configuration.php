<?php namespace Delphinium\Blossom\Models;

use Model;

/**
 * configuration Model
 */
class Configuration extends Model
{

    /**
     * @var string The database table used by the model.
     */
    public $table = 'delphinium_blossom_configurations';

    public $rules = [
        'Name'=>'required',
        'Component' => 'required'
    ];

}