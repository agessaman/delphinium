<?php namespace Delphinium\Redwood\Models;

use Model;

class Authorization extends Model
{
    use \October\Rain\Database\Traits\Validation;

    public $table = 'delphinium_redwood_pm_authorization';

    protected $fillable = array('*');
    /*
     * Validation
     */
    public $rules = [
        'workspace'=>'required',
        'encrypted_access_token' => 'required',
        'encrypted_refresh_token' => 'required',
        'expires_in' => 'required',
        'token_type' => 'required',
        'scope' => 'required'
    ];


}