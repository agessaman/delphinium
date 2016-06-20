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
    protected $description;

    public function  __construct($modelUseStmt, $modelAlias, $controllerUseStmt, $controllerAlias, $studlyModel, $description)
    {
        $this->modelUseStmt = $modelUseStmt;
        $this->modelAlias = $modelAlias;
        $this->hasModel = false;
        $this->studlyModel = $studlyModel;
        $this->controllerUseStmt = $controllerUseStmt;
        $this->controllerAlias = $controllerAlias;
        $this->hasController = false;
        $this->description = $description;
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
        }
        else if($node instanceof Node\Stmt\Class_)//look for the componentDetails method
        {
            $methods = $node->stmts;
            foreach($methods as $method)
            {
                if($method->name =="componentDetails" &&count($method->stmts)>0)
                {
                    $method->stmts[0]->expr->items[1]->value->value = $this->description;
                    var_dump($method->stmts[0]->expr->items[1]->value->value);
                    $this->hasBoot = true;
                }
            }
        }
        else
        {
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