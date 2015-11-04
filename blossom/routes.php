<?php

Route::get('getMilestoneClearanceInfo', 'Delphinium\Blossom\Controllers\RestfulApi@getMilestoneClearanceInfo');
Route::get('getStudentsScores', 'Delphinium\Blossom\Controllers\RestfulApi@getStudentsScores');
Route::get('getStudentGradebookData', 'Delphinium\Blossom\Controllers\RestfulApi@getStudentGradebookData');
Route::get('getStudentChartData', 'Delphinium\Blossom\Controllers\RestfulApi@getStudentChartData');