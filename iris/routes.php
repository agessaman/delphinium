<?php
Route::post('moveItemToTop', 'Delphinium\Iris\Controllers\RestApi@moveItemToTop');

Route::post('saveModules', 'Delphinium\Iris\Controllers\RestApi@saveModules');

Route::post('addTags', 'Delphinium\Iris\Controllers\RestApi@addTags');
Route::post('deleteTag', 'Delphinium\Iris\Controllers\RestApi@deleteTag');
Route::get('getAvailableTags', 'Delphinium\Iris\Controllers\RestApi@getAvailableTags');

Route::get('getModuleStates', 'Delphinium\Iris\Controllers\RestApi@getModuleStates');
Route::get('getStudentSubmissions', 'Delphinium\Iris\Controllers\RestApi@getStudentSubmissions');