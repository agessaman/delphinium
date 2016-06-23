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

class CreteRootsQuizSubmissionsTable extends Migration
{
    public function up()
    {
     	if ( !Schema::hasTable('delphinium_roots_quiz_submissions') )
    	{
            Schema::create('delphinium_roots_quiz_submissions', function($table)
            {
            	$table->engine = 'InnoDB';
                $table->integer('quiz_submission_id')->index()->unsigned();
                $table->integer('user_id');
                $table->integer('quiz_id');
                $table->integer('submission_id');
                $table->string('validation_token', 400);
                $table->integer('quiz_version')->nullable();
                $table->integer('attempt')->nullable();
                $table->integer('extra_attempts')->nullable();
                $table->integer('attempts_left');
                $table->bigInteger('time_spent')->nullable();
                $table->integer('extra_time')->nullable();
                $table->dateTime('started_at')->nullable();
                $table->dateTime('finished_at')->nullable();
                $table->dateTime('end_at')->nullable();
                $table->string('workflow_state');
                $table->boolean('has_seen_results')->nullable();
                $table->boolean('manually_unlocked')->nullable();
                $table->boolean('overdue_and_needs_submission')->nullable();
                $table->integer('score'); 
                $table->integer('score_before_regrade')->nullable();
                $table->integer('quiz_points_possible')->nullable();
                $table->integer('kept_score')->nullable();
                $table->integer('fudge_points')->nullable();
                $table->string('html_url')->nullable();
                $table->timestamps();
            });
       	}
    }

    public function down()
    {
        if ( Schema::hasTable('delphinium_roots_quiz_submissions') )
    	{
        	Schema::drop('delphinium_roots_quiz_submissions');
    	}
    }

}
