<?php  namespace Delphinium\Testing\Classes;

use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;
use PhpParser\NodeTraverser;
use PhpParser\BuilderFactory;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Expr;
use PhpParser\Builder\Namespace_;

class PluginFileNodeVisitor extends NodeVisitorAbstract
{
    public function leaveNode(Node $node) {
    }

    public function beforeTraverse(array $nodes)
    {
    }

    public function enterNode(\PhpParser\Node $node)
    {
        if($node instanceof Node\Stmt\ClassMethod && $node->name == 'registerComponents')
        {
            //traverse the children of the registerComponents method. Find the return statement
            $children = $node->getStmts();
            foreach($children as $nodeChild)
            {
                //check that we have the return statement and that it's an array
                if($nodeChild instanceof Node\Stmt\Return_)
                {
                    $components = $nodeChild->expr->items;
                    //make new array item
                    $value = new Node\Scalar\String_("createdcomp");

                    $name = new Node\Name("\\Delphinium\\Uliop\\Components\\CreatedComp");
                    $something= new Node\Name($name->toString());

                    $key = new Node\Scalar\String_($name->toString());

                    $newItem = new ArrayItem($value, $key);
                    $components[]=$newItem;
                    $nodeChild->expr->items = $components;

                }
            }
        }
        else
        {
            NodeTraverser::DONT_TRAVERSE_CHILDREN;
        }
    }

    public function afterTraverse(array $nodes)
    {

    }
}