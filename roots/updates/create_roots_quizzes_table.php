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

class CreateRootsQuizzesTable extends Migration
{

    public function up()
    {
     	if ( !Schema::hasTable('delphinium_roots_quizzes') )
    	{
            Schema::create('delphinium_roots_quizzes', function($table)
            {
                $table->engine = 'InnoDB';
                $table->integer('quiz_id')->unsigned()->index();
                $table->integer('course_id');
                $table->string('title');
                $table->string('description');
                $table->string('html_url');
                $table->string('quiz_type');
                $table->integer('assignment_group_id');
                $table->integer('time_limit');
                $table->integer('question_count');
                $table->integer('points_possible');
                $table->dateTime('due_at');
                $table->dateTime('lock_at');
                $table->dateTime('unlock_at');
                $table->boolean('published');
                $table->boolean('locked_for_user');
                $table->string('scoring_policy');
                $table->integer('allowed_attempts');
                $table->timestamps();
            });
       	 }
    }

    public function down()
    {
        //need to drop foreign table first.
        if( Schema::hasTable('delphinium_roots_quiz_questions') )
    	{
            Schema::dropIfExists('delphinium_roots_quiz_questions');
        }
        
        if ( Schema::hasTable('delphinium_roots_quizzes') )
    	{
            Schema::drop('delphinium_roots_quizzes');
    	}
    }

}
