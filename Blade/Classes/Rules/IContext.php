<?php

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
