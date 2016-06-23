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
//        var_dump($node);

//        if($node instanceof Node\Stmt\Namespace_)
//        {
//            var_dump($node);
//        }
//
//        return;

//        if($node instanceof Node\Stmt\Namespace_)
//        {
//            var_dump($node);
//        }else
//        {
//            NodeTraverser::DONT_TRAVERSE_CHILDREN;
//        }
//        return;
//        if($node instanceof Comment\Doc)
//        {
//            var_dump($node);
//            return;
//        }
//        else
//        {
//            NodeTraverser::DONT_TRAVERSE_CHILDREN;
//        }
//
//
//        return;



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