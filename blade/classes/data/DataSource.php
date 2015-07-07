<?php

namespace Delphinium\Blade\Classes\Data;

use Delphinium\Roots\Roots;
use Delphinium\Roots\RequestObjects\AssignmentsRequest;
use Delphinium\Roots\RequestObjects\ModulesRequest;
use Delphinium\Roots\RequestObjects\AssignmentGroupsRequest;
use Delphinium\Roots\RequestObjects\SubmissionsRequest;
use Delphinium\Roots\Enums\CommonEnums\ActionType;
use Delphinium\Blade\Classes\Rules\Context;
use Delphinium\Blade\Classes\Rules\RuleGroup;

/**
 *
 * @author Daniel Clark
 */
class DataSource implements IDataSource {

    public function test() {
        return $this->getModules(null);
    }

    public function __construct() {
        $this->roots = new Roots();
    }

    // gets rulegroups parameter
    private function rg($params) {
        return (isset($params['rg'])) ? explode(',', $params['rg']) : [];
    }

    public function getAssignments($params) {
        $request = new AssignmentsRequest(ActionType::GET);
        return $this->runRules('assignment', $this->rg($params), $this->roots->assignments($request)->toArray());
    }

    public function getAssignment($id, $params) {
        $request = DataSource::createGetAssignmentsRequest($id);
        return $this->runRules('assignment', $this->rg($params), $this->roots->assignments($request)->toArray());
    }

    public function getModules($params) {
        $request = DataSource::createGetModulesRequest();
        DataSource::setModuleParams($request, $params);
        return $this->runRules('module', $this->rg($params), $this->roots->modules($request)->toArray());
    }

    //TODO: fix so this gets one module only
    public function getModule($id, $params) {
        $request = DataSource::createGetModulesRequest($id);
        DataSource::setModuleParams($request, $params);
        return $this->runRules('module', $this->rg($params), $this->roots->modules($request)->toArray());
    }

    public function getAssignmentGroups($params) {
        $request = DataSource::createGetAssignmentGroupsRequest();
        return $this->requestAssignmentGroups($this->rg($params), $request, $params);
    }

    public function getAssignmentGroup($id, $params) {
        $request = DataSource::createGetAssignmentGroupsRequest($id);
        return $this->requestAssignmentGroups($this->rg($params), $request, $params);
    }

    private function requestAssignmentGroups($request, $params) {
        DataSource::setAssignmentGroupParams($request, $params);
        return $this->runRules('assignment_group', $this->rg($params), $this->roots->assignmentGroups($request)->toArray());
    }

    public function getAssignmentSubmissions($assignment_id, $params) {
        $request = DataSource::createGetSubmissionsRequest();
        return $this->requestSubmissions($this->rg($params), $request, $params);
    }

    public function getAssignmentSubmission($assignment_id, $id, $params) {
        $request = DataSource::createGetSubmissionsRequest();
        return $this->requestSubmissions($this->rg($params), $request, $params);
    }

    public function getSubmissions($id, $params) {
        $request = DataSource::createGetSubmissionsRequest();
        return $this->requestSubmissions($this->rg($params), $request, $params);
    }

    private function requestSubmissions($request, $params) {
        DataSource::setSubmissionParams($request, $params);
        return $this->runRules('submission', $this->rg($params), $this->roots->submissions($request)->toArray());
    }

    private function runRules($datatype, $rulegroups, $data) {
        if (empty($rulegroups)) {
            return $data;
        }

        $groups = array_map(function ($rg) {
            return (new RuleGroup($rg))->getRules();
        }, $rulegroups);

        $results = [];

        foreach ($data as $d) {
            $ctx = new Context($d);

            foreach ($groups as $group) {
                foreach ($group as $rule) {
                    if ($rule->getDatatype() == $datatype) {
                        $rule->execute(DataSource::createRuleContext($rule, $ctx));
                    }
                }
            }

            $results[] = $ctx->getData();
        }
        return $results;
    }
    
    private static function createRuleContext($rule, $ctx) {
        return new \Delphinium\Blade\Classes\Data\RuleContext($rule, $ctx);
    }

    private static function createGetModulesRequest($id = null) {
        $request = new ModulesRequest(ActionType::GET, $id);
        return $request;
    }

    private static function setModuleParams($request, $params) {
        $request->setFreshData(isset($params['fresh_data']) ? (boolean) $params['fresh_data'] : true);
        $request->setIncludeContentItems(isset($params['include_content_items']) ? (boolean) $params['include_content_items'] : false);
        $request->setIncludeContentDetails(isset($params['include_content_details']) ? (boolean) $params['include_content_details'] : false);
    }

    private static function createGetAssignmentsRequest($id = null) {
        $request = new AssignmentsRequest(ActionType::GET, $id);
        return $request;
    }

    private static function createGetAssignmentGroupsRequest($id = null) {
        $request = new AssignmentGroupsRequest(ActionType::GET, false, $id);
        return $request;
    }

    private static function setAssignmentGroupParams($request, $params) {
        $request->setFresh_data(isset($params['fresh_data']) ? $params['fresh_data'] : true);
        $request->setInclude_assignments(isset($params['include_assignments']) ? $params['include_assignments'] : false);
    }

    private static function createGetSubmissionsRequest($assignment_id = null, $id = null) {
        $request = new SubmissionsRequest(ActionType::GET);
    }

    private static function setSubmissionParams($request, $params) {
        
    }

}
