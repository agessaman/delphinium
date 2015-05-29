<?php

Route::get('core/getContentByType', 'Delphinium\Core\Controllers\RestfulApi@getContentByType');


Route::post('core/addModule', 'Delphinium\Core\Controllers\RestfulApi@addModule');
Route::post('core/addModuleItem', 'Delphinium\Core\Controllers\RestfulApi@addModuleItem');
Route::post('core/updateModule', 'Delphinium\Core\Controllers\RestfulApi@updateModule');
Route::post('core/updateModuleItem', 'Delphinium\Core\Controllers\RestfulApi@updateModuleItem');