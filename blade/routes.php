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

// params:
//      student_ids: comma seperated list of student-ids to get submissions for, "all" or unspecified for all
//      assignment_ids: comma sperated list of assignments-ids, "all" or unspecified for all
//      include_tags
Route::get($v1prefix . 'courses/{course_id}/students/submissions', function($course_id) {
    $source = init($course_id);
    return $source->getMultipleSubmissions(\Input::all());
});