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

namespace Delphinium\Blade\Classes\Rules\Action;

use \Model;
use \Delphinium\Blade\Models\AssignAction as ActionModel;
use Delphinium\Blade\Classes\Rules\Variable;
use Delphinium\Blade\Classes\Rules\Action;
use Delphinium\Blade\Classes\Rules\IContext;

/**
 * 
 *
 * @author Daniel Clark
 */
class AssignAction implements Action {

    protected $lvariable;
    protected $value;

    public function __construct(Variable $var, Variable $value) {
        $this->lvariable = $var;
        $this->value = $value;
    }

    public function execute(IContext $context) {
        $context[$this->lvariable->getName()] = $this->value->prepareValue($context)->getValue();
    }

    public function matches(Model $model) {
        return $this->lvariable->getName() === $model->variable_name &&
                $this->value->matches($model->variable);
    }

    public function save(Model $parent, Model $parent_rule, $order) {
        $model = new ActionModel([
            'variable_name' => $this->lvariable->getName(),
            'order' => $order]);
        $model->rule()->associate($parent);
        $model->save();

        $this->value->save($model, $parent_rule, 0);
    }
    
    public function isWhitelistAction() {
        return false;
    }
    
    public function isBlacklistAction() {
        return false;
    }

}
