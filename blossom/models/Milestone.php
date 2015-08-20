<?php namespace Delphinium\Blossom\Models;

use Model;

/**
 * Milestone Model
 */
class Milestone extends Model
{
use \October\Rain\Database\Traits\Validation;
    /**
     * @var string The database table used by the model.
     */
    public $table = 'delphinium_blossom_milestones';

    public $rules = [
        'id'=>'required',
        'name'=>'required',
        'points'=>'required'
    ];
    /**
     * @var array Fillable fields
     */
    protected $fillable = ['*'];

    /**
     * @var array Relations
     */
    public $belongsTo = [
        'experience' => [
            '\Delphinium\Blossom\Models\Experience',
            'table' => 'delphinium_blossom_experiences',
            'foreignKey' => 'id',
            'delete'=>'true']
    ];
    
}