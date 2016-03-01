<?php

Route::get('getMilestoneClearanceInfo', 'Delphinium\Blossom\Controllers\RestfulApi@getMilestoneClearanceInfo');
Route::get('getStudentsScores', 'Delphinium\Blossom\Controllers\RestfulApi@getStudentsScores');
Route::get('getStudentGradebookData', 'Delphinium\Blossom\Controllers\RestfulApi@getStudentGradebookData');
Route::get('getStudentChartData', 'Delphinium\Blossom\Controllers\RestfulApi@getStudentChartData');
Route::get('gradebook/getAllStudentSubmissions', 'Delphinium\Blossom\Controllers\RestfulApi@getAllStudentSubmissions');
Route::get('gradebook/getBottomTableData', 'Delphinium\Blossom\Controllers\RestfulApi@getAllUserClearedMilestoneData');
Route::get('gradebook/getSetOfUsersMilestoneInfo', 'Delphinium\Blossom\Controllers\RestfulApi@getSetOfUsersMilestoneInfo');
Route::get('gradebook/getSetOfUsersTotalScores', 'Delphinium\Blossom\Controllers\RestfulApi@getSetOfUsersTotalScores');