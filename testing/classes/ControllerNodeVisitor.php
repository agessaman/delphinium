<?php  namespace Delphinium\Testing\Classes;

use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;
use PhpParser\NodeTraverser;
use PhpParser\BuilderFactory;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Expr;
use PhpParser\Builder\Namespace_;

class ControllerNodeVisitor extends NodeVisitorAbstract
{

    protected $modelUseStmt;
    protected $modelAlias;
    protected $hasModel;

    public function  __construct($modelUseStmt, $modelAlias)
    {
        $this->modelUseStmt = $modelUseStmt;
        $this->hasModel = false;
        $this->modelAlias = $modelAlias;
    }
    public function leaveNode(Node $node) {
    }

    public function beforeTraverse(array $nodes)
    {
    }

    public function afterTraverse(array $nodes)
    {
    }

    public function enterNode(\PhpParser\Node $node)
    {//the model use statement in array form
        $modelArr = explode('\\', $this->modelUseStmt);

        if ($node instanceof Node\Stmt\Namespace_)//if namespace
        {
            $children = $node->stmts;
            foreach ($children as $child) {
                //grab all the children that are use statements
                if ($child instanceof Node\Stmt\Use_) {
                    if (count($child->uses) > 0) {
                        $name = ($child->uses[0]->name->parts);
                        $diff = array_diff($modelArr, $name);
                        $this->hasModel = count($diff) == 0 ? true : false;
                    }
                } else {
                    NodeTraverser::DONT_TRAVERSE_CHILDREN;
                }
            }

            //traverse the use statements if we don't have the model
            if ($this->hasModel) {
                NodeTraverser::DONT_TRAVERSE_CHILDREN;
            } else {//insert the use statement
                $newUseName = new Node\Name($modelArr);
                $newUse = new Node\Stmt\UseUse($newUseName, $this->modelAlias);
                $use = new Node\Stmt\Use_(array($newUse));
                array_unshift($node->stmts, $use);
            }
        } else {
            NodeTraverser::DONT_TRAVERSE_CHILDREN;
        }
    }
}