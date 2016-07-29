<?php
namespace Delphinium\Irisnew\Models;

use Model;

/**
 * IrisChart Model
 */
class IrisChart extends Model
{

    /**
     * @var string The database table used by the model.
     */
    public $table = 'delphinium_irisnew_iris_charts';

    /**
     * @var array Guarded fields
     */
    protected $guarded = ['*'];

    /**
     * @var array Fillable fields
     */
    protected $fillable = ['name','animate','size'];

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