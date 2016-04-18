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

/**
 * Description of RuleGroupContext
 *
 * @author Daniel
 */
class ExternalContext implements IContext {

    private $ctx;
    private $keys = array();
    private $values = array();

    public function wrap(IContext $ctx) {
        $this->ctx = $ctx;
        return $this;
    }
    
    public function getGroupData() {
        $arr = [];
        foreach(array_keys($this->keys) as $key) {
            $arr[$key] = $this->values[$key];
        }
        return $arr;
    }

    public function getData() {
        return $this->ctx->getData();
    }

    public function isExcluded() {
        return $this->ctx->isExcluded();
    }

    public function keys() {
        return array_merge($keys, $this->ctx->keys());
    }

    public function offsetExists($offset) {
        $var = $this->offsetGet($offset);
        return isset($var);
    }

    public function offsetGet($offset) {
        $var = ExternalContext::stripParens($offset);
        if (isset($var) && isset($this->keys[$var])) {
            return $this->values[$var];
        } else {
            return $this->ctx->offsetGet($offset);
        }
    }

    public function offsetSet($offset, $value) {
        $var = ExternalContext::stripParens($offset);
        if (isset($var)) {
            $this->keys[$var] = true;
            $this->values[$var] = $value;
        } else {
            $this->ctx->offsetSet($offset, $value);
        }
    }

    public function offsetUnset($offset) {
        $var = ExternalContext::stripParens($offset);
        if (isset($var)) {
            unset($this->keys[$var]);
            unset($this->values[$var]);
        } else {
            $this->ctx->offsetUnset($offset);
        }
    }

    public function protect($callable) {
        $this->ctx->protect($callable);
    }

    public function raw($name) {
        $this->ctx->raw($name);
    }

    public function setExcluded($excluded) {
        $this->ctx->setExcluded($excluded);
    }

    public function share($callable) {
        $this->ctx->share($callable);
    }

    private static function stripParens($str) {
        $varPattern = '/\(([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)\)/';

        $arr = [];
        if (preg_match($varPattern, $str, $arr)) {
            return $arr[1];
        }
        return null;
    }

}
