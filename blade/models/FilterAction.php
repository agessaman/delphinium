<?php

namespace Delphinium\Blade\Models;

use Model;

/**
 * FilterAction Model
 */
class FilterAction extends Model implements IRuleComponent {

    /**
     * @var string The database table used by the model.
     */
    public $table = 'delphinium_blade_filter_actions';

    /**
     * @var array Fillable fields
     */
    protected $fillable = ['order', 'excluded'];

    /**
     * @var array Relations
     */
    public $hasOne = [];
    public $hasMany = [];
    public $belongsTo = ['rule' =>
        [
            'Delphinium\Blade\Models\Rule',
            'delete' => true
        ]
    ];
    public $belongsToMany = [];
    public $morphTo = [];
    public $morphOne = [];
    public $morphMany = [];
    public $attachOne = [];
    public $attachMany = [];
    
    public function toExecutable() {
        return new Delphinium\Blade\Classes\Rules\Action\FilterAction($this->excluded);
    }

}
