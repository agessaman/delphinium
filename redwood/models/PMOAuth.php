<?php namespace Delphinium\Redwood\Models;

use Model;

/**
 * OAuth Model
 */
class PMOAuth extends Model
{

    use \October\Rain\Database\Traits\Validation;
    /**
     * @var string The database table used by the model.
     */
    public $table = 'delphinium_redwood_pm_credentials';

    public $rules = [
        'name'=>'required',
        'client_id'=>'required',
        'client_secret' => 'required',
        'key' => 'required',
        'secret' => 'required',
        'workspace' => 'required',
        'server_url'=>'required|url'
    ];
    /**
     * @var array Guarded fields
     */
    protected $guarded = ['*'];

    /**
     * @var array Fillable fields
     */
    protected $fillable = [];

    /**
     * @var array Relations
     */
    public $hasOne = [];
    public $hasMany = [];
    public $belongsTo = [];
    public $belongsToMany = [];
    public $morphTo = [];
    public $morphOne = [];
    public $morphMany = [];
    public $attachOne = [];
    public $attachMany = [];

}