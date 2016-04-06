<?php  namespace Delphinium\Testing\Classes;

use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;
use PhpParser\NodeTraverser;
use PhpParser\BuilderFactory;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Expr;

class MyNodeVisitor extends NodeVisitorAbstract
{
    public function leaveNode(Node $node) {
//        if ($node instanceof Node\Scalar\String_) {
//            var_dump($node);
//        }


        //var_dump($node);
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
                if($nodeChild instanceof Node\Stmt\Return_)// && $nodeChild instanceof PhpParser\Node\Expr\Array_
                {
                    $components = $nodeChild->expr->items;// instanceof PhpParser\Node\Expr\Array_)

                    //make new array item
                    $value = new Node\Scalar\String_('createdcomp');
                    $key = new Node\Scalar\String_('\Delphinium\Uliop\Components\CreatedComp');


                    //THIS LINE ABOVE GETS TRANSLATED TO DOUBLE SLASHES> NEED TO FIX THAT

                    $newItem = new ArrayItem($value, $key);
                    $components[]=$newItem;

                    $nodeChild->expr->items = $components;

                    var_dump($nodeChild);
                }
            }
            //var_dump($children);
            //PhpParser\Node\Stmt\Return_
//            $var = $node->getStmts();
//            var_dump($var);
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