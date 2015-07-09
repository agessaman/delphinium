<?php

namespace Delphinium\Blade\Classes\Data;

use Delphinium\Blade\Classes\Rules\IContext;
use Delphinium\Blade\Models\RuleInstance;

class RuleInstanceContext implements IContext {
    
    private $ri;
    private $ctx;
    private $unset = [];
    
    public function __construct(RuleInstance $ri, IContext $ctx) {
        $this->ri = $ri;
        $this->ctx = $ctx;
    }
    
    public function getData() {
        return $ctx->getData();
    }

    public function keys() {
        return $ctx->keys();
    }
    
    public function offsetExists($offset) {
        $var = offsetGet($offset);
        return isset($var) ? true : $ctx->offsetExists($offset);
    }

    public function offsetGet($offset) {
        if (isset($this->unset[$offset])) {
            return null;
        }
        
        $var = $ri->variables()->where('name', '=', $offset)->first();
        return isset($var) ? $var : $ctx->offsetGet($offset);
    }

    public function offsetSet($offset, $value) {
        $ctx->offsetSet($offset, $value);
    }

    public function offsetUnset($offset) {
        $unset[$offset] = true;
    }

    public function protect($callable) {
        $ctx->protect($callable);
    }

    public function raw($name) {
        $ctx->raw($name);
    }

    public function share($callable) {
        $ctx->share($callable);
    }

}

