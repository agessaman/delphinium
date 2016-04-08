<?php  namespace Delphinium\Testing\Classes;

use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;
use PhpParser\NodeTraverser;
use PhpParser\BuilderFactory;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Expr;
use PhpParser\Builder\Namespace_;

class PluginNodeVisitor extends NodeVisitorAbstract
{
    protected $componentPath;
    protected $componentAlias;
    protected $controllerUrl;
    protected $hasComponent;
    protected $hasController;

    public function  __construct($componentPath,$componentAlias,$controllerUrl)
    {
        $this->componentPath = $componentPath;
        $this->componentAlias = $componentAlias;
        $this->controllerUrl = $controllerUrl;
        $this->hasComponent = false;
        $this->hasController = false;
    }
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
                    var_dump($nodeChild->expr);
                    //make a new item to go in the array
                    $newItem =$this->newComponentItem($this->componentAlias,$this->componentPath);
                    if(isset($nodeChild->expr->items))
                    {
//                        $components[]=$newItem;
//                        $nodeChild->expr->items = $components;
                    }
                    else{
//                        echo"here";
//                        var_dump($nodeChild);
                    }
//                    $components = $nodeChild->expr->items;
//                    //make new array item
//                    $value = new Node\Scalar\String_("newcomponent");
//
//                    $name = new Node\Name("\\Author\\Newplugin\\Components\\NewComponent");
//                    $something= new Node\Name($name->toString());
//
//                    $key = new Node\Scalar\String_($name->toString());
//
//                    $newItem = new ArrayItem($value, $key);
//                    $components[]=$newItem;
//                    $nodeChild->expr->items = $components;

                }
            }
        }
        else
        {
            NodeTraverser::DONT_TRAVERSE_CHILDREN;
        }
    }

    private function newComponentItem($componentAlias,$componentPath)
    {
        $value = new Node\Scalar\String_($componentAlias);
        $name = new Node\Name($componentPath);
        $key = new Node\Scalar\String_($name->toString());

        $newItem = new ArrayItem($value, $key);
        return $newItem;
    }
    public function afterTraverse(array $nodes)
    {

    }
}