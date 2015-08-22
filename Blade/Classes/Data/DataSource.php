<?php

namespace Delphinium\Blade\Classes\Data;

use Delphinium\Roots\Roots;
use Delphinium\Roots\Requestobjects\AssignmentsRequest;
use Delphinium\Roots\Requestobjects\ModulesRequest;
use Delphinium\Roots\Requestobjects\AssignmentGroupsRequest;
use Delphinium\Roots\Requestobjects\SubmissionsRequest;
use Delphinium\Roots\Enums\ActionType;
use Delphinium\Blade\Classes\Rules\Context;
use Delphinium\Blade\Classes\Rules\RuleGroup;
use Delphinium\Blade\Classes\Rules\Rule;

/**
 *
 * @author Daniel Clark
 */
class DataSource implements IDataSource {

    private $prettyprint;

    // these are the different types you can define when creating a rule
    const ASSIGNMENT = 'assignment';
    const MODULE = 'module';
    const SUBMISSION = 'submission';
    const ASSN_GROUP = 'assignment_group';
    const ASSN_ANALYTICS = 'assignment_analytics';

    public function __construct($prettyprint = false) {
        $this->prettyprint = $prettyprint;
        $this->roots = new Roots();
    }

    // gets rulegroups parameter
    private function rg($params) {
        return (isset($params['rg'])) ? explode(',', $params['rg']) : [];
    }

    public function getAssignments($params) {
        $request = DataSource::createGetAssignmentsRequest();
        DataSource::setAssignmentParams($request, $params);
        return $this->runRules(DataSource::ASSIGNMENT, $this->rg($params), $this->roots->assignments($request));
        //var_dump($this->roots->assignments($request));
    }

//    public function getAssignment($id, $params) {
//        $request = DataSource::createGetAssignmentsRequest($id);
//        DataSource::setAssignmentParams($request, $params);
//        return $this->runRules('assignment', $this->rg($params), $this->roots->assignments($request)->toArray());
//    }

    public function getModules($params) {
        $request = DataSource::createGetModulesRequest();
        DataSource::setModuleParams($request, $params);
        return $this->runRules(DataSource::MODULE, $this->rg($params), $this->roots->modules($request)->toArray());
    }

    //TODO: fix so this gets one module only
//    public function getModule($id, $params) {
//        $request = DataSource::createGetModulesRequest($id);
//        DataSource::setModuleParams($request, $params);
//        return $this->runRules('module', $this->rg($params), $this->roots->modules($request)->toArray());
//    }

    public function getAssignmentGroups($params) {
        $request = DataSource::createGetAssignmentGroupsRequest();
        DataSource::setAssignmentGroupParams($request, $params);
        return $this->runRules(DataSource::ASSN_GROUP, $this->rg($params), $this->roots->assignmentGroups($request)->toArray());
    }

//    public function getAssignmentGroup($id, $params) {
//        $request = DataSource::createGetAssignmentGroupsRequest($id);
//        return $this->requestAssignmentGroups($this->rg($params), $request, $params);
//    }
//    public function getAssignmentSubmissions($assignment_id, $params) {
//        $request = DataSource::createGetSubmissionsRequest();
//        return $this->requestSubmissions($this->rg($params), $request, $params);
//    }
//    public function getAssignmentSubmission($assignment_id, $id, $params) {
//        $request = DataSource::createGetSubmissionsRequest();
//        return $this->requestSubmissions($this->rg($params), $request, $params);
//    }

    public function getSubmissions($assignment_id, $params) {
        $request = DataSource::createGetSubmissionsRequest($assignment_id);
        DataSource::setSubmissionParams($request, $params);
        return $this->runRules(DataSource::SUBMISSION, $this->rg($params), $this->roots->submissions($request)->toArray());
    }

    public function getMultipleSubmissions($params) {
        $all_students = !isset($params['student_ids']) || $params['student_ids'] == 'all';
        $all_assignments = !isset($params['assignment_ids']) || $params['assignment_ids'] == 'all';
        $student_ids = $all_students ? [] : explode(',', $params['student_ids']);
        $assignment_ids = $all_assignments ? [] : explode(',', $params['assignment_ids']);
        $multiple_students = $all_students || count($student_ids) > 1;
        $multiple_assignments = $all_assignments || count($assignment_ids) > 1;

        $request = new SubmissionsRequest(ActionType::GET, $student_ids, $all_students, $assignment_ids, $all_assignments, $multiple_students, $multiple_assignments);

        DataSource::setSubmissionParams($request, $params);
        $results = $this->roots->submissions($request);
        return $this->runRules(DataSource::SUBMISSION, $this->rg($params), $results);
    }

    public function getUserAssignmentAnalytics($params) {
        $include_tags = isset($params['include_tags']) ? (boolean) $params['include_tags'] : false;
        $results = $this->roots->getAnalyticsStudentAssignmentData($include_tags);
        $data = [];
        foreach ($results as $item) {
            $data[] = (array) $item;
        }
        return $this->runRules(DataSource::ASSN_ANALYTICS, $this->rg($params), $data);
    }

    private function processResults($data) {
        if ($this->prettyprint) {
            var_dump($data);
            return null;
        }

        return $data;
    }

    private function getRules($rulegroups) {
        $groups = array_map(function ($name) {
            return new RuleGroup($name);
        }, $rulegroups);

        $rules = [];
        foreach ($groups as $rg) {
            foreach ($rg->getRules() as $rule) {
                $rules[$rule->getId()] = $rule; // assigning to dictionary to avoid running a rule more than once
            }
        }

        return array_values($rules);
    }

    // TODO: don't run rules on excluded groups if all whitelist rules have finished
    private function runRules($datatype, $rulegroups, $data) {
        if (empty($rulegroups)) {
            return $this->processResults($data);
        }

        $rules = $this->getRules($rulegroups);
        $this->sortRules($rules);

        $results = [];
        $extctx = new ExternalContext();

        foreach ($data as $d) {
            $ctx = new Context($d);
            if (isset($rules[0]) && $rules[0]->isWhitelistRule()) {
                $ctx->setExcluded(true);
            }

            foreach ($rules as $rule) {
                if ($rule->getDatatype() == $datatype) {
                    $rule->execute($extctx->wrap(new RuleContext($rule, $ctx)));
                }
            }

            $data = $ctx->getData();
            if ($data != null) {
                $results[] = $data;
            }
        }

        $results = array_merge($extctx->getGroupData(), $results);
        return $this->processResults($results);
    }

    // TODO: fix rule sorting
    private function sortRules(&$rules) {
        usort($rules, function(Rule $rule1, Rule $rule2) {
            if ($rule1->isWhitelistRule() && !$rule2->isWhitelistRule()) {
                return -1;
            }

            if (!$rule1->isWhitelistRule() && $rule2->isWhitelistRule()) {
                return 1;
            }

            return 0;
        });

        //var_dump($rules);
        return $rules;
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

    private static function setAssignmentParams($request, $params) {
        $request->setFresh_data(isset($params['fresh_data']) ? (boolean) $params['fresh_data'] : true);
        $request->setIncludeTags(isset($params['include_tags']) ? (boolean) $params['include_tags'] : false);
    }

    private static function createGetAssignmentGroupsRequest($id = null) {
        $request = new AssignmentGroupsRequest(ActionType::GET, false, $id);
        return $request;
    }

    private static function setAssignmentGroupParams($request, $params) {
        $request->setFresh_data(isset($params['fresh_data']) ? $params['fresh_data'] : true);
        $request->setInclude_assignments(isset($params['include_assignments']) ? $params['include_assignments'] : false);
    }

    private static function createGetSubmissionsRequest($assignment_id) {
        $request = new SubmissionsRequest(ActionType::GET, null, null, [$assignment_id]);
        return $request;
    }

    private static function setSubmissionParams($request, $params) {
        $request->setIncludeTags(isset($params['include_tags']) ? (boolean) $params['include_tags'] : false);
    }

}
