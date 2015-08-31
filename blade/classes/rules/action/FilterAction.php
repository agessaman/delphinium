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

    private $excluded;
    private $saved = false;

    public function __construct($excluded) {
        $this->excluded = $excluded;
    }

    public function execute(IContext $ctx) {
        $ctx->setExcluded($this->excluded);
    }
    
    public function matches(Model $model) {
        return $this->excluded === (boolean) $model->excluded;
    }

    public function save(Model $parent, Model $parent_rule, $order) {
        if (!$this->saved) {
            $model = new FilterActionModel(
                    ['excluded' => $this->excluded, 'order' => $order]);
            $model->rule()->associate($parent);
            $model->save();
        }
    }

    public function isWhitelistAction() {
        return $this->excluded == false;
    }
    
    public function isBlacklistAction() {
        return $this->excluded;
    }

}
