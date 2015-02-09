<?php namespace Delphinium\Core;

use Delphinium\Core\RequestObjects\SubmissionsRequest;
use Delphinium\Core\RequestObjects\ModulesRequest;
use Delphinium\Core\RequestObjects\AssignmentsRequest;

class Roots
{
    /*
     * Public Functions
     */
    public function submissions(SubmissionsRequest $request)
    {
        return $this->parseSubmissions($request);
    }
    
    public function assignments(AssignmentsRequest $request)
    {
        return $this->parseAssignments($request);
    }
    
    public function modules(ModulesRequest $request)
    {
        return $this->parseModules($request);
    }
    
    
    
    /*
     * Private Functions
     */
    private function parseSubmissions(SubmissionsRequest $request)
    {
        $url = $_SESSION['userID'];
    }
    
    private function parseAssignments(AssignmentsRequest $request)
    {
        echo "in assignments function from roots";
    }
    
    private function parseModules(ModulesRequest $request)
    {
        echo "in modules function from roots";
    }
}

