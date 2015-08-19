<?php namespace Delphinium\Vanilla\Models;

use Delphinium\Xylum\Models\CustomModel;


/**
 * Bonus Model
 */
class Bonus extends CustomModel
{

    use \October\Rain\Database\Traits\Validation;
    /**
     * @var string The database table used by the model.
     */
    public $table = 'delphinium_vanilla_bonuses';
    
//    public $guarded = ['id'];
    
    public $rules = [
    	'name'=>'required',
        'course_id' => 'required'
    ];
    
    public function save(array $data = null, $sessionKey = null)
    {
        if(is_null($data))
        {
            $data = array();
        }
        $data['type'] = "Bonus";
        
        return parent::save($data);
    }
}