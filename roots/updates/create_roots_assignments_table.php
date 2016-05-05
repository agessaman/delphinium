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

class CreateRootsAssignmentsTable extends Migration
{

    public function up()
    {
     	if ( !Schema::hasTable('delphinium_roots_assignments') )
    	{
        	Schema::create('delphinium_roots_assignments', function($table)
        	{
            	$table->engine = 'InnoDB';
                $table->integer('assignment_id');
                $table->integer('assignment_group_id');
                $table->string('name');
                $table->string('description')->nullable();
                $table->dateTime('due_at')->nullable();
                $table->dateTime('lock_at')->nullable();
                $table->dateTime('unlock_at')->nullable();
                $table->string('all_dates')->nullable();
                $table->integer('course_id');
                $table->string('html_url');
                $table->integer('points_possible');
                $table->boolean('locked_for_user');
                $table->integer('quiz_id')->nullable();
                $table->text('additional_info');//TODO: decide if we do want to implement all the other fields available in the API
                $table->timestamps();
       	 	});
       	 }
    }

    public function down()
    {
	    if ( Schema::hasTable('delphinium_roots_assignments') )
    	{
        	Schema::drop('delphinium_roots_assignments');
    	}
    }

}
