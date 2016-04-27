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

namespace Delphinium\Testing\Classes;

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

    public function  __construct($componentPath,$componentAlias,$controllerUrl, $controllerAlias, $lowerPlugin, $lowerAuthor)
    {
        $this->componentPath = $componentPath;
        $this->componentAlias = $componentAlias;
        $this->controllerUrl = $controllerUrl;
        $this->controllerAlias = $controllerAlias;
        $this->plugin = $lowerPlugin;
        $this->author = $lowerAuthor;
        $this->hasComponent = false;
        $this->hasController = false;
    }

    public function enterNode(\PhpParser\Node $node)
    {
        if($node instanceof Node\Stmt\ClassMethod && $node->name == 'registerComponents')
        {
            $this->traverseComponents($node);
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
                                                    var_dump($i);
                                                }
                                            }
                                            if($item->key->value == $this->controllerAlias)
                                            {
                                                $this->hasController=true;
                                            }
                                        }
                                        if(!$this->hasController)
                                        {
                                            $url = $this->author."/".$this->plugin."/".strtolower($this->controllerAlias);
                                            $value = $this->newStaticCall('Backend', 'url', $url);
                                            $name = new Node\Name('url');
                                            $key = new Node\Scalar\String_($name->toString());
                                            $urlItem = new ArrayItem($value, $key);

                                            $label = $this->newArrayItem('label', $this->controllerAlias);
                                            $icon = $this->newArrayItem('icon', 'icon-leaf');
                                            $owner = $this->newArrayItem('owner', 'Delphinium.Greenhouse');
                                            $group = $this->newArrayItem('group', $this->controllerAlias);
                                            $arr = array($label, $icon, $owner, $urlItem, $group);

                                            $newSideItem = $this->newArrayItem($this->componentAlias,$arr, false);
                                            $methodArg->value->items[] = $newSideItem;
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
            return;
        }
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
        $className = new Node\Name($className);
        $expr = new Node\Scalar\String_($args);
        $argObj = new Node\Arg($expr);
        $call = new Node\Expr\StaticCall($className, $methodName, array($argObj));
        return $call;
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