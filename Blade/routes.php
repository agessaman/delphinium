<?php

use Delphinium\Blade\Classes\Data\DataSource;
use Delphinium\Dev\Models\Configuration;

$v1prefix = 'blade/api/v1/';

function init($course_id) {
    if (!isset($_SESSION)) {
        session_start();
    }

    $prettyprint = \Input::get('prettyprint');
    $source = new DataSource(isset($prettyprint) ? $prettyprint : false);

    $config = Configuration::where('Enabled', '=', '1')->first();

    $_SESSION['userID'] = $config->User_id;
    $_SESSION['userToken'] = \Crypt::encrypt($config->Token);
    $_SESSION['courseID'] = $course_id;
    $_SESSION['domain'] = $config->Domain;
    $_SESSION['lms'] = $config->Lms;
    
    return $source;
}

Route::get($v1prefix . 'courses/{course_id}/assignments', function($course_id) {
    $source = init($course_id);
    return $source->getAssignments(\Input::all());
});

//Route::get($v1prefix . 'courses/{course_id}/assignments/{assignment_id}', function($course_id, $assignment_id) {
//    $source = init($course_id);
//    return $source->getAssignments($assignment_id, \Input::all());
//});

Route::get($v1prefix . 'courses/{course_id}/modules', function($course_id) {
    $source = init($course_id);
    return $source->getModules(\Input::all());
});

//Route::get($v1prefix . 'courses/{course_id}/modules/{module_id}', function($course_id, $module_id) {
//    $source = init($course_id);
//    return $source->getModule($module_id, \Input::all());
//});

Route::get($v1prefix . 'courses/{course_id}/assignment_groups', function($course_id) {
    $source = init($course_id);
    return $source->getAssignmentGroups(\Input::all());
});

Route::get($v1prefix . 'courses/{course_id}/assignments/{assignment_id}/submissions', function($course_id, $assignment_id){
    $source = init($course_id);
    return $source->getSubmissions($assignment_id, \Input::all());
});

Route::get($v1prefix . 'courses/{course_id}/analytics/assignments', function($course_id){
    $source = init($course_id);
    return $source->getUserAssignmentAnalytics(\Input::all());
});
