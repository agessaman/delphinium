<?php


Route::post('updateModule', 'Delphinium\Raspberry\Controllers\RestApi@updateModule');
Route::post('saveModules', 'Delphinium\Raspberry\Controllers\RestApi@saveModules');
Route::get('getModuleItems', 'Delphinium\Raspberry\Controllers\RestApi@getModuleItems');

Route::get('getTags', 'Delphinium\Raspberry\Controllers\RestApi@getTags');
Route::post('addTags', 'Delphinium\Raspberry\Controllers\RestApi@addTags');
Route::post('deleteTag', 'Delphinium\Raspberry\Controllers\RestApi@deleteTag');
Route::get('getAvailableTags', 'Delphinium\Raspberry\Controllers\RestApi@getAvailableTags');

Route::get('getModuleStates', 'Delphinium\Raspberry\Controllers\RestApi@getModuleStates');
Route::get('getStudentSubmissions', 'Delphinium\Raspberry\Controllers\RestApi@getStudentSubmissions');

Route::get('test', 'Delphinium\Raspberry\Controllers\RestApi@index');