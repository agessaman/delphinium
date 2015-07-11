<?php

namespace Delphinium\Blade\Classes\Rules\Action;

use Delphinium\Blade\Classes\Rules\Action;
use Delphinium\Blade\Classes\Rules\IContext;
use Delphinium\Blade\Models\FilterAction as FilterActionModel;
use \Model;

/**
 * Description of IncludeAction
 *
 * @author Daniel
 */
class FilterAction implements Action {

    private $filter;
    private $saved = false;

    public function __construct($excluded) {
        $this->filter = $excluded;
    }

    public function execute(IContext $ctx) {
        $ctx->whitelist($filter);
    }
    
    public function matches(Model $model) {
        return $this->filter === $model->excluded;
    }

    public function save(Model $parent, Model $parent_rule, $order) {
        if (!$this->saved) {
            $model = new FilterActionModel(
                    ['filter' => $this->filter, 'order' => $order]);
            $model->rule()->associate($parent);
            $model->save();
        }
    }

}
