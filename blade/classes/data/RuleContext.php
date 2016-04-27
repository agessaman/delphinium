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

use Delphinium\Blade\Classes\Rules\Rule;
use Delphinium\Blade\Classes\Rules\IContext;

/**
 * A Decorator of the Ruler Context object that seperates out
 * data that should not go into the main Context.
 * (Default variable values and custom variable values).
 *
 * @author Daniel Clark
 */
class RuleContext implements IContext {

    private $context;
    private $rule;
    private $keys = [];
    private $values = [];
    private $unset = [];

    public function __construct(Rule $rule, IContext $context) {
        $this->rule = $rule;
        $this->context = $context;
    }

    public function offsetGet($name) {
        if (isset($this->unset[$name])) {
            return null;
        }

        $value = $this->context->offsetGet($name);
        return (isset($value)) ? $value :
                $value = $this->rule->getVariableDefaultValue($name);
    }

    public function offsetExists($offset) {
        $value = $this->offsetGet($offset);
        return isset($value);
    }

    public function offsetSet($offset, $value) {
        $this->context->offsetSet($offset, $value);
    }

    public function offsetUnset($offset) {
        $this->unset[$offset] = true;
        $this->context->offsetUnset($offset);
    }

    public function share($callable) {
        return $this->context->share($callable);
    }

    public function protect($callable) {
        return $this->context->protect($callable);
    }

    public function raw($name) {
        return $this->context->raw($name);
    }

    public function keys() {
        return array_merge($this->rule->getKeys(), $this->context->keys());
    }

    public function getData() {
        return $this->context->getData();
    }

    public function isExcluded() {
        return $this->context->isExcluded();
    }

    public function setExcluded($excluded) {
        $this->context->setExcluded($excluded);
    }

}
