<?php  namespace Delphinium\Testing\Classes;

use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;
use PhpParser\NodeTraverser;
use PhpParser\BuilderFactory;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Expr;
use PhpParser\Builder\Namespace_;

class ComponentNodeVisitor extends NodeVisitorAbstract
{
    protected $modelUseStmt;
    protected $modelAlias;
    protected $controllerUseStmt;
    protected $controllerAlias;
    protected $studlyModel;
    protected $hasModel;
    protected $hasController;

    public function  __construct($modelUseStmt, $modelAlias, $controllerUseStmt, $controllerAlias, $studlyModel)
    {
        $this->modelUseStmt = $modelUseStmt;
        $this->modelAlias = $modelAlias;
        $this->hasModel = false;
        $this->studlyModel = $studlyModel;
        $this->controllerUseStmt = $controllerUseStmt;
        $this->controllerAlias = $controllerAlias;
        $this->hasController = false;
    }

    public function enterNode(\PhpParser\Node $node)
    {
        $modelArr = explode('\\', $this->modelUseStmt);
        $controllerArr = explode('\\', $this->controllerUseStmt);

        if ($node instanceof Node\Stmt\Namespace_)//if namespace
        {
            $children = $node->stmts;
            foreach ($children as $child) {
                //grab all the children that are use statements
                if ($child instanceof Node\Stmt\Use_) {
                    if (count($child->uses) > 0) {
                        $name = ($child->uses[0]->name->parts);
                        //check if this use statement matches the use stmt for the model
                        $diffModel = array_diff($modelArr, $name);
                        if(count($diffModel)==0 && $name[sizeof($name)-1]=$this->modelAlias)
                        {
                            $this->hasModel = true;
                        }
                        //check if this use statement matches the use stmt for the controller
                        $diffController = array_diff($controllerArr,$name);
                        if(count($diffController)==0 && $name[sizeof($name)-1]=$this->controllerAlias)
                        {
                            $this->hasController = true;
                        }

                    }
                }
                else if($child instanceof Node\Stmt\Class_)
                {
                    $methods = $child->stmts;
                    foreach($methods as $method)
                    {
                        if($method instanceof Node\Stmt\ClassMethod && $method->name == 'onUpdate')
                        {
                            $contents = $method->stmts;
                            foreach($contents as $content)
                            {
                                if($content instanceof Node\Expr\Assign &&
                                    $content->var instanceof Node\Expr\Variable && $content->var->name == "data")
                                {
                                    //modify the first argument and set the $this->studlyModel as an argument
                                    $args = $content->expr->args;
                                    if(count($args)>0)
                                    {
                                        if($args[0]->value->value!=$this->studlyModel)
                                        {
                                            $args[0]->value->value = $this->studlyModel;
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
                else
                {
                    NodeTraverser::DONT_TRAVERSE_CHILDREN;
                }
            }

            //traverse the use statements if we don't have the model
            if (!$this->hasModel) {
                $useM = $this->createUse($modelArr, $this->modelAlias);

                array_unshift($node->stmts, $useM);
            }

            //traverse the use statements if we don't have the model
            if($this->hasController)
            {
                NodeTraverser::DONT_TRAVERSE_CHILDREN;
            }
            else
            {
                $useC = $this->createUse($controllerArr, $this->controllerAlias);
                array_unshift($node->stmts, $useC);
            }
        } else {
            NodeTraverser::DONT_TRAVERSE_CHILDREN;
        }
    }

    private function createUse(array $nameParts, $alias)
    {
        $useName = new Node\Name($nameParts);
        $useUse = new Node\Stmt\UseUse($useName, $alias);
        $use = new Node\Stmt\Use_(array($useUse));
        return $use;
    }
}