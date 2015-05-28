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

use \Model;
use Delphinium\Blade\Models\Rule as RuleModel;

/**
 * Rule class.
 *
 * A Rule is a conditional Proposition with an (optional) action which is
 * executed upon successful evaluation.
 *
 * @author Justin Hileman <justin@justinhileman.info>
 */
class Rule implements IPersistent {

    protected $name;
    protected $condition;
    private $actions;
    private $datatype;
    protected $dbid; // database id
    protected $model;

    /**
     * Rule constructor.
     *
     * @param Typename    $datatype  The type of object that this rule will apply to.
     * @param Proposition $condition Propositional condition for this Rule
     * @param callback    $actions    Action (callable) to take upon successful Rule execution (default: null)
     */
    public function __construct($name, $datatype, Proposition $condition, array $actions = null) {
        $this->name = $name;
        $this->datatype = $datatype;
        $this->condition = $condition;
        $this->actions = $actions;
        if ($actions == null) {
            $this->actions = [];
        }
    }

    /**
     * Evaluate the Rule with the given Context.
     *
     * @param Context $context Context with which to evaluate this Rule
     *
     * @return boolean
     */
    public function evaluate(Context $context) {
        return $this->condition->evaluate($context);
    }

    /**
     * Execute the Rule with the given Context.
     *
     * The Rule will be evaluated, and if successful, will execute its
     * $action callback.
     *
     * @param  Context         $context Context with which to execute this Rule
     * @throws \LogicException
     */
    public function execute(Context $context) {
        if ($this->evaluate($context) && isset($this->actions)) {
            foreach ($this->actions as $act) {
                $act->execute($context);
            }
        }
    }

    public function getId() {
        return $this->dbid;
    }
    
    public function getName() {
        return $this->name;
    }

    private function save() {
        $model = new RuleModel(
                ['name' => $this->name,
            'datatype' => $this->datatype]);
        $model->save();
        $this->dbid = $model->id;
        $this->model = $model;
        $this->condition->save($model, 0);

        for ($i = 0; $i < count($this->actions); $i++) {
            $this->actions[$i]->save($model, $i);
        }
    }

    public function findOrCreate() {
        if (!$this->exists()) {
            $this->save();
        }
    }

    public function exists() {
        if (isset($this->dbid)) {
            return true;
        }
        
        $m = RuleModel::where('name', $this->name)->first();

        if (!isset($m)) return false;
        if (!$this->actionsMatch($m)) {
            return false;
        }

        $op = $m->operator; // this property is dynamic
        $o = isset($op);
        $c = isset($this->condition);
        if (!$o && !$c) {
            $this->dbid = $m->id;
            $this->model = $m;
            return true;
        } else if ($o xor $c) {
            return false;
        }

        if ($this->condition->matches($op)) {
            $this->dbid = $m->id;
            $this->model = $m;
            return true;
        }

        return false;
    }

    private function actionsMatch(Model $m) {
        $a = $this->actions;
        $b = $m->getActions();

        if (count($a) != count($b)) {
            return false;
        }

        for ($i = 0; $i < count($a); $i++) {
            if (!$a[$i]->matches($b[$i])) {
                return false;
            }
        }
        return true;
    }

    public function delete() {
        if ($this->exists()) {
            //TODO: deletion code here
            // remember to unset $this->dbid
            $this->model->delete();
            $this->model = null;
            $this->dbid = null;
        }
    }

}
