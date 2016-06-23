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

namespace Delphinium\Roots\Models;

use Model;

class Module extends Model
{
    use \October\Rain\Database\Traits\Validation;

    public $table = 'delphinium_roots_modules';

    protected $primaryKey = 'module_id';
    public $incrementing = false;//since we are using our own custom primary key (and it's not auto-incrementing)
    //we need to set this incrementing property to false
    //TODO: decide which items will be "fillable" and which ones will not
    protected $fillable = array('module_id','course_id','name', 'position', 'unlock_at', 'email',
        'require_sequential_progress', 'publish_final_grade', 'prerequisite_module_ids', 'items_count',
        'published','state', 'items');
    
    //Validation 
    public $rules = [
    	'module_id'=>'required'
    ];


    public $hasMany = [
            'module_items' => ['Delphinium\Roots\Models\ModuleItem', 
            'foreignKey' => 'module_item_id',
            'delete'=>'true']
    ];
    
}