<?php
/**
 * Copyright (C) 2012-2016 Project Delphinium - All Rights Reserved
 *
 * This file is subject to the terms and conditions defined in
 * file 'https://github.com/ProjectDelphinium/delphinium/blob/master/EULA',
 * which is part of this source code package.
 *
 * NOTICE:  All information contained herein is, and remains the property of Project Delphinium. The intellectual and technical concepts contained
 * herein are proprietary to Project Delphinium and may be covered by U.S. and Foreign Patents, patents in process, and are protected by trade secret or copyright law.
 * Dissemination of this information or reproduction of this material is strictly forbidden unless prior written permission is obtained
 * from Project Delphinium.
 *
 * THE RECEIPT OR POSSESSION OF THIS SOURCE CODE AND/OR RELATED INFORMATION DOES NOT CONVEY OR IMPLY ANY RIGHTS
 * TO REPRODUCE, DISCLOSE OR DISTRIBUTE ITS CONTENTS, OR TO MANUFACTURE, USE, OR SELL ANYTHING THAT IT  MAY DESCRIBE, IN WHOLE OR IN PART.
 *
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Non-commercial use only, you may not charge money for the software
 * You can modify personal copy of source-code but cannot distribute modifications
 * You may not distribute any version of this software, modified or otherwise
 */

namespace Delphinium\Vanilla\Classes;

use Backend;
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
    protected $controllerAlias;
    protected $plugin;
    protected $author;
    protected $hasComponent;
    protected $hasController;
    protected $icon;

    private $hasBoot = false;

    public function  __construct($componentPath=null,$componentAlias=null,$controllerUrl=null, $controllerAlias=null, $lowerPlugin, $lowerAuthor, $icon=null)
    {
        $this->componentPath = $componentPath;
        $this->componentAlias = $componentAlias;
        $this->controllerUrl = $controllerUrl;
        $this->controllerAlias = $controllerAlias;
        $this->plugin = $lowerPlugin;
        $this->author = $lowerAuthor;
        $this->icon = $icon?$icon:'icon-lemon-o';//default to this icon if it was null
        $this->hasComponent = false;
        $this->hasController = false;
    }

    public function enterNode(\PhpParser\Node $node)
    {
        if($node instanceof Node\Stmt\Class_)
        {
            $methods = $node->stmts;
            foreach($methods as $method)
            {
                if($method->name =="boot")
                {
                    $this->hasBoot = true;
                }
            }
            if(!$this->hasBoot)
            {
                $method=  $this->addBootFunction();
                array_push($node->stmts,$method);
                $this->hasBoot = true;
            }
        }
        else
        {
            NodeTraverser::DONT_TRAVERSE_CHILDREN;
        }



        if($node instanceof Node\Stmt\ClassMethod && $node->name == 'registerComponents')
        {
            if($this->componentPath && $this->componentAlias)//when the plugin is first created no components will be added. so we need to double check
            {
                $this->traverseComponents($node);
            }
        }
        else if($node instanceof Node\Stmt\ClassMethod && $node->name == 'boot')
        {
            $this->traverseBootMethod($node);
        }
        else
        {
            NodeTraverser::DONT_TRAVERSE_CHILDREN;
        }
    }

    private function traverseComponents($node)
    {
        //traverse the children of the registerComponents method. Find the return statement
        $children = $node->getStmts();
        if(!$children||!isset($children)||count($children)<0)//no components have been registered
        {
            $retStmt = $this->addComponentsArr();
            $node->stmts = $retStmt;
        }
        else
        {
            foreach($children as $nodeChild)
            {
                if($nodeChild instanceof Node\Expr\Assign && $nodeChild->var instanceof Node\Expr\Variable && $nodeChild->var->name=='componentArray')
                {
                    $currentComponents = $nodeChild->expr->items;
                    foreach($currentComponents as $arrayItem)
                    {
                        $existingParts = explode('\\',$arrayItem->key->value);
                        $componentParts = explode('\\',$this->componentPath);
                        //check if this use statement matches the use stmt for the model
                        $diffModel = array_diff($existingParts, $componentParts);
                        $this->hasComponent = count($diffModel) == 0 ? true : false;
                    }

                    if(!$this->hasComponent)
                    {//add the component
                        $newComponent = $this->newArrayItem($this->componentPath,$this->componentAlias);
                        $nodeChild->expr->items[]=$newComponent;
                    }
                }
            }
        }
    }

    private function traverseBootMethod($node)
    {
        $children = $node->getStmts();

        foreach($children as $nodeChild)
        {
            if($nodeChild instanceof Node\Expr\StaticCall && $nodeChild->name =='listen')
            {
                $args = $nodeChild->args;
                foreach($args as $arg)
                {
                    if($arg->value instanceof Node\Expr\Closure)
                    {
                        $statements = $arg->value->stmts;
                        foreach($statements as $stmt)
                        {
                            if($stmt->name == 'addSideMenuItems')
                            {
                                $methodArgs = $stmt->args;
                                foreach($methodArgs as $methodArg)
                                {
                                    if($methodArg->value instanceof Node\Expr\Array_)
                                    {
                                        $existingItems = $methodArg->value->items;
                                        foreach($existingItems as $item)
                                        {//compare the URLs and the $keys
                                            $allItems = $item->value->items;
                                            foreach($allItems as $i)
                                            {
                                                if($i->value instanceof Node\Expr\StaticCall)
                                                {
//                                                    var_dump($i);
                                                }
                                            }
                                            if($item->key->value == $this->controllerAlias)
                                            {
                                                $this->hasController=true;
                                            }
                                        }
                                        if(!$this->hasController)
                                        {
                                            $methodArg->value->items[] =$this->addSideNavigationItem();
                                            $this->hasController=true;
                                        }
                                    }
                                }

                                if(!$this->hasController)//there were no side navigation items
                                {
                                    $methodArg->value->items[] =$this->addSideNavigationItem();
                                }
                            }
                        }
                    }
                }
            }
            return;
        }
    }

    private function addSideNavigationItem()
    {

        $url = $this->author."/".$this->plugin."/".strtolower($this->controllerAlias);
        $value = $this->newStaticCall('Backend', 'url', $url);
        $name = new Node\Name('url');
        $key = new Node\Scalar\String_($name->toString());
        $urlItem = new ArrayItem($value, $key);

        $label = $this->newArrayItem('label', $this->controllerAlias);
        $icon = $this->newArrayItem('icon', $this->icon);
        $owner = $this->newArrayItem('owner', 'Delphinium.Greenhouse');
        $group = $this->newArrayItem('group', $this->controllerAlias);
        $arr = array($label, $icon, $owner, $urlItem, $group);

        $newSideItem = $this->newArrayItem($this->controllerAlias,$arr, false);
        return $newSideItem;

    }

    private function newArrayItem($key,$value, $valueAsString=true)
    {
        $value = $valueAsString?new Node\Scalar\String_($value):new Node\Expr\Array_($value);
        $name = new Node\Name($key);
        $key = new Node\Scalar\String_($name->toString());

        $newItem = new ArrayItem($value, $key);
        return $newItem;
    }

    private function newStaticCall($className, $methodName, $args)
    {
        $className = new Node\Name\FullyQualified($className);
        $expr = new Node\Scalar\String_($args);
        $argObj = new Node\Arg($expr);
        $call = new Node\Expr\StaticCall($className, $methodName, array($argObj));
        return $call;
    }

    private function addBootFunction()
    {   //closure Argument
        $var = new Node\Expr\Variable("manager");

        //make three arguments
        $fullyQualifiedGreenhouse = new Node\Arg(new Node\Scalar\String_('Delphinium.Greenhouse'));
        $greenhouse = new Node\Arg(new Node\Scalar\String_('greenhouse'));

        //array items for navigation menu parameters (third argument)
//        $newSideItem = $this->addSideNavigationItem();
//        $thirdParamArr = new Node\Expr\Array_([$newSideItem]);

        //method call
//        $methodCall = new Node\Expr\MethodCall($var, "addSideMenuItems", [$fullyQualifiedGreenhouse,$greenhouse,$thirdParamArr]);
        $methodCall = new Node\Expr\MethodCall($var, "addSideMenuItems", [$fullyQualifiedGreenhouse,$greenhouse, new Node\Arg(new Node\Expr\Array_([]))]);//,$thirdParamArr]);
        $managerParam = new Node\Param("manager");
        $closure = new Node\Expr\Closure();
        $closure->params = [$managerParam];
        $closure->stmts = [$methodCall];

        $args = [new Node\Arg(new Node\Scalar\String_("backend.menu.extendItems")),new Node\Arg($closure)];
        $class = new Node\Name\FullyQualified("Event");

        $staticCall = new Node\Expr\StaticCall($class, "listen", $args);

        $method = new Node\Stmt\ClassMethod("boot");
        $method->stmts = [$staticCall];
        return $method;
    }

    private function makeArray($arrName, $arrItems)
    {
        $arr = new Node\Expr\Array_($arrItems);
        $var = new Node\Expr\Variable($arrName);
        $assign = new Node\Expr\Assign($var, $arr);
        return $assign;
    }

    private function addComponentsArr()
    {
        $newComponent = $this->newArrayItem($this->componentPath,$this->componentAlias);
        $arr = new Node\Expr\Array_([$newComponent]);
        $var = new Node\Expr\Variable("componentArray");
        $assign = new Node\Expr\Assign($var, $arr);
        //add return statement
        $return = new Node\Stmt\Return_($var);

        $arrRet = [$assign, $return];
        return $arrRet;
    }

    public function afterTraverse(array $nodes)
    {
    }

    public function leaveNode(Node $node)
    {
    }

    public function beforeTraverse(array $nodes)
    {
    }


}