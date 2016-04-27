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

namespace Delphinium\Blade\Classes\Rules;

/*
 * Author: Daniel Clark
 */

interface IContext extends \ArrayAccess {
    
    /**
     * Define a fact as "shared". This lazily evaluates and stores the result
     * of the callable for the scope of this Context instance.
     *
     * @param callable $callable A fact callable to share
     *
     * @return callable The passed callable
     *
     * @throws InvalidArgumentException if the callable is not a Closure or invokable object
     */
    public function share($callable);

    /**
     * Protect a callable from being interpreted as a lazy fact definition.
     *
     * This is useful when you want to store a callable as the literal value of
     * a fact.
     *
     * @param callable $callable A callable to protect from being evaluated
     *
     * @return callable The passed callable
     *
     * @throws InvalidArgumentException if the callable is not a Closure or invokable object
     */
    public function protect($callable);

    /**
     * Get a fact or the closure defining a fact.
     *
     * @param string $name The unique name for the fact
     *
     * @return mixed The value of the fact or the closure defining the fact
     *
     * @throws InvalidArgumentException if the name is not defined
     */
    public function raw($name);

    /**
     * Get all defined fact names.
     *
     * @return array An array of fact names
     */
    public function keys();

    public function setExcluded($excluded);
    
    public function isExcluded();
    
    public function getData();
}
