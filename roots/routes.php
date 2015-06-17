<?php

Route::get('roots/getContentByType', 'Delphinium\Roots\Controllers\RestfulApi@getContentByType');


Route::post('roots/addModule', 'Delphinium\Roots\Controllers\RestfulApi@addModule');
Route::post('roots/addModuleItem', 'Delphinium\Roots\Controllers\RestfulApi@addModuleItem');
Route::post('roots/updateModule', 'Delphinium\Roots\Controllers\RestfulApi@updateModule');
Route::post('roots/updateModuleItem', 'Delphinium\Roots\Controllers\RestfulApi@updateModuleItem');