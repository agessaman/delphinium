<?php

namespace Delphinium\Blade\Classes\Rules;

use Model;

/**
 * The Proposition interface represents a propositional statement.
 *
 * @author Justin Hileman <justin@justinhileman.info>
 */
interface Proposition
{

    /**
     * Evaluate the Proposition with the given IContext.
     *
     * @param IContext $context IContext with which to evaluate this Proposition
     *
     * @return boolean
     */
    public function evaluate(IContext $context);
}
