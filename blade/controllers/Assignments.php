<?php
namespace Delphinium\Blade\Controllers;

use Backend\Classes\Controller;
use Delphinium\Core\Roots;
use Delphinium\Core\RequestObjects\AssignmentsRequest;
use Delphinium\Core\Enums\CommonEnums\ActionType;

/**
 *
 * @author Daniel Clark
 */
class Data extends Controller {
    
    private $roots;
    
    public function __construct() {
        $this->roots = new Roots();
    }
    
    function prepare() {
        if (!isset($_SESSION)) session_start();
    }
    
    function assignments() {
        prepare();
        $request = new AssignmentsRequest(ActionType::GET);
        $response = $this->roots->assignments($request);
        return $response;
    }
    
    function get() {
        
    }
}
