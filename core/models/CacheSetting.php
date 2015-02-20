<?php namespace Delphinium\Core\Models;

use Model;

class CacheSetting extends Model
{
    use \October\Rain\Database\Traits\Validation;

    public $table = 'delphinium_core_cache_settings';

    protected $primaryKey = 'cache_setting_id';
    /*
     * Validation
     */
     
    public $rules = [
        'cache_setting_id' => 'required',
        'time' => 'required',
    	'data_type'=>'required'
    ];
}