<?php

namespace Delphinium\Blade\Classes\Rules;

use \Model;

interface ISavable {
    public function save(Model $parent, Model $parent_rule, $order);
}
