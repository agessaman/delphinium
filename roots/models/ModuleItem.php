<?php 

namespace Delphinium\Roots\Models;

use Model;
/**
 * Description of ModuleItem
 *
 * @author Delphinium
 */
class ModuleItem extends Model {
    use \October\Rain\Database\Traits\Validation;

    public $incrementing = false;
    protected $primaryKey = 'module_item_id';
    protected $fillable = array('*');//as of right now, we will only create Modules with data coming from the API, so we can make all of the attributes fillable
    
    public $table = 'delphinium_roots_module_items';
    
    public $belongsTo = [
        'module' => ['Delphinium\Roots\Models\Module', 
        'foreignKey' => 'module_id',
        'delete'=>'true']
    ];
    
    public $rules = [
    	'module_item_id'=>'required',
        'module_id' => 'required'
    ];
    
    public $hasMany = [
        'content' => ['Delphinium\Roots\Models\Content', 'foreignKey' => 'content_id']
    ];
}