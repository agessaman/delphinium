<?php
/**
 * Copyright (C) 2012-2016 Project Delphinium - All Rights Reserved
 *
 * This file is subject to the terms and conditions defined in
 * file 'https://github.com/ProjectDelphinium/delphinium/blob/master/EULA',
 * which is part of this source code package.
 *
 * NOTICE:  All information contained herein is, and remains the property of Project Delphinium. The intellectual and technical concepts contained
 * herein are proprietary to Project Delphinium and may be covered by U.S. and Foreign Patents, patents in process, and are protected by trade secret or copyright law.
 * Dissemination of this information or reproduction of this material is strictly forbidden unless prior written permission is obtained
 * from Project Delphinium.
 *
 * THE RECEIPT OR POSSESSION OF THIS SOURCE CODE AND/OR RELATED INFORMATION DOES NOT CONVEY OR IMPLY ANY RIGHTS
 * TO REPRODUCE, DISCLOSE OR DISTRIBUTE ITS CONTENTS, OR TO MANUFACTURE, USE, OR SELL ANYTHING THAT IT  MAY DESCRIBE, IN WHOLE OR IN PART.
 *
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Non-commercial use only, you may not charge money for the software
 * You can modify personal copy of source-code but cannot distribute modifications
 * You may not distribute any version of this software, modified or otherwise
 */

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

