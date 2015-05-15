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

Route::post('addModule', 'Delphinium\Iris\Controllers\RestApi@addModule');
Route::post('addModuleItem', 'Delphinium\Iris\Controllers\RestApi@addModuleItem');
Route::post('updateModule', 'Delphinium\Iris\Controllers\RestApi@updateModule');
Route::post('updateModuleItem', 'Delphinium\Iris\Controllers\RestApi@updateModuleItem');

Route::post('deleteModule', 'Delphinium\Iris\Controllers\RestApi@deleteModule');
Route::post('deleteModuleItem', 'Delphinium\Iris\Controllers\RestApi@deleteModuleItem');
