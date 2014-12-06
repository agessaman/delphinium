<?php 

namespace Delphinium\Raspberry\Models;

use Model;
use October\Rain\Support\ValidationException;
/**
 * Description of ModuleItem
 *
 * @author damariszarco
 */
class ModuleItem extends Model {
    use \October\Rain\Database\Traits\Validation;

    protected $primaryKey = 'module_item_id';
    protected $fillable = array('*');//as of right now, we will only create Modules with data coming from the API, so we can make all of the attributes fillable
    
    public $table = 'delphinium_raspberry_module_items';
    
    public $belongsTo = [
        'module' => ['Delphinium\Raspberry\Models\Module', 'foreignKey' => 'module_id']
    ];
    
    public $rules = [
    	'module_item_id'=>'required',
        'module_id' => 'required'
    ];
    
    public $hasMany = [
        'content' => ['Delphinium\Raspberry\Models\Content', 'foreignKey' => 'content_id']
    ];
}