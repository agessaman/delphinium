<?php namespace Delphinium\Xylum\Models;

use Model as OctoberModel;
use Illuminate\Database\Eloquent\Model as EloquentModel;
use Delphinium\Xylum\Models\ComponentInstance;

class CustomModel extends OctoberModel {
    /**
     * Save the model to the database.
     * @return bool
     */
    public function save(array $data = null, $sessionKey = null)
    {
        $componentInstance = new ComponentInstance();
        if(!is_null($data))
        {
            $componentInstance->type = strtolower($data['type']); 
        }
        $data = null;//The parent model doesn't have a type field, so we need to clear this field.
        $componentInstance->data = json_encode($this->attributes);
        if(isset($this->attributes['course_id'])){$componentInstance->course_id = $this->attributes['course_id'];}
        $componentInstance->save();

        return parent::save($data, $sessionKey);
    }
    
    public function delete()
    {
        //TODO: do something
       EloquentModel::delete();//the parent doesn't have a delete method, so gotta call the grandparent 
    }
    
}