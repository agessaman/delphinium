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