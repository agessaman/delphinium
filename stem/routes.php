<?php
Route::post('moveItemToTop', 'Delphinium\Stem\Controllers\RestfulApi@moveItemToTop');
Route::post('saveModules', 'Delphinium\Stem\Controllers\RestfulApi@saveModules');

Route::post('addTags', 'Delphinium\Stem\Controllers\RestfulApi@addTags');
Route::post('deleteTag', 'Delphinium\Stem\Controllers\RestfulApi@deleteTag');
Route::post('toggleModulePublishedState','Delphinium\Stem\Controllers\RestfulApi@toggleModulePublishedState');
Route::post('toggleModuleItemPublishedState','Delphinium\Stem\Controllers\RestfulApi@toggleModuleItemPublishedState');

Route::get('getAvailableTags', 'Delphinium\Stem\Controllers\RestfulApi@getAvailableTags');
Route::get('getFreshData', 'Delphinium\Stem\Controllers\RestfulApi@getFreshData');
Route::get('getModuleItemTypes', 'Delphinium\Stem\Controllers\RestfulApi@getModuleItemTypes');
Route::get('getPageEditingRoles', 'Delphinium\Stem\Controllers\RestfulApi@getPageEditingRoles');
Route::get('getAssignmentGroups', 'Delphinium\Stem\Controllers\RestfulApi@getAssignmentGroups');

Route::post('addNewPage', 'Delphinium\Stem\Controllers\RestfulApi@addNewPage');
Route::post('addNewDiscussionTopic', 'Delphinium\Stem\Controllers\RestfulApi@addNewDiscussionTopic');
Route::post('addNewAssignment', 'Delphinium\Stem\Controllers\RestfulApi@addNewAssignment');
Route::post('addNewQuiz', 'Delphinium\Stem\Controllers\RestfulApi@addNewQuiz');
Route::post('uploadFile', 'Delphinium\Stem\Controllers\RestfulApi@uploadFile');
Route::post('uploadFileStepTwo', 'Delphinium\Stem\Controllers\RestfulApi@uploadFileStepTwo');
Route::post('uploadFileStepThree', 'Delphinium\Stem\Controllers\RestfulApi@uploadFileStepThree');
Route::post('addNewExternalTool', 'Delphinium\Stem\Controllers\RestfulApi@addNewExternalTool');

Route::post('deleteModule', 'Delphinium\Stem\Controllers\RestfulApi@deleteModule');
Route::post('deleteModuleItem', 'Delphinium\Stem\Controllers\RestfulApi@deleteModuleItem');

Route::post('updateModulePrerequisites', 'Delphinium\Stem\Controllers\RestfulApi@updateModulePrereqs');
Route::post('updateModuleName', 'Delphinium\Stem\Controllers\RestfulApi@updateModuleName');
Route::post('updateModuleItemCompletionRequirement', 'Delphinium\Stem\Controllers\RestfulApi@updateModuleItemCompletionRequirement');