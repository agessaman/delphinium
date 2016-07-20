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

namespace Delphinium\Roots\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class CreateRootsModuleItemsTable extends Migration
{

    public function up()
    {
     	if ( !Schema::hasTable('delphinium_roots_module_items') )
    	{
            Schema::create('delphinium_roots_module_items', function($table)
            {
            $table->engine = 'InnoDB';
            $table->bigIncrements('id');
            //we have to create our own id, because this will be coming from canvas, and we don't want it to be auto-incrementing
            $table->integer('module_item_id');//make this the primary key
            $table->integer('course_id');
            $table->integer('module_id');
            $table->integer('position');
            $table->string('title');
            $table->integer('indent');
            $table->string('type');
            $table->boolean('published')->nullable();
            $table->integer('content_id');
            $table->string('html_url');
            $table->string('url');
            $table->string('page_url');
            $table->string('external_url');
            $table->boolean('new_tab');
            $table->string('completion_requirement');//a json representation of an array
            $table->timestamps();
            });
       	 }
    }

    public function down()
    {
	    if ( Schema::hasTable('delphinium_roots_module_items') )
    	{
        	Schema::drop('delphinium_roots_module_items');
    	}
    }

}
