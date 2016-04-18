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

namespace Delphinium\Roots\Requestobjects;

use Delphinium\Roots\Enums\Lms;
use Delphinium\Roots\UpdatableObjects\Module;
use Delphinium\Roots\UpdatableObjects\ModuleItem;

class ModulesRequest extends RootsRequest
{
    private $moduleId;
    private $moduleItemId;
    private $includeContentItems;
    private $includeContentDetails;
    public $module;
    public $moduleItem;
    private $freshData;
   
    function getModuleId() {
        return $this->moduleId;
    }

    function getModuleItemId() {
        return $this->moduleItemId;
    }

    function getIncludeContentItems() {
        return $this->includeContentItems;
    }
    
    function setIncludeContentItems($include) {
        $this->includeContentItems = $include;
    }

    function getIncludeContentDetails() {
        return $this->includeContentDetails;
    }
    
    function setIncludeContentDetails($include) {
        $this->includeContentDetails = $include;
    }

    function getModule() {
        return $this->module;
    }

    function getModuleItem() {
        return $this->moduleItem;
    }

    function getFreshData() {
        return $this->freshData;
    }
    
    function setFreshData($fresh_data) {
        $this->freshData = $fresh_data;
    }

    function setModule(Module $module) {
        $this->module = $module;
    }

    function setModuleItem($moduleItem) {
        $this->moduleItem = $moduleItem;
    }
    
            
    function __construct($actionType, $moduleId = null, $moduleItemId = null,  $includeContentItems = false, 
            $includeContentDetails = false, Module $module = null, ModuleItem $moduleItem = null, $freshData = null) 
    {
        //this takes care of setting the lms and the ActionType in the parent class (RootsRequest)
        parent::__construct($actionType);
        

        $lms = strtoupper($_SESSION['lms']);
        if(Lms::isValidValue($lms))
        {
            $this->lms = $lms;
        }
        else
        {
            throw new \Exception("Invalid LMS"); 
        }

        $this->moduleId = $moduleId;
        $this->moduleItemId = $moduleItemId;
        $this->includeContentDetails = $includeContentDetails;
        $this->includeContentItems= $includeContentItems;
        $this->module = $module;
        $this->moduleItem = $moduleItem;
        $this->freshData = $freshData;
    }
}