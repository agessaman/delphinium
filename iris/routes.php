<?php
Route::post('moveItemToTop', 'Delphinium\Iris\Controllers\RestApi@moveItemToTop');

Route::post('saveModules', 'Delphinium\Iris\Controllers\RestApi@saveModules');

Route::post('addTags', 'Delphinium\Iris\Controllers\RestApi@addTags');
Route::post('deleteTag', 'Delphinium\Iris\Controllers\RestApi@deleteTag');
Route::post('toggleModulePublishedState','Delphinium\Iris\Controllers\RestApi@toggleModulePublishedState');
Route::post('toggleModuleItemPublishedState','Delphinium\Iris\Controllers\RestApi@toggleModuleItemPublishedState');

Route::get('getAvailableTags', 'Delphinium\Iris\Controllers\RestApi@getAvailableTags');
Route::get('getModuleStates', 'Delphinium\Iris\Controllers\RestApi@getModuleStates');
Route::get('getStudentSubmissions', 'Delphinium\Iris\Controllers\RestApi@getStudentSubmissions');
Route::get('getFreshData', 'Delphinium\Iris\Controllers\RestApi@getFreshData');
Route::get('getModuleItemTypes', 'Delphinium\Iris\Controllers\RestApi@getModuleItemTypes');
Route::get('getPageEditingRoles', 'Delphinium\Iris\Controllers\RestApi@getPageEditingRoles');
Route::get('getAssignmentGroups', 'Delphinium\Iris\Controllers\RestApi@getAssignmentGroups');

Route::post('addNewPage', 'Delphinium\Iris\Controllers\RestApi@addNewPage');
Route::post('addNewDiscussionTopic', 'Delphinium\Iris\Controllers\RestApi@addNewDiscussionTopic');
Route::post('addNewAssignment', 'Delphinium\Iris\Controllers\RestApi@addNewAssignment');
Route::post('addNewQuiz', 'Delphinium\Iris\Controllers\RestApi@addNewQuiz');
Route::post('uploadFile', 'Delphinium\Iris\Controllers\RestApi@uploadFile');
Route::post('uploadFileStepTwo', 'Delphinium\Iris\Controllers\RestApi@uploadFileStepTwo');
Route::post('uploadFileStepThree', 'Delphinium\Iris\Controllers\RestApi@uploadFileStepThree');
Route::post('addNewExternalTool', 'Delphinium\Iris\Controllers\RestApi@addNewExternalTool');

Route::post('deleteModule', 'Delphinium\Iris\Controllers\RestApi@deleteModule');
Route::post('deleteModuleItem', 'Delphinium\Iris\Controllers\RestApi@deleteModuleItem');
