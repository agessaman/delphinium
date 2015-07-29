<?php namespace Delphinium\Roots\Classes;

use Model as OctoberModel;
use Illuminate\Database\Eloquent\Model as EloquentModel;
class CustomModel extends OctoberModel {
    /**
     * Save the model to the database.
     * @return bool
     */
    public function save(array $data = null, $sessionKey = null)
    {
//        do somethin
        return parent::save($data, $sessionKey);
    }
    
    public function delete()
    {
        //do something
       EloquentModel::delete();//the parent doesn't have a delete method, so gotta call the grandparent 
    }
}