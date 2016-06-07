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

class CreateRootsQuizQuestionsTable extends Migration
{

    public function up()
    {
        if ( !Schema::hasTable('delphinium_roots_quiz_questions') )
    	{
            Schema::create('delphinium_roots_quiz_questions', function($table)
            {
                $table->engine = 'InnoDB';
                $table->integer('question_id')->unsigned();
                $table->integer('quiz_id')->unsigned()->index();
                $table->integer('position')->unsigned();
                $table->integer('points_possible')->unsigned();
                $table->string('name');
                $table->string('type');
                $table->longText('text');
                $table->longText('correct_comments');
                $table->longText('incorrect_comments');
                $table->longText('neutral_comments');
                $table->longText('answers');
                $table->timestamps();
                

                $table->foreign('quiz_id')->references('quiz_id')->on('delphinium_roots_quizzes');

            });
        }
    }

    public function down()
    {
        if ( Schema::hasTable('delphinium_roots_quiz_questions') )
    	{
            Schema::dropIfExists('delphinium_roots_quiz_questions');
        }
    }

}
