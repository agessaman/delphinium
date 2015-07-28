<?php namespace Delphinium\Roots\Classes;

use Model;
class CustomModel extends Model {
    /**
     * Save the model to the database.
     * @return bool
     */
    public function save(array $data = null, $sessionKey = null)
    {
        return parent::save($data, $sessionKey);
    }
}