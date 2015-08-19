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

use \Delphinium\Blade\Models\Operator as OperatorModel;
use \Model;

/**
 * @author Jordan Raub <jordan@raub.me>
 */
abstract class Operator implements ISavable {

    const UNARY = 'UNARY';
    const BINARY = 'BINARY';
    const MULTIPLE = 'MULTIPLE';

    protected $operands;

    /**
     * @param array $operands
     */
    public function __construct() {
        foreach (func_get_args() as $operand) {
            $this->addOperand($operand);
        }
    }

    public function getOperands() {
        switch ($this->getOperandCardinality()) {
            case self::UNARY:
                if (1 != count($this->operands)) {
                    throw new \LogicException(get_class($this) . ' takes only 1 operand');
                }
                break;
            case self::BINARY:
                if (2 != count($this->operands)) {
                    throw new \LogicException(get_class($this) . ' takes 2 operands');
                }
                break;
            case self::MULTIPLE:
                if (0 == count($this->operands)) {
                    throw new \LogicException(get_class($this) . ' takes at least 1 operand');
                }
                break;
        }

        return $this->operands;
    }

    abstract public function addOperand($operand);

    abstract protected function getOperandCardinality();

    //author Daniel Clark
    public function save(Model $parent, Model $parent_rule, $order) {
        $op = new OperatorModel(['type' => get_class($this), 'order' => $order]);
        $op->save();

        $operands = $this->getOperands();

        for ($i = 0; $i < count($operands); $i++) {
            $child = $operands[$i]->save($op, $parent_rule, $i);
        }
        
        $parent->operator()->save($op);
    }

    public function matches(Model $model) {
        if (!($model instanceof OperatorModel)) return false;
        if ($model->type != get_class($this)) {
            return false;
        }

        $children = $model->getChildren();
        
        for ($i = 0; $i < count($children); $i++) {
            if (!$this->operands[$i]->matches($children[$i])) {
                return false;
            }
        }

        return true;
    }


}
