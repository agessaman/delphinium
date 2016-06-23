<?php

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
    private $whitelist; // whether this rule is a whitelisting rule
    private $course_id;

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
        
        $this->disallowMultipleFilterActions();
        
        $this->whitelist = $this->isWhitelistRule();
    }

    /**
     * Evaluate the Rule with the given Context.
     *
     * @param Context $context Context with which to evaluate this Rule
     *
     * @return boolean
     */
    public function evaluate(IContext $context) {
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
    public function execute(IContext $context) {
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

    public function getDatatype() {
        return $this->datatype;
    }

    // TODO: add saving of course id
    private function save() {
        $model = new RuleModel(
                ['name' => $this->name,
            'datatype' => $this->datatype]);
        $model->save();
        $this->dbid = $model->id;
        $this->model = $model;
        $this->condition->save($model, $model, 0);

        for ($i = 0; $i < count($this->actions); $i++) {
            $this->actions[$i]->save($model, $model, $i);
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

        if (!isset($m))
            return false;
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

    public function getVariableDefaultValue($name) {
        if ($this->exists()) {
            return $this->model->getVariableDefaultValue($name);
        }
        return null;
    }

    public function getKeys() {
        if ($this->exists()) {
            return $this->model->getKeys();
        }
        return [];
    }
    
    public function isWhitelistRule() {
        if ($this->whitelist == true) return true;
        
        foreach ($this->actions as $action) {
            if ($action->isWhitelistAction()) {
                $this->whitelist = true;
                return true;
            }
        }
    }
    
    private function disallowMultipleFilterActions() {
        $count = 0;
        foreach($this->actions as $action) {
            if ($action->isWhitelistAction() || $action->isBlacklistAction()) {
                $count++;
            }
        }
        
        if ($count > 1) {
            throw new \InvalidArgumentException('A rule may not have more than one filtering action');
        }
    }
//    
//    public function setCourseId($id) {
//        $this->course_id = $id;
//    }
//    
//    public function getCourseId() {
//        return $this->course_id;
//    }
}
