<?php 

namespace Delphinium\Core\Models;

use Model;
/**
 * Description of ModuleItem
 *
 * @author damariszarco
 */
class ModuleItem extends Model {
    use \October\Rain\Database\Traits\Validation;

    public $incrementing = false;
    protected $primaryKey = 'module_item_id';
    protected $fillable = array('*');//as of right now, we will only create Modules with data coming from the API, so we can make all of the attributes fillable
    
    public $table = 'delphinium_core_module_items';
    
    public $belongsTo = [
        'module' => ['Delphinium\Core\Models\Module', 'foreignKey' => 'module_id']
    ];
    
    public $rules = [
    	'module_item_id'=>'required',
        'module_id' => 'required'
    ];
    
    public $hasMany = [
        'content' => ['Delphinium\Core\Models\Content', 'foreignKey' => 'content_id']
    ];
}