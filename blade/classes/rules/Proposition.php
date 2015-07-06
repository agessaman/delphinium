<?php

/*
 * This file is part of the Ruler package, an OpenSky project.
 *
 * (c) 2011 OpenSky Project Inc
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

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
