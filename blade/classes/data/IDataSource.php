<?php

namespace Delphinium\Blade\Classes\Data;

/**
 * @author Daniel Clark
 */
interface IDataSource {
    
    /**
     * 
     * @param Array $params
     * Parameters include
     * rg: rulegroups
     * fresh_data: whether to use cached results from roots
     * 
     */
    function getModules($params);
    //function getModule($id, $params);
    function getAssignments($params);
    //function getAssignment($id, $params);
//    function getModuleItems($rulegroups, $modId);
//    function getModuleItem($rulegroups, $modId, $id);
    function getAssignmentGroups($params);
    //function getAssignmentGroup($id, $params);
    function getSubmissions($assignment_id, $params);
    function getUserAssignmentAnalytics($params);
}
